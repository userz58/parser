<?php

namespace App\Downloader;

use App\Utils\PathGenerator;
use League\Flysystem\Filesystem;

/**
 * Скачивание картинок
 */
class DownloaderFiles
{
    private string $userAgent = 'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)';

    public function __construct(
        private Filesystem    $filesystem,
        private PathGenerator $pathGenerator
    )
    {
    }

    // скачать и сохранить файл
    public function download(string $url, ?string $ext = null): string
    {
        if (null === $ext) {
            $ext = pathinfo($url, PATHINFO_EXTENSION);
        }

        $filepath = sprintf('%s.%s', $this->pathGenerator->generate($url), $ext);

        if (!$this->filesystem->fileExists($filepath)) {
            $content = $this->getContent($url);
            $this->filesystem->write($filepath, $content);
        }

        return $filepath;
    }


    public function downloadInto(string $url, string $subDir, ?string $ext = null): string
    {
        if (null === $ext) {
            $ext = pathinfo($url, PATHINFO_EXTENSION);
        }

        $filepath = sprintf('%s/%s.%s', $subDir, $this->pathGenerator->generate($url), $ext);

        if (!$this->filesystem->fileExists($filepath)) {
            $content = $this->getContent($url);
            $this->filesystem->write($filepath, $content);
        }

        return $filepath;
    }


    // скачать и сохранить в указанный файл
    public function downloadTo(string $url, string $filepath): string
    {
        if (!$this->filesystem->fileExists($filepath)) {
            $content = $this->getContent($url);
            $this->filesystem->write($filepath, $content);
        }

        return $filepath;
    }

    protected function getContent(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ((200 !== $httpCode) || (404 == $httpCode)) {
            throw new \Exception(sprintf("Ошибка загрузки страницы. Код ошибки: %d\nURL: %s", $httpCode, $url));
        }

        return $content;
    }

    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    public function getPathGenerator(): PathGenerator
    {
        return $this->pathGenerator;
    }
}
