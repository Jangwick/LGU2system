<?php
/**
 * Simple accessibility checks
 */

require_once __DIR__ . '/../bootstrap.php';

$baseUrl = 'http://localhost:8000';
$pages = [
    '/modules/authentication/views/login.php',
    '/modules/public-portal/views/search.php'
];

$passed = 0;
$failed = 0;

function assert_test($name, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        $failed++;
    }
}

foreach ($pages as $page) {
    $html = @file_get_contents($baseUrl . $page);
    if (!$html) {
        assert_test("Can load $page", false);
        continue;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Check for images without alt
    $images = $dom->getElementsByTagName('img');
    $missingAlt = 0;
    foreach ($images as $img) {
        if (!$img->hasAttribute('alt')) {
            $missingAlt++;
        }
    }
    assert_test("Page $page has no images without alt", $missingAlt === 0);

    // Check form inputs have labels
    $inputs = $dom->getElementsByTagName('input');
    $missingLabel = 0;
    foreach ($inputs as $input) {
        $type = strtolower($input->getAttribute('type'));
        if (in_array($type, ['text', 'email', 'password', 'search'], true)) {
            $id = $input->getAttribute('id');
            $name = $input->getAttribute('name');
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
            if (!$hasLabel && !$ariaLabel && !$ariaLabelledBy && $name) {
                $missingLabel++;
            }
        }
    }
    assert_test("Page $page has no inputs without labels", $missingLabel === 0);

    // Check heading order
    $headings = [];
    for ($i = 1; $i <= 6; $i++) {
        foreach ($dom->getElementsByTagName("h$i") as $heading) {
            $headings[$i] = true;
        }
    }
    $headingsOrder = array_keys($headings);
    assert_test("Page $page has valid heading order", empty($headingsOrder) || max($headingsOrder) === end($headingsOrder));
}

echo "\nAccessibility Tests: $passed passed, $failed failed\n";
exit($failed > 0 ? 1 : 0);
