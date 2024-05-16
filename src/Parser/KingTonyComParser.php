<?php

namespace App\Parser;

use App\Doctrine\Saver;
use App\Downloader\DownloaderHtml;
use App\Entity\ExtractedData;
use App\Manager\PageProcessorManager;
use App\Pool\Pool;
use App\Repository\CategoryRepository;
use App\Repository\ExtractedDataRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductRepository;
use App\Utils\WriterXlsx;
use Symfony\Component\DomCrawler\Crawler;

class KingTonyComParser implements ParserInterface
{
    public const CODE = 'kingtony.com';

    private const BATCH_SIZE = 20;

    public const BASE_HREF = 'https://www.kingtony.com';

    private array $skipUrls = [
        'https://www.kingtony.com/',
        'https://www.kingtony.com/index.php',
        'product_new.php',
        'https://www.kingtony.com/contact.php',
        'https://www.kingtony.com/support.php',
        'https://www.kingtony.com/profile.php',
        'https://www.kingtony.com/register.php',
        'https://www.kingtony.com/Register.php',
        'https://www.kingtony.com/members.php',
        'https://www.kingtony.com/product_2539MR-AM_39-PC-Combination-Socket-Set.php',
        'https://www.kingtony.com/product_2539MRV04-39-PC-Ratchet-Screwdriver-Set.php',
        'https://www.kingtony.com/product_20109MW-9-PC-Color-Coded-Extra-Long-Arm-Ball-End-Hex-Key-Set',
    ];

    private array $startUrls = [
        //'https://www.kingtony.com/product.php',
        'https://www.kingtony.com/sitemap.xml',

        //'https://www.kingtony.com/catalogs/Promotional-Merchandise/',
        //'https://www.kingtony.com/product/Long-Head-Double-End-Power-Bit-1317', // много вариантов и общая фотка
        //'https://www.kingtony.com/product/12-Point-Inch-Standard-Socket-6330S', // recommended
        //'https://www.kingtony.com/productlist/Open-End-Wrench-Set/Open-End-Wrench-1900', // variants
        //'https://www.kingtony.com/productlist/Tool-Trolley-Set-Tray-Foam/173-PC-Tool-Trolley-Set-932-000MR-B', // parts
        //'https://www.kingtony.com/productlist/KINGTONY-Rock/51-PC-6-Point-Socket-Wrench-Set-2551MR', // video
    ];

    private array $processors = [];

    public function __construct(
        private DownloaderHtml             $downloader,
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

            $crawler = new Crawler($html, $url, self::BASE_HREF);
            //$crawler = new Crawler($html, str_replace(self::BASE_HREF, '', $url), self::BASE_HREF);

            foreach ($this->processors as $processor) {
                if ($processor->isSupport($crawler)) {
                    print_r(sprintf("[%d из %d] %s\n", $i, $this->pool->length(), $url));

                    try {
                        $data = $processor->process($url, $crawler);
                    } catch (\Exception $exception) {
                        dump($exception);
                        dump($processor->getType() . ' - ERROR');
                        die();
                        //$extractedData = (new ExtractedData())->setUrl($url)->setStatus(ExtractedData::STATUS_ERROR);
                        //$this->saver->save($extractedData, true);
                        //$this->saver->detach($extractedData);
                        continue;
                    }
                }
                //dump($data);
                //die();
            }

            unset($html, $crawler, $data);
        }
    }

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
