<?php

namespace App\Repository;

use App\Entity\IdeaCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IdeaCategory>
 */
class IdeaCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IdeaCategory::class);
    }

    /**
     * Find all categories ordered by name
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find category by name
     */
    public function findByName(string $name): ?IdeaCategory
    {
        return $this->createQueryBuilder('c')
            ->where('c.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get default categories
     */
    public function getDefaultCategories(): array
    {
        return [
            'Other',
            'Magic_Systems',
            'Creatures',
            'Gods_Demons',
            'Dimensions_Realms',
            'Physics_Reality',
            'Races_Beings',
            'Items_Artifacts',
            'Lore_History',
            'Geography',
            'Politics',
            'Technology',
            'Culture',
        ];
    }

    /**
     * Initialize default categories if they don't exist
     */
    public function initializeDefaultCategories(): void
    {
        $defaultCategories = $this->getDefaultCategories();
        $em = $this->getEntityManager();

        foreach ($defaultCategories as $categoryName) {
            $existing = $this->findByName($categoryName);
            if (!$existing) {
                $category = new IdeaCategory();
                $category->setName($categoryName);
                $category->setIsDefault(true);
                $em->persist($category);
            }
        }

        $em->flush();
    }
}
