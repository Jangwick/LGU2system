<?php
/**
 * Register Controller
 * Handles user registration requests
 */

// Prevent any output before JSON
if (ob_get_level()) ob_end_clean();
ob_start();

session_start();

// Set JSON header
header('Content-Type: application/json');

// Disable error display (log errors instead)
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Include configuration
    require_once __DIR__ . '/../../core/config/config.php';
    require_once __DIR__ . '/../../core/config/database.php';
    require_once __DIR__ . '/../../core/utils/Logger.php';

    // Clear any output buffer from includes
    if (ob_get_level()) ob_end_clean();

    // Handle registration request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);

        // Basic validation
        if (empty($name) || empty($email) || empty($department) || empty($role) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
            exit;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit;
        }

        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
            exit;
        }

        if (!$terms) {
            echo json_encode(['success' => false, 'message' => 'You must agree to the terms.']);
            exit;
        }

        // Get database connection
        $conn = getDatabase();
        if (!$conn) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
            exit;
        }
        
        $logger = new Logger($conn);

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit;
        }

        // Generate username and hash password
        $username = explode('@', $email)[0];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        // We include name, full_name, email, username, password, department, position, role, status
        $sql = "INSERT INTO users (name, full_name, email, username, password, department, position, role, status, created_at) 
                VALUES (:name, :full_name, :email, :username, :password, :department, :position, :role, 'active', NOW())";
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':name' => $name,
            ':full_name' => $name,
            ':email' => $email,
            ':username' => $username,
            ':password' => $hashedPassword,
            ':department' => $department,
            ':position' => $position,
            ':role' => $role
        ]);

        if ($success) {
            $newUserId = $conn->lastInsertId();
            
            // Log successful registration
            $logger->logSession($newUserId, 'USER_REGISTERED', [
                'email' => $email,
                'role' => $role,
                'department' => $department
            ]);

            echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database insertion failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
} catch (PDOException $e) {
    if (ob_get_level()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (ob_get_level()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
