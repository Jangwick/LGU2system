<?php
/**
 * Standalone Document Decryptor
 * 
 * Usage (CLI):
 *   php decrypt_file.php <document_id> <output_path>
 * 
 * Usage (Web):
 *   decrypt_file.php?id=<document_id>&output=1   (streams decrypted file to browser)
 * 
 * Requires: config.local.php database credentials
 */

// ─── CLI or Web ────────────────────────────────────────────────
$isCli = php_sapi_name() === 'cli';

if ($isCli) {
    if ($argc < 2) {
        echo "Usage: php decrypt_file.php <document_id> [output_path]\n";
        echo "Example: php decrypt_file.php 42 decrypted_output.pdf\n";
        exit(1);
    }
    $documentId = (int)$argv[1];
    $outputPath = $argv[2] ?? null;
} else {
    $documentId = (int)($_GET['id'] ?? 0);
    $outputPath = null;
    $streamOutput = isset($_GET['output']);
}

if (!$documentId) {
    echo "Document ID is required.\n";
    exit(1);
}

// ─── Bootstrap ────────────────────────────────────────────────
require_once __DIR__ . '/../../core/config/config.php';
require_once __DIR__ . '/../../core/config/database.php';

$db = getDatabase();

// ─── Master Key (same as EncryptionService) ───────────────────
$masterKey = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : hash('sha256', 'default-encryption-key-change-in-production', true);
$cipherMethod = 'AES-256-CBC';
$ivLength = openssl_cipher_iv_length($cipherMethod);

// ─── Fetch Document ───────────────────────────────────────────
$stmt = $db->prepare("SELECT * FROM legislative_documents WHERE id = :id AND deleted_at IS NULL");
$stmt->execute([':id' => $documentId]);
$document = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$document) {
    echo "Document #{$documentId} not found.\n";
    exit(1);
}

echo "Document: {$document['title']}\n";
echo "File: {$document['file_name']}\n";
echo "Encrypted: " . ($document['is_encrypted'] ? 'Yes' : 'No') . "\n";

// ─── Resolve File Path ────────────────────────────────────────
$basePath = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));
$filePath = $basePath . '/' . $document['file_path'];

if (!file_exists($filePath)) {
    echo "File not found at: {$filePath}\n";
    exit(1);
}

// ─── Decrypt ──────────────────────────────────────────────────
if (!$document['is_encrypted']) {
    // Not encrypted, just copy/read
    $decrypted = file_get_contents($filePath);
    echo "File is not encrypted. Reading directly.\n";
} else {
    // Step 1: Decrypt the file key with master key
    $encryptedKey = $document['encryption_key'];
    
    if (empty($encryptedKey)) {
        echo "No encryption key in DB. Trying master key directly (backward compat).\n";
        $fileKey = null;
    } else {
        $rawKey = base64_decode($encryptedKey);
        $keyIv = substr($rawKey, 0, $ivLength);
        $encryptedFileKey = substr($rawKey, $ivLength);
        $fileKey = openssl_decrypt($encryptedFileKey, $cipherMethod, $masterKey, 0, $keyIv);
        
        if ($fileKey === false) {
            echo "Failed to decrypt file key. Master key may be incorrect.\n";
            exit(1);
        }
        echo "File key decrypted successfully.\n";
    }
    
    // Step 2: Decrypt the file content
    $encryptedData = base64_decode(file_get_contents($filePath));
    $fileIv = substr($encryptedData, 0, $ivLength);
    $encryptedContent = substr($encryptedData, $ivLength);
    
    $decrypted = openssl_decrypt($encryptedContent, $cipherMethod, $fileKey ?? $masterKey, 0, $fileIv);
    
    if ($decrypted === false) {
        echo "Failed to decrypt file content.\n";
        exit(1);
    }
    echo "File decrypted successfully (" . strlen($decrypted) . " bytes).\n";
}

// ─── Output ───────────────────────────────────────────────────
if ($isCli) {
    if ($outputPath) {
        file_put_contents($outputPath, $decrypted);
        echo "Saved to: {$outputPath}\n";
    } else {
        // Default output filename
        $outputPath = 'decrypted_' . $document['file_name'];
        file_put_contents($outputPath, $decrypted);
        echo "Saved to: {$outputPath}\n";
    }
} else {
    if ($streamOutput) {
        header('Content-Type: ' . $document['file_type']);
        header('Content-Disposition: inline; filename="' . $document['file_name'] . '"');
        header('Content-Length: ' . strlen($decrypted));
        echo $decrypted;
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'document_id' => $documentId,
            'title' => $document['title'],
            'file_name' => $document['file_name'],
            'file_size' => strlen($decrypted),
            'decrypted' => true,
        ]);
    }
}
