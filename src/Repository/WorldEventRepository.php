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
        
        // Get max end year from completed events
        $maxEndYear = $qb->select('MAX(e.endYear) as maxYear')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Get max start year (for ongoing events)
        $maxStartYear = $qb->select('MAX(e.startYear) as maxYear')
            ->getQuery()
            ->getSingleScalarResult();
        
        // Use the greater of the two
        $maxYear = max($maxEndYear ?? 0, $maxStartYear ?? 0);
        
        // Check if custom "present date" is set
        $configFile = dirname(__DIR__, 2) . '/config/timeline_settings.json';
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            if (isset($config['present_year'])) {
                // Use the defined present date as the timeline end
                $maxYear = max($maxYear, $config['present_year']);
            } else {
                // Fallback: add 200 years for ongoing events
                $maxYear += 200;
            }
        } else {
            // Default: add 200 years for ongoing events
            $maxYear += 200;
        }
        
        return [
            'minYear' => $minDate ?? 0,
            'maxYear' => $maxYear
        ];
    }
}
