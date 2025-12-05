<?php
// handlers/auth_process.php (FINAL VERSION: PLAIN TEXT PASSWORD CHECK)

session_start();
// Corrected Path: Stepping up one directory level (from handlers/)
require_once __DIR__ . '/../includes/config.php';

// Check if database connection failed
if ($conn->connect_error) {
    header("Location: " . BASE_URL . "views/public/login.php?error=server_error");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: " . BASE_URL . "views/public/login.php?error=auth_failed");
        exit;
    }

    // --------------------------------------------------------
    // 2. FETCH USER DATA (Retrieving the plain text password)
    // --------------------------------------------------------
    
    $sql = "SELECT user_id, password_hash, user_type FROM users WHERE email = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                // Binding the plain text password from the DB to $stored_password
                $stmt->bind_result($user_id, $stored_password, $user_type);
                $stmt->fetch();

                // --------------------------------------------------------
                // 3. CRITICAL CHANGE: PLAIN TEXT COMPARISON
                // --------------------------------------------------------
                if ($password === $stored_password) {
                    
                    // Successful Login
                    session_regenerate_id(true); 

                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['email'] = $email;

                    // Redirection logic remains the same
                    $dashboard_path = '';
                    switch ($user_type) {
                        case 'donor':
                            $dashboard_path = 'views/user_dashboards/donor_dashboard.php';
                            break;
                        case 'hospital':
                            $dashboard_path = 'views/user_dashboards/hospital_dashboard.php';
                            break;
                        case 'blood_bank':
                            $dashboard_path = 'views/user_dashboards/bank_dashboard.php';
                            break;
                        case 'admin':
                            $dashboard_path = 'views/user_dashboards/admin_dashboard.php';
                            break;
                        default:
                            session_destroy();
                            header("Location: " . BASE_URL . "views/public/login.php?error=invalid_user_type");
                            exit;
                    }

                    $stmt->close();
                    $conn->close();
                    header("Location: " . BASE_URL . $dashboard_path);
                    exit;

                } else {
                    // Invalid password
                    $stmt->close();
                    $conn->close();
                    header("Location: " . BASE_URL . "views/public/login.php?error=auth_failed");
                    exit;
                }

            } else {
                // No user found with that email
                $stmt->close();
                $conn->close();
                header("Location: " . BASE_URL . "views/public/login.php?error=auth_failed");
                exit;
            }
        } else {
            // Execution failed
            $stmt->close();
            $conn->close();
            header("Location: " . BASE_URL . "views/public/login.php?error=server_error");
            exit;
        }
    } else {
        // Preparation failed
        $conn->close();
        header("Location: " . BASE_URL . "views/public/login.php?error=server_error");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "views/public/login.php");
    exit;
}
?>