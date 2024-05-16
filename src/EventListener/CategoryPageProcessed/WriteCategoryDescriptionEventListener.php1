<?php

namespace App\EventListener\CategoryPageProcessed;

use App\Event\CategoryPageProcessedEvent;
use App\Utils\WriterXlsx;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class WriteCategoryDescriptionEventListener
{
    const PAGE_NAME = 'Категории (описание)';

    public function __construct(
        private WriterXlsx $writer,
    )
    {
        $this->writer->add(self::PAGE_NAME, [
            'hash',
            'Название',
            'Краткое описание',
            'Описание',
        ]);
    }

    public function __invoke(CategoryPageProcessedEvent $event): void
    {
        $data = $event->getData();
        $values = $data->toArray();

        $this->writer->add(self::PAGE_NAME, [
            'hash' => sha1($data->getUrl()),
            'Название' => $values['Название'],
            'Краткое описание' => isset($values['Краткое описание']) ? $values['Краткое описание'] : null,
            'Описание' => $values['Описание'],
        ]);
    }
}
