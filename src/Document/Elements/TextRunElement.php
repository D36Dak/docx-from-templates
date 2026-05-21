<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

/**
 * @internal
 */
class TextRunElement extends DocxElement
{
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
    private readonly array $options;

    /**
     * @param array{
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options
     */
    public function __construct(
        private readonly string $text,
        array $options = [],
    ) {
        $this->options = self::normalizeOptions($options);
    }

    /**
     * @param array{
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $defaultOptions
     */
    public function toXml(RenderContext $context, array $defaultOptions = []): string
    {
        $options = array_replace($defaultOptions, $this->options);

        return sprintf(
            '<w:r>%s<w:t%s>%s</w:t></w:r>',
            $this->renderRunProperties($options),
            $this->shouldPreserveWhitespace() ? ' xml:space="preserve"' : '',
            htmlspecialchars($this->text, ENT_XML1 | ENT_COMPAT, 'UTF-8')
        );
    }

    /**
     * @param array<string, mixed> $options
     * @return array{
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * }
     */
    public static function normalizeOptions(array $options): array
    {
        $normalized = [];

        if (array_key_exists('fontFamily', $options)) {
            if (!is_string($options['fontFamily']) || $options['fontFamily'] === '') {
                throw new \InvalidArgumentException('Text run option "fontFamily" must be a non-empty string.');
            }

            $normalized['fontFamily'] = $options['fontFamily'];
        }

        if (array_key_exists('fontSize', $options)) {
            if (!is_float($options['fontSize']) && !is_int($options['fontSize'])) {
                throw new \InvalidArgumentException('Text run option "fontSize" must be a number.');
            }

            $fontSize = (float) $options['fontSize'];
            if ($fontSize <= 0) {
                throw new \InvalidArgumentException('Text run option "fontSize" must be greater than zero.');
            }

            $normalized['fontSize'] = $fontSize;
        }

        if (array_key_exists('color', $options)) {
            if (!is_string($options['color'])) {
                throw new \InvalidArgumentException('Text run option "color" must be a hex color string.');
            }

            $color = ltrim($options['color'], '#');
            if (!preg_match('/\A[0-9a-fA-F]{6}\z/', $color)) {
                throw new \InvalidArgumentException('Text run option "color" must be a 6-digit hex color.');
            }

            $normalized['color'] = strtoupper($color);
        }

        foreach (['bold', 'italic', 'underline'] as $key) {
            if (!array_key_exists($key, $options)) {
                continue;
            }

            if (!is_bool($options[$key])) {
                throw new \InvalidArgumentException(sprintf('Text run option "%s" must be a boolean.', $key));
            }

            $normalized[$key] = $options[$key];
        }

        return $normalized;
    }

    /**
     * @param array{
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options
     */
    protected function renderRunProperties(array $options): string
    {
        $properties = '';

        if (array_key_exists('fontFamily', $options)) {
            $fontFamily = htmlspecialchars($options['fontFamily'], ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $properties .= sprintf('<w:rFonts w:ascii="%s" w:hAnsi="%s"/>', $fontFamily, $fontFamily);
        }

        if (array_key_exists('fontSize', $options)) {
            $properties .= sprintf('<w:sz w:val="%d"/>', (int) round($options['fontSize'] * 2));
        }

        if (array_key_exists('color', $options)) {
            $properties .= sprintf('<w:color w:val="%s"/>', $options['color']);
        }

        if (array_key_exists('bold', $options)) {
            $properties .= $options['bold'] ? '<w:b/>' : '<w:b w:val="false"/>';
        }

        if (array_key_exists('italic', $options)) {
            $properties .= $options['italic'] ? '<w:i/>' : '<w:i w:val="false"/>';
        }

        if (array_key_exists('underline', $options)) {
            $properties .= $options['underline'] ? '<w:u w:val="single"/>' : '<w:u w:val="none"/>';
        }

        if ($properties === '') {
            return '';
        }

        return sprintf('<w:rPr>%s</w:rPr>', $properties);
    }

    private function shouldPreserveWhitespace(): bool
    {
        return $this->text !== trim($this->text);
    }
}
