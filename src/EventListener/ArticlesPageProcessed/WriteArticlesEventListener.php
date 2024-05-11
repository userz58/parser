<?php

namespace App\EventListener\ArticlesPageProcessed;

use App\Event\ArticleIndexPageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class WriteArticlesEventListener
{
    const PAGE_NAME = 'Статьи (кратко)';

    public function __construct(
        private WriterXlsx $writer,
    )
    {
        $this->writer->add(self::PAGE_NAME, [
            'hash',
            'Название',
            'Изображение для анонса',
        ]);
    }

    public function __invoke(ArticleIndexPageProcessedEvent $event): void
    {
        $data = $event->getData();

        $products = $data->get('Статьи');

        foreach ($products as $key => $product) {
            $this->writer->add(self::PAGE_NAME, [
                'hash' => $product['hash'],
                'Название' => $product['Название'],
                'Изображение для анонса' => $product['Изображение'],
            ]);
        }
    }
}
