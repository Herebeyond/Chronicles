<?php

namespace App\Entity;

use App\Repository\IdeaRepository;
use App\Validator\NoCircularParentReference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IdeaRepository::class)]
#[ORM\Table(name: 'ideas')]
#[NoCircularParentReference]
class Idea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_idea')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 100)]
    private ?string $category = null;

    #[ORM\Column(length: 50)]
    private ?string $certaintyLevel = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = 'Draft';

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tags = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_idea_id', referencedColumnName: 'id_idea', onDelete: 'SET NULL')]
    private ?self $parentIdea = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parentIdea')]
    private Collection $children;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comments = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $inspirationSource = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $priority = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getCertaintyLevel(): ?string
    {
        return $this->certaintyLevel;
    }

    public function setCertaintyLevel(string $certaintyLevel): static
    {
        $this->certaintyLevel = $certaintyLevel;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    public function getParentIdea(): ?self
    {
        return $this->parentIdea;
    }

    public function setParentIdea(?self $parentIdea): static
    {
        $this->parentIdea = $parentIdea;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParentIdea($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParentIdea() === $this) {
                $child->setParentIdea(null);
            }
        }

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): static
    {
        $this->comments = $comments;
        return $this;
    }

    public function getInspirationSource(): ?string
    {
        return $this->inspirationSource;
    }

    public function setInspirationSource(?string $inspirationSource): static
    {
        $this->inspirationSource = $inspirationSource;
        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): static
    {
        $this->priority = $priority;
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

    /**
     * Update the updatedAt timestamp
     */
    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get count of child ideas
     */
    public function getChildCount(): int
    {
        return $this->children->count();
    }

    /**
     * Check if this idea has children
     */
    public function hasChildren(): bool
    {
        return !$this->children->isEmpty();
    }

    /**
     * Get all descendant IDs (children, grandchildren, etc.)
     * Used to prevent circular parent-child relationships
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        
        foreach ($this->children as $child) {
            $ids[] = $child->getId();
            // Recursively get all descendants
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        
        return array_unique($ids);
    }

    /**
     * Get formatted tags as string
     */
    public function getTagsAsString(): string
    {
        if (!$this->tags) {
            return '';
        }
        return implode(', ', $this->tags);
    }

    /**
     * Set tags from string
     */
    public function setTagsFromString(?string $tagsString): static
    {
        if (empty($tagsString)) {
            $this->tags = null;
            return $this;
        }

        $tags = array_map('trim', explode(',', $tagsString));
        $tags = array_filter($tags); // Remove empty values
        $this->tags = empty($tags) ? null : array_values($tags);
        
        return $this;
    }
}
