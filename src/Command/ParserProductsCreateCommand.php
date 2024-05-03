<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductAttribute;
use App\Repository\CategoryRepository;
use App\Repository\ExtractedDataRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductRepository;
use App\Doctrine\Saver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'parser:products-create',
    description: 'Обработка данных и создание товаров',
)]
class ParserProductsCreateCommand extends Command
{
    private const BATCH_SIZE = 30;
    private const KEY_CATEGORY_HASH = '_category_hash';
    private const KEY_CATEGORY_NAME = '_category_name';

    public function __construct(
        private ExtractedDataRepository    $dataRepository,
        private ProductAttributeRepository $attributeRepository,
        private ProductRepository          $productRepository,
        private CategoryRepository         $categoryRepository,
        private Saver                      $saver,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        //$this->addArgument('parser', InputArgument::REQUIRED, 'Название парсера');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $i = 0;

        // обойти все скачанные ссылки
        $iterator = $this->dataRepository->iterateAll();
        foreach ($iterator as $entity) {
            $i++;
            $data = $entity->getData();

            //--> todo: сохранить все свойства
            $keys = array_keys($data);

            foreach ($keys as $key) {
                if (null !== $attr = $this->attributeRepository->findOneByName($key)) {
                    $this->saver->detach($attr);
                } else {
                    $this->saver->persist(new ProductAttribute($key));
                }
            }

            $this->saver->flush();
            //<-- todo: сохранить все свойства

            //--> todo: найти товар по артикулу, если нет - создать
            $productSku = $data['Артикул'];

            print_r(sprintf("[%d] - %s\n", $i, $productSku));

            if (null === $product = $this->productRepository->findOneBySku($productSku)) {
                // todo: установить свойства для товара
                $product = new Product($productSku, $data);
            }

            // todo: добавить категорию
            $categoryHash = $data[self::KEY_CATEGORY_HASH];
            $categoryName = $data[self::KEY_CATEGORY_NAME];
            if (null === $category = $this->categoryRepository->findOneByHash($categoryHash)) {
                $category = (new Category())
                    ->setHash($data[self::KEY_CATEGORY_HASH])
                    ->setName($data[self::KEY_CATEGORY_NAME]);
                $this->saver->persist($category);
            }

            $category->addProduct($product);

            $this->saver->persist($product);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->saver->flush(true);
            }

            $this->saver->detach($entity);
            //$this->saver->detach($category);
            //$this->saver->detach($product);
        }

        $this->saver->flush(true);

        $io->success('Товары сохранены.');

        return Command::SUCCESS;
    }
}
