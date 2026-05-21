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
$imagePath = __DIR__ . '/general-img-landscape.png';

if (!is_file($imagePath)) {
    throw new RuntimeException(sprintf('Example image file does not exist: %s', $imagePath));
}

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
        'width' => 360,
        'height' => 240,
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
