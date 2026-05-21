# docx-builder

`docx-builder` is a small PHP library for creating DOCX documents programmatically. It provides a fluent builder API for composing paragraphs, tables, images, page breaks, headers, and footers, then writing the result as a `.docx` file, or returning the generated document contents as a string.

The package is designed around a lightweight document model and bundled OpenXML resources, so simple documents can be generated without preparing a separate template file.

## Features

- Create DOCX documents from PHP code.
- Add styled paragraphs with font family, font size, color, bold, italic, and underline options.
- Configure paragraph alignment, spacing before and after, and line spacing.
- Build paragraphs from multiple text runs.
- Add page number and total page count fields.
- Add page breaks.
- Add tables with configurable header rows and cell styling.
- Add images with width, height, alignment, and alt text.
- Add default and first-page headers and footers.
- Configure page format and margins.
- Save documents to disk or return generated DOCX contents as a string.

## Requirements

- PHP 8.2 or newer
- PHP `zip` extension
- PHP `dom` extension

## Installation

This package is installed via Composer. To add a dependency to `docx-builder` in your project run the following command:

```sh
composer require d36dak/docx-builder
```

## Usage examples

The following example can be used in any PHP project after installing the package with Composer.
This example is also available as `examples/basic.php` and can be run from the project root:

```sh
php examples/basic.php
```

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use D36Dak\DocxBuilder\Builder\DocxBuilder;
use D36Dak\DocxBuilder\Builder\ParagraphBuilder;

$outputPath = __DIR__ . '/example.docx';

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
    ->addParagraph('Images can be embedded when a local image file is available.')
    ->addImage(__DIR__ . '/example-image.png', [
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

// Save the document to disk.
$builder->save($outputPath);

// Or get the generated DOCX contents as a string, for example to return from a web response.
$contents = $builder->getContents();
```

### Examples of each feature

Feature index:

- [Creating a document](#creating-a-document)
- [Adding paragraphs](#adding-paragraphs)
- [Building paragraphs with multiple text runs](#building-paragraphs-with-multiple-text-runs)
- [Adding page numbers](#adding-page-numbers)
- [Adding page breaks](#adding-page-breaks)
- [Adding tables](#adding-tables)
- [Adding images](#adding-images)
- [Adding headers and footers](#adding-headers-and-footers)
- [Saving a document](#saving-a-document)
- [Returning document contents](#returning-document-contents)

#### Creating a document

Create a new DOCX document builder with optional page format and margin settings.

Method:

`new DocxBuilder(array $documentOptions = [])`

Parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `$documentOptions` | `array` | No | Document-level options passed to the `DocxBuilder` constructor. |

Options for `$documentOptions`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `format` | `string` | `a4`, `us-letter`, `legal` | No explicit page size is written | Sets the page size for the document. |
| `margins` | `array` | See margin options below | No explicit margins are written | Sets page margins. Margin values are Word twips, where 1440 equals 1 inch. |

Options for `margins`:

| Option | Type | Accepted values | Default when `margins` is provided | Description |
| --- | --- | --- | --- | --- |
| `top` | `int` | Any non-negative integer | `1440` | Top page margin in twips. |
| `right` | `int` | Any non-negative integer | `1440` | Right page margin in twips. |
| `bottom` | `int` | Any non-negative integer | `1440` | Bottom page margin in twips. |
| `left` | `int` | Any non-negative integer | `1440` | Left page margin in twips. |

```php
use D36Dak\DocxBuilder\Builder\DocxBuilder;

$builder = new DocxBuilder([
    'format' => 'a4',
    'margins' => [
        'top' => 1440,
        'right' => 1440,
        'bottom' => 1440,
        'left' => 1440,
    ],
]);
```

[Back to feature index](#examples-of-each-feature)

#### Adding paragraphs

Add a paragraph to the document from plain text or from a prepared `ParagraphElement`.

Method:

`$builder->addParagraph(string|ParagraphElement $paragraph, array $options = []): self`

Parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `$paragraph` | `string` or `ParagraphElement` | Yes | Paragraph content to append to the document. |
| `$options` | `array` | No | Paragraph and text styling options. Used only when `$paragraph` is a string. |

Options for `$options`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `alignment` | `string` | `left`, `right`, `center`, `both` | Word default | Paragraph alignment. |
| `spacingBefore` | `int` | Any integer | Word default | Space before the paragraph in twips. |
| `spacingAfter` | `int` | Any integer | Word default | Space after the paragraph in twips. |
| `lineSpacing` | `int` or `float` | Number greater than `0` | Word default | Paragraph line spacing multiplier. |
| `fontFamily` | `string` | Any non-empty string | Word default | Font family for the paragraph text. |
| `fontSize` | `int` or `float` | Number greater than `0` | Word default | Font size in points. |
| `color` | `string` | 6-digit hex color, with or without `#` | Word default | Text color. |
| `bold` | `bool` | `true`, `false` | Word default | Enables or disables bold text. |
| `italic` | `bool` | `true`, `false` | Word default | Enables or disables italic text. |
| `underline` | `bool` | `true`, `false` | Word default | Enables or disables underline text. |

```php
$builder->addParagraph('A centered title', [
    'alignment' => 'center',
    'fontSize' => 18,
    'bold' => true,
    'spacingAfter' => 240,
]);
```

[Back to feature index](#examples-of-each-feature)

#### Building paragraphs with multiple text runs

Build one paragraph from multiple text runs when different parts of the same paragraph need different text styling.

Methods:

`new ParagraphBuilder(array $defaultOptions = [])`

`$paragraphBuilder->addTextRun(string $text, array $options = []): self`

`$paragraphBuilder->build(): ParagraphElement`

Parameters:

| Method | Parameter | Type | Required | Description |
| --- | --- | --- | --- | --- |
| `ParagraphBuilder::__construct()` | `$defaultOptions` | `array` | No | Default paragraph and text options used by the built paragraph. |
| `addTextRun()` | `$text` | `string` | Yes | Text to append to the paragraph. |
| `addTextRun()` | `$options` | `array` | No | Text styling options for this text run. |

Options for `$defaultOptions`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `alignment` | `string` | `left`, `right`, `center`, `both` | Word default | Paragraph alignment. |
| `spacingBefore` | `int` | Any integer | Word default | Space before the paragraph in twips. |
| `spacingAfter` | `int` | Any integer | Word default | Space after the paragraph in twips. |
| `lineSpacing` | `int` or `float` | Number greater than `0` | Word default | Paragraph line spacing multiplier. |
| `fontFamily` | `string` | Any non-empty string | Word default | Default font family for text runs. |
| `fontSize` | `int` or `float` | Number greater than `0` | Word default | Default font size in points. |
| `color` | `string` | 6-digit hex color, with or without `#` | Word default | Default text color. |
| `bold` | `bool` | `true`, `false` | Word default | Default bold setting. |
| `italic` | `bool` | `true`, `false` | Word default | Default italic setting. |
| `underline` | `bool` | `true`, `false` | Word default | Default underline setting. |

Options for `addTextRun()` `$options`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `fontFamily` | `string` | Any non-empty string | Paragraph default or Word default | Font family for this text run. |
| `fontSize` | `int` or `float` | Number greater than `0` | Paragraph default or Word default | Font size in points for this text run. |
| `color` | `string` | 6-digit hex color, with or without `#` | Paragraph default or Word default | Text color for this text run. |
| `bold` | `bool` | `true`, `false` | Paragraph default or Word default | Enables or disables bold text for this text run. |
| `italic` | `bool` | `true`, `false` | Paragraph default or Word default | Enables or disables italic text for this text run. |
| `underline` | `bool` | `true`, `false` | Paragraph default or Word default | Enables or disables underline text for this text run. |

```php
use D36Dak\DocxBuilder\Builder\ParagraphBuilder;

$paragraph = (new ParagraphBuilder(['fontSize' => 12]))
    ->addTextRun('This paragraph uses ')
    ->addTextRun('multiple text runs', ['bold' => true, 'color' => '0055AA'])
    ->addTextRun(' inside one paragraph.')
    ->build();

$builder->addParagraph($paragraph);
```

[Back to feature index](#examples-of-each-feature)

#### Adding page numbers

Add Word page number fields inside a paragraph, usually for headers or footers.

Methods:

`$paragraphBuilder->addPageNumber(array $options = []): self`

`$paragraphBuilder->addTotalPagesNumber(array $options = []): self`

Parameters:

| Method | Parameter | Type | Required | Description |
| --- | --- | --- | --- | --- |
| `addPageNumber()` | `$options` | `array` | No | Text styling options for the current page number field. |
| `addTotalPagesNumber()` | `$options` | `array` | No | Text styling options for the total pages field. |

Options for `$options`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `fontFamily` | `string` | Any non-empty string | Paragraph default or Word default | Font family for the field. |
| `fontSize` | `int` or `float` | Number greater than `0` | Paragraph default or Word default | Font size in points for the field. |
| `color` | `string` | 6-digit hex color, with or without `#` | Paragraph default or Word default | Field text color. |
| `bold` | `bool` | `true`, `false` | Paragraph default or Word default | Enables or disables bold field text. |
| `italic` | `bool` | `true`, `false` | Paragraph default or Word default | Enables or disables italic field text. |
| `underline` | `bool` | `true`, `false` | Paragraph default or Word default | Enables or disables underlined field text. |

```php
$footer = (new ParagraphBuilder(['alignment' => 'right', 'fontSize' => 10]))
    ->addTextRun('Page ')
    ->addPageNumber(['bold' => true])
    ->addTextRun(' of ')
    ->addTotalPagesNumber(['bold' => true])
    ->build();

$builder->addFooter($footer);
```

[Back to feature index](#examples-of-each-feature)

#### Adding page breaks

Insert a page break so following content starts on a new page.

Method:

`$builder->addPageBreak(): self`

Parameters: none.

```php
$builder
    ->addParagraph('This content is on the first page.')
    ->addPageBreak()
    ->addParagraph('This content starts on the next page.');
```

[Back to feature index](#examples-of-each-feature)

#### Adding tables

Add a simple DOCX table from an array of rows.

Method:

`$builder->addTable(array $rows, array $options = []): self`

Parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `$rows` | `array<array<string>>` | Yes | Table rows. Each nested array is one row, and each string is one cell. |
| `$options` | `array` | No | Table options for header rows and cell styling. |

Options for `$options`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `headerRowCount` | `int` | Any non-negative integer | `0` | Number of rows from the start of `$rows` to mark as table header rows. |
| `cellOptions` | `array` | Paragraph options | `[]` | Paragraph and text options for regular cells. |
| `headerCellOptions` | `array` | Paragraph options | `[]` | Paragraph and text options for header cells. |

Options for `cellOptions` and `headerCellOptions`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `alignment` | `string` | `left`, `right`, `center`, `both` | Word default | Cell paragraph alignment. |
| `spacingBefore` | `int` | Any integer | Word default | Space before the cell paragraph in twips. |
| `spacingAfter` | `int` | Any integer | Word default | Space after the cell paragraph in twips. |
| `lineSpacing` | `int` or `float` | Number greater than `0` | Word default | Cell paragraph line spacing multiplier. |
| `fontFamily` | `string` | Any non-empty string | Word default | Cell text font family. |
| `fontSize` | `int` or `float` | Number greater than `0` | Word default | Cell text font size in points. |
| `color` | `string` | 6-digit hex color, with or without `#` | Word default | Cell text color. |
| `bold` | `bool` | `true`, `false` | Word default | Enables or disables bold cell text. |
| `italic` | `bool` | `true`, `false` | Word default | Enables or disables italic cell text. |
| `underline` | `bool` | `true`, `false` | Word default | Enables or disables underlined cell text. |

```php
$builder->addTable([
    ['Metric', 'Current', 'Previous'],
    ['Revenue', '$120,000', '$98,000'],
    ['Customers', '340', '295'],
], [
    'headerRowCount' => 1,
    'headerCellOptions' => [
        'alignment' => 'center',
        'bold' => true,
    ],
    'cellOptions' => [
        'fontSize' => 10,
    ],
]);
```

[Back to feature index](#examples-of-each-feature)

#### Adding images

Embed a local image file into the document.

Method:

`$builder->addImage(string $imagePath, array $options): self`

Parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `$imagePath` | `string` | Yes | Path to an existing local image file. |
| `$options` | `array` | Yes | Image sizing and accessibility options. |

Options for `$options`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `width` | `int` | Any positive integer | Required | Image width in pixels. |
| `height` | `int` | Any positive integer | Required | Image height in pixels. |
| `alignment` | `string` | `left`, `right`, `center` | Word default | Paragraph alignment for the image. |
| `altText` | `string` | Any string | None | Alternative text written to the image description. |

```php
$builder
    ->addParagraph('Images can be embedded from a local image path.')
    ->addImage(__DIR__ . '/example-image.png', [
        'width' => 200,
        'height' => 200,
        'alignment' => 'center',
        'altText' => 'Example image',
    ]);
```

[Back to feature index](#examples-of-each-feature)

#### Adding headers and footers

Set default or first-page headers and footers for the document.

Methods:

`$builder->addHeader(string|ParagraphElement $header, array $options = []): self`

`$builder->addFirstPageHeader(string|ParagraphElement $header, array $options = []): self`

`$builder->addFooter(string|ParagraphElement $footer, array $options = []): self`

`$builder->addFirstPageFooter(string|ParagraphElement $footer, array $options = []): self`

Parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `$header` | `string` or `ParagraphElement` | Yes | Header content. Used by `addHeader()` and `addFirstPageHeader()`. |
| `$footer` | `string` or `ParagraphElement` | Yes | Footer content. Used by `addFooter()` and `addFirstPageFooter()`. |
| `$options` | `array` | No | Paragraph and text styling options. Used only when the header or footer is a string. |

Options for `$options`:

| Option | Type | Accepted values | Default | Description |
| --- | --- | --- | --- | --- |
| `alignment` | `string` | `left`, `right`, `center`, `both` | Word default | Header or footer paragraph alignment. |
| `spacingBefore` | `int` | Any integer | Word default | Space before the header or footer paragraph in twips. |
| `spacingAfter` | `int` | Any integer | Word default | Space after the header or footer paragraph in twips. |
| `lineSpacing` | `int` or `float` | Number greater than `0` | Word default | Header or footer paragraph line spacing multiplier. |
| `fontFamily` | `string` | Any non-empty string | Word default | Header or footer text font family. |
| `fontSize` | `int` or `float` | Number greater than `0` | Word default | Header or footer text font size in points. |
| `color` | `string` | 6-digit hex color, with or without `#` | Word default | Header or footer text color. |
| `bold` | `bool` | `true`, `false` | Word default | Enables or disables bold text. |
| `italic` | `bool` | `true`, `false` | Word default | Enables or disables italic text. |
| `underline` | `bool` | `true`, `false` | Word default | Enables or disables underlined text. |

Calling the same header or footer method multiple times replaces the previous value.

```php
$builder
    ->addHeader('Quarterly report', [
        'alignment' => 'center',
        'fontSize' => 10,
        'color' => '777777',
    ])
    ->addFirstPageFooter('Generated on ' . date('Y-m-d H:i:s'), [
        'fontSize' => 10,
    ]);
```

[Back to feature index](#examples-of-each-feature)

#### Saving a document

Write the generated DOCX document to disk.

Method:

`$builder->save(string $outputPath): void`

Parameters:

| Parameter | Type | Required | Description |
| --- | --- | --- | --- |
| `$outputPath` | `string` | Yes | Path where the generated `.docx` file should be saved. Missing directories are created automatically. |

```php
$builder->save(__DIR__ . '/example.docx');
```

[Back to feature index](#examples-of-each-feature)

#### Returning document contents

Return the generated DOCX document as a binary string.

Method:

`$builder->getContents(): string`

Parameters: none.

```php
$contents = $builder->getContents();

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
echo $contents;
```

[Back to feature index](#examples-of-each-feature)

## License

This project is released under the MIT License.
