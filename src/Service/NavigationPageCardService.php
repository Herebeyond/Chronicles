<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class NavigationPageCardService
{
    private const CONFIG_FILE = 'var/navigation-page-cards.json';
    private const IMAGE_DIR = 'public/images/navigation-pages';

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @param array<int, array<string, string>> $pages
     * @return array<int, array<string, mixed>>
     */
    public function hydratePages(array $pages): array
    {
        $config = $this->loadConfig();

        return array_map(function (array $page) use ($config): array {
            $pageKey = $page['key'];
            $pageConfig = $config[$pageKey] ?? [];

            $crop = [
                'x' => $this->clamp((float) ($pageConfig['crop']['x'] ?? 0), 0, 100),
                'y' => $this->clamp((float) ($pageConfig['crop']['y'] ?? 0), 0, 100),
                'size' => $this->clamp((float) ($pageConfig['crop']['size'] ?? 100), 10, 100),
            ];

            $image = (string) ($pageConfig['image'] ?? '');

            return [
                ...$page,
                'cardImage' => $image,
                'cardImageUrl' => $image !== '' ? 'images/navigation-pages/' . $image : null,
                'cardCrop' => $crop,
                'cardBackgroundStyle' => $this->buildBackgroundStyle($image, $crop),
            ];
        }, $pages);
    }

    /**
     * @param array{ x: float, y: float, size: float } $crop
     */
    public function updatePage(string $pageKey, ?UploadedFile $imageFile, array $crop): void
    {
        $config = $this->loadConfig();

        if (!isset($config[$pageKey])) {
            $config[$pageKey] = [];
        }

        if ($imageFile instanceof UploadedFile) {
            $this->ensureImageDirExists();

            $oldImage = (string) ($config[$pageKey]['image'] ?? '');
            // Always save as JPEG square so display never distorts
            $newFileName = $pageKey . '-' . bin2hex(random_bytes(6)) . '.jpg';
            $destPath = $this->imageDirectoryPath() . DIRECTORY_SEPARATOR . $newFileName;

            $this->cropAndSaveSquare($imageFile->getPathname(), (string) $imageFile->getMimeType(), $destPath, $crop);

            $config[$pageKey]['image'] = $newFileName;

            if ($oldImage !== '' && $oldImage !== $newFileName) {
                $oldImagePath = $this->imageDirectoryPath() . DIRECTORY_SEPARATOR . $oldImage;
                if (is_file($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }
        }

        // Persist crop params so the editor can restore state on re-open
        $size = $this->clamp((float) ($crop['size'] ?? 100), 10, 100);
        $maxOffset = 100 - $size;

        $config[$pageKey]['crop'] = [
            'x' => $this->clamp((float) ($crop['x'] ?? 0), 0, $maxOffset),
            'y' => $this->clamp((float) ($crop['y'] ?? 0), 0, $maxOffset),
            'size' => $size,
        ];

        $this->saveConfig($config);
    }

    /**
     * Crop the source image to a square region then scale it to 600×600 JPEG.
     *
     * @param array{ x: float, y: float, size: float } $crop
     */
    private function cropAndSaveSquare(string $sourcePath, string $mimeType, string $destPath, array $crop): void
    {
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            throw new \RuntimeException('Cannot read image dimensions.');
        }

        $imageWidth = (int) $imageInfo[0];
        $imageHeight = (int) $imageInfo[1];

        $source = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png'  => imagecreatefrompng($sourcePath),
            'image/gif'  => imagecreatefromgif($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default      => throw new \RuntimeException('Unsupported image type: ' . $mimeType),
        };

        if ($source === false) {
            throw new \RuntimeException('Failed to load source image.');
        }

        // Square side = crop.size % of the shortest edge (mirrors JS editor logic)
        $size       = max(1.0, (float) ($crop['size'] ?? 100));
        $minEdge    = min($imageWidth, $imageHeight);
        $squarePx   = max(1, (int) round(($size / 100) * $minEdge));
        $maxOffset  = max(0.0001, 100 - $size);
        $cropX      = (float) ($crop['x'] ?? 0);
        $cropY      = (float) ($crop['y'] ?? 0);

        $srcX = (int) round(($cropX / $maxOffset) * ($imageWidth - $squarePx));
        $srcY = (int) round(($cropY / $maxOffset) * ($imageHeight - $squarePx));
        $srcX = max(0, min($srcX, $imageWidth - $squarePx));
        $srcY = max(0, min($srcY, $imageHeight - $squarePx));

        $outputSize = 600;
        $output = imagecreatetruecolor($outputSize, $outputSize);

        if ($output === false) {
            imagedestroy($source);
            throw new \RuntimeException('Failed to create output image canvas.');
        }

        imagecopyresampled($output, $source, 0, 0, $srcX, $srcY, $outputSize, $outputSize, $squarePx, $squarePx);
        imagejpeg($output, $destPath, 92);

        imagedestroy($source);
        imagedestroy($output);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadConfig(): array
    {
        $configPath = $this->configPath();
        if (!is_file($configPath)) {
            return [];
        }

        $json = file_get_contents($configPath);
        if ($json === false || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, array<string, mixed>> $config
     */
    private function saveConfig(array $config): void
    {
        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('Failed to encode navigation page card configuration.');
        }

        $configDir = dirname($this->configPath());
        if (!is_dir($configDir)) {
            mkdir($configDir, 0775, true);
        }

        file_put_contents($this->configPath(), $json);
    }

    /**
     * The saved image is already a 600×600 square crop, so we just need cover.
     *
     * @param array{ x: float, y: float, size: float } $crop
     */
    private function buildBackgroundStyle(string $image, array $crop): string
    {
        if ($image === '') {
            return '';
        }

        return sprintf(
            'background-image: url("/images/navigation-pages/%s"); background-size: cover; background-position: center;',
            $image
        );
    }

    private function configPath(): string
    {
        return $this->projectDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE;
    }

    private function imageDirectoryPath(): string
    {
        return $this->projectDir . DIRECTORY_SEPARATOR . self::IMAGE_DIR;
    }

    private function ensureImageDirExists(): void
    {
        $path = $this->imageDirectoryPath();
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
