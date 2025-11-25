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

    /**
     * Find all species with related counts for table view
     */
    public function findAllWithRelatedCounts(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.races', 'r')
            ->leftJoin('s.characters', 'c')
            ->addSelect('r', 'c')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find species with full details
     */
    public function findWithFullDetails(int $id): ?object
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.races', 'r')
            ->leftJoin('s.characters', 'c')
            ->addSelect('r', 'c')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all species with race count and pagination
     */
    public function findAllWithRaceCountPaginated(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        
        return $this->createQueryBuilder('s')
            ->leftJoin('s.races', 'r')
            ->groupBy('s.id')
            ->addSelect('COUNT(r.id) as HIDDEN raceCount')
            ->orderBy('s.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find species by search term and filter with pagination
     */
    public function findBySearchAndFilter(string $searchTerm = '', string $filterSpecie = '', int $page = 1, int $perPage = 12): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.races', 'r')
            ->groupBy('s.id')
            ->addSelect('COUNT(r.id) as HIDDEN raceCount');

        // Add search conditions
        if (!empty($searchTerm)) {
            $qb->where('LOWER(s.name) LIKE LOWER(:searchTerm) OR LOWER(s.description) LIKE LOWER(:searchTerm)')
               ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if (!empty($filterSpecie)) {
            if (!empty($searchTerm)) {
                $qb->andWhere('s.name = :filterSpecie');
            } else {
                $qb->where('s.name = :filterSpecie');
            }
            $qb->setParameter('filterSpecie', $filterSpecie);
        }

        $offset = ($page - 1) * $perPage;
        
        return $qb->orderBy('s.name', 'ASC')
                  ->setFirstResult($offset)
                  ->setMaxResults($perPage)
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Count species by search term and filter
     */
    public function countBySearchAndFilter(string $searchTerm = '', string $filterSpecie = ''): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)');

        // Add search conditions
        if (!empty($searchTerm)) {
            $qb->where('LOWER(s.name) LIKE LOWER(:searchTerm) OR LOWER(s.description) LIKE LOWER(:searchTerm)')
               ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        if (!empty($filterSpecie)) {
            if (!empty($searchTerm)) {
                $qb->andWhere('s.name = :filterSpecie');
            } else {
                $qb->where('s.name = :filterSpecie');
            }
            $qb->setParameter('filterSpecie', $filterSpecie);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get all species names for dropdown filter
     */
    public function findAllNames(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.name')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}