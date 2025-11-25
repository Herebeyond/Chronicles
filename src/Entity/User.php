<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà pris')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Available roles in the system
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_MODERATOR = 'ROLE_MODERATOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastName = null;

    /**
     * @var array<string> The user roles - can have multiple roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatarFilename = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getFullName(): string
    {
        if ($this->firstName && $this->lastName) {
            return trim($this->firstName . ' ' . $this->lastName);
        }
        return $this->username ?? $this->email ?? 'Utilisateur inconnu';
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return array<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER if they have any roles
        if (!empty($roles) && !in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        
        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Add a role to the user
     */
    public function addRole(string $role): static
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Remove a role from the user
     */
    public function removeRole(string $role): static
    {
        $this->roles = array_diff($this->roles, [$role]);
        return $this;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Check if user is admin (has ROLE_ADMIN or ROLE_SUPER_ADMIN)
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN) || $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    /**
     * Check if user has any roles assigned
     */
    public function hasAnyRoles(): bool
    {
        return !empty($this->roles);
    }

    /**
     * Get role labels for display
     * @return array<string>
     */
    public function getRoleLabels(): array
    {
        $labels = [];
        foreach ($this->getRoles() as $role) {
            $labels[] = match ($role) {
                self::ROLE_SUPER_ADMIN => 'Super Administrateur',
                self::ROLE_ADMIN => 'Administrateur',
                self::ROLE_MODERATOR => 'Modérateur',
                self::ROLE_USER => 'Utilisateur',
                default => ucfirst(strtolower(str_replace(['ROLE_', '_'], ['', ' '], $role)))
            };
        }
        return $labels;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    public function getAvatarFilename(): ?string
    {
        return $this->avatarFilename;
    }

    public function setAvatarFilename(?string $avatarFilename): static
    {
        $this->avatarFilename = $avatarFilename;
        return $this;
    }

    /**
     * Get the full avatar URL or default if none set
     */
    public function getAvatarUrl(): string
    {
        if ($this->avatarFilename) {
            return 'images/user_icon/' . $this->avatarFilename;
        }
        return 'images/icons/default_user_icon.png';
    }

    /**
     * Get all available roles with their labels
     * @return array<string, string>
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_USER => 'Utilisateur',
            self::ROLE_MODERATOR => 'Modérateur',
            self::ROLE_ADMIN => 'Administrateur',
            self::ROLE_SUPER_ADMIN => 'Super Administrateur',
        ];
    }
}