<?php

namespace App\EventListener\ProductPageProcessed;

use App\Event\ProductPageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class WriteCategoriesEventListener
{
    const KEY_BREADCRUMBS = '_breadcrumbs';

    //const KEY_CATEGORIES_HASH = '_category_hash';

    //const KEY_CATEGORIES_NAME = '_category_name';

    const PAGE_NAME_CATEGORIES_HASH = 'Категории1';
    const PAGE_NAME_CATEGORIES = 'Категории2';

    public function __construct(
        private SluggerInterface $slugger,
        private WriterXlsx       $writer,
    )
    {
    }

    public function __invoke(ProductPageProcessedEvent $event): void
    {
        $data = $event->getData();
        $breadcrumbs = $data->get(self::KEY_BREADCRUMBS);

        $rowOnHashPage = [];
        $productCategories = [];
        foreach ($breadcrumbs as $name => $url) {
            $slug = $this->slugger->slug($name)->lower()->toString();
            $hash = sha1($url);

            $productCategories[] = $name;
            $productCategories[] = $slug;

            $rowOnHashPage[] = $hash;
            $rowOnHashPage[] = $slug;
        }

        // запись вложенных категорий по уникальным занчениям HASH
        $this->writer->add(self::PAGE_NAME_CATEGORIES_HASH, $rowOnHashPage);

        // запись вложенных категорий
        $this->writer->add(self::PAGE_NAME_CATEGORIES, $productCategories);
    }
}
