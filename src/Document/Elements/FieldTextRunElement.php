<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

/**
 * @internal
 */
class FieldTextRunElement extends TextRunElement
{
    public const PAGE = 'PAGE';
    public const NUMPAGES = 'NUMPAGES';

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
        private readonly string $fieldName,
        array $options = [],
    ) {
        if (!in_array($fieldName, [self::PAGE, self::NUMPAGES], true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported Word field: %s.', $fieldName));
        }

        parent::__construct('', $options);

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
        $runProperties = $this->renderRunProperties($options);

        return sprintf(
            '<w:fldSimple w:instr="%s"><w:r>%s<w:t>1</w:t></w:r></w:fldSimple>',
            $this->fieldName,
            $runProperties
        );
    }
}
