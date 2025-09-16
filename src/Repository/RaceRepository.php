<?php

namespace App\Repository;

use App\Entity\Race;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Race>
 */
class RaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Race::class);
    }

    /**
     * Find races by species ID
     */
    public function findBySpecies(int $speciesId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.species = :speciesId')
            ->setParameter('speciesId', $speciesId)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find races by name search within a species
     */
    public function findByNameAndSpecies(string $search, int $speciesId): array
    {
        return $this->createQueryBuilder('r')
            ->where('LOWER(r.name) LIKE LOWER(:search)')
            ->andWhere('r.species = :speciesId')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('speciesId', $speciesId)
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count races per species
     */
    public function countBySpecies(): array
    {
        return $this->createQueryBuilder('r')
            ->select('IDENTITY(r.species) as speciesId, COUNT(r.id) as raceCount')
            ->groupBy('r.species')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find races with character count
     */
    public function findAllWithCharacterCount(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.characters', 'c')
            ->leftJoin('r.species', 's')
            ->addSelect('s')
            ->groupBy('r.id')
            ->addSelect('COUNT(c.id) as HIDDEN characterCount')
            ->orderBy('s.name', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}