<?php

namespace App\EventListener\ProductPagePostProcessed;

use App\Doctrine\Saver;
use App\Entity\ExtractedData;
use App\Event\ProductPagePostProcessedEvent;
use App\Repository\ExtractedDataRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class SaveDownloadedUrlDataEventListener
{
    //private const BATCH_SIZE = 10;
    //private int $i = 1;

    public function __construct(
        private ExtractedDataRepository $extractedDataRepository,
        private Saver                   $saver,
    )
    {
    }

    public function __invoke(ProductPagePostProcessedEvent $event): void
    {
        $data = $event->getData();

        $downloadedUrlEntity = (new ExtractedData())
            ->setUrl($data->getUrl())
            ->setData($data->toArray())
            ->setStatus(ExtractedData::STATUS_OK);

        $this->saver->save($downloadedUrlEntity, true);
        $this->saver->detach($downloadedUrlEntity);

        //$this->entityManager->persist($downloadedUrlEntity);
        //$this->entityManager->flush();
        //$this->entityManager->detach($downloadedUrlEntity);
        //dump('4');

        //if ($this->i % self::BATCH_SIZE === 0) {
        //    $this->saver->clear();
        //}
        //$this->i = $this->i + 1;
    }
}
