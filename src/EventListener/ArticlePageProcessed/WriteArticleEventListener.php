<?php

namespace App\EventListener\ArticlePageProcessed;

use App\Event\ArticlePageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class WriteArticleEventListener
{
    const PAGE_NAME = 'Статьи';

    public function __construct(
        private WriterXlsx $writer,
    )
    {
        $this->writer->add(self::PAGE_NAME, [
            'hash',
            'Название',
            'Дата',
            'Изображение детальное',
            'Детальное описание',
        ]);
    }

    public function __invoke(ArticlePageProcessedEvent $event): void
    {
        $data = $event->getData();
        $hash = sha1($data->getUrl());

        $this->writer->add(self::PAGE_NAME, [
            'hash' => $hash,
            'Название' => $data->get('Название'),
            'Дата' => $data->get('Дата'),
            'Изображение детальное' => $data->get('Изображение'),
            'Детальное описание' => $data->get('Детальное описание'),
        ]);

    }
}
