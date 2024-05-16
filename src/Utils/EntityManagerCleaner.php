<?php

namespace App\Utils;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerCleaner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function clean(): static
    {
        $this->entityManager->clear();

        gc_collect_cycles();

        return $this;
    }
}
