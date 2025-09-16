<?php

namespace App\Repository;

use App\Entity\Species;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Species>
 */
class SpeciesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Species::class);
    }

    /**
     * Find all species with their races count
     */
    public function findAllWithRaceCount(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.races', 'r')
            ->groupBy('s.id')
            ->addSelect('COUNT(r.id) as HIDDEN raceCount')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find species by name (case insensitive search)
     */
    public function findByNameSearch(string $search): array
    {
        return $this->createQueryBuilder('s')
            ->where('LOWER(s.name) LIKE LOWER(:search)')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get species statistics
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) as speciesCount')
            ->leftJoin('s.races', 'r')
            ->addSelect('COUNT(r.id) as totalRaces')
            ->leftJoin('s.characters', 'c')
            ->addSelect('COUNT(c.id) as totalCharacters');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Find species with most races
     */
    public function findSpeciesWithMostRaces(int $limit = 5): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.races', 'r')
            ->groupBy('s.id')
            ->orderBy('COUNT(r.id)', 'DESC')
            ->addOrderBy('s.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}