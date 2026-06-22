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
        // Get form data (sanitized)
        $name = Sanitizer::plainText($_POST['name'] ?? '');
        $email = Sanitizer::email($_POST['email'] ?? '');
        $department = Sanitizer::plainText($_POST['department'] ?? '');
        $role = Sanitizer::enum($_POST['role'] ?? '', ['viewer', 'staff', 'officer'], '');
        $password = Sanitizer::string($_POST['password'] ?? '');
        $confirmPassword = Sanitizer::string($_POST['confirm_password'] ?? '');
        $terms = isset($_POST['terms']);

        // Determine initial status based on role
        // viewer: active (auto-approved)
        // staff, officer: pending (requires admin approval)
        $status = ($role === 'viewer') ? 'active' : 'pending';

        // Basic validation
        if (empty($name) || empty($email) || empty($department) || empty($role) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }

        // Prevent registration of administrators through public form
        if ($role === 'administrator') {
            echo json_encode(['success' => false, 'message' => 'Administrator registration is not allowed.']);
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

        $passwordLength = strlen($password);
        $uniqueCharsCount = count(count_chars($password, 1));

        if ($passwordLength < 8 || $passwordLength > 14) {
            echo json_encode(['success' => false, 'message' => 'Password must be between 8 and 14 characters.']);
            exit;
        }

        if ($uniqueCharsCount < $passwordLength) {
            echo json_encode(['success' => false, 'message' => 'Password must contain unique characters (no repeats).']);
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

        // Auto-generate Employee ID (format: LGU-YYYY-XXXX)
        $year = date('Y');
        $stmt = $conn->prepare("SELECT employee_id FROM users WHERE employee_id LIKE ? ORDER BY employee_id DESC LIMIT 1");
        $stmt->execute(["LGU-{$year}-%"]);
        $lastId = $stmt->fetchColumn();
        if ($lastId) {
            $lastNum = intval(substr($lastId, -4));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }
        $employeeId = sprintf("LGU-%s-%04d", $year, $nextNum);

        // Generate username and hash password
        $username = explode('@', $email)[0];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $sql = "INSERT INTO users (name, employee_id, full_name, email, username, password, department, role, status, created_at) 
                VALUES (:name, :employee_id, :full_name, :email, :username, :password, :department, :role, :status, NOW())";
        
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':name' => $name,
            ':employee_id' => $employeeId,
            ':full_name' => $name,
            ':email' => $email,
            ':username' => $username,
            ':password' => $hashedPassword,
            ':department' => $department,
            ':role' => $role,
            ':status' => $status
        ]);

        if ($success) {
            $newUserId = $conn->lastInsertId();
            
            // Log successful registration
            $logger->logSession($newUserId, 'USER_REGISTERED', [
                'email' => $email,
                'employee_id' => $employeeId,
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
