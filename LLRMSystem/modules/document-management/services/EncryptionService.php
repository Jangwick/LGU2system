<?php

class EncryptionService {
    private $masterKey;
    private $cipherMethod;
    
    public function __construct() {
        // Use a 32-byte key for AES-256
        $this->masterKey = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : hash('sha256', 'default-encryption-key-change-in-production', true);
        $this->cipherMethod = 'AES-256-CBC';
    }
    
    /**
     * Generate a random encryption key for a file
     */
    public function generateFileKey() {
        return random_bytes(32); // 256-bit key
    }
    
    /**
     * Encrypt a file key with the master key
     */
    public function encryptFileKey($fileKey) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipherMethod));
        $encrypted = openssl_encrypt($fileKey, $this->cipherMethod, $this->masterKey, 0, $iv);
        
        if ($encrypted === false) {
            return ['success' => false, 'error' => 'Key encryption failed'];
        }
        
        return ['success' => true, 'encrypted_key' => base64_encode($iv . $encrypted)];
    }
    
    /**
     * Decrypt a file key with the master key
     */
    public function decryptFileKey($encryptedKey) {
        $encryptedKey = base64_decode($encryptedKey);
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = substr($encryptedKey, 0, $ivLength);
        $encrypted = substr($encryptedKey, $ivLength);
        
        $decrypted = openssl_decrypt($encrypted, $this->cipherMethod, $this->masterKey, 0, $iv);
        
        if ($decrypted === false) {
            return ['success' => false, 'error' => 'Key decryption failed'];
        }
        
        return ['success' => true, 'file_key' => $decrypted];
    }
    
    /**
     * Encrypt file content with a specific key
     */
    public function encryptFile($filePath, $fileKey = null) {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return ['success' => false, 'error' => 'Failed to read file'];
        }
        
        // Use provided file key or master key for backward compatibility
        $key = $fileKey ?? $this->masterKey;
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipherMethod));
        $encrypted = openssl_encrypt($fileContent, $this->cipherMethod, $key, 0, $iv);
        
        if ($encrypted === false) {
            return ['success' => false, 'error' => 'Encryption failed'];
        }
        
        // Combine IV and encrypted content
        $encryptedData = base64_encode($iv . $encrypted);
        
        // Write encrypted content back to file
        $result = file_put_contents($filePath, $encryptedData);
        
        if ($result === false) {
            return ['success' => false, 'error' => 'Failed to write encrypted file'];
        }
        
        return ['success' => true, 'encrypted' => true];
    }
    
    /**
     * Decrypt file content with a specific key
     */
    public function decryptFile($filePath, $fileKey = null) {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        $encryptedData = file_get_contents($filePath);
        if ($encryptedData === false) {
            return ['success' => false, 'error' => 'Failed to read file'];
        }
        
        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = substr($encryptedData, $ivLength);
        
        // Use provided file key or master key for backward compatibility
        $key = $fileKey ?? $this->masterKey;
        
        $decrypted = openssl_decrypt($encrypted, $this->cipherMethod, $key, 0, $iv);
        
        if ($decrypted === false) {
            return ['success' => false, 'error' => 'Decryption failed'];
        }
        
        return ['success' => true, 'content' => $decrypted];
    }
    
    /**
     * Check if file is encrypted
     */
    public function isEncrypted($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }

        // Read first 4 bytes to check for common file magic bytes
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return false;
        }
        $header = fread($handle, 4);
        fclose($handle);

        // ZIP archives (DOCX, XLSX, PPTX, etc.) start with PK (0x50 0x4B)
        if ($header === 'PK' || substr($header, 0, 2) === 'PK') {
            return false;
        }
        // PDF files start with %PDF
        if (substr($header, 0, 4) === '%PDF') {
            return false;
        }

        // For other files, try to decode as base64 - if successful, likely encrypted
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $decoded = base64_decode($content);
        return ($decoded !== false && $decoded !== $content);
    }
    
    /**
     * Encrypt data string
     */
    public function encryptData($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipherMethod));
        $encrypted = openssl_encrypt($data, $this->cipherMethod, $this->masterKey, 0, $iv);
        
        if ($encrypted === false) {
            return ['success' => false, 'error' => 'Encryption failed'];
        }
        
        return ['success' => true, 'encrypted' => base64_encode($iv . $encrypted)];
    }
    
    /**
     * Decrypt data string
     */
    public function decryptData($encryptedData) {
        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = substr($encryptedData, 0, $ivLength);
        $encrypted = substr($encryptedData, $ivLength);
        
        $decrypted = openssl_decrypt($encrypted, $this->cipherMethod, $this->masterKey, 0, $iv);
        
        if ($decrypted === false) {
            return ['success' => false, 'error' => 'Decryption failed'];
        }
        
        return ['success' => true, 'data' => $decrypted];
    }
}
