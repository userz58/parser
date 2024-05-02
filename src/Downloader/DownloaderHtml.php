<?php

namespace App\Downloader;

/**
 * Скачивание HTML страницы
 */
class DownloaderHtml extends AbstractDownloader
{
    private string $userAgent = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    protected function getContent(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->getUserAgent());

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ((200 !== $httpCode) || (404 == $httpCode)) {
            throw new \Exception(sprintf("Ошибка загрузки страницы. Код ошибки: %d\nURL: %s", $httpCode, $url));
        }

        return $content;
    }
}
