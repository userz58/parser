<?php

namespace App\Downloader;

use App\Utils\PathGenerator;
use League\Flysystem\Filesystem;
use Symfony\Component\Panther\Client;

/**
 * Скачивание HTML страницы браузером
 */
// https://webscraping.ai/faq/symfony-panther/how-do-i-handle-file-downloads-during-web-scraping-with-symfony-panther
class DownloaderChrome extends AbstractDownloader
{
    public function __construct(
        protected BrowserClient $browserClient,
        protected Filesystem    $filesystem,
        protected PathGenerator $pathGenerator,
    )
    {
    }

    public function getClient(): Client
    {
        return $this->browserClient->getClient();
    }

    protected function getContent(string $url): string
    {
        try {
            //$this->browserClient->getUrl($url, 'body[style]');
            $this->getClient()->request('GET', $url);
            //sleep(2);
        } catch (\Exception $e) {
            $this->getClient()->takeScreenshot(sprintf('/app/error-screen-page-tss-%s.png', sha1($url)));
            $this->getClient()->quit();
            dump($e);
            print_r(sprintf('Ошибка при загрузке URL %s %s', $url, $e->getMessage()));
            dd('ERROR DownloaderChrome getContent');
        }

        sleep(2);

        // скриншот окна браузера
        //$this->getClient()->takeScreenshot(sprintf('/app/screen-page-tss-%s.png', sha1($url)));

        return $this->getClient()->getCrawler()->html();
    }
}
