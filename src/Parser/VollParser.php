<?php

namespace App\Parser;

use App\Doctrine\Saver;
use App\Downloader\DownloaderHtml;
use App\Entity\ExtractedData;
use App\Manager\PageProcessorManager;
use App\Pool\Pool;
use App\Utils\WriterXlsx;
use Symfony\Component\DomCrawler\Crawler;

class VollParser implements ParserInterface
{
    public const CODE = 'voll';

    public const BASE_HREF = 'https://voll.ru';

    const OUTPUT_FILENAME = 'voll-data.xlsx';

    private array $processors = [];

    private array $startUrls = [
        'https://voll.ru/product/',
        //'https://voll.ru/product/rezbonareznye-kluppy/elektricheskie-kluppy/elektricheskiy-klupp-voll-v-matic-b2-s-naborom-golovok-1-2-quot-2-quot-2-10050/',
        //'https://voll.ru/product/rezbonareznye-kluppy/elektricheskie-kluppy/elektricheskiy-klupp-voll-v-matic-b1-s-naborom-golovok-1-2-quot-1-1-4-quot-2-10040/',
        // --> test
        // <-- test
    ];

    private array $skipUrls = [
        'https://voll.ru/product/novinki/',
        'https://voll.ru/product/rasprodazha/',
        //'',
    ];


    public function __construct(
        private DownloaderHtml       $downloader,
        private Pool                 $pool,
        private PageProcessorManager $processorManager,
        //private Saver                $saver,
        private WriterXlsx           $writer,
    )
    {
        // добавить в ссылки которые надо пропустить (нестандартные или где какие-то проблемы)
        $pool->skip($this->skipUrls);

        // добавить в очередь начальные ссылки на разделы сайта
        foreach ($this->startUrls as $url) {
            $pool->add($url);
        }

        // отфильтровать только процессоры для этого парсера
        $processors = $this->processorManager->get(self::CODE);
        foreach ($processors as $processor) {
            $processor->setParser($this);
            $this->processors[] = $processor;
        }

        // todo: setup ...
    }

    public function parse(): void
    {
        $i = 0;

        while ($this->pool->length() !== 0) {
            $i++;
            $url = $this->pool->get();
            print_r(sprintf("[%d из %d] %s\n", $i, $this->pool->length(), $url));

            $html = $this->downloader->download($url);

            if (empty($html)) {
                throw new \Exception(sprintf('Ошибка - пустой HTML-файл, %s', $url));
            }

            $crawler = new Crawler($html);

            foreach ($this->processors as $processor) {
                if ($processor->isSupport($crawler)) {
                    try {
                        $data = $processor->process($url, $crawler);
                        //dump($processor->getType() . ' - OK');
                    } catch (\Exception $exception) {
                        dump($exception);
                        dump($processor->getType() . ' - ERROR');
                        die();
                        //$extractedData = (new ExtractedData())->setUrl($url)->setStatus(ExtractedData::STATUS_ERROR);
                        //$this->saver->save($extractedData, true);
                        //$this->saver->detach($extractedData);
                        continue;
                    }

                    /*
                    if($processor->getType() === 'category') {
                        dump($data);
                        die();
                    }
                    */
                }
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
