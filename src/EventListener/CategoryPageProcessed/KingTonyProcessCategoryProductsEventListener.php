<?php

namespace App\EventListener\CategoryPageProcessed;

use App\Doctrine\Saver;
use App\Entity\Category;
use App\Entity\Product;
use App\Event\CategoryPageProcessedEvent;
use App\Parser\KingTonyComParser;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class KingTonyProcessCategoryProductsEventListener
{
    private const PAGE_NAME = 'Карегории (KingTony)';

    public function __construct(
        private CategoryRepository $categoryRepository,
        private ProductRepository  $productRepository,
        private Saver              $saver,
        private SluggerInterface   $slugger,
        private WriterXlsx         $writer,
    )
    {
    }

    public function __invoke(CategoryPageProcessedEvent $event): void
    {
        if (KingTonyComParser::CODE !== $event->getParserCode()) {
            return;
        }

        $data = $event->getData()->toArray();

        $categorySlug = $data['_slug'];
        $categoryHash = sha1($categorySlug);
        //$categoryName = $data['Название'];
        $categoryName = sprintf('%s %s', substr($categoryHash, -2), $data['Название']);

        if (null === $category = $this->categoryRepository->findOneBySlug($categorySlug)) {
            $category = (new Category())
                ->setSlug($categorySlug)
                ->setHash($categoryHash)
                ->setName($categoryName);

            $this->saver->persist($category);

            $products = isset($data['_products']) ? $data['_products'] : [];
            if ([] !== $products) {
                $this->writeInFile($data);
            }
        }


        $products = isset($data['_products']) ? $data['_products'] : [];
        foreach ($products as $productSku) {
            if (null === $product = $this->productRepository->findOneBySku($productSku)) {
                $product = new Product($productSku);
            }

            $category->addProduct($product);

            $this->saver->persist($product);
        }

        $this->saver->flush(true);
    }

    private function writeInFile(array $data): void
    {
        $breadcrumbs = $data['_breadcrumbs'];
        $names = array_keys($breadcrumbs);

        $rowOnHashPage = [];
        //$rowOnRenamePage = [];

        for ($i = 0; $i < count($names); $i++) {
            $selected = array_slice($names, 0, $i + 1);
            $name = end($selected);
            $hash = sha1(implode('/', array_map(fn($item) => $this->slugger->slug($item)->toString(), $selected)));

            $slug = $this->slugger->slug($name)->lower()->toString();
            //$slug = sprintf('%s-%s', substr($hash, 0, 4), $this->slugger->slug($name)->lower()->toString());

            $rowOnHashPage[] = sprintf('%s %s', substr($hash, 0, 2), $name);
            $rowOnHashPage[] = $slug;

            //$rowOnRenamePage[] = $name;
            //$rowOnRenamePage[] = $slug;

            //$this->writer->add('Категории3', ['hash' => $hash, 'Название' => $name, 'Символьный код' => $slug]);
        }

        // запись вложенных категорий по уникальным занчениям HASH
        $this->writer->add(self::PAGE_NAME, $rowOnHashPage);

        // запись вложенных категорий
        //$this->writer->add('Категории2', $rowOnRenamePage);
    }
}
