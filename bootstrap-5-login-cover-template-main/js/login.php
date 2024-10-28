<?php
// Initialize session
session_start();

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'lohith',
    'password' => 'lohith2004@',
    'database' => 'ecommerce_db'
];

// Custom error handler
function handleError($message, $redirect = null) {
    $_SESSION['error'] = $message;
    if ($redirect) {
        header("Location: $redirect");
        exit();
    }
    return false;
}

// Database connection with error handling
try {
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );

    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    handleError("System error. Please try again later.", "login.html");
}

// Process login request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        handleError("Invalid email format", "login.html");
    }

    $password = $_POST['password'];
    if (strlen($password) < 8) {
        handleError("Password must be at least 8 characters", "login.html");
    }

    try {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Query preparation failed");
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password using secure hash comparison
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Set secure session cookie
                session_regenerate_id(true);
                
                // Redirect based on user role
                switch ($user['role']) {
                    case 'admin':
                        header("Location: admin/dashboard.php");
                        break;
                    case 'driver':
                        header("Location: driver/dashboard.php");
                        break;
                    default:
                        header("Location: user/dashboard.php");
                }
                exit();
            } else {
                handleError("Invalid credentials", "login.html");
            }
        } else {
            handleError("Invalid credentials", "login.html");
        }

        $stmt->close();
    } catch (Exception $e) {
        handleError("Login failed. Please try again later.", "login.html");
    }
}

// Close database connection
$conn->close();

// Helper function to hash passwords (use this when creating new users)
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}
?>