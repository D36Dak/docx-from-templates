<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document;

use D36Dak\DocxBuilder\Document\Elements\DocxElement;
use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;

/**
 * @internal
 */
class DocxDocument
{
    private const PAGE_FORMATS = [
        'a4' => [
            'width' => 11906,
            'height' => 16838,
        ],
        'us-letter' => [
            'width' => 12240,
            'height' => 15840,
        ],
        'legal' => [
            'width' => 12240,
            'height' => 20160,
        ],
    ];

    private const DEFAULT_MARGINS = [
        'top' => 1440,
        'right' => 1440,
        'bottom' => 1440,
        'left' => 1440,
        'header' => 708,
        'footer' => 708,
        'gutter' => 0,
    ];

    /** @var array<DocxElement> */
    private array $elements = [];
    private ?ParagraphElement $defaultHeader = null;
    private ?ParagraphElement $firstPageHeader = null;
    private ?ParagraphElement $defaultFooter = null;
    private ?ParagraphElement $firstPageFooter = null;
    /** @var array{width: int, height: int}|null */
    private ?array $pageSize = null;
    /** @var array{top: int, right: int, bottom: int, left: int, header: int, footer: int, gutter: int}|null */
    private ?array $margins = null;

    /**
     * @param array{
     *     format?: 'a4'|'us-letter'|'legal',
     *     margins?: array{
     *         top?: int,
     *         right?: int,
     *         bottom?: int,
     *         left?: int,
     *     },
     * } $options
     */
    public function __construct(array $options = [])
    {
        if (array_key_exists('format', $options)) {
            $this->pageSize = $this->resolvePageSize($options['format']);
        }

        if (array_key_exists('margins', $options)) {
            $this->margins = $this->resolveMargins($options['margins']);
        }
    }

    public function addElement(DocxElement $element): void
    {
        $this->elements[] = $element;
    }

    public function addHeader(ParagraphElement $header): void
    {
        $this->defaultHeader = $header;
    }

    public function addFirstPageHeader(ParagraphElement $header): void
    {
        $this->firstPageHeader = $header;
    }

    public function addFooter(ParagraphElement $footer): void
    {
        $this->defaultFooter = $footer;
    }

    public function addFirstPageFooter(ParagraphElement $footer): void
    {
        $this->firstPageFooter = $footer;
    }

    public function toXml(RenderContext $context): string
    {
        $headerRelationshipIds = $this->registerHeaders($context);
        $footerRelationshipIds = $this->registerFooters($context);

        $xml = '<w:document'
            . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"'
            . '>';
        $xml .= '<w:body>';
        foreach ($this->elements as $element) {
            $xml .= $element->toXml($context);
        }
        $xml .= $this->renderSectionProperties($headerRelationshipIds, $footerRelationshipIds);
        $xml .= '</w:body>';
        $xml .= '</w:document>';

        return $xml;
    }

    /**
     * @return array{default: string|null, first: string|null}
     */
    private function registerHeaders(RenderContext $context): array
    {
        $defaultRelationshipId = $this->defaultHeader === null
            ? null
            : $context->addHeader($this->defaultHeader->toXml($context));
        $firstRelationshipId = $this->firstPageHeader === null
            ? null
            : $context->addHeader($this->firstPageHeader->toXml($context));

        return [
            'default' => $defaultRelationshipId,
            'first' => $firstRelationshipId ?? $defaultRelationshipId,
        ];
    }

    /**
     * @return array{default: string|null, first: string|null}
     */
    private function registerFooters(RenderContext $context): array
    {
        $defaultRelationshipId = $this->defaultFooter === null
            ? null
            : $context->addFooter($this->defaultFooter->toXml($context));
        $firstRelationshipId = $this->firstPageFooter === null
            ? null
            : $context->addFooter($this->firstPageFooter->toXml($context));

        return [
            'default' => $defaultRelationshipId,
            'first' => $firstRelationshipId ?? $defaultRelationshipId,
        ];
    }

    /**
     * @param array{default: string|null, first: string|null} $headerRelationshipIds
     * @param array{default: string|null, first: string|null} $footerRelationshipIds
     */
    private function renderSectionProperties(array $headerRelationshipIds, array $footerRelationshipIds): string
    {
        $properties = '';
        $usesFirstPage = $this->firstPageHeader !== null || $this->firstPageFooter !== null;

        if ($usesFirstPage) {
            $properties .= '<w:titlePg/>';
        }

        if ($headerRelationshipIds['default'] !== null) {
            $properties .= sprintf(
                '<w:headerReference w:type="default" r:id="%s"/>',
                $headerRelationshipIds['default']
            );
        }

        if ($usesFirstPage && $headerRelationshipIds['first'] !== null) {
            $properties .= sprintf('<w:headerReference w:type="first" r:id="%s"/>', $headerRelationshipIds['first']);
        }

        if ($footerRelationshipIds['default'] !== null) {
            $properties .= sprintf(
                '<w:footerReference w:type="default" r:id="%s"/>',
                $footerRelationshipIds['default']
            );
        }

        if ($usesFirstPage && $footerRelationshipIds['first'] !== null) {
            $properties .= sprintf('<w:footerReference w:type="first" r:id="%s"/>', $footerRelationshipIds['first']);
        }

        if ($this->pageSize !== null) {
            $properties .= sprintf(
                '<w:pgSz w:w="%d" w:h="%d"/>',
                $this->pageSize['width'],
                $this->pageSize['height']
            );
        }

        if ($this->margins !== null) {
            $properties .= sprintf(
                '<w:pgMar w:top="%d" w:right="%d" w:bottom="%d" w:left="%d"'
                . ' w:header="%d" w:footer="%d" w:gutter="%d"/>',
                $this->margins['top'],
                $this->margins['right'],
                $this->margins['bottom'],
                $this->margins['left'],
                $this->margins['header'],
                $this->margins['footer'],
                $this->margins['gutter']
            );
        }

        if ($properties === '') {
            return '';
        }

        return sprintf('<w:sectPr>%s</w:sectPr>', $properties);
    }

    /**
     * @return array{width: int, height: int}
     */
    private function resolvePageSize(mixed $format): array
    {
        if (! is_string($format)) {
            throw new InvalidArgumentException('Document format must be a string.');
        }

        if (! array_key_exists($format, self::PAGE_FORMATS)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported document format "%s". Supported formats are: %s.',
                $format,
                implode(', ', array_keys(self::PAGE_FORMATS))
            ));
        }

        return self::PAGE_FORMATS[$format];
    }

    /**
     * @return array{top: int, right: int, bottom: int, left: int, header: int, footer: int, gutter: int}
     */
    private function resolveMargins(mixed $margins): array
    {
        if (! is_array($margins)) {
            throw new InvalidArgumentException('Document margins must be an array.');
        }

        $resolvedMargins = self::DEFAULT_MARGINS;
        $supportedMargins = [
            'top' => true,
            'right' => true,
            'bottom' => true,
            'left' => true,
        ];

        foreach ($margins as $name => $value) {
            if (! array_key_exists($name, $supportedMargins)) {
                throw new InvalidArgumentException(sprintf('Unsupported document margin "%s".', (string) $name));
            }

            if (! is_int($value) || $value < 0) {
                throw new InvalidArgumentException(sprintf(
                    'Document margin "%s" must be a non-negative integer.',
                    (string) $name
                ));
            }

            $resolvedMargins[$name] = $value;
        }

        return $resolvedMargins;
    }
}
