<?php

class EncryptionService {
    private $encryptionKey;
    private $cipherMethod;
    
    public function __construct() {
        // Use a 32-byte key for AES-256
        $this->encryptionKey = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : hash('sha256', 'default-encryption-key-change-in-production', true);
        $this->cipherMethod = 'AES-256-CBC';
    }
    
    /**
     * Encrypt file content
     */
    public function encryptFile($filePath) {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found'];
        }
        
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return ['success' => false, 'error' => 'Failed to read file'];
        }
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipherMethod));
        $encrypted = openssl_encrypt($fileContent, $this->cipherMethod, $this->encryptionKey, 0, $iv);
        
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
     * Decrypt file content
     */
    public function decryptFile($filePath) {
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
        
        $decrypted = openssl_decrypt($encrypted, $this->cipherMethod, $this->encryptionKey, 0, $iv);
        
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
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }
        
        // Try to decode as base64 - if successful, likely encrypted
        $decoded = base64_decode($content);
        return ($decoded !== false && $decoded !== $content);
    }
    
    /**
     * Encrypt data string
     */
    public function encryptData($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipherMethod));
        $encrypted = openssl_encrypt($data, $this->cipherMethod, $this->encryptionKey, 0, $iv);
        
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
        
        $decrypted = openssl_decrypt($encrypted, $this->cipherMethod, $this->encryptionKey, 0, $iv);
        
        if ($decrypted === false) {
            return ['success' => false, 'error' => 'Decryption failed'];
        }
        
        return ['success' => true, 'data' => $decrypted];
    }
}
