<?php

/**
 * Lightweight DOCX to HTML converter for in-browser preview.
 *
 * DOCX is a ZIP archive containing XML. This class extracts the main
 * document.xml, applies the default style definitions, and converts the
 * XML structure to a semantic HTML document with inline CSS.
 */
class DocxToHtml
{
    private $zip;
    private $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private $relsNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private $styles = [];
    private $rels = [];
    private $numbering = [];
    private $footnotes = [];

    /**
     * Convert a DOCX file path to HTML.
     *
     * @param string $filePath
     * @return array ['success' => bool, 'html' => string, 'error' => string]
     */
    public function convert($filePath)
    {
        if (!extension_loaded('zip') || !class_exists('ZipArchive')) {
            return ['success' => false, 'error' => 'ZIP extension is not available', 'html' => ''];
        }

        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found', 'html' => ''];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ['success' => false, 'error' => 'Could not open DOCX archive', 'html' => ''];
        }

        $this->zip = $zip;

        // Load relationships (for images, hyperlinks, etc.)
        $this->loadRelationships();

        // Load styles
        $this->loadStyles();

        // Load numbering definitions
        $this->loadNumbering();

        // Load footnotes
        $this->loadFootnotes();

        // Get main document XML
        $documentXml = $zip->getFromName('word/document.xml');
        if ($documentXml === false) {
            return ['success' => false, 'error' => 'Could not read document.xml', 'html' => ''];
        }

        $dom = new DOMDocument();
        @$dom->loadXML($documentXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', $this->ns);

        $body = $xpath->query('//w:body')->item(0);
        if (!$body) {
            return ['success' => false, 'error' => 'No document body found', 'html' => ''];
        }

        $html = [];
        $html[] = '<div class="docx-preview" style="font-family: Georgia, Times New Roman, serif; font-size: 13pt; line-height: 1.8; color: #1a1a1a; padding: 2em; max-width: 8.5in; margin: 0 auto; background: #fff;">';

        foreach ($body->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if ($node->localName === 'p') {
                $html[] = $this->renderParagraph($node);
            } elseif ($node->localName === 'tbl') {
                $html[] = $this->renderTable($node);
            } elseif ($node->localName === 'sdt') {
                // Structured document tags (content controls): render inner block content
                $html[] = $this->renderSdt($node);
            }
        }

        $html[] = '</div>';

        $zip->close();

        return [
            'success' => true,
            'html' => implode("\n", $html),
            'error' => ''
        ];
    }

    /**
     * Load document relationships from word/_rels/document.xml.rels
     */
    private function loadRelationships()
    {
        $relsXml = $this->zip->getFromName('word/_rels/document.xml.rels');
        if ($relsXml === false) {
            return;
        }

        $dom = new DOMDocument();
        @$dom->loadXML($relsXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $rels = $xpath->query('//r:Relationship');
        foreach ($rels as $rel) {
            $id = $rel->getAttribute('Id');
            $type = $rel->getAttribute('Type');
            $target = $rel->getAttribute('Target');
            $this->rels[$id] = ['type' => $type, 'target' => $target];
        }
    }

    /**
     * Load styles from word/styles.xml
     */
    private function loadStyles()
    {
        $stylesXml = $this->zip->getFromName('word/styles.xml');
        if ($stylesXml === false) {
            return;
        }

        $dom = new DOMDocument();
        @$dom->loadXML($stylesXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', $this->ns);

        $styles = $xpath->query('//w:style');
        foreach ($styles as $style) {
            $styleId = $style->getAttribute('w:styleId');
            $styleType = $style->getAttribute('w:type');
            $name = $style->getAttribute('w:styleId');
            $basedOn = $style->getAttribute('w:basedOn');

            $pPrNode = $xpath->query('w:pPr', $style)->item(0);
            $rPrNode = $xpath->query('w:rPr', $style)->item(0);

            $this->styles[$styleId] = [
                'type' => $styleType,
                'basedOn' => $basedOn,
                'pPr' => $pPrNode,
                'rPr' => $rPrNode,
            ];
        }
    }

    /**
     * Load numbering definitions from word/numbering.xml
     */
    private function loadNumbering()
    {
        $numXml = $this->zip->getFromName('word/numbering.xml');
        if ($numXml === false) {
            return;
        }

        $dom = new DOMDocument();
        @$dom->loadXML($numXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', $this->ns);

        $abstractNums = $xpath->query('//w:abstractNum');
        foreach ($abstractNums as $num) {
            $abstractId = $num->getAttribute('w:abstractNumId');
            $levels = $xpath->query('w:lvl', $num);
            foreach ($levels as $lvl) {
                $ilvl = $lvl->getAttribute('w:ilvl');
                $numFmt = $xpath->query('w:numFmt', $lvl)->item(0);
                $numText = $xpath->query('w:lvlText', $lvl)->item(0);
                $this->numbering[$abstractId][$ilvl] = [
                    'numFmt' => $numFmt ? $numFmt->getAttribute('w:val') : 'decimal',
                    'text' => $numText ? $numText->getAttribute('w:val') : '%1.',
                ];
            }
        }

        $nums = $xpath->query('//w:num');
        foreach ($nums as $num) {
            $numId = $num->getAttribute('w:numId');
            $abstractId = $xpath->query('w:abstractNumId', $num)->item(0);
            if ($abstractId) {
                $this->numbering[$numId]['abstract'] = $abstractId->getAttribute('w:val');
            }
        }
    }

    /**
     * Load footnotes from word/footnotes.xml
     */
    private function loadFootnotes()
    {
        $footnotesXml = $this->zip->getFromName('word/footnotes.xml');
        if ($footnotesXml === false) {
            return;
        }

        $dom = new DOMDocument();
        @$dom->loadXML($footnotesXml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', $this->ns);

        $footnotes = $xpath->query('//w:footnote');
        foreach ($footnotes as $fn) {
            $id = $fn->getAttribute('w:id');
            $html = '';
            foreach ($fn->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'p') {
                    $html .= $this->renderParagraph($child);
                }
            }
            $this->footnotes[$id] = $html;
        }
    }

    /**
     * Render a paragraph (w:p) as HTML
     */
    private function renderParagraph($pNode)
    {
        $xpath = new DOMXPath($pNode->ownerDocument);
        $xpath->registerNamespace('w', $this->ns);

        $pPr = $xpath->query('w:pPr', $pNode)->item(0);
        $styleId = $this->getNodeAttribute($pPr, 'w:pStyle', 'w:val');
        $numId = $this->getNodeAttribute($pPr, 'w:numPr/w:numId', 'w:val');
        $ilvl = $this->getNodeAttribute($pPr, 'w:numPr/w:ilvl', 'w:val');

        // Heading detection
        $isHeading = false;
        $headingLevel = 0;
        if ($styleId && preg_match('/Heading(\d)/i', $styleId, $m)) {
            $isHeading = true;
            $headingLevel = (int) $m[1];
        } elseif ($styleId && stripos($styleId, 'title') !== false) {
            $isHeading = true;
            $headingLevel = 1;
        }

        // Compute paragraph style
        $styles = $this->computeParagraphStyle($pPr, $styleId);

        // Build children
        $children = [];
        foreach ($pNode->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if ($child->localName === 'r') {
                $children[] = $this->renderRun($child, $styleId);
            } elseif ($child->localName === 'hyperlink') {
                $children[] = $this->renderHyperlink($child);
            } elseif ($child->localName === 'bookmarkStart' || $child->localName === 'bookmarkEnd') {
                // Ignore bookmarks
            }
        }

        $content = trim(implode('', $children));
        if ($content === '') {
            // Empty paragraph
            return '<p style="margin: 0; padding: 0; line-height: 1.8;">&nbsp;</p>';
        }

        if ($isHeading) {
            $tag = 'h' . max(1, min(6, $headingLevel));
            return "<{$tag} style=\"{$styles}\">{$content}</{$tag}>";
        }

        if ($numId !== '') {
            $listType = $this->getListType($numId, $ilvl);
            $listStyle = $styles . ' margin-left: ' . ((int) $ilvl * 1.5 + 0.5) . 'em; ';
            return "<div class=\"docx-list\" style=\"{$listStyle}\">{$content}</div>";
        }

        $align = $this->getNodeAttribute($pPr, 'w:jc', 'w:val');
        if ($align) {
            $map = ['left' => 'left', 'right' => 'right', 'center' => 'center', 'both' => 'justify'];
            if (isset($map[$align])) {
                $styles .= 'text-align: ' . $map[$align] . '; ';
            }
        }

        return "<p style=\"{$styles}\">{$content}</p>";
    }

    /**
     * Render a run (w:r) of text with formatting
     */
    private function renderRun($rNode, $paragraphStyleId = null)
    {
        $xpath = new DOMXPath($rNode->ownerDocument);
        $xpath->registerNamespace('w', $this->ns);

        $rPr = $xpath->query('w:rPr', $rNode)->item(0);
        $style = $this->computeRunStyle($rPr, $paragraphStyleId);

        $parts = [];
        foreach ($rNode->childNodes as $child) {
            if ($child->localName === 't') {
                $parts[] = htmlspecialchars($child->nodeValue, ENT_QUOTES, 'UTF-8');
            } elseif ($child->localName === 'tab') {
                $parts[] = '&nbsp;&nbsp;&nbsp;&nbsp;';
            } elseif ($child->localName === 'br') {
                $parts[] = '<br>';
            } elseif ($child->localName === 'footnoteReference') {
                $id = $child->getAttribute('w:id');
                $parts[] = ' <sup>(' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . ')</sup> ';
            } elseif ($child->localName === 'drawing' || $child->localName === 'pict') {
                $parts[] = $this->renderDrawing($child);
            }
        }

        $text = implode('', $parts);

        if ($text === '') {
            return '';
        }

        $tags = [];
        if ($style['bold']) $tags[] = 'strong';
        if ($style['italic']) $tags[] = 'em';
        if ($style['underline']) $tags[] = 'u';
        if ($style['strike'] || $style['dstrike']) $tags[] = 's';

        $inline = '';
        if ($style['fontSize']) $inline .= 'font-size: ' . $style['fontSize'] . 'pt; ';
        if ($style['color']) $inline .= 'color: #' . $style['color'] . '; ';
        if ($style['bgColor']) $inline .= 'background-color: #' . $style['bgColor'] . '; ';
        if ($style['fontFamily']) $inline .= 'font-family: ' . $style['fontFamily'] . '; ';
        if ($style['sub']) {
            $inline .= 'vertical-align: sub; font-size: 0.8em; ';
        }
        if ($style['sup']) {
            $inline .= 'vertical-align: super; font-size: 0.8em; ';
        }

        if ($style['highlight']) {
            $inline .= 'background-color: yellow; ';
        }

        $inner = $text;
        foreach ($tags as $tag) {
            $inner = "<{$tag}>{$inner}</{$tag}>";
        }

        if ($inline) {
            return "<span style=\"{$inline}\">{$inner}</span>";
        }

        return $inner;
    }

    /**
     * Render a hyperlink (w:hyperlink)
     */
    private function renderHyperlink($hyperlinkNode)
    {
        $id = $hyperlinkNode->getAttribute('r:id');
        $href = '#';
        if (isset($this->rels[$id])) {
            $href = $this->rels[$id]['target'];
        }

        $text = '';
        foreach ($hyperlinkNode->childNodes as $child) {
            if ($child->localName === 'r') {
                $text .= $this->renderRun($child);
            }
        }

        return '<a href="' . htmlspecialchars($href) . '" style="color: #1a73e8; text-decoration: underline;">' . $text . '</a>';
    }

    /**
     * Render a table (w:tbl)
     */
    private function renderTable($tblNode)
    {
        $xpath = new DOMXPath($tblNode->ownerDocument);
        $xpath->registerNamespace('w', $this->ns);

        $html = ['<table style="width:100%; border-collapse: collapse; margin: 1em 0; border: 1px solid #ccc;">'];

        foreach ($tblNode->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            if ($child->localName === 'tr') {
                $html[] = '<tr>';
                foreach ($child->childNodes as $cell) {
                    if ($cell->nodeType !== XML_ELEMENT_NODE) {
                        continue;
                    }
                    if ($cell->localName === 'tc') {
                        $html[] = '<td style="border: 1px solid #ccc; padding: 0.5em; vertical-align: top;">';
                        foreach ($cell->childNodes as $cellChild) {
                            if ($cellChild->nodeType !== XML_ELEMENT_NODE) {
                                continue;
                            }
                            if ($cellChild->localName === 'p') {
                                $html[] = $this->renderParagraph($cellChild);
                            }
                        }
                        $html[] = '</td>';
                    }
                }
                $html[] = '</tr>';
            }
        }

        $html[] = '</table>';
        return implode("\n", $html);
    }

    /**
     * Render a structured document tag (w:sdt)
     */
    private function renderSdt($sdtNode)
    {
        $xpath = new DOMXPath($sdtNode->ownerDocument);
        $xpath->registerNamespace('w', $this->ns);

        $content = $xpath->query('w:sdtContent', $sdtNode)->item(0);
        if (!$content) {
            return '';
        }

        $html = '';
        foreach ($content->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            if ($child->localName === 'p') {
                $html .= $this->renderParagraph($child);
            } elseif ($child->localName === 'tbl') {
                $html .= $this->renderTable($child);
            }
        }

        return $html;
    }

    /**
     * Render a drawing / image
     */
    private function renderDrawing($drawingNode)
    {
        $xpath = new DOMXPath($drawingNode->ownerDocument);
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
        $xpath->registerNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $blip = $xpath->query('.//a:blip', $drawingNode)->item(0);
        if (!$blip) {
            return '';
        }

        $embedId = $blip->getAttribute('r:embed');
        if (!$embedId || !isset($this->rels[$embedId])) {
            return '';
        }

        $target = $this->rels[$embedId]['target'];
        $imagePath = 'word/' . ltrim($target, '/');
        $imageData = $this->zip->getFromName($imagePath);
        if ($imageData === false) {
            return '';
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $mime = $this->getImageMime($ext);
        $base64 = base64_encode($imageData);

        return '<img src="data:' . $mime . ';base64,' . $base64 . '" style="max-width: 100%; height: auto; margin: 0.5em 0;" alt="">';
    }

    /**
     * Get image MIME type from extension
     */
    private function getImageMime($ext)
    {
        $map = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'wmf' => 'image/x-wmf',
            'emf' => 'image/x-emf',
        ];
        return $map[$ext] ?? 'image/png';
    }

    /**
     * Compute paragraph CSS style
     */
    private function computeParagraphStyle($pPrNode, $styleId)
    {
        $style = '';

        if ($pPrNode) {
            $spacing = $this->getNode($pPrNode, 'w:spacing');
            if ($spacing) {
                $before = $this->getTwipToPt($spacing->getAttribute('w:before'));
                $after = $this->getTwipToPt($spacing->getAttribute('w:after'));
                $line = $this->getLineHeight($spacing->getAttribute('w:line'));
                if ($before) $style .= 'margin-top: ' . $before . 'pt; ';
                if ($after) $style .= 'margin-bottom: ' . $after . 'pt; ';
                if ($line) $style .= 'line-height: ' . $line . '; ';
            }

            $indent = $this->getNode($pPrNode, 'w:ind');
            if ($indent) {
                $left = $this->getTwipToPt($indent->getAttribute('w:left'));
                $firstLine = $this->getTwipToPt($indent->getAttribute('w:firstLine'));
                if ($left) $style .= 'margin-left: ' . $left . 'pt; ';
                if ($firstLine) $style .= 'text-indent: ' . $firstLine . 'pt; ';
            }
        }

        // Apply style definitions
        if ($styleId && isset($this->styles[$styleId])) {
            $s = $this->styles[$styleId];
            if ($s['pPr']) {
                $sp = $this->getNode($s['pPr'], 'w:spacing');
                if ($sp && empty($style)) {
                    $before = $this->getTwipToPt($sp->getAttribute('w:before'));
                    $after = $this->getTwipToPt($sp->getAttribute('w:after'));
                    if ($before) $style .= 'margin-top: ' . $before . 'pt; ';
                    if ($after) $style .= 'margin-bottom: ' . $after . 'pt; ';
                }
            }
        }

        return $style;
    }

    /**
     * Compute run formatting
     */
    private function computeRunStyle($rPrNode, $paragraphStyleId = null)
    {
        $style = [
            'bold' => false,
            'italic' => false,
            'underline' => false,
            'strike' => false,
            'dstrike' => false,
            'sup' => false,
            'sub' => false,
            'highlight' => false,
            'fontSize' => null,
            'color' => null,
            'bgColor' => null,
            'fontFamily' => null,
        ];

        if (!$rPrNode) {
            return $style;
        }

        $style['bold'] = $this->getNode($rPrNode, 'w:b') !== null;
        $style['italic'] = $this->getNode($rPrNode, 'w:i') !== null;
        $style['underline'] = $this->getNode($rPrNode, 'w:u') !== null;
        $style['strike'] = $this->getNode($rPrNode, 'w:strike') !== null;
        $style['dstrike'] = $this->getNode($rPrNode, 'w:dstrike') !== null;
        $style['sup'] = $this->getNode($rPrNode, 'w:vertAlign') && $this->getNodeAttribute($rPrNode, 'w:vertAlign', 'w:val') === 'superscript';
        $style['sub'] = $this->getNode($rPrNode, 'w:vertAlign') && $this->getNodeAttribute($rPrNode, 'w:vertAlign', 'w:val') === 'subscript';
        $style['highlight'] = $this->getNode($rPrNode, 'w:highlight') !== null;

        $sz = $this->getNode($rPrNode, 'w:sz');
        if ($sz) {
            $val = $sz->getAttribute('w:val');
            if ($val) $style['fontSize'] = $val / 2;
        }

        $color = $this->getNode($rPrNode, 'w:color');
        if ($color) {
            $val = $color->getAttribute('w:val');
            if ($val && $val !== 'auto') $style['color'] = $val;
        }

        $shd = $this->getNode($rPrNode, 'w:shd');
        if ($shd) {
            $val = $shd->getAttribute('w:fill');
            if ($val && $val !== 'auto') $style['bgColor'] = $val;
        }

        $rFonts = $this->getNode($rPrNode, 'w:rFonts');
        if ($rFonts) {
            $ascii = $rFonts->getAttribute('w:ascii');
            if ($ascii) $style['fontFamily'] = $ascii;
        }

        return $style;
    }

    /**
     * Get list type for numbering
     */
    private function getListType($numId, $ilvl)
    {
        if (!isset($this->numbering[$numId])) {
            return 'disc';
        }

        $abstractId = $this->numbering[$numId]['abstract'] ?? null;
        if ($abstractId && isset($this->numbering[$abstractId][$ilvl])) {
            $fmt = $this->numbering[$abstractId][$ilvl]['numFmt'];
        } elseif (isset($this->numbering[$numId][$ilvl])) {
            $fmt = $this->numbering[$numId][$ilvl]['numFmt'];
        } else {
            return 'disc';
        }

        $map = [
            'decimal' => 'decimal',
            'lowerLetter' => 'lower-alpha',
            'upperLetter' => 'upper-alpha',
            'lowerRoman' => 'lower-roman',
            'upperRoman' => 'upper-roman',
            'bullet' => 'disc',
        ];

        return $map[$fmt] ?? 'disc';
    }

    /**
     * Helper: get a child node
     */
    private function getNode($parent, $query)
    {
        if (!$parent || !isset($parent->ownerDocument)) {
            return null;
        }
        $xpath = new DOMXPath($parent->ownerDocument);
        $xpath->registerNamespace('w', $this->ns);
        $node = $xpath->query($query, $parent)->item(0);
        return $node;
    }

    /**
     * Helper: get a node's attribute value
     */
    private function getNodeAttribute($parent, $query, $attr)
    {
        $node = $this->getNode($parent, $query);
        if (!$node) {
            return '';
        }
        return $node->getAttribute($attr);
    }

    /**
     * Convert twips to points
     */
    private function getTwipToPt($twips)
    {
        if ($twips === '') {
            return 0;
        }
        $twips = (int) $twips;
        if ($twips === 0) {
            return 0;
        }
        return round($twips / 20, 1);
    }

    /**
     * Convert line value to CSS line-height
     */
    private function getLineHeight($line)
    {
        if ($line === '') {
            return '';
        }
        $val = (int) $line;
        if ($val === 0) {
            return '';
        }
        return round($val / 240, 2);
    }
}
