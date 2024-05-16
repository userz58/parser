<?php

namespace App\EventListener\ParserFinished;

use App\Doctrine\Saver;
use App\Event\ParserFinishedEvent;
use App\Parser\KingTonyComParser;
use App\Repository\ProductRepository;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class KingTonyWriteProductsEventListener
{
    public function __construct(
        private ProductRepository  $productRepository,
        private Saver              $saver,
        private SluggerInterface   $slugger,
        private WriterXlsx         $writer,
    )
    {
    }

    public function __invoke(ParserFinishedEvent $event): void
    {
        if (KingTonyComParser::CODE !== $event->getParserCode()) {
            return;
        }

        $this->writer->add('Товары в категориях', [
            'Категории',
            'Название товара',
            'Артикул',
        ]);

        // обойти все скачанные ссылки
        $iterator = $this->productRepository->iterateAll();
        $i = 0;
        foreach ($iterator as $product) {
            $i++;
            print_r(sprintf("[%d] %s\n", $i, $product->getSku()));

            $row = [];

            $categoriesHash = array_map(fn($c) => sprintf('%s %s', substr($c->getHash(), 0, 2), $c->getName()), $product->getCategories()->toArray());
            $row['Категории'] = implode(';', $categoriesHash);

            //$categoriesHash = array_map(fn($c) => $c->getHash(), $product->getCategories()->toArray());
            //$row['Категории (HASH)'] = implode(';', $categoriesHash);
            //$categoriesNames = array_map(fn($c) => $c->getName(), $product->getCategories()->toArray());
            //$row['Категории (Названия)'] = implode(';', $categoriesNames);

            $row['Название товара'] = $product->getSku();

            $row['Артикул'] = $product->getSku();

            // todo: записать свойства товара
            $this->writer->add('Товары в категориях', $row);

            $this->saver->detach($product);
        }
    }
}
