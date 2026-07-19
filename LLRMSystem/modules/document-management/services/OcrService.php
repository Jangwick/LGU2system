<?php
/**
 * OCR Service
 * 
 * Extracts text from documents using Tesseract OCR (for images/scanned PDFs)
 * and PHP native extraction (for digital PDFs, Word, Excel, PowerPoint).
 * 
 * No external API required — everything runs locally.
 */

require_once dirname(dirname(dirname(__DIR__))) . '/modules/ai/services/GroqService.php';

class OcrService {
    private $tesseractPath;
    private $ghostscriptPath;
    private $language;
    private $timeout;
    private $tempDir;
    private $enabled;
    private $groqService = null;
    private $groqFallback = false;
    private $groqEnhance = false;
    private $groqMaxPages = 0;
    private $groqModel = '';
    private $groqPagesUsed = 0;

    public function __construct() {
        $tessConfig = defined('OCR_TESSERACT_PATH') ? OCR_TESSERACT_PATH : '';
        $this->tesseractPath = !empty($tessConfig) ? $tessConfig : $this->detectTesseract();
        $gsConfig = defined('OCR_GHOSTSCRIPT_PATH') ? OCR_GHOSTSCRIPT_PATH : '';
        $this->ghostscriptPath = !empty($gsConfig) ? $gsConfig : $this->detectGhostscript();
        $this->language = defined('OCR_LANGUAGE') ? OCR_LANGUAGE : 'eng';
        $this->timeout = defined('OCR_TIMEOUT') ? OCR_TIMEOUT : 30;
        $this->enabled = defined('OCR_ENABLED') ? OCR_ENABLED : true;
        $this->groqFallback = defined('OCR_GROQ_FALLBACK') ? OCR_GROQ_FALLBACK : false;
        $this->groqEnhance = defined('OCR_GROQ_ENHANCE') ? OCR_GROQ_ENHANCE : true;
        $this->groqMaxPages = defined('OCR_GROQ_MAX_PAGES') ? OCR_GROQ_MAX_PAGES : 0;
        $this->groqModel = defined('OCR_GROQ_MODEL') ? OCR_GROQ_MODEL : 'qwen/qwen3.6-27b';
        $this->tempDir = dirname(dirname(dirname(__DIR__))) . '/storage/temp/ocr';
        $this->ensureTempDir();
    }

    /**
     * Lazy-load GroqService for AI vision OCR fallback
     */
    private function getGroqService() {
        if ($this->groqService !== null) {
            return $this->groqService;
        }

        if (!defined('GROQ_API_KEY') || GROQ_API_KEY === '') {
            return null;
        }

        $this->groqService = new GroqService();
        return $this->groqService;
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
     * Process image files with Tesseract OCR, falling back to Groq vision
     */
    private function processImage($filePath) {
        $text = '';
        $tesseract = $this->getTesseractPath();

        if ($tesseract) {
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

            if ($output !== null) {
                $text = trim($output);
            }
        }

        // Use Groq vision to either supplement Tesseract (enhance) or replace it (fallback)
        $useGroq = false;
        if ($this->groqPagesUsed < $this->groqMaxPages) {
            if ($this->groqEnhance) {
                $useGroq = true;
            } elseif ($this->groqFallback && empty($text)) {
                $useGroq = true;
            }
        }

        if ($useGroq) {
            $groq = $this->getGroqService();
            if ($groq) {
                @set_time_limit(180);
                $result = $groq->extractTextFromImage($filePath, $text, $this->groqModel, 2048);
                if (is_array($result)) {
                    $this->groqPagesUsed++;
                    if (empty($text)) {
                        $text = $result['text'] ?? '';
                    } else {
                        $additional = $result['additional_text'] ?? '';
                        if (!empty($additional)) {
                            $text .= "\n\n[Additional text detected by AI OCR]\n" . trim($additional);
                        }
                    }
                    $visual = $result['visual_elements'] ?? '';
                    if (!empty($visual)) {
                        $text .= "\n\n[Visual elements]\n" . trim($visual);
                    }
                } elseif ($groq->getLastError()) {
                    throw new Exception('Groq vision OCR failed: ' . $groq->getLastError());
                }
            }
        }

        if (empty($text) && !$tesseract) {
            throw new Exception('Tesseract binary not found and Groq fallback not available');
        }

        return $text;
    }

    /**
     * Decide whether extracted text contains enough real content to be useful.
     */
    private function isTextUsable($text) {
        $text = trim((string) $text);
        if (empty($text)) {
            return false;
        }

        // Strip common OCR/PDF utility markers and page-break noise
        $text = preg_replace('/\[[^\]]+\]|---[^-]+---|Page Break|Tesseract|Ghostscript|pdftotext|smalot|Created (with|by)|Title|Author|Subject|Keywords|Producer|Creator|ProducerID|ModDate|CreationDate|Scanned|OCR|PDF|Document/i', '', $text);

        // Collapse whitespace and count alphanumeric words/characters
        $text = preg_replace('/\s+/', '', $text);
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $text);
        $wordCount = preg_match_all('/[A-Za-z0-9]+/', $text);

        return strlen($clean) >= 150 && $wordCount >= 20;
    }

    /**
     * Process PDF files — try digital extraction first, fall back to OCR
     */
    private function processPdf($filePath) {
        // First, try to extract embedded text using smalot/pdfparser
        $digitalText = $this->extractDigitalPdfText($filePath);

        if ($this->isTextUsable($digitalText)) {
            return $digitalText;
        }

        // Digital text is empty or only markers/noise — treat as a scanned PDF
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

        // Fallback 1: try pdftotext command if available
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
                if (!empty(trim($text))) {
                    return $text;
                }
            }
        }

        // Fallback 2: use Ghostscript txtwrite to extract digital text (no OCR needed)
        return $this->extractPdfTextWithGhostscript($filePath);
    }

    /**
     * Extract text from digital PDFs using Ghostscript txtwrite device
     */
    private function extractPdfTextWithGhostscript($filePath) {
        $gs = $this->getGhostscriptPath();
        if (!$gs) {
            return '';
        }

        $escapedGs = escapeshellarg($gs);
        $escapedPath = escapeshellarg($filePath);

        if (PHP_OS_FAMILY === 'Windows') {
            $command = "$escapedGs -dNOPAUSE -dBATCH -sDEVICE=txtwrite -sOutputFile=- -q $escapedPath 2>nul";
        } else {
            $command = "$escapedGs -dNOPAUSE -dBATCH -sDEVICE=txtwrite -sOutputFile=- -q $escapedPath 2>/dev/null";
        }

        $output = shell_exec($command);

        if ($output !== null) {
            $text = trim($output);
            if (!empty($text)) {
                return $text;
            }
        }

        return '';
    }

    /**
     * Process scanned PDFs — convert to images then OCR each page
     */
    private function processScannedPdf($filePath) {
        if (!$this->getTesseractPath() && !$this->getGroqService()) {
            throw new Exception('Tesseract OCR binary not found and Groq fallback not available — scanned PDF text extraction is unavailable');
        }

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
     * Extract text from .docx using ZipArchive or unzip fallback
     */
    private function extractFromDocx($filePath) {
        $text = '';

        if ($this->isZipAvailable()) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception('Cannot open .docx file');
            }

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
        } else {
            // Fallback: use unzip command
            $documentXml = $this->extractZipFile($filePath, 'word/document.xml');
            if ($documentXml !== null) {
                $text .= $this->extractTextFromXml($documentXml);
            }

            for ($i = 1; $i <= 3; $i++) {
                $headerXml = $this->extractZipFile($filePath, "word/header{$i}.xml");
                if ($headerXml !== null) {
                    $text .= $this->extractTextFromXml($headerXml) . "\n";
                }
                $footerXml = $this->extractZipFile($filePath, "word/footer{$i}.xml");
                if ($footerXml !== null) {
                    $text .= $this->extractTextFromXml($footerXml) . "\n";
                }
            }
        }

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
     * Extract text from .xlsx using ZipArchive or unzip fallback
     */
    private function extractFromXlsx($filePath) {
        $text = '';

        if ($this->isZipAvailable()) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception('Cannot open .xlsx file');
            }

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
        } else {
            // Fallback: use unzip command
            $sharedStringsXml = $this->extractZipFile($filePath, 'xl/sharedStrings.xml');
            if ($sharedStringsXml !== null) {
                $text .= $this->extractTextFromXml($sharedStringsXml);
            }

            for ($i = 1; $i <= 20; $i++) {
                $sheetXml = $this->extractZipFile($filePath, "xl/worksheets/sheet{$i}.xml");
                if ($sheetXml !== null) {
                    $sheetText = $this->extractTextFromXml($sheetXml);
                    if (!empty($sheetText)) {
                        $text .= "\n" . $sheetText;
                    }
                } else {
                    break;
                }
            }
        }

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
     * Extract text from .pptx using ZipArchive or unzip fallback
     */
    private function extractFromPptx($filePath) {
        $text = '';

        if ($this->isZipAvailable()) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception('Cannot open .pptx file');
            }

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
        } else {
            // Fallback: use unzip command
            for ($i = 1; $i <= 50; $i++) {
                $slideXml = $this->extractZipFile($filePath, "ppt/slides/slide{$i}.xml");
                if ($slideXml !== null) {
                    $text .= $this->extractTextFromXml($slideXml) . "\n--- Slide {$i} ---\n";
                } else {
                    break;
                }
            }

            for ($i = 1; $i <= 50; $i++) {
                $notesXml = $this->extractZipFile($filePath, "ppt/notesSlides/notesSlide{$i}.xml");
                if ($notesXml !== null) {
                    $text .= $this->extractTextFromXml($notesXml) . "\n";
                } else {
                    break;
                }
            }
        }

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
        if (!empty($this->tesseractPath) && @file_exists($this->tesseractPath)) {
            return $this->tesseractPath;
        }
        return $this->detectTesseract();
    }

    /**
     * Get Ghostscript binary path
     */
    private function getGhostscriptPath() {
        if (!empty($this->ghostscriptPath) && @file_exists($this->ghostscriptPath)) {
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
                if (@file_exists($path)) {
                    return $path;
                }
            }
        } else {
            // Check common user-space paths (for shared hosting without root)
            $home = $this->getHomeDir();
            $homePaths = [
                $home . '/bin/tesseract_wrapper.sh',
                $home . '/bin/tesseract',
                '/usr/local/bin/tesseract',
            ];
            foreach ($homePaths as $path) {
                if (!empty($path) && @is_executable($path)) {
                    return $path;
                }
            }
        }
        return $this->detectCommand('tesseract');
    }

    /**
     * Determine the user's home directory
     */
    private function getHomeDir() {
        $home = getenv('HOME');
        if (!empty($home)) {
            return $home;
        }
        if (function_exists('posix_getpwuid') && function_exists('posix_getuid')) {
            $info = posix_getpwuid(posix_getuid());
            if (!empty($info['dir'])) {
                return $info['dir'];
            }
        }
        return '/tmp';
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
                if (@file_exists($path)) {
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
                if (!empty($lines[0]) && @file_exists(trim($lines[0]))) {
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
     * Check if ZipArchive extension is available
     */
    private function isZipAvailable() {
        return class_exists('ZipArchive');
    }

    /**
     * Extract a single file from a zip archive using unzip command
     * Returns file contents or null if file not found
     */
    private function extractZipFile($zipPath, $internalPath) {
        $unzip = $this->detectCommand('unzip');
        if (!$unzip) {
            throw new Exception('Neither ZipArchive extension nor unzip command is available');
        }

        $tempFile = $this->tempDir . '/' . uniqid('zip_') . '.xml';
        $escapedZip = escapeshellarg($zipPath);
        $escapedInternal = escapeshellarg($internalPath);
        $escapedTemp = escapeshellarg($tempFile);

        if (PHP_OS_FAMILY === 'Windows') {
            shell_exec("$unzip -p $escapedZip $escapedInternal > $escapedTemp 2>nul");
        } else {
            shell_exec("$unzip -p $escapedZip $escapedInternal > $escapedTemp 2>/dev/null");
        }

        if (file_exists($tempFile) && filesize($tempFile) > 0) {
            $content = file_get_contents($tempFile);
            @unlink($tempFile);
            return $content;
        }

        @unlink($tempFile);
        return null;
    }

    /**
     * Clean up temporary image files
     */
    private function cleanTempImages($imagePaths) {
        foreach ($imagePaths as $path) {
            if (@file_exists($path)) {
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
