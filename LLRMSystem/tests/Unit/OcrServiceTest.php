<?php

use PHPUnit\Framework\TestCase;

class OcrServiceTest extends TestCase
{
    private $ocr;
    private $tempDir;

    protected function setUp(): void
    {
        $this->ocr = new OcrService();
        $this->tempDir = sys_get_temp_dir() . '/ocr_test_' . uniqid();
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tempDir);
    }

    private function cleanupDir($dir)
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->cleanupDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    // --- Initialization ---

    public function testOcrServiceInstantiates()
    {
        $this->assertInstanceOf(OcrService::class, $this->ocr);
    }

    public function testTesseractAvailabilityReturnsBool()
    {
        $this->assertIsBool($this->ocr->isTesseractAvailable());
    }

    public function testGhostscriptAvailabilityReturnsBool()
    {
        $this->assertIsBool($this->ocr->isGhostscriptAvailable());
    }

    // --- File Type Detection ---

    public function testIsOcrCapableForPdf()
    {
        $this->assertTrue($this->ocr->isOcrCapable('application/pdf', 'doc.pdf'));
    }

    public function testIsOcrCapableForJpeg()
    {
        $this->assertTrue($this->ocr->isOcrCapable('image/jpeg', 'scan.jpg'));
    }

    public function testIsOcrCapableForPng()
    {
        $this->assertTrue($this->ocr->isOcrCapable('image/png', 'scan.png'));
    }

    public function testIsOcrCapableForDocx()
    {
        $this->assertTrue($this->ocr->isOcrCapable(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc.docx'
        ));
    }

    public function testIsOcrCapableForXlsx()
    {
        $this->assertTrue($this->ocr->isOcrCapable(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'sheet.xlsx'
        ));
    }

    public function testIsOcrCapableForPptx()
    {
        $this->assertTrue($this->ocr->isOcrCapable(
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'slides.pptx'
        ));
    }

    public function testIsOcrCapableForUnsupportedType()
    {
        $this->assertFalse($this->ocr->isOcrCapable('text/plain', 'notes.txt'));
    }

    public function testIsOcrCapableForEmptyMimeType()
    {
        $this->assertFalse($this->ocr->isOcrCapable('', ''));
    }

    public function testIsOcrCapableFallsBackToExtension()
    {
        $this->assertTrue($this->ocr->isOcrCapable('application/octet-stream', 'file.pdf'));
    }

    // --- DOCX Extraction (PHP native, no Tesseract needed) ---

    public function testExtractTextFromDocx()
    {
        $docxPath = $this->createTestDocx();
        $result = $this->ocr->extractText($docxPath, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->assertEquals('completed', $result['status']);
        $this->assertNotEmpty($result['text']);
        $this->assertStringContainsString('ORDINANCE', $result['text']);
        $this->assertStringContainsString('Section 1', $result['text']);
    }

    public function testExtractTextFromDocxReturnsPhpNativeMethod()
    {
        $docxPath = $this->createTestDocx();
        $result = $this->ocr->extractText($docxPath, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->assertEquals('php-native', $result['method'] ?? '');
    }

    // --- XLSX Extraction ---

    public function testExtractTextFromXlsx()
    {
        $xlsxPath = $this->createTestXlsx();
        $result = $this->ocr->extractText($xlsxPath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertEquals('completed', $result['status']);
        $this->assertNotEmpty($result['text']);
        $this->assertStringContainsString('Budget', $result['text']);
        $this->assertStringContainsString('Personal Services', $result['text']);
    }

    // --- PPTX Extraction ---

    public function testExtractTextFromPptx()
    {
        $pptxPath = $this->createTestPptx();
        $result = $this->ocr->extractText($pptxPath, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        $this->assertEquals('completed', $result['status']);
        $this->assertNotEmpty($result['text']);
        $this->assertStringContainsString('Title Slide', $result['text']);
        $this->assertStringContainsString('Budget Proposal', $result['text']);
    }

    // --- Error Handling ---

    public function testExtractTextFromNonExistentFile()
    {
        $result = $this->ocr->extractText('/nonexistent/path/file.pdf', 'application/pdf');

        $this->assertEquals('failed', $result['status']);
        $this->assertNotEmpty($result['error']);
    }

    public function testExtractTextFromEmptyPath()
    {
        $result = $this->ocr->extractText('', 'application/pdf');

        $this->assertEquals('failed', $result['status']);
    }

    // --- Helpers ---

    private function createTestDocx()
    {
        $path = $this->tempDir . '/test.docx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);

        $zip->addFromString('word/document.xml', '<?xml version="1.0"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>
<w:p><w:r><w:t>AN ORDINANCE APPROPRIATING FUNDS FOR THE CITY GOVERNMENT.</w:t></w:r></w:p>
<w:p><w:r><w:t>Section 1. Two hundred million pesos for personal services.</w:t></w:r></w:p>
<w:p><w:r><w:t>Section 2. One hundred million pesos for operating expenses.</w:t></w:r></w:p>
<w:p><w:r><w:t>Section 3. This ordinance shall take effect immediately upon approval.</w:t></w:r></w:p>
</w:body>
</w:document>');

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="xml" ContentType="application/xml"/>
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>');

        $zip->close();
        return $path;
    }

    private function createTestXlsx()
    {
        $path = $this->tempDir . '/test.xlsx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);

        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="3" uniqueCount="3">
<si><t>Budget Category</t></si>
<si><t>Amount</t></si>
<si><t>Personal Services</t></si>
</sst>');

        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheetData>
<row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row>
<row r="2"><c r="A2" t="s"><v>2</v></c r="B2"><v>200000000</v></c></row>
</sheetData>
</worksheet>');

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="xml" ContentType="application/xml"/>
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></sheets>
</workbook>');

        $zip->close();
        return $path;
    }

    private function createTestPptx()
    {
        $path = $this->tempDir . '/test.pptx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);

        $zip->addFromString('ppt/slides/slide1.xml', '<?xml version="1.0"?>
<sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
<cSld><spTree>
<p:sp><p:txBody><a:p><a:r><a:t>Title Slide</a:t></a:r></a:p></p:txBody></p:sp>
</spTree></cSld>
</sld>');

        $zip->addFromString('ppt/slides/slide2.xml', '<?xml version="1.0"?>
<sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
<cSld><spTree>
<p:sp><p:txBody><a:p><a:r><a:t>Budget Proposal for 2025</a:t></a:r></a:p></p:txBody></p:sp>
</spTree></cSld>
</sld>');

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="xml" ContentType="application/xml"/>
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Override PartName="/ppt/slides/slide1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>
<Override PartName="/ppt/slides/slide2.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>
</Relationships>');

        $zip->addFromString('ppt/presentation.xml', '<?xml version="1.0"?>
<p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">
<sldIdLst><sldId id="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/><sldId id="2" r:id="rId2" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></sldIdLst>
</p:presentation>');

        $zip->close();
        return $path;
    }
}
