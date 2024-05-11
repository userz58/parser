<?php

namespace App\Parser;

use App\Downloader\DownloaderHtml;
use App\Manager\PageProcessorManager;
use App\Pool\Pool;
use App\Utils\WriterXlsx;
use Symfony\Component\DomCrawler\Crawler;

class RothenbergerParser implements ParserInterface
{
    public const CODE = 'rothenberger';

    public const BASE_HREF = 'https://rothenberger.ru';

    const OUTPUT_FILENAME = 'rothenberger-data.xlsx';

    private array $processors = [];

    private array $startUrls = [
        'https://www.rothenberger.ru/catalog/',
        'https://www.rothenberger.ru/articles/',
        // --> test
        // <-- test
    ];

    private array $skipUrls = [
        //'https://www.rothenberger.ru/catalog/akkumulyatornaya_sistema_cas/',
        //'https://www.rothenberger.ru/catalog/novinki/',
        //'https://www.rothenberger.ru/catalog/rasprodazha/',
        //'',
    ];


    public function __construct(
        private DownloaderHtml       $downloader,
        private Pool                 $pool,
        private PageProcessorManager $processorManager,
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
