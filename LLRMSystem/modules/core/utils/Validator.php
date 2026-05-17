<?php

class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Validate required fields
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required';
        }
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Invalid email format';
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength($field, $min, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be at least ' . $min . ' characters';
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength($field, $max, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must not exceed ' . $max . ' characters';
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be a number';
        }
        return $this;
    }
    
    /**
     * Validate date format
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field])) {
            $date = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? 'Invalid date format';
            }
        }
        return $this;
    }
    
    /**
     * Validate date is not in the future
     */
    public function dateNotFuture($field, $message = null) {
        if (isset($this->data[$field])) {
            $date = strtotime($this->data[$field]);
            if ($date && $date > time()) {
                $this->errors[$field] = $message ?? ucfirst($field) . ' cannot be in the future';
            }
        }
        return $this;
    }
    
    /**
     * Validate allowed values
     */
    public function in($field, $allowed, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed)) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is not a valid option';
        }
        return $this;
    }
    
    /**
     * Validate document type
     */
    public function documentType($field, $message = null) {
        $allowedTypes = ['ordinance', 'resolution', 'session', 'agenda', 'committee', 'hearing', 'consultation', 'research'];
        return $this->in($field, $allowedTypes, $message ?? 'Invalid document type');
    }
    
    /**
     * Validate document status
     */
    public function documentStatus($field, $message = null) {
        $allowedStatuses = ['draft', 'pending', 'approved', 'rejected', 'archived', 'superseded'];
        return $this->in($field, $allowedStatuses, $message ?? 'Invalid document status');
    }
    
    /**
     * Validate confidentiality level
     */
    public function confidentialityLevel($field, $message = null) {
        $allowedLevels = ['public', 'internal', 'confidential', 'restricted'];
        return $this->in($field, $allowedLevels, $message ?? 'Invalid confidentiality level');
    }
    
    /**
     * Validate file extension
     */
    public function fileExtension($field, $allowed, $message = null) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed)) {
                $this->errors[$field] = $message ?? 'File type not allowed. Allowed types: ' . implode(', ', $allowed);
            }
        }
        return $this;
    }
    
    /**
     * Validate file size
     */
    public function fileSize($field, $maxSizeMB, $message = null) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $maxSizeBytes = $maxSizeMB * 1024 * 1024;
            if ($_FILES[$field]['size'] > $maxSizeBytes) {
                $this->errors[$field] = $message ?? 'File size must not exceed ' . $maxSizeMB . 'MB';
            }
        }
        return $this;
    }
    
    /**
     * Validate reference number format
     */
    public function referenceNumber($field, $message = null) {
        if (isset($this->data[$field])) {
            // Format: TYPE-YYYY-NNN (e.g., ORD-2025-042)
            if (!preg_match('/^[A-Z]{3,4}-\d{4}-\d{3,}$/', strtoupper($this->data[$field]))) {
                $this->errors[$field] = $message ?? 'Invalid reference number format. Use format: TYPE-YYYY-NNN (e.g., ORD-2025-042)';
            }
        }
        return $this;
    }
    
    /**
     * Custom validation callback
     */
    public function custom($field, $callback, $message = null) {
        if (isset($this->data[$field])) {
            $result = $callback($this->data[$field]);
            if ($result !== true) {
                $this->errors[$field] = $message ?? $result;
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get validation errors
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get first error message
     */
    public function firstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Get all error messages as array
     */
    public function getMessages() {
        return array_values($this->errors);
    }
    
    /**
     * Get errors as JSON
     */
    public function getErrorsJson() {
        return json_encode(['success' => false, 'errors' => $this->errors]);
    }
    
    /**
     * Validate document data
     */
    public static function validateDocument($data) {
        $validator = new self($data);
        
        return $validator
            ->required('title')
            ->required('document_type')
            ->documentType('document_type')
            ->required('reference_number')
            ->referenceNumber('reference_number')
            ->required('document_date')
            ->date('document_date')
            ->dateNotFuture('document_date')
            ->required('file')
            ->fileExtension('file', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'])
            ->fileSize('file', 50);
    }
    
    /**
     * Validate user data
     */
    public static function validateUser($data) {
        $validator = new self($data);
        
        return $validator
            ->required('name')
            ->required('email')
            ->email('email')
            ->required('password')
            ->minLength('password', 8)
            ->required('role')
            ->in('role', ['viewer', 'staff', 'officer', 'administrator', 'super_admin']);
    }
}
