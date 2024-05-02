<?php

namespace App\Command;

use App\Repository\CategoryRepository;
use App\Repository\ExtractedDataRepository;
use App\Repository\ProductAttributeRepository;
use App\Repository\ProductRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'parser:clear-data',
    description: 'Удаление данных',
)]
class ParserClearDataCommand extends Command
{
    public function __construct(
        private ExtractedDataRepository    $extractedDataRepository,
        private ProductAttributeRepository $attributeRepository,
        private ProductRepository          $productRepository,
        private CategoryRepository         $categoryRepository,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->extractedDataRepository->deleteAll();
        $this->productRepository->deleteAll();
        $this->categoryRepository->deleteAll();
        $this->attributeRepository->deleteAll();

        $io->success('Данные удалены');

        return Command::SUCCESS;
    }
}
