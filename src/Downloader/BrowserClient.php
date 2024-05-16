<?php

namespace App\Downloader;

use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Panther\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Symfony\Component\DomCrawler\Link;
use Facebook\WebDriver\WebDriverDimension;

class BrowserClient
{
    private const  FOLLOW_REDIRECTS = true;

    private const TIMEOUT = 15; // Seconds

    private const TIME_INTERVAL = 4999; // Milliseconds

    private ?Client $client = null;

    public function __construct(
        string $browser = 'chrome',
        int $windowWidth = 1920,
        int $windowHeight = 4280,
    )
    {
        switch ($browser) {
            case 'chrome':
                $this->client =  Client::createChromeClient();
                break;
            case 'firefox':
                $this->client = Client::createFirefoxClient();
                break;
            case 'selenium':
                $this->client = Client::createSeleniumClient();
                break;
        }

        $this->client->manage()->window()->maximize()->setSize(new WebDriverDimension($windowWidth, $windowHeight));
        $this->client->followRedirects(self::FOLLOW_REDIRECTS);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Закрыть браузер
     */
    public function quit(): void
    {
        $this->client->quit();
    }

    public function getUrl(string $url, ?string $waitSelector = null): Crawler|PantherCrawler|null
    {
        try {
            $crawler = $this->client->get($url);
        } catch (\Exception $e) {
            $this->client->quit();
            dump($e);
            dd(sprintf('Ошибка при загрузке URL %s %s', $url, $e->getMessage()));
        }

        if (null === $waitSelector) {
            sleep(1);

            return $this->client->getCrawler();
        }

        return $this->waitFor($waitSelector);
    }

    protected function waitFor(string $selector): ?PantherCrawler
    {
        try {
            $crawler = $this->client->waitFor($selector, self::TIMEOUT, self::TIME_INTERVAL);
        } catch (\Exception $e) {
            $this->client->quit();
            dump($e);
            dd(sprintf('Ошибка при ожидании элемента %s %s %s', $this->client->getCurrentURL(), $selector, $e->getMessage()));
        } finally {

        }

        return $crawler;
    }

    public function clickLink(Link $link, ?string $waitSelector = null): ?Crawler
    {
        try {
            $crawler = $this->client->click($link);
        } catch (\Exception $e) {
            $this->client->quit();
            dump($e);
            dd(sprintf('Ошибка загрузки при клике на ссылку %s %s %s', $link->getUri(), $waitSelector, $e->getMessage()));
        }

        if (null === $waitSelector) {
            return $crawler;
        }

        return $this->waitFor($waitSelector);
    }
}
