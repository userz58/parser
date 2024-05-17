<?php

namespace App\EventListener\ProductPageProcessed;

use App\Event\ProductPageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class WriteProductEventListener
{
    const KEY_VARIANTS = '_variants';

    const PAGE_NAME_PRODUCTS = 'Товары (кратко)';

    const PAGE_NAME_VARIANTS = 'Товары - Торг предложения (кратко)';

    public function __construct(
        private SluggerInterface $slugger,
        private WriterXlsx       $writer,
    )
    {
    }

    public function __invoke(ProductPageProcessedEvent $event): void
    {
        $data = $event->getData();

        $prodRow = [
            'Категория HASH' => $data->get('_category_hash'),
            'Категория' => $data->get('_category_name'),
            'Артикул' => $data->get('Артикул'),
            'Наименование' => $data->get('Наименование'),
        ];

        $this->writer->add(self::PAGE_NAME_PRODUCTS, $prodRow);

        $variants = $data->get(self::KEY_VARIANTS);

        foreach ($variants as $key => $variant) {
            $row = ['Товар' => $data->get('Наименование'), 'Артикул товара' => $data->get('Артикул')] + $variant;
            $this->writer->add(self::PAGE_NAME_VARIANTS, $row);
        }
    }
}
