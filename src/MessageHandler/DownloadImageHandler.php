<?php

namespace App\MessageHandler;

use App\Downloader\DownloaderFiles;
use App\Message\DownloadImage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DownloadImageHandler implements EventHandlerInterface
{
    public function __construct(
        private DownloaderFiles $downloader,
    )
    {
    }

    public function __invoke(DownloadImage $message)
    {
            $this->downloader->downloadTo($message->getUrl(), $message->getFilepath());
    }
}
