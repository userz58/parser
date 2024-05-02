<?php

namespace App\Parser;

use App\Doctrine\Saver;
use App\Downloader\DownloaderChrome;
use App\Entity\ExtractedData;
use App\Manager\PageProcessorManager;
use App\Pool\Pool;
use App\Repository\CategoryRepository;
use App\Repository\ExtractedDataRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductRepository;
use App\Utils\WriterXlsx;
use Symfony\Component\DomCrawler\Crawler;

class TssParser implements ParserInterface
{
    public const CODE = 'tss';

    private const BATCH_SIZE = 20;

    public const BASE_HREF = 'https://tss.ru';

    private array $skipUrls = [
        'https://tss.ru/catalog/stroitelnoe_oborudovanie/portativnye_vibratory/gibkiy_val_tss_vvn_1_5_25_sh_shestigrannik_203736/',
        'https://tss.ru/catalog/elektrostantsii/dizelnye_elektrostantsii/tss_premium/dizelnyy_generator_tss_ad_100s_t400_1rkm9_045665/',
        'https://tss.ru/catalog/elektrostantsii/dizelnye_elektrostantsii/tss_premium/dizelnyy_generator_tss_ad_450s_t400_1rkm17_032738/',
        'https://tss.ru/catalog/elektrostantsii/dizelnye_elektrostantsii/tss_premium/dizelnyy_generator_tss_ad_300s_t400_1rkm17_032665/',
        'https://tss.ru/catalog/elektrostantsii/dizelnye_elektrostantsii/tss_premium/dizelnyy_generator_tss_ad_550s_t400_1rkm17_032775/',
        'https://tss.ru/catalog/zapchasti/zapasnye_chasti_dlya_stroitelnogo_oborudovaniya/zapasnye_chasti_dlya_vibroplit/seriya_tss_wp/tss_wp60tl_th_l/fiksator_rukoyatki_m10_021742/',
        'https://tss.ru/catalog/zapchasti/zapasnye_chasti_dlya_dizelnykh_dvigateley_portativnykh_elektrostantsiy/zapchasti_kipor/zapchasti_dlya_kipor_km376ag_daihatsu/datchik_temperatury_ozh_dlya_elad16_19_radiator_fan_controlling_temperature_switch_km376qc_1008009a_/',
        //'https://tss.ru/portable-generators/',
    ];

    private array $startUrls = [
        //'https://www.tss.ru/catalog/elektrostantsii/svarochnye_elektrostantsii/odnopostovye_i_dvukhpostovye_svarochnye_agregaty/dvukhpostovoy_dizelnyy_svarochnyy_generator_tss_dual_dwg_500_039736/',
        'https://tss.ru/',
        'https://tss.ru/catalog/stroitelnoe_oborudovanie/',
        'https://tss.ru/catalog/elektrostantsii/',
        'https://tss.ru/catalog/blok_konteynery/',
        'https://tss.ru/catalog/svarochnoe_oborudovanie/',
        'https://tss.ru/catalog/dvigateli/',
        'https://tss.ru/catalog/sinkhronnye_generatory/',
        'https://tss.ru/catalog/materialy_i_komplektuyushchie/',
        'https://tss.ru/catalog/zapchasti/',
        //'https://tss.ru/portable-generators/', // это посадочная страница
    ];

    private array $processors = [];

    public function __construct(
        private DownloaderChrome           $downloader,
        private Pool                       $pool,
        private PageProcessorManager       $processorManager,
        private ExtractedDataRepository    $extractedDataRepository,
        private ProductAttributeRepository $productAttributeRepository,
        private ProductRepository          $productRepository,
        private CategoryRepository         $categoryRepository,
        private Saver                      $saver,
        private WriterXlsx                 $writer,
    )
    {
        // добавить в ссылки которые надо пропустить (нестандартные или где какие-то проблемы)
        $pool->skip($this->skipUrls);

        // добавить в очередь начальные ссылки на разделы сайта
        foreach ($this->startUrls as $url) {
            $pool->insert($url);
        }

        // отфильтровать только процессоры для этого парсера
        $processors = $this->processorManager->get(self::CODE);
        foreach ($processors as $processor) {
            $processor->setParser($this);
            $this->processors[] = $processor;
        }
    }

    public function parse(): void
    {
        $i = 0;
        while ($this->pool->length() !== 0) {
            $i++;
            $url = $this->pool->get();

            if ($this->extractedDataRepository->count(['url' => $url]) > 0) {
                print_r(sprintf("сохранён %s\n", $url));
                continue;
            }

            $html = $this->downloader->download($url);

            if (empty($html)) {
                throw new \Exception(sprintf('Ошибка - пустой HTML-файл, %s', $url));
            }

            $crawler = new Crawler($html);
            foreach ($this->processors as $processor) {
                if ($processor->isSupport($crawler)) {
                    print_r(sprintf("[%d из %d] %s\n", $i, $this->pool->length(), $url));

                    try {
                        $data = $processor->process($url, $crawler);
                    } catch (\Exception $exception) {
                        die();
                        $extractedData = (new ExtractedData())->setUrl($url)->setStatus(ExtractedData::STATUS_ERROR);
                        $this->saver->save($extractedData, true);
                        $this->saver->detach($extractedData);

                        continue;
                    }
                }
            }

            unset($html, $crawler, $data);
        }
    }

    /*
        public function createProducts(): void
        {
            $i = 0;

            // обойти все скачанные ссылки
            $iterator = $this->extractedDataRepository->iterateAll();
            foreach ($iterator as $entity) {
                $i++;
                //dump($entity);
                $data = $entity->getData();

                //--> todo: сохранить все свойства
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    if (0 == $this->productAttributeRepository->count(['name' => $key])) {
                        $this->saver->persist(new ProductAttribute($key));
                    }
                }
                //<-- todo: сохранить все свойства

                //--> todo: найти товар по артикулу, если нет - создать
                $productSku = $data['Артикул'];
                if (null === $product = $this->productRepository->findOneBySku($productSku)) {
                    // todo: установить свойства для товара
                    $product = new Product($productSku, $data);
                }

                // todo: добавить категорию
                $categoryHash = $data['Категория (HASH)'];
                //$categoryName = $data['Категория'];
                $category = $this->categoryRepository->findOneBy($categoryHash);
                $category->addProduct($product);

                $this->saver->persist($product);
                //$this->saver->persist($category);

                if (($i % self::BATCH_SIZE) === 0) {
                    $this->saver->flush(true);
                }

                $this->saver->detach($entity);
            }

            $this->saver->flush(true);
        }
    */
    public function getCode(): string
    {
        return self::CODE;
    }

    public function getBaseHref(): string
    {
        return self::BASE_HREF;
    }

    public function getPool(): Pool
    {
        return $this->pool;
    }

    public function getWriter(): WriterXlsx
    {
        return $this->writer;
    }
}
