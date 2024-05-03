<?php

namespace App\EventListener\CategoryPageProcessed;

use App\Event\CategoryPageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class WriteCategoryProductsEventListener
{
    const PAGE_NAME = 'Товары (кратко)';

    public function __construct(
        private WriterXlsx $writer,
    )
    {
        $this->writer->add(self::PAGE_NAME, [
            'hash',
            'Артикул',
            'Название',
            'Цена',
            'Валюта',
            'Хит',
            'Изображение для анонса',
        ]);
    }

    public function __invoke(CategoryPageProcessedEvent $event): void
    {
        $data = $event->getData();
        $products = $data->get('Товары');

        foreach ($products as $key => $product) {
            $this->writer->add(self::PAGE_NAME, [
                'hash' => $product['hash'],
                'Артикул' => $product['Артикул'],
                'Название' => $product['Название'],
                'Цена' => $product['Цена'],
                'Валюта' => $product['Валюта'],
                'Хит' => $product['Хит'],
                'Изображение для анонса' => $product['Изображение'],
            ]);
        }
    }
}
