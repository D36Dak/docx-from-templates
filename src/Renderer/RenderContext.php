<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Renderer;

use RuntimeException;

/**
 * @internal
 */
final class RenderContext
{
    private const IMAGE_RELATIONSHIP_TYPE = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image';
    private const HEADER_RELATIONSHIP_TYPE =
        'http://schemas.openxmlformats.org/officeDocument/2006/relationships/header';
    private const FOOTER_RELATIONSHIP_TYPE =
        'http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer';

    /** @var int starting at 5 because 1-4 are already reserved for other resources */
    private int $nextRelationshipNumber = 5;
    private int $nextImageNumber = 1;
    private int $nextHeaderNumber = 1;
    private int $nextFooterNumber = 1;

    /** @var array<string, string> target path => source path */
    private array $images = [];

    /** @var array<string, array{type: string, target: string}> relationship id => relationship data */
    private array $relationships = [];

    /** @var array<string, string> target path => XML contents */
    private array $headers = [];

    /** @var array<string, string> target path => XML contents */
    private array $footers = [];

    /**
     * @param string $imagePath
     * @return array{
     *     'relationshipId': string,
     *     'imageNumber': int,
     * }
     */
    public function addImage(string $imagePath): array
    {
        if (!is_file($imagePath)) {
            throw new RuntimeException(sprintf('Image file does not exist: %s', $imagePath));
        }

        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        if ($extension === '') {
            throw new RuntimeException(sprintf('Image file has no extension: %s', $imagePath));
        }

        $imageNumber = $this->nextImageNumber++;
        $targetPath = 'media/image' . $imageNumber . '.' . $extension;
        $relationshipId = $this->addRelationship(self::IMAGE_RELATIONSHIP_TYPE, $targetPath);

        $this->images[$targetPath] = $imagePath;

        return ['relationshipId' => $relationshipId, 'imageNumber' => $imageNumber];
    }

    public function addHeader(string $xml): string
    {
        $targetPath = 'header' . $this->nextHeaderNumber++ . '.xml';
        $relationshipId = $this->addRelationship(self::HEADER_RELATIONSHIP_TYPE, $targetPath);
        $this->headers[$targetPath] = $this->wrapHeaderFooterXml('hdr', $xml);

        return $relationshipId;
    }

    public function addFooter(string $xml): string
    {
        $targetPath = 'footer' . $this->nextFooterNumber++ . '.xml';
        $relationshipId = $this->addRelationship(self::FOOTER_RELATIONSHIP_TYPE, $targetPath);
        $this->footers[$targetPath] = $this->wrapHeaderFooterXml('ftr', $xml);

        return $relationshipId;
    }

    /**
     * @return array<string, string>
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return array<string, array{type: string, target: string}>
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array<string, string>
     */
    public function getFooters(): array
    {
        return $this->footers;
    }

    private function addRelationship(string $type, string $targetPath): string
    {
        $relationshipId = 'rId' . $this->nextRelationshipNumber++;
        $this->relationships[$relationshipId] = [
            'type' => $type,
            'target' => $targetPath,
        ];

        return $relationshipId;
    }

    private function wrapHeaderFooterXml(string $elementName, string $xml): string
    {
        return sprintf(
            '<w:%1$s xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">%2$s</w:%1$s>',
            $elementName,
            $xml
        );
    }
}
