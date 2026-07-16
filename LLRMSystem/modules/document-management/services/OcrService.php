<?php
/**
 * OCR Service
 * 
 * Extracts text from documents using Tesseract OCR (for images/scanned PDFs)
 * and PHP native extraction (for digital PDFs, Word, Excel, PowerPoint).
 * 
 * No external API required — everything runs locally.
 */

class OcrService {
    private $tesseractPath;
    private $ghostscriptPath;
    private $language;
    private $timeout;
    private $tempDir;
    private $enabled;

    public function __construct() {
        $tessConfig = defined('OCR_TESSERACT_PATH') ? OCR_TESSERACT_PATH : '';
        $this->tesseractPath = !empty($tessConfig) ? $tessConfig : $this->detectTesseract();
        $gsConfig = defined('OCR_GHOSTSCRIPT_PATH') ? OCR_GHOSTSCRIPT_PATH : '';
        $this->ghostscriptPath = !empty($gsConfig) ? $gsConfig : $this->detectGhostscript();
        $this->language = defined('OCR_LANGUAGE') ? OCR_LANGUAGE : 'eng';
        $this->timeout = defined('OCR_TIMEOUT') ? OCR_TIMEOUT : 30;
        $this->enabled = defined('OCR_ENABLED') ? OCR_ENABLED : true;
        $this->tempDir = dirname(dirname(dirname(__DIR__))) . '/storage/temp/ocr';
        $this->ensureTempDir();
    }

    /**
     * Main entry point — extract text from any supported file
     */
    public function extractText($filePath, $mimeType = null) {
        if (!$this->enabled) {
            return ['text' => '', 'status' => 'skipped', 'error' => 'OCR disabled in config'];
        }

        if (!file_exists($filePath)) {
            return ['text' => '', 'status' => 'failed', 'error' => 'File not found: ' . $filePath];
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeType ?: mime_content_type($filePath);

        try {
            $text = '';
            $method = '';

            switch (true) {
                // Images — direct Tesseract OCR
                case in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp']):
                case strpos($mimeType, 'image/') === 0:
                    $text = $this->processImage($filePath);
                    $method = 'tesseract';
                    break;

                // PDF — try digital extraction first, fall back to OCR
                case $extension === 'pdf' || $mimeType === 'application/pdf':
                    $text = $this->processPdf($filePath);
                    $method = strpos($text, '[OCR]') === 0 ? 'tesseract' : 'pdfparser';
                    break;

                // Word documents
                case in_array($extension, ['docx', 'doc']):
                case strpos($mimeType, 'word') !== false:
                    $text = $this->processWord($filePath);
                    $method = 'php-native';
                    break;

                // Excel spreadsheets
                case in_array($extension, ['xlsx', 'xls']):
                case strpos($mimeType, 'spreadsheet') !== false || strpos($mimeType, 'excel') !== false:
                    $text = $this->processExcel($filePath);
                    $method = 'php-native';
                    break;

                // PowerPoint presentations
                case in_array($extension, ['pptx', 'ppt']):
                case strpos($mimeType, 'presentation') !== false || strpos($mimeType, 'powerpoint') !== false:
                    $text = $this->processPowerPoint($filePath);
                    $method = 'php-native';
                    break;

                default:
                    return ['text' => '', 'status' => 'skipped', 'error' => 'Unsupported file type: ' . $extension];
            }

            $text = trim($text);

            if (empty($text)) {
                return ['text' => '', 'status' => 'completed', 'error' => 'No text could be extracted', 'method' => $method];
            }

            return ['text' => $text, 'status' => 'completed', 'error' => null, 'method' => $method];

        } catch (Exception $e) {
            return ['text' => '', 'status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if a file type is OCR-capable
     */
    public function isOcrCapable($mimeType, $fileName = '') {
        $extension = strtolower(pathinfo($fileName ?: '', PATHINFO_EXTENSION));
        $capableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp',
                         'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        $capableMimes = ['application/pdf', 'image/', 'word', 'spreadsheet', 'excel',
                         'presentation', 'powerpoint', 'msword', 'officedocument'];

        if (in_array($extension, $capableTypes)) {
            return true;
        }

        foreach ($capableMimes as $mime) {
            if (strpos($mimeType, $mime) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process image files with Tesseract OCR
     */
    private function processImage($filePath) {
        $tesseract = $this->getTesseractPath();
        if (!$tesseract) {
            throw new Exception('Tesseract binary not found');
        }

        $escapedTesseract = escapeshellarg($tesseract);
        $escapedPath = escapeshellarg($filePath);
        // Language is a controlled alphanumeric value (e.g. "eng", "fil"), no need to escape
        // escapeshellarg on Windows can add newlines for short strings
        $lang = $this->language;

        if (PHP_OS_FAMILY === 'Windows') {
            $command = "$escapedTesseract $escapedPath stdout -l $lang 2>nul";
        } else {
            $command = "$escapedTesseract $escapedPath stdout -l $lang 2>/dev/null";
        }

        $output = shell_exec($command);

        if ($output === null) {
            throw new Exception('Tesseract command failed: ' . $command);
        }

        return $output;
    }

    /**
     * Process PDF files — try digital extraction first, fall back to OCR
     */
    private function processPdf($filePath) {
        // First, try to extract embedded text using smalot/pdfparser
        $digitalText = $this->extractDigitalPdfText($filePath);

        if (!empty(trim($digitalText))) {
            return $digitalText;
        }

        // No embedded text — likely a scanned PDF, use OCR
        return $this->processScannedPdf($filePath);
    }

    /**
     * Extract text from digital (born-digital) PDFs
     */
    private function extractDigitalPdfText($filePath) {
        // Try smalot/pdfparser if available
        $parserClass = @class_exists('Smalot\PdfParser\Parser');

        if (!$parserClass) {
            $autoload = dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
                $parserClass = class_exists('Smalot\PdfParser\Parser');
            }
        }

        if ($parserClass) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            } catch (Exception $e) {
                // Fall through to OCR
            }
        }

        // Fallback: try pdftotext command if available
        $pdftotext = $this->detectCommand('pdftotext');
        if ($pdftotext) {
            $escapedPath = escapeshellarg($filePath);
            $tempOutput = $this->tempDir . '/' . uniqid('pdf_text_') . '.txt';
            $escapedOutput = escapeshellarg($tempOutput);

            if (PHP_OS_FAMILY === 'Windows') {
                shell_exec("$pdftotext $escapedPath $escapedOutput 2>nul");
            } else {
                shell_exec("$pdftotext $escapedPath $escapedOutput 2>/dev/null");
            }

            if (file_exists($tempOutput)) {
                $text = file_get_contents($tempOutput);
                unlink($tempOutput);
                return $text;
            }
        }

        return '';
    }

    /**
     * Process scanned PDFs — convert to images then OCR each page
     */
    private function processScannedPdf($filePath) {
        $images = $this->convertPdfToImages($filePath);

        if (empty($images)) {
            throw new Exception('Failed to convert PDF to images (Ghostscript may not be installed)');
        }

        $fullText = '[OCR]' . "\n";
        foreach ($images as $imagePath) {
            try {
                $pageText = $this->processImage($imagePath);
                $fullText .= $pageText . "\n\n--- Page Break ---\n\n";
            } catch (Exception $e) {
                $fullText .= "[Page OCR failed: " . $e->getMessage() . "]\n\n";
            }
        }

        $this->cleanTempImages($images);
        return $fullText;
    }

    /**
     * Convert PDF pages to PNG images using Ghostscript
     */
    private function convertPdfToImages($pdfPath) {
        $gs = $this->getGhostscriptPath();
        if (!$gs) {
            return [];
        }

        $prefix = $this->tempDir . '/pdf_page_' . uniqid();
        $escapedPdf = escapeshellarg($pdfPath);
        $escapedPrefix = escapeshellarg($prefix);

        // Ghostscript command: render at 300 DPI for good OCR accuracy
        $dpi = 300;
        $escapedGs = escapeshellarg($gs);
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "$escapedGs -dNOPAUSE -dBATCH -sDEVICE=png16m -r$dpi -sOutputFile={$escapedPrefix}_%d.png $escapedPdf 2>nul";
        } else {
            $command = "$escapedGs -dNOPAUSE -dBATCH -sDEVICE=png16m -r$dpi -sOutputFile={$escapedPrefix}_%d.png $escapedPdf 2>/dev/null";
        }

        shell_exec($command);

        // Collect generated images
        $images = [];
        $files = glob($prefix . '_*.png');
        if ($files) {
            sort($files);
            $images = $files;
        }

        return $images;
    }

    /**
     * Extract text from Word documents (.docx)
     */
    private function processWord($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            return $this->extractFromDocx($filePath);
        }

        // .doc (legacy binary format) — try antiword or fallback
        $antiword = $this->detectCommand('antiword');
        if ($antiword) {
            $escapedPath = escapeshellarg($filePath);
            $output = shell_exec("$antiword $escapedPath 2>/dev/null");
            if ($output) return $output;
        }

        // Cannot extract from legacy .doc without external tools
        return '[Legacy .doc format — text extraction not available. Convert to .docx for full support.]';
    }

    /**
     * Extract text from .docx using ZipArchive
     */
    private function extractFromDocx($filePath) {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Cannot open .docx file');
        }

        $text = '';
        // Main document content
        $documentXml = $zip->getFromName('word/document.xml');
        if ($documentXml !== false) {
            $text .= $this->extractTextFromXml($documentXml);
        }

        // Headers and footers
        for ($i = 1; $i <= 3; $i++) {
            $headerXml = $zip->getFromName("word/header{$i}.xml");
            if ($headerXml !== false) {
                $text .= $this->extractTextFromXml($headerXml) . "\n";
            }
            $footerXml = $zip->getFromName("word/footer{$i}.xml");
            if ($footerXml !== false) {
                $text .= $this->extractTextFromXml($footerXml) . "\n";
            }
        }

        $zip->close();
        return $text;
    }

    /**
     * Extract text from Excel files (.xlsx)
     */
    private function processExcel($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            return $this->extractFromXlsx($filePath);
        }

        return '[Legacy .xls format — text extraction not available. Convert to .xlsx for full support.]';
    }

    /**
     * Extract text from .xlsx using ZipArchive
     */
    private function extractFromXlsx($filePath) {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Cannot open .xlsx file');
        }

        $text = '';
        // Shared strings (contains all text values)
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $text .= $this->extractTextFromXml($sharedStringsXml);
        }

        // Also check sheet files for inline text
        for ($i = 1; $i <= 20; $i++) {
            $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if ($sheetXml !== false) {
                $sheetText = $this->extractTextFromXml($sheetXml);
                if (!empty($sheetText)) {
                    $text .= "\n" . $sheetText;
                }
            } else {
                break;
            }
        }

        $zip->close();
        return $text;
    }

    /**
     * Extract text from PowerPoint files (.pptx)
     */
    private function processPowerPoint($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'pptx') {
            return $this->extractFromPptx($filePath);
        }

        return '[Legacy .ppt format — text extraction not available. Convert to .pptx for full support.]';
    }

    /**
     * Extract text from .pptx using ZipArchive
     */
    private function extractFromPptx($filePath) {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception('Cannot open .pptx file');
        }

        $text = '';
        // Slide files: ppt/slides/slide1.xml, slide2.xml, etc.
        for ($i = 1; $i <= 50; $i++) {
            $slideXml = $zip->getFromName("ppt/slides/slide{$i}.xml");
            if ($slideXml !== false) {
                $text .= $this->extractTextFromXml($slideXml) . "\n--- Slide {$i} ---\n";
            } else {
                break;
            }
        }

        // Notes slides
        for ($i = 1; $i <= 50; $i++) {
            $notesXml = $zip->getFromName("ppt/notesSlides/notesSlide{$i}.xml");
            if ($notesXml !== false) {
                $text .= $this->extractTextFromXml($notesXml) . "\n";
            } else {
                break;
            }
        }

        $zip->close();
        return $text;
    }

    /**
     * Extract text content from XML (strip tags, decode entities)
     */
    private function extractTextFromXml($xml) {
        // Remove XML tags but preserve text content
        $text = preg_replace('/<[^>]+>/', ' ', $xml);
        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Get Tesseract binary path
     */
    private function getTesseractPath() {
        if (!empty($this->tesseractPath) && file_exists($this->tesseractPath)) {
            return $this->tesseractPath;
        }
        return $this->detectTesseract();
    }

    /**
     * Get Ghostscript binary path
     */
    private function getGhostscriptPath() {
        if (!empty($this->ghostscriptPath) && file_exists($this->ghostscriptPath)) {
            return $this->ghostscriptPath;
        }
        return $this->detectGhostscript();
    }

    /**
     * Detect Tesseract binary on the system
     */
    private function detectTesseract() {
        if (PHP_OS_FAMILY === 'Windows') {
            $paths = [
                'C:\Program Files\Tesseract-OCR\tesseract.exe',
                'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
                'C:\Tesseract-OCR\tesseract.exe',
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }
        return $this->detectCommand('tesseract');
    }

    /**
     * Detect Ghostscript binary on the system
     */
    private function detectGhostscript() {
        if (PHP_OS_FAMILY === 'Windows') {
            $paths = [
                'C:\Program Files\gs\gs10.07.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.53.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.52.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.51.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.50.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.04.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.03.1\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.03.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.02.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.01.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs10.00.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs9.56.1\bin\gswin64c.exe',
                'C:\Program Files\gs\gs9.56.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs9.55.0\bin\gswin64c.exe',
                'C:\Program Files\gs\gs9.54.0\bin\gswin64c.exe',
                'C:\Program Files (x86)\gs\gs9.56.1\bin\gswin32c.exe',
                'C:\Program Files (x86)\gs\gs9.55.0\bin\gswin32c.exe',
            ];
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
            // Try glob pattern for any version
            $globs = glob('C:\Program Files\gs\gs*\bin\gswin64c.exe');
            if (!empty($globs)) {
                return $globs[0];
            }
            $globs = glob('C:\Program Files (x86)\gs\gs*\bin\gswin32c.exe');
            if (!empty($globs)) {
                return $globs[0];
            }
        }
        return $this->detectCommand('gs');
    }

    /**
     * Detect a command in PATH
     */
    private function detectCommand($name) {
        if (PHP_OS_FAMILY === 'Windows') {
            $result = shell_exec("where $name 2>nul");
            if ($result) {
                $lines = explode("\n", trim($result));
                if (!empty($lines[0]) && file_exists(trim($lines[0]))) {
                    return trim($lines[0]);
                }
            }
        } else {
            $result = shell_exec("which $name 2>/dev/null");
            if ($result) {
                $path = trim($result);
                if (!empty($path)) {
                    return $path;
                }
            }
        }
        return null;
    }

    /**
     * Clean up temporary image files
     */
    private function cleanTempImages($imagePaths) {
        foreach ($imagePaths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Ensure temp directory exists
     */
    private function ensureTempDir() {
        if (!is_dir($this->tempDir)) {
            @mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Check if Tesseract is available
     */
    public function isTesseractAvailable() {
        return $this->getTesseractPath() !== null;
    }

    /**
     * Check if Ghostscript is available
     */
    public function isGhostscriptAvailable() {
        return $this->getGhostscriptPath() !== null;
    }
}
