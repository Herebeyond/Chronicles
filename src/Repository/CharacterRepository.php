<?php

namespace App\Repository;

use App\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Character>
 */
class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    /**
     * Find characters by species
     */
    public function findBySpecies(int $speciesId): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.species', 's')
            ->leftJoin('c.race', 'r')
            ->addSelect('s', 'r')
            ->where('c.species = :speciesId')
            ->setParameter('speciesId', $speciesId)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find characters by race
     */
    public function findByRace(int $raceId): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.species', 's')
            ->leftJoin('c.race', 'r')
            ->addSelect('s', 'r')
            ->where('c.race = :raceId')
            ->setParameter('raceId', $raceId)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search characters by name
     */
    public function findByNameSearch(string $search): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.species', 's')
            ->leftJoin('c.race', 'r')
            ->addSelect('s', 'r')
            ->where('LOWER(c.name) LIKE LOWER(:search)')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find characters with specific trait
     */
    public function findByTrait(string $trait): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.species', 's')
            ->leftJoin('c.race', 'r')
            ->addSelect('s', 'r')
            ->where('JSON_CONTAINS(c.traits, :trait) = 1')
            ->setParameter('trait', json_encode($trait))
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get character statistics
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as totalCharacters')
            ->addSelect('AVG(c.age) as averageAge')
            ->addSelect('SUM(CASE WHEN c.gender = \'male\' THEN 1 ELSE 0 END) as maleCount')
            ->addSelect('SUM(CASE WHEN c.gender = \'female\' THEN 1 ELSE 0 END) as femaleCount')
            ->addSelect('SUM(CASE WHEN c.gender IS NULL OR c.gender NOT IN (\'male\', \'female\') THEN 1 ELSE 0 END) as otherCount');

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Find characters grouped by species
     */
    public function findGroupedBySpecies(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.species', 's')
            ->leftJoin('c.race', 'r')
            ->addSelect('s', 'r')
            ->orderBy('s.name', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find recent characters (last 30 days)
     */
    public function findRecent(int $limit = 10): array
    {
        $thirtyDaysAgo = new \DateTimeImmutable('-30 days');
        
        return $this->createQueryBuilder('c')
            ->leftJoin('c.species', 's')
            ->leftJoin('c.race', 'r')
            ->addSelect('s', 'r')
            ->where('c.createdAt >= :thirtyDaysAgo')
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}