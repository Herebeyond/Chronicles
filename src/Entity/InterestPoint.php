<?php

namespace App\Entity;

use App\Repository\InterestPointRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InterestPointRepository::class)]
#[ORM\Table(name: 'interest_points')]
class InterestPoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $xCoordinate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 6)]
    private ?string $yCoordinate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherNames = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mainImage = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $gallery = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'interestPoints')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Map $map = null;

    #[ORM\ManyToOne(inversedBy: 'interestPoints')]
    private ?InterestPointType $type = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getXCoordinate(): ?string
    {
        return $this->xCoordinate;
    }

    public function setXCoordinate(string $xCoordinate): static
    {
        $this->xCoordinate = $xCoordinate;

        return $this;
    }

    public function getYCoordinate(): ?string
    {
        return $this->yCoordinate;
    }

    public function setYCoordinate(string $yCoordinate): static
    {
        $this->yCoordinate = $yCoordinate;

        return $this;
    }

    public function getOtherNames(): ?string
    {
        return $this->otherNames;
    }

    public function setOtherNames(?string $otherNames): static
    {
        $this->otherNames = $otherNames;

        return $this;
    }

    public function getMainImage(): ?string
    {
        return $this->mainImage;
    }

    public function setMainImage(?string $mainImage): static
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    public function getGallery(): ?array
    {
        return $this->gallery;
    }

    public function setGallery(?array $gallery): static
    {
        $this->gallery = $gallery;

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

    public function getMap(): ?Map
    {
        return $this->map;
    }

    public function setMap(?Map $map): static
    {
        $this->map = $map;

        return $this;
    }

    public function getType(): ?InterestPointType
    {
        return $this->type;
    }

    public function setType(?InterestPointType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get a URL-friendly slug from the name
     */
    public function getSlug(): string
    {
        $slug = strtolower(trim($this->name ?? ''));
        // Normalize accented characters
        $slug = preg_replace('/[àáâãäåæ]/u', 'a', $slug);
        $slug = preg_replace('/[èéêë]/u', 'e', $slug);
        $slug = preg_replace('/[ìíîï]/u', 'i', $slug);
        $slug = preg_replace('/[òóôõöø]/u', 'o', $slug);
        $slug = preg_replace('/[ùúûü]/u', 'u', $slug);
        $slug = preg_replace('/[ýÿ]/u', 'y', $slug);
        $slug = preg_replace('/[ñ]/u', 'n', $slug);
        $slug = preg_replace('/[ç]/u', 'c', $slug);
        $slug = preg_replace('/[œ]/u', 'oe', $slug);
        // Replace non-alphanumeric with hyphens
        $slug = preg_replace('/[^a-z0-9]/', '-', $slug);
        // Clean up multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Get the folder path for this place's images
     */
    public function getFolderPath(): string
    {
        return 'images/places/' . $this->getSlug() . '/';
    }

    /**
     * Get other names as array
     */
    public function getOtherNamesArray(): array
    {
        if (empty($this->otherNames)) {
            return [];
        }
        return array_filter(array_map('trim', explode('|', $this->otherNames)));
    }

    /**
     * Set other names from array
     */
    public function setOtherNamesFromArray(array $names): static
    {
        $this->otherNames = implode('|', array_filter(array_map('trim', $names)));
        return $this;
    }

    /**
     * Add an image to the gallery
     * @param string $filename The filename of the uploaded image
     * @param string|null $name Optional display name (defaults to filename without extension)
     */
    public function addGalleryImage(string $filename, ?string $name = null): static
    {
        $gallery = $this->gallery ?? [];
        
        if ($name === null) {
            // Use filename without extension as default name
            $name = pathinfo($filename, PATHINFO_FILENAME);
            // Clean up the name (replace underscores/dashes with spaces)
            $name = str_replace(['_', '-'], ' ', $name);
            $name = ucfirst($name);
        }
        
        $gallery[] = [
            'filename' => $filename,
            'name' => $name,
        ];
        
        $this->gallery = $gallery;
        return $this;
    }

    /**
     * Remove an image from the gallery by filename
     */
    public function removeGalleryImage(string $filename): static
    {
        if ($this->gallery === null) {
            return $this;
        }
        
        $this->gallery = array_values(array_filter($this->gallery, function($item) use ($filename) {
            return ($item['filename'] ?? $item) !== $filename;
        }));
        
        return $this;
    }

    /**
     * Update a gallery image's name
     */
    public function updateGalleryImageName(string $filename, string $newName): static
    {
        if ($this->gallery === null) {
            return $this;
        }
        
        foreach ($this->gallery as &$item) {
            if (is_array($item) && ($item['filename'] ?? null) === $filename) {
                $item['name'] = $newName;
                break;
            }
        }
        
        $this->gallery = $this->gallery; // Trigger update
        return $this;
    }

    /**
     * Get gallery as normalized array (ensures all items have filename and name)
     */
    public function getGalleryNormalized(): array
    {
        if ($this->gallery === null) {
            return [];
        }
        
        return array_map(function($item) {
            if (is_string($item)) {
                // Legacy format: just filename
                $name = pathinfo($item, PATHINFO_FILENAME);
                $name = str_replace(['_', '-'], ' ', $name);
                return [
                    'filename' => $item,
                    'name' => ucfirst($name),
                ];
            }
            return $item;
        }, $this->gallery);
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
