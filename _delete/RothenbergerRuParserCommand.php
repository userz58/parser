<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;
use Facebook\WebDriver\WebDriverDimension;
use Symfony\Component\String\Slugger\AsciiSlugger;
use \XLSXWriter;
use League\Flysystem\Filesystem;

#[AsCommand( name: 'parser:rothenberger', description: 'парсинг инструмента rothenberger.ru' )]
class RothenbergerRuParserCommand extends Command
{
    /** @var string */
    const FILESYSTEM_DIR = 'rothenberger-ru';

    /** @var string */
    protected $filepath = '/srv/app/public/rothenberger-ru.xls';

    /** @var string */
    const BASE_HREF = 'https://www.rothenberger.ru';

    /** @var Filesystem */
    protected $filesystem;

    /** @var AsciiSlugger */
    protected $slugger;

    /** @var XLSXWriter */
    protected $writer;

    /** @var Client */
    protected $client;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->slugger = new AsciiSlugger();
        $this->writer = new XLSXWriter();
        $this->client = Client::createFirefoxClient();

        parent::__construct();
    }

    protected function configure()
    {
        date_default_timezone_set('Europe/Moscow');

        // Настройка браузера
        $this->client->manage()->window()->maximize()->setSize(new WebDriverDimension(1920, 4280));
        $this->client->followRedirects(true);

        $this->productsPageHeader = [
            'Артикул',
            'Модель',
            'Артикул производителя (MPN)',
            'Символьный код',
            'Наименование',
            'Бренд',
            'Цена',
            'Валюта',
            'Изображение',
            'Доп изображения',
            'Категория',
            'SEO заголовок',
            'Детальное описание',
            'Вам также может понравиться',
            'С этим товаром покупают',
        ];
        $this->writer->writeSheetRow('Товары', $this->productsPageHeader);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeStart = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));

        $io = new SymfonyStyle($input, $output);
        $io->info('START ' . $timeStart->format('d-m-Y, H:i:s'));

        $brand = 'Rothenberger';
        $skip = [
            'https://www.rothenberger.ru/catalog/novinki/',
            'https://www.rothenberger.ru/catalog/rasprodazha/',
        ];

        $categories = [];
        $catalogCategories = $this->parseIndexCategories('https://www.rothenberger.ru/catalog/');

        foreach ($catalogCategories as $categoryUrl) {
            if (in_array($categoryUrl, $skip)) continue; // пропустить эту категорию
            //dump($categoryUrl);

            $products = $this->parseProductsList($categoryUrl);
            //dump($products);
            //die();

            foreach ($products as $product) {
                //dump($product);
                print_r(sprintf("Товар: %s\n%s\n\n", $product['name'], $product['url']));

                $data = $this->parseProductPage($product['url'], $product);
                //dump($data);

                //$linkedProducts = $this->parseProductPageRecommendedProducts($product['url']);
                //dump($linkedProducts);
                //die();

                $cat = [];
                foreach ($data['categories'] as $category) {
                    $cat[] = sprintf('%s %s', $brand, $category['name']);
                    $cat[] = $category['code'];
                }
                $this->writer->writeSheetRow('Категории с подкатегориями', $cat);

                $name = trim(preg_replace('/\s+/', ' ', $data['name']));
                $category = array_pop($data['categories']);
                $images = $data['images'];

                $row = [
                    'Артикул' => $data['sku'],
                    'Модель' => str_replace(['', '-', '_'], '', $data['sku']),
                    'Артикул производителя (MPN)' => $data['sku'],
                    'Символьный код' => $this->slugger->slug(sprintf('%s-%s', $brand, $data['sku']))->lower()->toString(),
                    'Наименование' => $name,// sprintf('%s (%s)', $name, $data['sku']),
                    'Бренд' => $brand,
                    'Цена' => $data['price'],
                    'Валюта' => $data['currency'],
                    'Изображение' => array_shift($images),
                    'Доп изображения' => implode(';', $images),
                    'Категория' => sprintf('%s %s', $brand, $category['name']),
                    'SEO заголовок' => sprintf('%s (%s)', $name, $data['sku']),
                    'Детальное описание' => implode('<hr/>', $data['description']),
                    //'Вам также может понравиться' => implode(';', $data['recommended']),
                    //'Вам также может понравиться' => implode(';', $data['parts']),
                ];
                $this->writer->writeSheetRow('Товары', $row);
            }
        }

        $io->writeln(sprintf('Запись результатов XLS-файл - %s', $this->filepath));
        $this->writer->writeToFile($this->filepath);

        $timeFinish = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $timeDifference = $timeFinish->diff($timeStart);
        $io->info('Времы выполнения команды: ' . $timeDifference->format('%H часов  %i минут'));

        return Command::SUCCESS;
    }


    protected function parseIndexCategories(string $url)
    {
        $html = $this->downloadHtml($url, 'html/category/index.html');
        $crawler = new Crawler($html);

        $links = $crawler->filter('.main-catalog-wrapper .items .item a')->each(function (Crawler $node, $i) {
            return sprintf('%s%s', self::BASE_HREF, $node->attr('href'));
        });

        return array_unique($links);
    }

    protected function parseProductsList(string $url, string $name = '???')
    {
        print_r(sprintf("Категория: %s => %s\n", $name, $url));

        $filesystem = $this->getFilesystem();
        $filepath = sprintf('%s/json/categories/%s.json', self::FILESYSTEM_DIR, $this->generateFilepath($url));

        // проверка есть-ли сохраненный список товаров
        if ($filesystem->fileExists($filepath)) {
            print_r("Возвращаем сохраненные данные\n");
            return json_decode($filesystem->read($filepath), true);
        }

        $products = [];
        $page = 1;
        $hasNextPage = true;
        while (true === $hasNextPage) {
            print_r(sprintf("Страница: %d\n", $page));

            $html = $this->downloadHtml($url, sprintf('html/category/%s.html', $this->generateFilepath($url)));
            $crawler = new Crawler($html);

            $productsList = $crawler->filter('.catalog_block .catalog_item')->each(function (Crawler $node, $i) {
                return [
                    'url' => sprintf('%s%s', self::BASE_HREF, $node->filter('.item-title a')->attr('href')),
                    'name' => trim(preg_replace('/\s+/', ' ', $node->filter('.item-title a')->text())),
                    'sku' => $node->filter('.article_block')->attr('data-value'),
                ];
            });

            $products = array_merge($products, $productsList);

            // следующая страница в пагинаторе
            if ($crawler->filter('.module-pagination')->count() > 0) {
                $nextPageLink = $crawler->filter('.module-pagination .flex-nav-next a');
                if ($nextPageLink->count() > 0) {
                    $url = sprintf('%s%s', self::BASE_HREF, $nextPageLink->attr('href'));
                    $page++;
                    continue;
                }
            }

            $hasNextPage = false;
        }

        // сохранение загруженных товаров
        $filesystem->write($filepath, json_encode($products));

        return $products;
    }

    /**
     * Парснг детальной страницы товара
     */
    protected function parseProductPage(string $url, array $data = [])
    {
        $filesystem = $this->getFilesystem();
        $filepath = sprintf('%s/json/products/%s.json', self::FILESYSTEM_DIR, sha1($url));

        // проверка есть-ли сохраненный список товаров
        if ($filesystem->fileExists($filepath)) {
            print_r("Возвращаем сохраненные данные\n");

            return json_decode($filesystem->read($filepath), true);
        }

        $html = $this->downloadHtml($url, sprintf('html/products/%s.html', sha1($url)));
        $crawler = new Crawler($html);

        $data['price'] = null;
        $data['currency'] = null;
        if($crawler->filter('.price_val[itemprop=price]')->count() > 0) {
            $data['price'] = round($crawler->filter('.price_val[itemprop=price]')->attr('content'));
            $data['currency'] = $crawler->filter('.currency[itemprop=priceCurrency]')->attr('content');
        }

        $data['images'] = $crawler->filter('.product-detail-gallery .product-detail-gallery__item a.product-detail-gallery__link')->each(function (Crawler $node, $i) {
            return sprintf('%s%s', self::BASE_HREF, $node->attr('href'));
        });

        $description = $crawler->filter('.section-content-wrapper #desc .content[itemprop=description]')->html();
        $description = trim(str_replace([chr(9), chr(10), "; </li>", '; </li>', ';</li>', '.</li>'], ['', '', '</li>', '</li>', '</li>', '</li>'], $description));
        $description = trim(str_replace('Представительство ROTHENBERGER в России ROTHENBERGER RUSSIA осуществляет комплексные поставки товара', '', $description));

        $text = [];
        $text[] = $description;

        $characteristics = $crawler->filter('.tabs-block .tab-content #props .char_block');
        if ($characteristics->count() > 0) {
            $characteristicsHtml = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si", '<$1$2>', $characteristics->html());
            $characteristicsHtml = str_ireplace([chr(9), chr(10), '<table></table>'], '', $characteristicsHtml);
            $characteristicsHtml = str_replace(['<table>', '<span>', '</span>'], ['<table class="table table-striped table-hover">', '', ''], $characteristicsHtml);
            $text[] = '<h3>Характеристики</h3>' . $characteristicsHtml;
        }

        $data['description'] = $text;

        /*
        $modifications = $crawler->filter('.tabs-block .tab-content #modifications meta[itemprop=name]')->each(function (Crawler $node, $i) {
            return trim(preg_replace('/\s+/', ' ', $node->attr('content')));
        });

        $accessories = $crawler->filter('.tabs-block .tab-content #accessories meta[itemprop=name]')->each(function (Crawler $node, $i) {
            return trim(preg_replace('/\s+/', ' ', $node->attr('content')));
        });

        $parts = $crawler->filter('.tabs-block .tab-content #dopparts meta[itemprop=name]')->each(function (Crawler $node, $i) {
            return trim(preg_replace('/\s+/', ' ', $node->attr('content')));
        });

        //Вам также может понравиться
        $data['recommended'] = array_merge($modifications, $accessories);

        //С этим товаром покупают
        $data['parts'] = $parts;
        */

        $breadcrumbs = $crawler->filter('.breadcrumbs .breadcrumbs__link')->each(function (Crawler $node, $i) {
            return [
                'url' => sprintf('%s%s', self::BASE_HREF, $node->attr('href')),
                'name' => $node->text(),
                'code' => $this->slugger->slug($node->text())->lower()->toString(),
            ];
        });

        $data['categories'] = array_slice($breadcrumbs, 2);

        // сохранение данных товара
        $filesystem->write($filepath, json_encode($data));

        return $data;
    }

    protected function parseProductPageRecommendedProducts($url)
    {
        $this->client->get($url);
        $crawler = $this->client->waitFor('.main-catalog-wrapper', 10, 3550);

        $data = [];

        if ($crawler->filter('.tabs a[href="#modifications"]')->count() > 0) {
            dump('click Модификации');
            $this->client->getMouse()->clickTo('.tabs a[href="#modifications"]');
            sleep(3);
        }

        $data['modifications'] = $crawler->filter('.tabs-block .tab-content #modifications .item-title a')->each(function (Crawler $node, $i) {
            return trim(preg_replace('/\s+/', ' ', $node->text()));
        });

        if ($crawler->filter('.tabs a[href="#accessories"]')->count() > 0) {
            dump('click Комплектующие');
            $this->client->getMouse()->clickTo('.tabs a[href="#accessories"]');
            sleep(3);
        }

        $data['accessories'] = $crawler->filter('.tabs-block .tab-content #accessories .item-title a')->each(function (Crawler $node, $i) {
            return trim(preg_replace('/\s+/', ' ', $node->text()));
        });

        // Запчасти
        if ($crawler->filter('.tabs a[href="#dopparts"]')->count() > 0) {
            dump('click Доп оборудование');
            $this->client->getMouse()->clickTo('.tabs a[href="#dopparts"]');
            sleep(3);
        }

        $data['parts'] = $crawler->filter('.tabs-block .tab-content #dopparts .item-title a')->each(function (Crawler $node, $i) {
            return trim(preg_replace('/\s+/', ' ', $node->text()));
        });

        dump($data);
        die();




        return $data;
    }


    /**
     * Скачивание HTML страницы
     *
     * @param $url
     * @return bool|string
     */
    protected function downloadHtml($url, $path = null)
    {
        $filesystem = $this->getFilesystem();

        //$html = $this->downloadHtml($url, sprintf('%s/html/%s.html', self::FILESYSTEM_DIR, sha1($url)));

        if (null === $path) {
            $filepath = sprintf('%s/html/%s.html', self::FILESYSTEM_DIR, $this->generateFilepath($url));
        } else {
            $filepath = sprintf('%s/%s', self::FILESYSTEM_DIR, $path);
        }

        if ($filesystem->fileExists($filepath)) {
            return $filesystem->read($filepath);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)');

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ((200 !== $httpCode) || (404 == $httpCode)) {
            throw new \Exception(sprintf("Ошибка загрузки страницы. Код ошибки: %d\nURL: %s", $httpCode, $url));
        }

        $filesystem->write($filepath, $html);

        return $html;
    }

    /**
     * Скачивание изображения
     *
     * @param $url
     * @return string
     */
    protected function downloadImage($url)
    {
        $filesystem = $this->getFilesystem();

        $filepath = sprintf('%s/images/%s.jpg', self::FILESYSTEM_DIR, $this->generateFilepath($url));

        //print_r(sprintf("download image: %s\nsave to: %s\n", $url, $filepath));

        if ($filesystem->fileExists($filepath)) {
            return $filepath;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)');

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ((200 !== $httpCode) || (404 == $httpCode)) {
            throw new \Exception(sprintf('Ошибка загрузки изображения. Код ошибки: %d', $httpCode));
        }

        $filesystem->write($filepath, $content);

        return $filepath;
    }

    /**
     * @return Filesystem
     */
    protected function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Генерирование уникального имени файла по URL
     *
     * @param string $url
     * @return string
     */
    protected function generateFilepath(string $url)
    {
        $uuid = sha1($url);

        return sprintf('%s/%s/%s/%s/%s', $uuid[0], $uuid[1], $uuid[2], $uuid[3], $uuid);
    }
}
