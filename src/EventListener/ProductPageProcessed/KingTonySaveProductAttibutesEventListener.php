<?php

namespace App\EventListener\ProductPageProcessed;

use App\Event\ProductPageProcessedEvent;
use App\Manager\AttributesManager;
use App\Parser\KingTonyComParser;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class KingTonySaveProductAttibutesEventListener
{
    const KEY_VARIANTS = '_variants';

    public function __construct(
        private AttributesManager $attributesManager,
    )
    {
    }

    public function __invoke(ProductPageProcessedEvent $event): void
    {
        if (KingTonyComParser::CODE !== $event->getParserCode()) {
            return;
        }

        $data = $event->getData()->toArray();

        // сохранение аттрибутов товара
        foreach (array_keys($data) as $attr) {
            $this->attributesManager->addProductAttribute($attr);
        }

        // сохранение аттрибутов варинтов товара
        //if (!isset($data[self::KEY_VARIANTS])) {
        /*
        if (empty($data[self::KEY_VARIANTS])) {
            return;
        }
        if([] !== $variants = $data[self::KEY_VARIANTS]) {
            foreach (array_keys(reset($variants)) as $attr) {
                $this->attributesManager->addVariantAttribute($attr);
            }
        }
        */

        if (!empty($data[self::KEY_VARIANTS])) {
            $attributes = array_keys(reset($data[self::KEY_VARIANTS]));
            foreach ($attributes as $attr) {
                $this->attributesManager->addVariantAttribute($attr);
            }
        }
    }
}
