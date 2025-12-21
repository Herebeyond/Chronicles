<?php

namespace App\Repository;

use App\Entity\WorldEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorldEvent>
 */
class WorldEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorldEvent::class);
    }

    /**
     * Find all events ordered chronologically
     */
    public function findAllChronological(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.startYear', 'ASC')
            ->addOrderBy('e.startMonth', 'ASC')
            ->addOrderBy('e.startDay', 'ASC')
            ->addOrderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all events ordered by display order (alias for chronological)
     */
    public function findAllByDisplayOrder(): array
    {
        return $this->findAllChronological();
    }

    /**
     * Get the date range of all events
     */
    public function getDateRange(): array
    {
        $qb = $this->createQueryBuilder('e');
        
        $minDate = $qb->select('MIN(e.startYear) as minYear')
            ->getQuery()
            ->getSingleScalarResult();
            
        $maxYear = $qb->select('MAX(e.endYear) as maxYear')
            ->getQuery()
            ->getSingleScalarResult();
            
        // If no end year, use the latest start year
        if ($maxYear === null) {
            $maxYear = $qb->select('MAX(e.startYear) as maxYear')
                ->getQuery()
                ->getSingleScalarResult();
        }
        
        return [
            'minYear' => $minDate ?? 0,
            'maxYear' => $maxYear ?? 0
        ];
    }
}
