<?php

namespace App\Entity;

use App\Repository\CharacterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterRepository::class)]
#[ORM\Table(name: 'characters')]
class Character
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthplace = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $occupation = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $traits = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $background = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'characters')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Species $species = null;

    #[ORM\ManyToOne(inversedBy: 'characters')]
    private ?Race $race = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->traits = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(?string $birthplace): static
    {
        $this->birthplace = $birthplace;

        return $this;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function setOccupation(?string $occupation): static
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getTraits(): ?array
    {
        return $this->traits;
    }

    public function setTraits(?array $traits): static
    {
        $this->traits = $traits;

        return $this;
    }

    public function addTrait(string $trait): static
    {
        if (!in_array($trait, $this->traits ?? [])) {
            $this->traits[] = $trait;
        }

        return $this;
    }

    public function removeTrait(string $trait): static
    {
        $traits = $this->traits ?? [];
        $key = array_search($trait, $traits);
        if ($key !== false) {
            unset($traits[$key]);
            $this->traits = array_values($traits);
        }

        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): static
    {
        $this->background = $background;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getSpecies(): ?Species
    {
        return $this->species;
    }

    public function setSpecies(?Species $species): static
    {
        $this->species = $species;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }

    /**
     * Get the character's full species and race designation
     */
    public function getFullSpeciesName(): string
    {
        $species = $this->species?->getName() ?? 'Unknown Species';
        $race = $this->race?->getName();
        
        return $race ? "$species - $race" : $species;
    }

    /**
     * Get character age display with appropriate formatting
     */
    public function getAgeDisplay(): string
    {
        if ($this->age === null) {
            return 'Age inconnu';
        }

        return $this->age . ' ans';
    }

    /**
     * Check if character has a specific trait
     */
    public function hasTrait(string $trait): bool
    {
        return in_array($trait, $this->traits ?? []);
    }
}