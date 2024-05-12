<?php

namespace App\Command;

use App\Parser\KingTonyComParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'parser:kingtony',
    description: 'kingtony.com - website parser',
)]
class ParserKingTonyCommand extends Command
{
    public function __construct(
        private KingTonyComParser $parser,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        date_default_timezone_set('Europe/Moscow');

        $timeStart = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));

        $io = new SymfonyStyle($input, $output);
        $io->info('START ' . $timeStart->format('d-m-Y, H:i:s'));

        $this->parser->parse();

        $writer = $this->parser->getWriter();
        $io->writeln(sprintf('Запись результатов XLS-файл - %s%s', $writer->getFilepath(), $writer->getFilename()));
        $writer->finish();

        $timeFinish = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Moscow'));
        $timeDifference = $timeFinish->diff($timeStart);
        $io->info('Времы выполнения команды: ' . $timeDifference->format('%H часов  %i минут'));

        return Command::SUCCESS;
    }
}
