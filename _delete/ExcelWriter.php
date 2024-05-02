<?php

namespace App\Writer;

use App\ValueObject\DataInterface;
use \XLSXWriter;
use League\Flysystem\Filesystem;

final class ExcelWriter
{
    protected ?XLSXWriter $writer = null;

    public function __construct(
        private string     $workdir,
        private string     $filepath,
        private Filesystem $filesystem,
    )
    {
        $this->writer = new XLSXWriter();
    }

    // запись данных в эксель файл
    protected function writeRow(DataInterface $data): void
    {
        foreach ($data->getValues() as $values) {
            foreach ($values->toArray() as $value) {
                $this->writer->writeSheetRow($data->getPageName(), $value);
            }
        }
    }

    // запись данных в эксель файл
    protected function finish(): void
    {
        $this->writer->writeToFile($this->filepath);
    }
}
