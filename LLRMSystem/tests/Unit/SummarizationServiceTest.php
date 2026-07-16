<?php

use PHPUnit\Framework\TestCase;

class SummarizationServiceTest extends TestCase
{
    private $summ;

    protected function setUp(): void
    {
        $this->summ = new SummarizationService();
    }

    // --- Initialization ---

    public function testSummarizationServiceInstantiates()
    {
        $this->assertInstanceOf(SummarizationService::class, $this->summ);
    }

    // --- Key Points Generation ---

    public function testGenerateKeyPointsReturnsArray()
    {
        $text = $this->getSampleLegislativeText();
        $points = $this->summ->generateKeyPoints($text, 5);

        $this->assertIsArray($points);
        $this->assertNotEmpty($points);
    }

    public function testGenerateKeyPointsRespectsCount()
    {
        $text = $this->getSampleLegislativeText();
        $points = $this->summ->generateKeyPoints($text, 3);

        $this->assertLessThanOrEqual(3, count($points));
    }

    public function testGenerateKeyPointsAreBulletFormatted()
    {
        $text = $this->getSampleLegislativeText();
        $points = $this->summ->generateKeyPoints($text, 3);

        foreach ($points as $point) {
            $this->assertStringStartsWith('•', $point);
        }
    }

    public function testGenerateKeyPointsStringReturnsString()
    {
        $text = $this->getSampleLegislativeText();
        $str = $this->summ->generateKeyPointsString($text, 3);

        $this->assertIsString($str);
        $this->assertNotEmpty($str);
    }

    public function testGenerateKeyPointsStringContainsNewlines()
    {
        $text = $this->getSampleLegislativeText();
        $str = $this->summ->generateKeyPointsString($text, 3);

        $this->assertStringContainsString("\n", $str);
    }

    // --- Edge Cases ---

    public function testGenerateKeyPointsWithEmptyText()
    {
        $points = $this->summ->generateKeyPoints('', 5);
        $this->assertIsArray($points);
        $this->assertEmpty($points);
    }

    public function testGenerateKeyPointsWithVeryShortText()
    {
        $points = $this->summ->generateKeyPoints('Hello world.', 5);
        $this->assertIsArray($points);
        $this->assertEmpty($points);
    }

    public function testGenerateKeyPointsWithNullInput()
    {
        $points = $this->summ->generateKeyPoints(null, 5);
        $this->assertIsArray($points);
        $this->assertEmpty($points);
    }

    public function testGenerateKeyPointsStringWithEmptyText()
    {
        $str = $this->summ->generateKeyPointsString('', 5);
        $this->assertSame('', $str);
    }

    // --- Keyword Extraction ---

    public function testExtractKeywordsReturnsArray()
    {
        $text = $this->getSampleLegislativeText();
        $keywords = $this->summ->extractKeywords($text, 10);

        $this->assertIsArray($keywords);
        $this->assertNotEmpty($keywords);
    }

    public function testExtractKeywordsRespectsCount()
    {
        $text = $this->getSampleLegislativeText();
        $keywords = $this->summ->extractKeywords($text, 5);

        $this->assertLessThanOrEqual(5, count($keywords));
    }

    public function testExtractKeywordsExcludesStopWords()
    {
        $text = "The ordinance shall be enacted by the council and approved by the mayor.";
        $keywords = $this->summ->extractKeywords($text, 10);

        $this->assertNotContains('the', $keywords);
        $this->assertNotContains('and', $keywords);
        $this->assertNotContains('by', $keywords);
    }

    public function testExtractKeywordsWithEmptyText()
    {
        $keywords = $this->summ->extractKeywords('', 10);
        $this->assertIsArray($keywords);
        $this->assertEmpty($keywords);
    }

    // --- Content Quality ---

    public function testKeyPointsContainLegislativeKeywords()
    {
        $text = $this->getSampleLegislativeText();
        $str = $this->summ->generateKeyPointsString($text, 5);

        $this->assertTrue(
            stripos($str, 'ordinance') !== false ||
            stripos($str, 'section') !== false ||
            stripos($str, 'pesos') !== false ||
            stripos($str, 'appropriat') !== false,
            'Key points should contain legislative terms'
        );
    }

    public function testKeyPointsFromMultipleParagraphs()
    {
        $text = "AN ORDINANCE APPROPRIATING FUNDS FOR THE CITY GOVERNMENT FOR FISCAL YEAR 2025. " .
                "Be it enacted by the Sangguniang Panlungsod that the sum of five hundred million pesos is hereby appropriated. " .
                "Section 1. Two hundred million pesos shall be allocated for personal services and compensation of employees. " .
                "Section 2. One hundred fifty million pesos is appropriated for maintenance and other operating expenses. " .
                "Section 3. Fifty million pesos shall be allocated for capital outlay programs including construction of new roads. " .
                "Section 4. The remaining one hundred million pesos is appropriated for development projects. " .
                "Section 5. This ordinance shall take effect on January 1, 2025 upon approval.";

        $points = $this->summ->generateKeyPoints($text, 3);

        $this->assertNotEmpty($points);
        $this->assertGreaterThanOrEqual(1, count($points));
    }

    // --- Helpers ---

    private function getSampleLegislativeText()
    {
        return "AN ORDINANCE APPROPRIATING FUNDS FOR THE CITY GOVERNMENT FOR FISCAL YEAR 2025. " .
               "Be it enacted by the Sangguniang Panlungsod that the sum of five hundred million pesos " .
               "is hereby appropriated for the operational expenses of the city government. " .
               "Section 1. Two hundred million pesos shall be allocated for personal services and " .
               "compensation of all regular employees of the city government. " .
               "Section 2. One hundred fifty million pesos is appropriated for maintenance and other " .
               "operating expenses including utilities, supplies, and travel expenses. " .
               "Section 3. Fifty million pesos shall be allocated for capital outlay programs including " .
               "construction of new roads, bridges, and public buildings. " .
               "Section 4. The remaining one hundred million pesos is appropriated for development " .
               "projects and infrastructure improvements throughout the city. " .
               "Section 5. This ordinance shall take effect on January 1, 2025 upon approval by the " .
               "Sangguniang Panlalawigan and publication in a newspaper of general circulation. " .
               "Any violation of the provisions of this ordinance shall be subject to appropriate " .
               "penalties under existing laws and regulations of the Republic of the Philippines.";
    }
}
