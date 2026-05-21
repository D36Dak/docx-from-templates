<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

class ParagraphElement extends DocxElement
{
    private const ALIGNMENT_VALUES = [
        'left' => 'left',
        'right' => 'right',
        'center' => 'center',
        'both' => 'both',
    ];

    /** @var array<TextRunElement> */
    private readonly array $textRuns;
    private readonly ?string $alignment;
    private readonly ?int $spacingBefore;
    private readonly ?int $spacingAfter;
    private readonly ?float $lineSpacing;
    /**
     * @var array{
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * }
     */
    private readonly array $textRunOptions;

    /**
     * @param array{
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
     * } $options
     * @param array<mixed>|null $textRuns
     *
     * @internal Use ParagraphBuilder to create paragraph elements.
     */
    public function __construct(
        string $text,
        array $options = [],
        ?array $textRuns = null,
    ) {
        $this->alignment = $this->normalizeAlignment($options['alignment'] ?? null);
        $this->spacingBefore = $this->normalizeIntegerOption($options, 'spacingBefore');
        $this->spacingAfter = $this->normalizeIntegerOption($options, 'spacingAfter');
        $this->lineSpacing = $this->normalizeLineSpacing($options);
        $this->textRunOptions = TextRunElement::normalizeOptions($options);

        if ($textRuns === null) {
            $this->textRuns = [new TextRunElement($text)];

            return;
        }

        $this->textRuns = self::normalizeTextRuns($textRuns);
    }

    /**
     * @param array<mixed> $textRuns
     * @param array{
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
     * } $options
     *
     * @internal Use ParagraphBuilder to create paragraph elements.
     */
    public static function fromTextRuns(array $textRuns, array $options = []): self
    {
        return new self('', $options, $textRuns);
    }

    /**
     * @param array<mixed> $textRuns
     * @return array<TextRunElement>
     */
    private static function normalizeTextRuns(array $textRuns): array
    {
        $normalizedTextRuns = [];
        foreach ($textRuns as $textRun) {
            if (!$textRun instanceof TextRunElement) {
                throw new \InvalidArgumentException(sprintf(
                    'ParagraphElement expects only %s instances.',
                    TextRunElement::class
                ));
            }

            $normalizedTextRuns[] = $textRun;
        }

        return $normalizedTextRuns;
    }

    /**
     * @internal
     */
    public function toXml(RenderContext $context): string
    {
        $xml = '<w:p>';
        $xml .= $this->renderParagraphProperties();
        foreach ($this->textRuns as $textRun) {
            $xml .= $textRun->toXml($context, $this->textRunOptions);
        }
        $xml .= '</w:p>';

        return $xml;
    }

    private function renderParagraphProperties(): string
    {
        $properties = '';

        if ($this->alignment !== null) {
            $properties .= sprintf('<w:jc w:val="%s"/>', $this->alignment);
        }

        $spacingAttributes = [];
        if ($this->spacingBefore !== null) {
            $spacingAttributes[] = sprintf('w:before="%d"', $this->spacingBefore);
        }
        if ($this->spacingAfter !== null) {
            $spacingAttributes[] = sprintf('w:after="%d"', $this->spacingAfter);
        }
        if ($this->lineSpacing !== null) {
            $spacingAttributes[] = sprintf('w:line="%d"', (int) round($this->lineSpacing * 240));
            $spacingAttributes[] = 'w:lineRule="auto"';
        }

        if ($spacingAttributes !== []) {
            $properties .= sprintf('<w:spacing %s/>', implode(' ', $spacingAttributes));
        }

        if ($properties === '') {
            return '';
        }

        return sprintf('<w:pPr>%s</w:pPr>', $properties);
    }

    private function normalizeAlignment(mixed $alignment): ?string
    {
        if ($alignment === null) {
            return null;
        }

        if (!is_string($alignment) || !array_key_exists($alignment, self::ALIGNMENT_VALUES)) {
            throw new \InvalidArgumentException(sprintf(
                'Paragraph alignment must be one of: %s.',
                implode(', ', array_keys(self::ALIGNMENT_VALUES))
            ));
        }

        return self::ALIGNMENT_VALUES[$alignment];
    }

    /**
     * @param array<string, mixed> $options
     */
    private function normalizeIntegerOption(array $options, string $key): ?int
    {
        if (!array_key_exists($key, $options)) {
            return null;
        }

        $value = $options[$key];
        if (!is_int($value)) {
            throw new \InvalidArgumentException(sprintf('Paragraph option "%s" must be an integer.', $key));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function normalizeLineSpacing(array $options): ?float
    {
        if (!array_key_exists('lineSpacing', $options)) {
            return null;
        }

        $value = $options['lineSpacing'];
        if (!is_float($value) && !is_int($value)) {
            throw new \InvalidArgumentException('Paragraph option "lineSpacing" must be a number.');
        }

        $lineSpacing = (float) $value;
        if ($lineSpacing <= 0) {
            throw new \InvalidArgumentException('Paragraph option "lineSpacing" must be greater than zero.');
        }

        return $lineSpacing;
    }
}
