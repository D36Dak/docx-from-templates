<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

/**
 * @internal
 */
class ImageElement extends DocxElement
{
    private const ALIGNMENT_VALUES = [
        'left' => 'left',
        'right' => 'right',
        'center' => 'center',
    ];

    private readonly int $width;
    private readonly int $height;
    private readonly ?string $alignment;
    private readonly ?string $altText;

    /**
     * @param array{
     *     width: int,
     *     height: int,
     *     alignment?: 'left'|'right'|'center',
     *     altText?: string,
     * } $options
     */
    public function __construct(
        private readonly string $imagePath,
        array $options,
    ) {
        $this->width = $this->normalizeDimension($options, 'width');
        $this->height = $this->normalizeDimension($options, 'height');
        $this->alignment = $this->normalizeAlignment($options['alignment'] ?? null);
        $this->altText = $this->normalizeAltText($options['altText'] ?? null);
    }

    public function toXml(RenderContext $context): string
    {
        $renderedImage = $context->addImage($this->imagePath);
        $widthEmu = $this->width * 9525; // Convert pixels to EMUs
        $heightEmu = $this->height * 9525; // Convert pixels to EMUs

        return sprintf(
            '<w:p>%s<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">
            <wp:extent cx="%d" cy="%d"/>
            <wp:effectExtent l="0" t="0" r="0" b="0"/>
            <wp:docPr id="%d" name="Image%d"%s/>
            <wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr>
            <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
            <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:nvPicPr><pic:cNvPr id="%d" name="Image%d"/>
            <pic:cNvPicPr/></pic:nvPicPr>
            <pic:blipFill><a:blip r:embed="%s"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>
            <pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="%d" cy="%d"/></a:xfrm>
            <a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic>
            </a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>',
            $this->renderParagraphProperties(),
            $widthEmu,
            $heightEmu,
            $renderedImage['imageNumber'],
            $renderedImage['imageNumber'],
            $this->renderAltTextAttribute(),
            $renderedImage['imageNumber'],
            $renderedImage['imageNumber'],
            $renderedImage['relationshipId'],
            $widthEmu,
            $heightEmu
        );
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function normalizeDimension(array $options, string $key): int
    {
        if (!array_key_exists($key, $options)) {
            throw new \InvalidArgumentException(sprintf('Image option "%s" is required.', $key));
        }

        $value = $options[$key];
        if (!is_int($value) || $value <= 0) {
            throw new \InvalidArgumentException(sprintf('Image option "%s" must be a positive integer.', $key));
        }

        return $value;
    }

    private function normalizeAlignment(mixed $alignment): ?string
    {
        if ($alignment === null) {
            return null;
        }

        if (!is_string($alignment) || !array_key_exists($alignment, self::ALIGNMENT_VALUES)) {
            throw new \InvalidArgumentException(sprintf(
                'Image alignment must be one of: %s.',
                implode(', ', array_keys(self::ALIGNMENT_VALUES))
            ));
        }

        return self::ALIGNMENT_VALUES[$alignment];
    }

    private function normalizeAltText(mixed $altText): ?string
    {
        if ($altText === null) {
            return null;
        }

        if (!is_string($altText)) {
            throw new \InvalidArgumentException('Image option "altText" must be a string.');
        }

        return $altText;
    }

    private function renderParagraphProperties(): string
    {
        if ($this->alignment === null) {
            return '';
        }

        return sprintf('<w:pPr><w:jc w:val="%s"/></w:pPr>', $this->alignment);
    }

    private function renderAltTextAttribute(): string
    {
        if ($this->altText === null) {
            return '';
        }

        return sprintf(' descr="%s"', htmlspecialchars($this->altText, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
    }
}
