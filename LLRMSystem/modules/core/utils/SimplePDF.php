<?php

/**
 * Simple PDF Generator
 * Creates basic PDF files without external dependencies
 */
class SimplePDF {
    private $lines = [];
    private $title = '';
    private $author = '';
    
    public function __construct() {
        $this->lines = [];
    }
    
    public function SetTitle($title) {
        $this->title = $title;
    }
    
    public function SetAuthor($author) {
        $this->author = $author;
    }
    
    public function AddText($text, $fontSize = 12, $align = 'left', $style = 'normal') {
        $this->lines[] = [
            'text' => $text,
            'size' => $fontSize,
            'align' => $align,
            'style' => $style
        ];
    }
    
    public function Output() {
        // Use a simpler approach - create a well-formatted text PDF
        $content = '';
        $yPos = 750;
        
        foreach ($this->lines as $line) {
            $text = $this->escapeText($line['text']);
            $size = $line['size'];
            $font = $line['style'] === 'bold' ? 'Courier-Bold' : 'Courier';
            
            // Calculate X position
            $xPos = 50;
            if ($line['align'] === 'center') {
                // For Courier, each character is exactly 0.6 * fontSize wide
                $textWidth = strlen($text) * $size * 0.6;
                $xPos = (595 - $textWidth) / 2; // Center on A4 width
            }
            
            $content .= "BT\n";
            $content .= "/$font $size Tf\n";
            $content .= "$xPos $yPos Td\n";
            $content .= "($text) Tj\n";
            $content .= "ET\n";
            
            $yPos -= ($size + 6);
            
            // New page if needed
            if ($yPos < 50) {
                $content .= "showpage\n";
                $yPos = 750;
            }
        }
        
        $streamLength = strlen($content);
        
        // Build PDF
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /Courier 4 0 R /Courier-Bold 5 0 R >> >> /MediaBox [0 0 595 842] /Contents 6 0 R >>\nendobj\n";
        $pdf .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>\nendobj\n";
        $pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Courier-Bold >>\nendobj\n";
        $pdf .= "6 0 obj\n<< /Length $streamLength >>\nstream\n$content\nendstream\nendobj\n";
        
        // XRef table
        $xref = "xref\n0 7\n";
        $xref .= "0000000000 65535 f \n";
        
        $objects = [
            strpos($pdf, "1 0 obj"),
            strpos($pdf, "2 0 obj"),
            strpos($pdf, "3 0 obj"),
            strpos($pdf, "4 0 obj"),
            strpos($pdf, "5 0 obj"),
            strpos($pdf, "6 0 obj")
        ];
        
        foreach ($objects as $pos) {
            $xref .= sprintf("%010d 00000 n \n", $pos);
        }
        
        $xrefPos = strlen($pdf);
        $pdf .= $xref;
        
        // Trailer
        $pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\n";
        $pdf .= "startxref\n$xrefPos\n%%EOF";
        
        return $pdf;
    }
    
    private function escapeText($text) {
        // Handle special characters for PDF
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace("\n", ' ', $text);
        
        // Truncate long text
        if (strlen($text) > 180) {
            $text = substr($text, 0, 177) . '...';
        }
        
        return $text;
    }
}
