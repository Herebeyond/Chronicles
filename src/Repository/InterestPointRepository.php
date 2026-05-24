<?php

namespace App\Repository;

use App\Entity\InterestPoint;
use App\Entity\Map;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InterestPoint>
 */
class InterestPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterestPoint::class);
    }

    /**
     * Find all points for a specific map
     * 
     * @return array<InterestPoint>
     */
    public function findByMap(Map $map): array
    {
        return $this->createQueryBuilder('ip')
            ->leftJoin('ip.type', 't')
            ->addSelect('t')
            ->where('ip.map = :map')
            ->setParameter('map', $map)
            ->orderBy('ip.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all points for a map ID
     * 
     * @return array<InterestPoint>
     */
    public function findByMapId(int $mapId): array
    {
        return $this->createQueryBuilder('ip')
            ->leftJoin('ip.type', 't')
            ->addSelect('t')
            ->leftJoin('ip.map', 'm')
            ->addSelect('m')
            ->where('ip.map = :mapId')
            ->setParameter('mapId', $mapId)
            ->orderBy('ip.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find points by search term
     * 
     * @return array<InterestPoint>
     */
    public function findBySearch(?string $search, ?Map $map = null): array
    {
        $qb = $this->createQueryBuilder('ip')
            ->leftJoin('ip.type', 't')
            ->addSelect('t')
            ->leftJoin('ip.map', 'm')
            ->addSelect('m')
            ->orderBy('ip.name', 'ASC');

        if ($search) {
            $qb->andWhere('ip.name LIKE :search OR ip.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($map) {
            $qb->andWhere('ip.map = :map')
               ->setParameter('map', $map);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find point by name (for duplicate checking)
     */
    public function findByName(string $name, ?int $excludeId = null): ?InterestPoint
    {
        $qb = $this->createQueryBuilder('ip')
            ->where('ip.name = :name')
            ->setParameter('name', $name);

        if ($excludeId) {
            $qb->andWhere('ip.id != :id')
               ->setParameter('id', $excludeId);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Delete all points from a specific map
     */
    public function deleteAllFromMap(Map $map): int
    {
        return $this->createQueryBuilder('ip')
            ->delete()
            ->where('ip.map = :map')
            ->setParameter('map', $map)
            ->getQuery()
            ->execute();
    }

    /**
     * Get points formatted for JSON API (for map display)
     * 
     * @return array<array<string, mixed>>
     */
    public function findByMapForApi(int $mapId): array
    {
        $points = $this->findByMapId($mapId);
        $result = [];

        foreach ($points as $point) {
            $result[] = [
                'id' => $point->getId(),
                'name' => $point->getName(),
                'description' => $point->getDescription(),
                'x' => (float) $point->getXCoordinate(),
                'y' => (float) $point->getYCoordinate(),
                'type' => $point->getType() ? $point->getType()->getName() : 'Location',
                'color' => $point->getType() ? $point->getType()->getColor() : '#ff4444',
            ];
        }

        return $result;
    }
}
