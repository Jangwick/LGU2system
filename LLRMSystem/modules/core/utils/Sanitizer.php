<?php
/**
 * Input Sanitization Utility
 *
 * Centralized, reusable methods for cleaning user-provided data at the
 * input boundary. Sanitization removes or neutralizes dangerous content
 * (control characters, HTML/script tags, path traversal, etc.) BEFORE
 * the data is validated, stored, or displayed.
 *
 * Sanitization vs. Validation:
 *  - Sanitizer  = cleans data (makes it safe to process)
 *  - Validator  = checks data (ensures it meets business rules)
 * Both are complementary and should be used together.
 *
 * Usage:
 *   $name = Sanitizer::plainText($_POST['name']);
 *   $page = Sanitizer::int($_GET['page'], 1);
 *   $role = Sanitizer::enum($_POST['role'], ['viewer','staff','officer'], 'viewer');
 *
 * @package Core\Utils
 */
class Sanitizer
{
    /**
     * Sanitize a generic string: trim, strip null bytes and non-printable
     * control characters (except tab/newline/cr), optionally cap length.
     *
     * @param  mixed    $value
     * @param  array    $opts  ['maxLength' => int|null]
     * @return string
     */
    public static function string($value, array $opts = []): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }
        $str = (string) $value;

        // Strip null bytes and non-printable control chars (keep \t \n \r)
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);

        // Normalize newlines (strip \r)
        $str = str_replace("\r", '', $str);

        $str = trim($str);

        $maxLen = $opts['maxLength'] ?? null;
        if ($maxLen !== null && $maxLen > 0 && mb_strlen($str) > $maxLen) {
            $str = mb_substr($str, 0, $maxLen);
        }

        return $str;
    }

    /**
     * Sanitize for plain-text fields that should never contain HTML
     * (names, titles, reference numbers, usernames, etc.).
     * Applies string() + strip_tags().
     *
     * @param  mixed    $value
     * @param  array    $opts
     * @return string
     */
    public static function plainText($value, array $opts = []): string
    {
        $str = self::string($value, $opts);
        // strip_tags removes HTML/PHP tags; ENT_QUOTES ensures quotes survive
        $str = strip_tags($str);
        return trim($str);
    }

    /**
     * Sanitize rich-text / description fields that may contain benign HTML
     * but must never contain scripts, event handlers, or dangerous URIs.
     *
     * Removes: <script>, <style>, <iframe>, <object>, <embed>, <link>,
     *          <meta>, on* event-handler attributes, javascript:/vbscript:
     *          URIs, and data: URIs in href/src attributes.
     *
     * @param  mixed    $value
     * @param  array    $opts
     * @return string
     */
    public static function richText($value, array $opts = []): string
    {
        $str = self::string($value, $opts);

        // Remove entire dangerous tag blocks (with content)
        $str = preg_replace('#<\s*(script|style|iframe|object|embed|link|meta|noscript|template)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $str);

        // Remove standalone dangerous tags
        $str = preg_replace('#<\s*/?\s*(script|style|iframe|object|embed|link|meta|noscript|template)\b[^>]*>#i', '', $str);

        // Remove on* event handler attributes (onclick, onload, onerror, etc.)
        $str = preg_replace('#\s+on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $str);

        // Remove javascript:, vbscript:, and data: URIs in href/src attributes
        $str = preg_replace('#(href|src)\s*=\s*("(javascript|vbscript|data):[^"]*"|\'(javascript|vbscript|data):[^\']*\'|(javascript|vbscript|data):[^\s>]+)#i', '', $str);

        return trim($str);
    }

    /**
     * Sanitize an email address.
     *
     * @param  mixed    $value
     * @return string   Cleaned email (may still be invalid — use Validator::email() to check)
     */
    public static function email($value): string
    {
        $str = self::string($value);
        $str = filter_var($str, FILTER_SANITIZE_EMAIL);
        // Remove any remaining angle brackets / commas
        $str = str_replace(['<', '>', ','], '', $str);
        return trim($str);
    }

    /**
     * Sanitize to a strict integer.
     *
     * @param  mixed    $value
     * @param  int      $default  Value to return if not a valid integer
     * @return int
     */
    public static function int($value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value)) {
            // Allow leading/trailing whitespace and optional sign
            $cleaned = trim($value);
            if (preg_match('/^-?\d+$/', $cleaned)) {
                return (int) $cleaned;
            }
            // Try float-as-string (e.g. "12.0")
            if (is_numeric($cleaned) && (float)$cleaned == (int)(float)$cleaned) {
                return (int)(float)$cleaned;
            }
        }
        if (is_float($value)) {
            return (int) $value;
        }
        return $default;
    }

    /**
     * Sanitize to a float.
     *
     * @param  mixed    $value
     * @param  float    $default
     * @return float
     */
    public static function float($value, float $default = 0.0): float
    {
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            $cleaned = trim($value);
            if (is_numeric($cleaned)) {
                return (float) $cleaned;
            }
        }
        return $default;
    }

    /**
     * Sanitize to a boolean.
     * Accepts: true/false, 1/0, "1"/"0", "true"/"false", "yes"/"no", "on"/"off"
     *
     * @param  mixed    $value
     * @return bool
     */
    public static function bool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1') {
            return true;
        }
        if ($value === 0 || $value === '0' || $value === null || $value === '') {
            return false;
        }
        $lower = strtolower(trim((string) $value));
        return in_array($lower, ['true', 'yes', 'on', '1'], true);
    }

    /**
     * Sanitize against a whitelist of allowed values (enum).
     * Returns the default if the value is not in the allowed list.
     *
     * @param  mixed    $value
     * @param  array    $allowed
     * @param  mixed    $default
     * @return mixed
     */
    public static function enum($value, array $allowed, $default = null)
    {
        $str = self::string($value);
        if (in_array($str, $allowed, true)) {
            return $str;
        }
        return $default;
    }

    /**
     * Sanitize a filename for safe storage/display.
     * - Takes basename only (strips path traversal ../, ..\, /, \)
     * - Removes null bytes and control characters
     * - Allows only [A-Za-z0-9._-] and spaces
     * - Collapses consecutive dots (prevents double-extension tricks)
     * - Caps length to 255 characters
     *
     * @param  mixed    $value
     * @param  int      $maxLength
     * @return string
     */
    public static function filename($value, int $maxLength = 255): string
    {
        if ($value === null) {
            return '';
        }
        $str = (string) $value;

        // Strip null bytes and control chars
        $str = preg_replace('/[\x00-\x1F\x7F]/u', '', $str);

        // Take basename only — strip any path components
        $str = basename($str);

        // Remove path traversal remnants
        $str = str_replace(['../', '..\\', '..'], '', $str);

        // Allow only safe characters: letters, digits, dot, dash, underscore, space
        $str = preg_replace('/[^A-Za-z0-9._\-\s]/u', '', $str);

        // Collapse consecutive dots to a single dot (prevents file.php.pdf tricks)
        $str = preg_replace('/\.{2,}/', '.', $str);

        // Trim leading dots (hidden files)
        $str = ltrim($str, '.');

        // Collapse whitespace
        $str = preg_replace('/\s+/', ' ', $str);
        $str = trim($str);

        if ($maxLength > 0 && mb_strlen($str) > $maxLength) {
            // Preserve extension if possible
            $ext = pathinfo($str, PATHINFO_EXTENSION);
            $baseLen = $maxLength - mb_strlen($ext) - 1;
            if ($baseLen > 0 && $ext) {
                $str = mb_substr(pathinfo($str, PATHINFO_FILENAME), 0, $baseLen) . '.' . $ext;
            } else {
                $str = mb_substr($str, 0, $maxLength);
            }
        }

        return $str;
    }

    /**
     * Sanitize a date string to Y-m-d format.
     * Returns the default if the value cannot be parsed as a valid date.
     *
     * @param  mixed    $value
     * @param  string   $default
     * @return string
     */
    public static function date($value, string $default = ''): string
    {
        $str = self::string($value);
        if ($str === '') {
            return $default;
        }
        $timestamp = strtotime($str);
        if ($timestamp === false) {
            return $default;
        }
        return date('Y-m-d', $timestamp);
    }

    /**
     * Recursively sanitize an array of values using a specified rule.
     *
     * @param  array    $arr
     * @param  string   $rule   'string'|'plaintext'|'email'|'int'|'float'
     * @return array
     */
    public static function array(array $arr, string $rule = 'string'): array
    {
        $result = [];
        foreach ($arr as $key => $value) {
            $cleanKey = self::string($key);
            if (is_array($value)) {
                $result[$cleanKey] = self::array($value, $rule);
            } else {
                $result[$cleanKey] = self::applyRule($value, $rule);
            }
        }
        return $result;
    }

    /**
     * Apply a named sanitization rule to a single value.
     *
     * @param  mixed    $value
     * @param  string   $rule
     * @return mixed
     */
    public static function applyRule($value, string $rule)
    {
        $rule = strtolower($rule);
        switch ($rule) {
            case 'string':
                return self::string($value);
            case 'enum':
                return self::enum($value, [], '');
            case 'plaintext':
            case 'plain':
                return self::plainText($value);
            case 'richtext':
            case 'rich':
                return self::richText($value);
            case 'email':
                return self::email($value);
            case 'int':
            case 'integer':
                return self::int($value);
            case 'float':
                return self::float($value);
            case 'bool':
            case 'boolean':
                return self::bool($value);
            case 'filename':
                return self::filename($value);
            case 'date':
                return self::date($value);
            case 'raw':
                return $value;
            default:
                return self::string($value);
        }
    }

    /**
     * HTML-escape a value for safe output (alias of the global e() helper).
     * Use this in views to prevent XSS.
     *
     * @param  mixed    $value
     * @return string
     */
    public static function forHtml($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
