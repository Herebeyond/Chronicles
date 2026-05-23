<?php

namespace App\Repository;

use App\Entity\Map;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Map>
 */
class MapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Map::class);
    }

    /**
     * Find all maps with their point counts
     * 
     * @return array<Map>
     */
    public function findAllWithPointCounts(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m', 'COUNT(ip.id) as pointCount')
            ->leftJoin('m.interestPoints', 'ip')
            ->groupBy('m.id')
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find maps by name search
     * 
     * @return array<Map>
     */
    public function findBySearch(?string $search): array
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.name', 'ASC');

        if ($search) {
            $qb->andWhere('m.name LIKE :search OR m.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find the first map (default map)
     */
    public function findDefault(): ?Map
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
