<?php

namespace App\EventListener\ParserFinished;

use App\Doctrine\Saver;
use App\Entity\Product;
use App\Event\ParserFinishedEvent;
use App\Manager\AttributesManager;
use App\Parser\KingTonyComParser;
use App\Repository\ProductRepository;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEventListener]
class KingTonyWriteProductsEventListener
{
    private const PAGE_NAME_PRODUCTS = 'Товары (с характеристиками)';
    private const PAGE_NAME_VARIANTS = 'Торговые предложения';
    private const PAGE_NAME_PRODUCTS_IN_CAT = 'Товары в категориях';
    private const KEY_CATEGORY_HASH = '_category_hash';
    private const KEY_CATEGORY_NAME = '_category_name';
    private const KEY_URL = 'url';
    private const KEY_HASH = 'hash';
    private const KEY_SKU = 'Артикул';
    private const KEY_NAME = 'Наименование';
    private const KEY_CATEGORIES = 'Категории';
    private const KEY_BREADCRUMBS = '_breadcrumbs';
    private const KEY_VARIANTS = '_variants';


    public function __construct(
        private ProductRepository $productRepository,
        private AttributesManager $attributesManager,
        private Saver             $saver,
        private SluggerInterface  $slugger,
        private WriterXlsx        $writer,
    )
    {
    }

    public function __invoke(ParserFinishedEvent $event): void
    {
        if (KingTonyComParser::CODE !== $event->getParserCode()) {
            return;
        }

        $this->writer->add('Товары в категориях', ['Категории', 'Название товара', 'Артикул']);

        $productsAttributes = $this->attributesManager->getProductAttributes();
        $exclude = [self::KEY_URL, self::KEY_BREADCRUMBS, self::KEY_VARIANTS, self::KEY_CATEGORY_HASH, self::KEY_CATEGORY_NAME];
        $productsAttributes = array_filter($productsAttributes, fn($attr) => !in_array($attr, $exclude));
        $this->writer->add(self::PAGE_NAME_PRODUCTS, $productsAttributes);

        $variantsAttrtibutes = ['Название товара', 'Артикул', 'Название', 'Сортировка'] + $this->attributesManager->getVariantAttributes();
        $this->writer->add(self::PAGE_NAME_VARIANTS, $variantsAttrtibutes);

        // обойти все скачанные ссылки
        $iterator = $this->productRepository->iterateAll();
        $i = 0;
        foreach ($iterator as $product) {
            $productSku = $product->getSku();

            $i++;
            print_r(sprintf("Запись в файл [%d] %s\n", $i, $productSku));

            $categoriesNames = [];
            foreach ($product->getCategories() as $category) {
                $categoriesNames[] = $category->getName();
            }
            $this->writer->add('Товары в категориях', ['Категории' => implode(';', $categoriesNames), 'Название товара' => $productSku, 'Артикул' => $productSku,]);

            // записать товар
            $data = $product->getProps();
            $rowProduct = [];

            foreach ($productsAttributes as $attr) {
                if (array_key_exists($attr, $data)) {
                    $rowProduct[$attr] = is_array($data[$attr]) ? implode(';', $data[$attr]) : $data[$attr];
                } else {
                    $rowProduct[$attr] = null;
                }
            }

            //dump('записать в эксель данные товара', $rowProduct);
            $this->writer->add(self::PAGE_NAME_PRODUCTS, $rowProduct);

            // записать варианты
            if (array_key_exists(self::KEY_VARIANTS, $data)) {
                $variants = $data[self::KEY_VARIANTS];
                $sortIndex = 1;
                foreach ($variants as $varSku => $values) {
                    $values['Название товара'] = $product->getSku();
                    $values['Артикул'] = $varSku;
                    $values['Название'] = $varSku;
                    $values['Сортировка'] = $sortIndex++;

                    // пройти по всем аттрибутам
                    $rowVariant = [];
                    foreach ($variantsAttrtibutes as $attr) {
                        if (array_key_exists($attr, $values)) {
                            $rowVariant[$attr] = is_array($values[$attr]) ? implode(';', $values[$attr]) : $values[$attr];
                        } else {
                            $rowVariant[$attr] = null;
                        }
                    }

                    $this->writer->add(self::PAGE_NAME_VARIANTS, $rowVariant);
                }
            }

            $this->saver->detach($product);
        }
    }
}
