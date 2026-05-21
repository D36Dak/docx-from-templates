<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use D36Dak\DocxBuilder\Builder\DocxBuilder;
use D36Dak\DocxBuilder\Builder\ParagraphBuilder;

$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created.', $outputDir));
}

$outputPath = $outputDir . '/basic-example.docx';
$imagePath = $outputDir . '/example-image.png';

file_put_contents(
    $imagePath,
    base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAIAAABRTy6nAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ'
        . 'bWFnZVJlYWR5ccllPAAAApVJREFUeNrs3MENgDAMBEH//3YBJfUgxB62QJcdFFBsZzvWecC7e72/'
        . 'Af4pYASDgBEMAkYwCBjBIGAEg4ARDALGAgYBIxgEjGAQMIJBwAgGASMYPJ5vD7ivM2s/eZ7P8wPD'
        . 'MxgEjGAQMIJBwAgGASMYPHu63+x5YHgGg4ARDALGAgYBIxgEjGAQMIJBwAgGASMYBIxgEDCCQcAI'
        . 'BgEjGASMYPB4fdDzwPAMBgljAINEYJAwMkgYGSQMDZL2m0iz/F8q/71x/28Uz2CQMAaBwCBhZJAw'
        . 'MkgYGSSMDJLGAcCh/wJGMGgYAQxikDAySBgZJIwMEkYGCWMAYwGDhBGMAgYwSBgZJAwMkgYGSQM'
        . 'DJKGAcaTAQYwSBgBI2AEg4ARDALGAgYBIxgEjGAQMIJBwAgGASMYBIxgEDCCQcAIBgEjGASMYPD7'
        . 'AAD//wMARXsEDfUY7uUAAAAASUVORK5CYII=',
        true
    )
);

$builder = new DocxBuilder([
    'format' => 'a4',
    'margins' => [
        'top' => 1440,
        'right' => 1440,
        'bottom' => 1440,
        'left' => 1440,
    ],
]);

$builder
    ->addHeader('Quarterly report', [
        'alignment' => 'center',
        'color' => '777777',
        'fontSize' => 10,
    ])
    ->addParagraph('Quarterly report', [
        'alignment' => 'center',
        'fontSize' => 18,
        'bold' => true,
        'spacingAfter' => 240,
    ])
    ->addParagraph(
        (new ParagraphBuilder(['fontSize' => 12]))
            ->addTextRun('This paragraph uses ')
            ->addTextRun('multiple text runs', ['bold' => true, 'color' => '0055AA'])
            ->addTextRun(' inside one paragraph.')
            ->build()
    )
    ->addParagraph(
        'Paragraph options can control alignment, spacing, line height, font family, font size, color, and basic text styling.',
        [
            'alignment' => 'both',
            'lineSpacing' => 1.15,
            'spacingAfter' => 200,
        ]
    )
    ->addTable([
        ['Metric', 'Current', 'Previous'],
        ['Revenue', '$120,000', '$98,000'],
        ['Customers', '340', '295'],
        ['Conversion', '7.4%', '6.8%'],
    ], [
        'headerRowCount' => 1,
        'headerCellOptions' => [
            'alignment' => 'center',
            'bold' => true,
        ],
        'cellOptions' => [
            'fontSize' => 10,
        ],
    ])
    ->addPageBreak()
    ->addParagraph('Second page', [
        'fontSize' => 16,
        'bold' => true,
    ])
    ->addParagraph('Images can be embedded from a local image path.')
    ->addImage($imagePath, [
        'width' => 200,
        'height' => 200,
        'alignment' => 'center',
        'altText' => 'Example image',
    ])
    ->addFirstPageFooter('Generated on ' . date('Y-m-d H:i:s'), [
        'fontSize' => 10,
        'color' => '777777',
    ])
    ->addFooter(
        (new ParagraphBuilder(['alignment' => 'right', 'fontSize' => 10]))
            ->addTextRun('Page ')
            ->addPageNumber(['bold' => true])
            ->addTextRun(' of ')
            ->addTotalPagesNumber(['bold' => true])
            ->build()
    );

$builder->save($outputPath);

echo sprintf("Document generated: %s\n", $outputPath);
