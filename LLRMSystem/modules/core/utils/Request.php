<?php
/**
 * Request Input Helper
 *
 * Centralized access to user-supplied input (GET, POST, REQUEST) with
 * automatic sanitization via the Sanitizer class. Endpoints should use
 * these methods instead of reading $_GET / $_POST / $_REQUEST directly.
 *
 * Usage:
 *   $name  = Request::post('name', '', 'plaintext');
 *   $page  = Request::get('page', 1, 'int');
 *   $role  = Request::post('role', 'viewer', 'enum', ['viewer','staff','officer']);
 *   $all   = Request::postAll(['name'=>'plaintext','email'=>'email','age'=>'int']);
 *
 * @package Core\Utils
 */
class Request
{
    /**
     * Get a sanitized value from $_GET.
     *
     * @param  string       $key
     * @param  mixed        $default
     * @param  string       $rule      Sanitization rule: string|plaintext|email|int|float|bool|filename|date|raw
     * @param  array|null   $allowed   For 'enum' rule — whitelist of allowed values
     * @return mixed
     */
    public static function get(string $key, $default = null, string $rule = 'string', ?array $allowed = null)
    {
        $value = $_GET[$key] ?? $default;
        return self::sanitize($value, $rule, $default, $allowed);
    }

    /**
     * Get a sanitized value from $_POST.
     *
     * @param  string       $key
     * @param  mixed        $default
     * @param  string       $rule
     * @param  array|null   $allowed
     * @return mixed
     */
    public static function post(string $key, $default = null, string $rule = 'string', ?array $allowed = null)
    {
        $value = $_POST[$key] ?? $default;
        return self::sanitize($value, $rule, $default, $allowed);
    }

    /**
     * Get a sanitized value from $_REQUEST (checks POST, GET, COOKIE).
     * Prefer get() or post() when the source is known.
     *
     * @param  string       $key
     * @param  mixed        $default
     * @param  string       $rule
     * @param  array|null   $allowed
     * @return mixed
     */
    public static function input(string $key, $default = null, string $rule = 'string', ?array $allowed = null)
    {
        $value = $_REQUEST[$key] ?? $default;
        return self::sanitize($value, $rule, $default, $allowed);
    }

    /**
     * Get a sanitized value from a JSON request body.
     * Reads php://input and decodes JSON once (cached per request).
     *
     * @param  string       $key
     * @param  mixed        $default
     * @param  string       $rule
     * @param  array|null   $allowed
     * @return mixed
     */
    public static function json(string $key, $default = null, string $rule = 'string', ?array $allowed = null)
    {
        static $jsonBody = null;
        if ($jsonBody === null) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $jsonBody = is_array($decoded) ? $decoded : [];
        }
        $value = $jsonBody[$key] ?? $default;
        return self::sanitize($value, $rule, $default, $allowed);
    }

    /**
     * Get the entire sanitized JSON body as an associative array.
     *
     * @param  array   $rules  Map of key => rule name (e.g. ['name'=>'plaintext','qty'=>'int'])
     *                         Keys not listed are returned as sanitized 'string'.
     * @return array
     */
    public static function jsonAll(array $rules = []): array
    {
        static $jsonBody = null;
        if ($jsonBody === null) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            $jsonBody = is_array($decoded) ? $decoded : [];
        }
        return self::sanitizeArray($jsonBody, $rules);
    }

    /**
     * Get multiple sanitized values from $_POST at once.
     *
     * @param  array   $rules  Map of key => rule name (e.g. ['name'=>'plaintext','email'=>'email','age'=>'int'])
     *                         Keys not listed are returned as sanitized 'string'.
     * @return array
     */
    public static function postAll(array $rules = []): array
    {
        return self::sanitizeArray($_POST, $rules);
    }

    /**
     * Get multiple sanitized values from $_GET at once.
     *
     * @param  array   $rules
     * @return array
     */
    public static function getAll(array $rules = []): array
    {
        return self::sanitizeArray($_GET, $rules);
    }

    /**
     * Check if a key exists in $_POST.
     *
     * @param  string  $key
     * @return bool
     */
    public static function hasPost(string $key): bool
    {
        return array_key_exists($key, $_POST);
    }

    /**
     * Check if a key exists in $_GET.
     *
     * @param  string  $key
     * @return bool
     */
    public static function hasGet(string $key): bool
    {
        return array_key_exists($key, $_GET);
    }

    /**
     * Get the HTTP request method (uppercase).
     *
     * @return string
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Check if the request is a POST.
     *
     * @return bool
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Check if the request is an AJAX/fetch request.
     *
     * @return bool
     */
    public static function isAjax(): bool
    {
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return strtolower($requestedWith) === 'xmlhttprequest';
    }

    // -----------------------------------------------------------------------
    //  Internal helpers
    // -----------------------------------------------------------------------

    /**
     * Apply a sanitization rule to a value.
     *
     * @param  mixed        $value
     * @param  string       $rule
     * @param  mixed        $default
     * @param  array|null   $allowed   For 'enum' rule
     * @return mixed
     */
    private static function sanitize($value, string $rule, $default = null, ?array $allowed = null)
    {
        if ($value === null) {
            return $default;
        }

        if ($rule === 'enum') {
            return Sanitizer::enum($value, $allowed ?? [], $default);
        }

        return Sanitizer::applyRule($value, $rule);
    }

    /**
     * Sanitize an array of input values using per-key rules.
     *
     * @param  array   $input   Raw input array (e.g. $_POST)
     * @param  array   $rules   Map of key => rule name
     * @return array
     */
    private static function sanitizeArray(array $input, array $rules): array
    {
        $result = [];
        foreach ($input as $key => $value) {
            $cleanKey = Sanitizer::string($key);
            $rule = $rules[$cleanKey] ?? 'string';
            if (is_array($value)) {
                $result[$cleanKey] = Sanitizer::array($value, $rule === 'raw' ? 'raw' : 'string');
            } else {
                $result[$cleanKey] = Sanitizer::applyRule($value, $rule);
            }
        }
        return $result;
    }
}
