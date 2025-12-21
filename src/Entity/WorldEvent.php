<?php

namespace App\Entity;

use App\Repository\WorldEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorldEventRepository::class)]
#[ORM\Table(name: 'world_events')]
class WorldEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $startYear = null;

    #[ORM\Column]
    private ?int $startMonth = null;

    #[ORM\Column]
    private ?int $startDay = null;

    #[ORM\Column(nullable: true)]
    private ?int $endYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $endMonth = null;

    #[ORM\Column(nullable: true)]
    private ?int $endDay = null;

    #[ORM\Column(length: 7, options: ['default' => '#3498db'])]
    private string $color = '#3498db';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $significance = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
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

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function setStartYear(int $startYear): static
    {
        $this->startYear = $startYear;
        return $this;
    }

    public function getStartMonth(): ?int
    {
        return $this->startMonth;
    }

    public function setStartMonth(int $startMonth): static
    {
        $this->startMonth = $startMonth;
        return $this;
    }

    public function getStartDay(): ?int
    {
        return $this->startDay;
    }

    public function setStartDay(int $startDay): static
    {
        $this->startDay = $startDay;
        return $this;
    }

    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    public function setEndYear(?int $endYear): static
    {
        $this->endYear = $endYear;
        return $this;
    }

    public function getEndMonth(): ?int
    {
        return $this->endMonth;
    }

    public function setEndMonth(?int $endMonth): static
    {
        $this->endMonth = $endMonth;
        return $this;
    }

    public function getEndDay(): ?int
    {
        return $this->endDay;
    }

    public function setEndDay(?int $endDay): static
    {
        $this->endDay = $endDay;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getSignificance(): ?string
    {
        return $this->significance;
    }

    public function setSignificance(?string $significance): static
    {
        $this->significance = $significance;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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

    /**
     * Check if this is an ongoing event (no end date)
     */
    public function isOngoing(): bool
    {
        return $this->endYear === null;
    }

    /**
     * Get formatted start date string
     */
    public function getFormattedStartDate(): string
    {
        return sprintf('%d-%02d-%02d', $this->startYear, $this->startMonth, $this->startDay);
    }

    /**
     * Get formatted end date string
     */
    public function getFormattedEndDate(): ?string
    {
        if ($this->isOngoing()) {
            return null;
        }
        return sprintf('%d-%02d-%02d', $this->endYear, $this->endMonth, $this->endDay);
    }

    /**
     * Get start date as comparable integer (YYYYMMDD)
     */
    public function getStartDateValue(): int
    {
        return ($this->startYear * 10000) + ($this->startMonth * 100) + $this->startDay;
    }

    /**
     * Get end date as comparable integer (YYYYMMDD), or a large number for ongoing events
     */
    public function getEndDateValue(): int
    {
        if ($this->isOngoing()) {
            return 99999999; // Ongoing events sort last
        }
        return ($this->endYear * 10000) + ($this->endMonth * 100) + $this->endDay;
    }
}
