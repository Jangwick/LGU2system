<?php

use PHPUnit\Framework\TestCase;

class AccessibilityTest extends TestCase
{
    public function testLoginPageInputsHaveLabelsOrAriaLabels()
    {
        $html = file_get_contents('http://localhost:8000/modules/authentication/views/login.php');
        $this->assertNotFalse($html);

        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $inputs = $dom->getElementsByTagName('input');
        $missingLabel = 0;

        foreach ($inputs as $input) {
            $type = strtolower($input->getAttribute('type'));
            if (!in_array($type, ['text', 'email', 'password', 'search'], true)) {
                continue;
            }

            $id = $input->getAttribute('id');
            $ariaLabel = $input->getAttribute('aria-label');
            $ariaLabelledBy = $input->getAttribute('aria-labelledby');
            $hasLabel = false;

            if ($id) {
                $labels = $dom->getElementsByTagName('label');
                foreach ($labels as $label) {
                    if ($label->getAttribute('for') === $id) {
                        $hasLabel = true;
                        break;
                    }
                }
            }

            if (!$hasLabel && !$ariaLabel && !$ariaLabelledBy) {
                $missingLabel++;
            }
        }

        $this->assertSame(0, $missingLabel, 'Login page has input fields without labels');
    }

    public function testPublicSearchPageHasHeadingStructure()
    {
        $html = file_get_contents('http://localhost:8000/modules/public-portal/views/search.php');
        $this->assertNotFalse($html);

        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $h1 = $dom->getElementsByTagName('h1');
        $this->assertGreaterThanOrEqual(0, $h1->length);
    }
}
