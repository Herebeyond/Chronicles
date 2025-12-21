<?php

namespace App\Repository;

use App\Entity\CalendarMonth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendarMonth>
 */
class CalendarMonthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarMonth::class);
    }

    /**
     * Find all months ordered by month number
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.monthNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total days in year
     */
    public function getTotalDaysInYear(): int
    {
        $result = $this->createQueryBuilder('m')
            ->select('SUM(m.daysCount) as total')
            ->getQuery()
            ->getSingleScalarResult();
            
        return (int) ($result ?? 0);
    }

    /**
     * Get month by number
     */
    public function findByMonthNumber(int $monthNumber): ?CalendarMonth
    {
        return $this->findOneBy(['monthNumber' => $monthNumber]);
    }
}
