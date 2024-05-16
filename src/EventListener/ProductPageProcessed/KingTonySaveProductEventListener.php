<?php

namespace App\EventListener\ProductPageProcessed;

use App\Doctrine\Saver;
use App\Entity\Product;
use App\Event\ProductPageProcessedEvent;
use App\Manager\AttributesManager;
use App\Parser\KingTonyComParser;
use App\Repository\CategoryRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class KingTonySaveProductEventListener
{
    private const KEY_SKU = 'Артикул';

    public function __construct(
        private AttributesManager          $attributesManager,
        private ProductAttributeRepository $attributeRepository,
        private ProductRepository          $productRepository,
        private CategoryRepository         $categoryRepository,
        private Saver                      $saver,
    )
    {
    }

    public function __invoke(ProductPageProcessedEvent $event): void
    {
        if (KingTonyComParser::CODE !== $event->getParserCode()) {
            return;
        }

        $data = $event->getData()->toArray();

        $productSku = $data[self::KEY_SKU];
        if (null === $product = $this->productRepository->findOneBySku($productSku)) {
            $product = new Product($productSku);
        }

        $product->setProps($data);

        $this->saver->persist($product);

        $this->saver->flush();
    }
}
