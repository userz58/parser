<?php

namespace App\Downloader;

use App\Utils\PathGenerator;
use League\Flysystem\Filesystem;

abstract class AbstractDownloader
{
    private string $dir = 'html';

    private string $ext = 'html';

    public function __construct(
        protected Filesystem    $filesystem,
        protected PathGenerator $pathGenerator,
    )
    {
    }

    // скачать и сохранить файл
    public function download(string $url): string
    {
        $filepath = sprintf('%s/%s.%s', $this->getDir(), $this->pathGenerator->generate($url), $this->getExt());

        if ($this->isFileExists($filepath)) {
            return $this->read($filepath);
        }

        $content = $this->getContent($url);

        $this->write($filepath, $content);

        return $content;
    }

    // скачать и сохранить в директорию
    public function downloadInto(string $url, string $subDir): string
    {
        $filepath = sprintf('%s/%s/%s.%s', $this->getDir(), $subDir, $this->pathGenerator->generate($url), $this->getExt());

        if ($this->isFileExists($filepath)) {
            return $this->read($filepath);
        }

        $content = $this->getContent($url);

        $this->write($filepath, $content);

        return $content;
    }

    // скачать и сохранить в указанный файл
    public function downloadTo(string $url, string $path): string
    {
        $filepath = sprintf('%s/%s', $this->getDir(), $path);

        if ($this->isFileExists($filepath)) {
            return $this->read($filepath);
        }

        $content = $this->getContent($url);

        $this->write($filepath, $content);

        return $content;
    }

    abstract protected function getContent(string $url): string;

    protected function isFileExists(string $filepath): bool
    {
        return $this->filesystem->fileExists($filepath);
    }

    protected function read(string $filepath): string
    {
        return $this->filesystem->read($filepath);
    }

    protected function write(string $filepath, $content): void
    {
        $this->filesystem->write($filepath, $content);
    }

    protected function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    protected function getPathGenerator(): PathGenerator
    {
        return $this->pathGenerator;
    }

    public function getDir(): string
    {
        return $this->dir;
    }

    public function setDir(string $dir): void
    {
        $this->dir = $dir;
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    public function setExt(string $ext): void
    {
        $this->ext = $ext;
    }
}
