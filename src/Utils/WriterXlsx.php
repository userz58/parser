<?php

namespace App\Utils;

/**
 * Записыватель в эксель файл
 */
class WriterXlsx
{
    private string $filename = 'results.xlsx';

    private string $filepath = '/app/public/';

    private ?\XLSXWriter $writer = null;

    public function __construct()
    {
        $this->create();
    }

    public function create(): void
    {
        $this->writer = new \XLSXWriter();
    }

    public function add(string $page, array $data, array $row_options = []): void
    {
        $this->writer->writeSheetRow($page, $data, $row_options);
    }

    public function finish(): void
    {
        $path = sprintf('%s%s', $this->filepath, $this->filename);
        $this->writer->writeToFile($path);
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }
}
