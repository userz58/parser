<?php

namespace App\Repository;

use App\Entity\ExtractedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExtractedData>
 *
 * @method ExtractedData|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExtractedData|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExtractedData[]    findAll()
 * @method ExtractedData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExtractedDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtractedData::class);
    }

    public function deleteAll()
    {
        $dql = $this->createQueryBuilder('e')->delete()->getDQL();

        return $this->getEntityManager()->createQuery($dql)->execute();
    }

    public function iterateAll(): iterable
    {
        return $this->createQueryBuilder('e')->getQuery()->toIterable([], Query::HYDRATE_SIMPLEOBJECT);
    }

    // https://github.com/doctrine/orm/issues/8410
    public function iterateAll2(): \Generator
    {
        $iterator = $this->createQueryBuilder('e')->getQuery()->toIterable([], Query::HYDRATE_SIMPLEOBJECT);

        foreach ($iterator as $data) {
            yield $data;
        }
    }

    //    /**
    //     * @return ExtractedData[] Returns an array of ExtractedData objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ExtractedData
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
