<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Find user by email or username
     */
    public function findByEmailOrUsername(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :identifier OR u.username = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find active users with specific role
     * @param string $role
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.userRoles', 'r')
            ->andWhere('r.name = :role')
            ->andWhere('u.isActive = :active')
            ->setParameter('role', $role)
            ->setParameter('active', true)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users with any roles assigned
     * @return User[]
     */
    public function findUsersWithRoles(): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.userRoles', 'r')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('u.id')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users without any roles (role-less users)
     * @return User[]
     */
    public function findUsersWithoutRoles(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.userRoles', 'r')
            ->andWhere('r.id IS NULL')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count users by role
     */
    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->innerJoin('u.userRoles', 'r')
            ->andWhere('r.name = :role')
            ->andWhere('u.isActive = :active')
            ->setParameter('role', $role)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Search users by name or email
     * @return User[]
     */
    public function searchUsers(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username LIKE :query OR u.email LIKE :query OR u.firstName LIKE :query OR u.lastName LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get user statistics
     * @return array<string, int>
     */
    public function getUserStats(): array
    {
        $totalUsers = $this->count(['isActive' => true]);
        $usersWithRoles = count($this->findUsersWithRoles());
        $usersWithoutRoles = count($this->findUsersWithoutRoles());

        $roleStats = [];
        foreach (User::getAvailableRoles() as $role => $label) {
            $roleStats[$role] = $this->countByRole($role);
        }

        return [
            'total' => $totalUsers,
            'with_roles' => $usersWithRoles,
            'without_roles' => $usersWithoutRoles,
            'roles' => $roleStats,
        ];
    }

    /**
     * Find all users with details for table view
     */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find user with details
     */
    public function findWithDetails(int $id): ?object
    {
        return $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}