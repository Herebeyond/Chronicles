<?php

namespace App\Repository;

use App\Entity\InterestPointType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InterestPointType>
 */
class InterestPointTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterestPointType::class);
    }

    /**
     * Find all types ordered by name
     * 
     * @return array<InterestPointType>
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find types with point counts
     * 
     * @return array<InterestPointType>
     */
    public function findAllWithPointCounts(): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'COUNT(ip.id) as pointCount')
            ->leftJoin('t.interestPoints', 'ip')
            ->groupBy('t.id')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if type is in use by any points
     */
    public function isTypeInUse(InterestPointType $type): bool
    {
        $count = $this->createQueryBuilder('t')
            ->select('COUNT(ip.id)')
            ->leftJoin('t.interestPoints', 'ip')
            ->where('t.id = :id')
            ->setParameter('id', $type->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Find type by name
     */
    public function findByName(string $name): ?InterestPointType
    {
        return $this->createQueryBuilder('t')
            ->where('t.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
