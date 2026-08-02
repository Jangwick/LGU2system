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
    private $groqService;
    private $groqEnhance;
    private $groqMaxPages;
    private $maxPdfPages;
    private $lastGroqRequestTime = 0;
    private $geminiService;
    private $geminiEnabled;
    private $geminiFallback;
    private $geminiEnhance;

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

        // Initialize GroqService for AI vision fallback (default disabled)
        $this->groqService = null;
        $groqFallbackEnabled = defined('OCR_GROQ_FALLBACK') && OCR_GROQ_FALLBACK;
        if ($groqFallbackEnabled && defined('GROQ_API_KEY') && !empty(GROQ_API_KEY)) {
            try {
                require_once __DIR__ . '/../../ai/services/GroqService.php';
                $this->groqService = new GroqService();
            } catch (Exception $e) {
                error_log('OcrService: GroqService init failed: ' . $e->getMessage());
            }
        }

        $this->groqEnhance = defined('OCR_GROQ_ENHANCE') ? (bool)OCR_GROQ_ENHANCE : false;  
        $this->groqMaxPages = defined('OCR_GROQ_MAX_PAGES') ? OCR_GROQ_MAX_PAGES : 1;
        $this->maxPdfPages = defined('OCR_MAX_PDF_PAGES') ? OCR_MAX_PDF_PAGES : 0;

        // Initialize Gemini OCR if enabled
        $this->geminiService = null;
        $this->geminiEnabled = defined('OCR_GEMINI_ENABLED') && OCR_GEMINI_ENABLED;
        $this->geminiFallback = defined('OCR_GEMINI_FALLBACK') ? (bool)OCR_GEMINI_FALLBACK : true;
        $this->geminiEnhance = defined('OCR_GEMINI_ENHANCE') ? (bool)OCR_GEMINI_ENHANCE : false;
        if ($this->geminiEnabled && defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY)) {
            try {
                require_once __DIR__ . '/../../ai/services/GeminiOcrService.php';
                $this->geminiService = new GeminiOcrService();
            } catch (Exception $e) {
                error_log('OcrService: GeminiOcrService init failed: ' . $e->getMessage());
            }
        }

        // Ensure Tesseract can find its libraries and language data on shared hosting
        $this->setupTesseractEnvironment();
    }

    /**
     * Set environment variables required for Tesseract to run in user-space installs
     */
    private function setupTesseractEnvironment() {
        if (PHP_OS_FAMILY === 'Windows') {
            return;
        }

        $home = getenv('HOME') ?: '/home/llrm.spvalenzuela.com';
        $libPaths = [];
        $tessdataPaths = [];

        // Only check paths within open_basedir allowed directories
        $allowedRoots = [$home, '/tmp'];
        $candidates = [
            $home . '/tesseract',
            $home . '/Tesseract-OCR',
        ];

        foreach ($candidates as $base) {
            if (@is_dir($base . '/usr/lib/x86_64-linux-gnu')) {
                $libPaths[] = $base . '/usr/lib/x86_64-linux-gnu';
            }
            if (@is_dir($base . '/lib/x86_64-linux-gnu')) {
                $libPaths[] = $base . '/lib/x86_64-linux-gnu';
            }
            if (@is_dir($base . '/lib')) {
                $libPaths[] = $base . '/lib';
            }
            if (@is_dir($base . '/usr/share/tesseract-ocr/4.00/tessdata')) {
                $tessdataPaths[] = $base . '/usr/share/tesseract-ocr/4.00/tessdata';
            }
            if (@is_dir($base . '/share/tessdata')) {
                $tessdataPaths[] = $base . '/share/tessdata';
            }
            if (@is_dir($base . '/usr/share/tessdata')) {
                $tessdataPaths[] = $base . '/usr/share/tessdata';
            }
        }

        // Also check $home/share/tessdata (common user-space location)
        if (@is_dir($home . '/share/tessdata')) {
            $tessdataPaths[] = $home . '/share/tessdata';
        }

        $ldLibrary = getenv('LD_LIBRARY_PATH') ?: '';
        $ldParts = array_filter(array_unique(array_merge($libPaths, explode(':', $ldLibrary))));
        if (!empty($ldParts)) {
            putenv('LD_LIBRARY_PATH=' . implode(':', $ldParts));
        }

        $tessdata = getenv('TESSDATA_PREFIX') ?: '';
        if (empty($tessdata) && !empty($tessdataPaths)) {
            // Only use a tessdata path that actually contains the traineddata files
            foreach ($tessdataPaths as $path) {
                if (file_exists($path . '/eng.traineddata')) {
                    putenv('TESSDATA_PREFIX=' . $path);
                    break;
                }
            }
        }
    }

    /**
     * Main entry point — extract text from any supported file
     */
    public function extractText($filePath, $mimeType = null, $options = []) {
        if (!$this->enabled) {
            return ['text' => '', 'status' => 'skipped', 'error' => 'OCR disabled in config'];
        }

        if (!file_exists($filePath)) {
            return ['text' => '', 'status' => 'failed', 'error' => 'File not found: ' . $filePath];
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeType ?: mime_content_type($filePath);
        $enhance = $options['enhance'] ?? $this->groqEnhance;

        try {
            $text = '';
            $method = '';

            switch (true) {
                // Images — direct Tesseract OCR
                case in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp']):
                case strpos($mimeType, 'image/') === 0:
                    $text = $this->processImage($filePath, $enhance);
                    $method = 'tesseract';
                    break;

                // PDF — try digital extraction first, fall back to OCR
                case $extension === 'pdf' || $mimeType === 'application/pdf':
                    $text = $this->processPdf($filePath, $enhance);
                    $method = (strpos($text, '[Page OCR failed') !== false || strpos($text, '--- Page Break ---') !== false) ? 'tesseract' : 'pdfparser';
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
     * Process image files with Tesseract OCR, enhancing/falling back to Groq vision
     */
    private function processImage($filePath, $enhance = true, $allowFallback = true) {
        $tesseractText = '';
        $tesseractError = null;

        // Try Gemini 2 vision first if enabled
        $geminiText = '';
        if ($this->geminiService && $this->geminiEnabled) {
            try {
                $geminiText = $this->geminiService->extractText($filePath);
            } catch (Throwable $e) {
                error_log('OcrService: Gemini OCR exception: ' . $e->getMessage());
            }
        }

        $trimmedGemini = trim($geminiText);

        if (!empty($trimmedGemini)) {
            // Optional enhancement with Groq on top of Gemini result
            if ($this->geminiEnhance && $this->groqService) {
                try {
                    $this->throttleGroqRequest();
                    $this->lastGroqRequestTime = microtime(true);
                    $groqResult = $this->groqService->extractTextFromImage($filePath, $geminiText);
                    if (is_array($groqResult)) {
                        $additionalText = trim($groqResult['additional_text'] ?? '');
                        $visualElements = trim($groqResult['visual_elements'] ?? '');

                        if (!empty($additionalText)) {
                            $geminiText .= "\n\n[Additional text from vision enhancement]\n" . $additionalText;
                        }

                        if (!empty($visualElements)) {
                            $geminiText .= "\n\n[Visual elements: " . $visualElements . ']';
                        }
                    }
                } catch (Throwable $e) {
                    error_log('OcrService: Groq enhancement after Gemini exception: ' . $e->getMessage());
                }
            }

            error_log('OcrService: Gemini OCR used for ' . basename($filePath));
            return $geminiText;
        }

        if ($this->geminiEnabled && !$this->geminiFallback) {
            throw new Exception('Gemini OCR returned no text and fallback is disabled');
        }

        // Try Tesseract
        $tesseract = $this->getTesseractPath();
        if ($tesseract) {
            $escapedTesseract = escapeshellarg($tesseract);
            $escapedPath = escapeshellarg($filePath);
            $lang = $this->language;
            $timeout = $this->timeout;

            if (PHP_OS_FAMILY === 'Windows') {
                $outputFile = $this->tempDir . '\\ocr_' . uniqid();
                $command = "$escapedTesseract $escapedPath " . escapeshellarg($outputFile) . " -l $lang 2>nul";
            } else {
                $outputFile = $this->tempDir . '/ocr_' . uniqid();
                $command = "$escapedTesseract $escapedPath " . escapeshellarg($outputFile) . " -l $lang";
            }

            $outputLines = [];
            $returnCode = 0;
            if (PHP_OS_FAMILY !== 'Windows' && $timeout > 0) {
                $wrappedCommand = "timeout $timeout $command 2>&1";
                exec($wrappedCommand, $outputLines, $returnCode);
                if ($returnCode == 124) {
                    @unlink($outputFile . '.txt');
                    $tesseractError = "Tesseract timed out after {$timeout}s";
                }
            } else {
                exec($command . ' 2>&1', $outputLines, $returnCode);
            }
            $output = implode("\n", $outputLines);
            $outputTxt = $outputFile . '.txt';

            if ($returnCode !== 0 && $tesseractError === null) {
                @unlink($outputTxt);
                $tesseractError = 'Tesseract command failed (exit ' . $returnCode . '): ' . ($output ?: $command);
            }

            if ($returnCode === 0) {
                $tesseractText = @file_get_contents($outputTxt) ?: '';
                @unlink($outputTxt);
            }
        } else {
            $tesseractError = 'Tesseract binary not found';
        }

        $trimmedTesseract = trim($tesseractText);

        // Decide whether to call Groq: explicit enhancement, or fallback because Tesseract produced nothing
        $useGroqEnhance = $this->groqService && $enhance && $this->groqEnhance;
        $useGroqFallback = $this->groqService && $allowFallback && empty($trimmedTesseract);

        if ($useGroqEnhance || $useGroqFallback) {
            try {
                // Enforce a minimum gap between Groq vision requests to avoid rate limits
                $this->throttleGroqRequest();
                $this->lastGroqRequestTime = microtime(true);

                // Pass Tesseract text so Groq only returns what Tesseract missed
                $groqResult = $this->groqService->extractTextFromImage($filePath, $tesseractText);
                if (is_array($groqResult)) {
                    $additionalText = trim($groqResult['additional_text'] ?? '');
                    $groqText = trim($groqResult['text'] ?? '');
                    $visualElements = trim($groqResult['visual_elements'] ?? '');

                    if (empty($trimmedTesseract)) {
                        // Groq fallback: no Tesseract output, use Groq's full extraction
                        $pageText = $groqText;
                    } else {
                        // Groq enhancement: start with Tesseract and append only what it missed
                        $pageText = $tesseractText;
                        if (!empty($additionalText)) {
                            $pageText .= "\n\n[Additional text from vision enhancement]\n" . $additionalText;
                        }
                    }

                    if (!empty($visualElements)) {
                        $pageText .= "\n\n[Visual elements: " . $visualElements . ']';
                    }

                    if (!empty($pageText)) {
                        error_log('OcrService: Groq vision used for ' . basename($filePath));
                        return $pageText;
                    }
                }
            } catch (Throwable $e) {
                error_log('OcrService: Groq vision exception: ' . $e->getMessage());
            }
        }

        if (!empty($trimmedTesseract)) {
            return $tesseractText;
        }

        if ($tesseractError) {
            throw new Exception($tesseractError);
        }
        throw new Exception('No text could be extracted from image');
    }

    /**
     * Process PDF files — try digital extraction first, fall back to OCR
     */
    private function throttleGroqRequest() {
        if ($this->lastGroqRequestTime <= 0) {
            return;
        }
        $gapMs = defined('OCR_GROQ_DELAY_MS') ? (int)OCR_GROQ_DELAY_MS : 1000;
        if ($gapMs <= 0) {
            return;
        }
        $elapsedUs = (microtime(true) - $this->lastGroqRequestTime) * 1000000;
        $gapUs = $gapMs * 1000;
        if ($elapsedUs < $gapUs) {
            usleep((int)($gapUs - $elapsedUs));
        }
    }

    private function processPdf($filePath, $enhance = null) {
        // First, try to extract embedded text using smalot/pdfparser
        $digitalText = $this->extractDigitalPdfText($filePath);

        // Only accept digital text if it's substantial enough
        // Scanned PDFs sometimes have minimal embedded text (headers, watermarks)
        // that isn't the actual document content
        $trimmedText = trim($digitalText);
        $wordCount = str_word_count($trimmedText);
        if (!empty($trimmedText) && strlen($trimmedText) >= 500 && $wordCount >= 100) {
            return $digitalText;
        }

        // Insufficient digital text — use OCR (Tesseract + Groq fallback)
        try {
            $ocrText = $this->processScannedPdf($filePath, $enhance);
            // If OCR produced more text than digital extraction, use OCR result
            if (strlen(trim($ocrText)) > strlen($trimmedText)) {
                return $ocrText;
            }
        } catch (Exception $e) {
            error_log('OcrService: OCR fallback failed in processPdf: ' . $e->getMessage());
        }

        // Return whatever digital text we have (even if minimal)
        return $digitalText;
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
     * Falls back to Groq AI vision when Tesseract fails or returns empty text
     */
    private function processScannedPdf($filePath, $enhance = null) {
        $images = $this->convertPdfToImages($filePath);

        if (empty($images)) {
            throw new Exception('Failed to convert PDF to images (Ghostscript may not be installed)');
        }

        $fullText = '';
        $pageCount = 0;
        foreach ($images as $index => $imagePath) {
            if ($this->maxPdfPages > 0 && $pageCount >= $this->maxPdfPages) {
                break;
            }
            $pageText = '';

            // Allow Groq vision only for the first N pages (0 = all pages)
            $pageEnhance = ($enhance === null) ? (($this->groqMaxPages <= 0) || ($index < $this->groqMaxPages)) : $enhance;
            $pageAllowFallback = ($this->groqMaxPages <= 0) || ($index < $this->groqMaxPages);

            try {
                $pageText = $this->processImage($imagePath, $pageEnhance, $pageAllowFallback);
            } catch (Exception $e) {
                error_log('OcrService: OCR failed on ' . basename($imagePath) . ': ' . $e->getMessage());
            }

            if (empty(trim($pageText))) {
                $fullText .= "[Page OCR failed: no text extracted]\n\n";
            } else {
                $fullText .= $pageText . "\n\n--- Page Break ---\n\n";
            }
            $pageCount++;
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

        // Ghostscript command: render at configured DPI (default 100) grayscale for faster OCR on shared hosting
        $dpi = defined('OCR_GS_DPI') ? (int)OCR_GS_DPI : 100;
        $escapedGs = escapeshellarg($gs);
        $outputPattern = escapeshellarg($prefix . '_%d.png');
        $lastPageOption = ($this->maxPdfPages > 0) ? (' -dLastPage=' . (int)$this->maxPdfPages) : '';
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "$escapedGs -dNOPAUSE -dBATCH -sDEVICE=pnggray -r$dpi$lastPageOption -sOutputFile=$outputPattern $escapedPdf 2>nul";
        } else {
            $command = "$escapedGs -dNOPAUSE -dBATCH -sDEVICE=pnggray -r$dpi$lastPageOption -sOutputFile=$outputPattern $escapedPdf";
        }

        $gsOutput = shell_exec($command . ' 2>&1');

        // Collect generated images
        $images = [];
        $files = glob($prefix . '_*.png');
        if ($files) {
            sort($files);
            $images = $files;
        }

        if (empty($images)) {
            throw new Exception('Ghostscript failed to convert PDF to images: ' . ($gsOutput ?: 'no output'));
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
                if (file_exists($path)) {
                    return $path;
                }
            }
        } else {
            // Check common user-space paths (for shared hosting without root)
            $home = getenv('HOME') ?: '/home/llrm.spvalenzuela.com';
            $homePaths = [
                $home . '/bin/tesseract',
                $home . '/bin/tesseract_wrapper.sh',
                '/home/llrm.spvalenzuela.com/bin/tesseract',
                '/home/llrm.spvalenzuela.com/bin/tesseract_wrapper.sh',
                '/usr/local/bin/tesseract',
            ];
            foreach ($homePaths as $path) {
                if (@file_exists($path) && @is_executable($path)) {
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
