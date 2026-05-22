# Changelog

All notable changes to this project will be documented in this file.

## [0.1.2] - 2026-05-22

### Fixed

- Improved generated image XML compatibility with Microsoft Word by adding inline drawing distances, effect extents, graphic frame locks, image stretch settings, transform extents, and rectangle geometry.
- Reused calculated EMU dimensions for image extent and shape transform output.

### Changed

- Updated the basic example to use a bundled landscape image file instead of generating an embedded placeholder image at runtime.

### Added

- Added a landscape sample image for examples.
- Added test coverage for the additional image XML required by Microsoft Word.

## [0.1.1] - 2026-05-21

### Changed

- Defined the supported public API as `DocxBuilder`, `ParagraphBuilder`, and `ParagraphElement`.
- Marked internal document, element, rendering, and writer implementation classes with `@internal` annotations.
- Documented the public API boundary in the README.

## [0.1.0] - 2026-05-21

### Added

- Added the initial Composer package structure for `d36dak/docx-builder`.
- Added support for building DOCX documents with paragraphs, text runs, tables, images, page breaks, headers, and footers.
- Added paragraph styling options, including alignment, indentation, spacing, font settings, bold, italic, and underline.
- Added simple image options, including width, height, alignment, and alternative text.
- Added table options for widths, borders, cell margins, alignment, and cell text styling.
- Added `ParagraphBuilder` for composing rich paragraphs instead of passing raw text-run arrays directly.
- Added page formatting options, including page size and margins.
- Added writer support for saving documents to a file or returning binary DOCX contents.
- Added a bundled empty DOCX template resource used as the base document package.
- Added README documentation and a basic example script.
- Added PHPUnit coverage for builders, document output, and document elements.
- Added PHPStan configuration and Composer development tooling.

### Changed

- Removed unused template resources and refreshed the Composer lock file before the first tagged release.
