<?php

namespace App\Entity;

use App\Repository\InterestPointTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterestPointTypeRepository::class)]
#[ORM\Table(name: 'interest_point_types')]
class InterestPointType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 7)]
    private ?string $color = '#ff4444';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, InterestPoint>
     */
    #[ORM\OneToMany(targetEntity: InterestPoint::class, mappedBy: 'type')]
    private Collection $interestPoints;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->interestPoints = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

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

    /**
     * @return Collection<int, InterestPoint>
     */
    public function getInterestPoints(): Collection
    {
        return $this->interestPoints;
    }

    public function addInterestPoint(InterestPoint $interestPoint): static
    {
        if (!$this->interestPoints->contains($interestPoint)) {
            $this->interestPoints->add($interestPoint);
            $interestPoint->setType($this);
        }

        return $this;
    }

    public function removeInterestPoint(InterestPoint $interestPoint): static
    {
        if ($this->interestPoints->removeElement($interestPoint)) {
            // set the owning side to null (unless already changed)
            if ($interestPoint->getType() === $this) {
                $interestPoint->setType(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
