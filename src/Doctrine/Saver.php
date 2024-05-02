<?php

namespace App\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

class Saver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function save(object $entity, bool $flush = true): static
    {
        $this->entityManager->persist($entity);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $this;
    }

    public function flush(bool $clear = false): static
    {
        $this->entityManager->flush();

        if ($clear) {
            $this->entityManager->clear();
            gc_collect_cycles();
        }

        return $this;
    }

    public function persist(object $entity): static
    {
        $this->entityManager->persist($entity);

        return $this;
    }

    public function detach(object $entity): static
    {
        $this->entityManager->detach($entity);

        return $this;
    }

    public function clear(): static
    {
        $this->entityManager->clear();

        gc_collect_cycles();

        return $this;
    }
}
