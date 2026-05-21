<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

/**
 * @internal
 */
class TableElement extends DocxElement
{
    private readonly int $headerRowCount;
    /**
     * @var array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * }
     */
    private readonly array $cellOptions;
    /**
     * @var array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * }
     */
    private readonly array $headerCellOptions;

    /**
     * @param array<array<string>> $rows
     * @param array{
     *     headerRowCount?: int,
     *     cellOptions?: array{
     *         alignment?: 'left'|'right'|'center'|'both',
     *         spacingBefore?: int,
     *         spacingAfter?: int,
     *         lineSpacing?: float,
     *         fontFamily?: string,
     *         fontSize?: float,
     *         color?: string,
     *         bold?: bool,
     *         italic?: bool,
     *         underline?: bool,
     *     },
     *     headerCellOptions?: array{
     *         alignment?: 'left'|'right'|'center'|'both',
     *         spacingBefore?: int,
     *         spacingAfter?: int,
     *         lineSpacing?: float,
     *         fontFamily?: string,
     *         fontSize?: float,
     *         color?: string,
     *         bold?: bool,
     *         italic?: bool,
     *         underline?: bool,
     *     },
     * } $options
     */
    public function __construct(
        private readonly array $rows,
        array $options = [],
    ) {
        $this->headerRowCount = $this->normalizeHeaderRowCount($options);
        $this->cellOptions = $this->normalizeParagraphOptions($options, 'cellOptions');
        $this->headerCellOptions = $this->normalizeParagraphOptions($options, 'headerCellOptions');
    }

    public function toXml(RenderContext $context): string
    {
        $xml = '<w:tbl><w:tblPr></w:tblPr>';
        foreach ($this->rows as $rowIndex => $row) {
            $isHeaderRow = $rowIndex < $this->headerRowCount;
            $xml .= '<w:tr>';
            if ($isHeaderRow) {
                $xml .= '<w:trPr><w:tblHeader/></w:trPr>';
            }

            foreach ($row as $cell) {
                $paragraph = new ParagraphElement(
                    $cell,
                    $isHeaderRow ? $this->headerCellOptions : $this->cellOptions
                );
                $xml .= sprintf(
                    '<w:tc>%s</w:tc>',
                    $paragraph->toXml($context)
                );
            }
            $xml .= '</w:tr>';
        }
        $xml .= '</w:tbl>';
        return $xml;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function normalizeHeaderRowCount(array $options): int
    {
        if (!array_key_exists('headerRowCount', $options)) {
            return 0;
        }

        $value = $options['headerRowCount'];
        if (!is_int($value)) {
            throw new \InvalidArgumentException('Table option "headerRowCount" must be an integer.');
        }

        if ($value < 0) {
            throw new \InvalidArgumentException('Table option "headerRowCount" must be greater than or equal to zero.');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * }
     */
    private function normalizeParagraphOptions(array $options, string $key): array
    {
        if (!array_key_exists($key, $options)) {
            return [];
        }

        $value = $options[$key];
        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Table option "%s" must be an array.', $key));
        }

        new ParagraphElement('', $value);

        return $value;
    }
}
