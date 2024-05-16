<?php

namespace App\Message;

class DownloadImage
{
    public function __construct(
        private string $url,
        private string $filepath,
    )
    {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }
}
