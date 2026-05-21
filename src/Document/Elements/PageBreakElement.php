<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

/**
 * @internal
 */
class PageBreakElement extends DocxElement
{
    public function toXml(RenderContext $context): string
    {
        return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }
}
