<?php

namespace App\EventListener\ProductPageProcessed;

use App\Event\ProductPageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class WriteVariantsEventListener
{
    const KEY_VARIANTS = '_variants';

    const PRODUCT_SKU = 'Артикул';
    const PRODUCT_NAME = 'Наименование';
    const PAGE_NAME_VARIANTS = 'Торговые предложения';

    public function __construct(
        private SluggerInterface $slugger,
        private WriterXlsx       $writer,
    )
    {
    }

    public function __invoke(ProductPageProcessedEvent $event): void
    {
        $data = $event->getData();

        if([] == $variants = $data->get(self::KEY_VARIANTS)) {
            return;
        }

        $productSku = $data->get(self::PRODUCT_SKU);
        $productName = $data->get(self::PRODUCT_NAME);

        $row = [];
        foreach ($variants as $hash => $values) {
            $row = ['Артикул товара' => $productSku,'Товар' => $productName] + $values;

            $this->writer->add(self::PAGE_NAME_VARIANTS, $row);
        }
    }
}
