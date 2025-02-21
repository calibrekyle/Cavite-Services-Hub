<?php
session_start();
$host = 'localhost';
$dbname = 'userDB';
$dbusername = 'root';
$dbpassword = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "message" => "Database connection failed!"]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    $action = $_POST['action'] ?? '';

    if ($action === "signup") {
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name']);
        $last_name = trim($_POST['last_name']);
        $address = trim($_POST['address']);
        $barangay = trim($_POST['barangay']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email format!"]);
            exit();
        }

        // Validate password
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/", $password)) {
            echo json_encode(["success" => false, "message" => "Password must be at least 8 characters long, contain at least one uppercase letter and one lowercase letter."]);
            exit();
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email exists
        $checkStmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            echo json_encode(["success" => false, "message" => "Email already registered!"]);
            exit();
        }

        // Generate verification token
        $token = bin2hex(random_bytes(32));
        $verification_link = "http://localhost/CaviteServicesHub/verify.php?token=$token";

            $subject = "Verify Your Email - Cavite Services Hub";
            $message = "Hello $first_name,\n\nClick the link below to verify your email:\n$verification_link\n\nIf you didn't sign up, please ignore this email.";
            $headers = "From: no-reply@yourwebsite.com\r\nContent-Type: text/plain; charset=UTF-8";
            
            mail($email, $subject, $message, $headers);
            

        // Insert user with verification token
        $stmt = $pdo->prepare("INSERT INTO users (first_name, middle_name, last_name, address, barangay, email, password, verification_token, verified) 
        VALUES (:first_name, :middle_name, :last_name, :address, :barangay, :email, :password, :token, 0)");


        try {
            $stmt->execute([
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':last_name' => $last_name,
                ':address' => $address,
                ':barangay' => $barangay,
                ':email' => $email,
                ':password' => $hashed_password,
                ':token' => $token
            ]);
            

            // Send verification email
            $subject = "Verify Your Email";
            $message = "Click the link below to verify your email:\n$verification_link";
            $headers = "From: no-reply@yourwebsite.com";

            mail($email, $subject, $message, $headers);

            echo json_encode(["success" => true, "message" => "Signup successful! Please check your email for verification."]);
        } catch (PDOException $e) {
            echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
        }
    } elseif ($action === "login") {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["success" => false, "message" => "Email not found!"]);
            exit();
        }

        if ($user['verified'] == 0) {
            echo json_encode(["success" => false, "message" => "Please verify your email before logging in."]);
            exit();
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            echo json_encode(["success" => true, "first_name" => $user['first_name'], "message" => "Login successful!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Incorrect password!"]);
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavite Services Hub - Login & Signup</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <form class="form-container" id="login-form">
        <div class="form-header">
            <h1>CAVITE SERVICES HUB</h1>
            <p>Bridging Service Gaps in Cavite City Through a Digital Platform</p>
        </div>
        <div class="form-group">
            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="form-actions">
            <button type="submit">Login</button>
        </div>
        <p id="error-message" style="color: red;"></p>
        <div class="switch-form">
            <p>Don't have an account? <a href="#" onclick="showSignupForm()">Sign up here</a></p>
        </div>
    </form>

    <form class="form-container" id="signup-form" style="display: none;">
        <div class="form-header">
            <h1>Sign Up</h1>
            <p>Create a new account</p>
        </div>
        <div class="form-group">
            <label for="signup-first-name">First Name</label>
            <input type="text" id="signup-first-name" name="first_name" placeholder="Enter your first name" required>
        </div>
        <div class="form-group">
            <label for="signup-middle-name">Middle Name</label>
            <input type="text" id="signup-middle-name" name="middle_name" placeholder="Enter your middle name">
        </div>
        <div class="form-group">
            <label for="signup-last-name">Last Name</label>
            <input type="text" id="signup-last-name" name="last_name" placeholder="Enter your last name" required>
        </div>

        <div class="form-group">
            <label for="signup-address">Address</label>
            <input type="text" id="signup-address" name="address" placeholder="Enter your address" required>
        </div>
        <div class="category-container">
            <label for="serviceCategory">Barangay</label>
            <select id="serviceCategory" name="barangay" required>
                <option value="">Select a Barangay</option>
                <option value="Barangay 1">Barangay 1</option>
                <option value="Barangay 2">Barangay 2</option>
                <option value="Barangay 3">Barangay 3</option>
                <option value="Barangay 4">Barangay 4</option>
            </select>
        </div>
        <div class="form-group">
            <label for="signup-email">Email</label>
            <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label for="signup-password">Password</label>
            <input type="password" id="signup-password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="form-actions">
            <button type="submit">Sign Up</button>
        </div>
        <div class="switch-form">
            <p>Already have an account? <a href="#" onclick="showLoginForm()">Login here</a></p>
        </div>
    </form>

    <script >
       document.addEventListener("DOMContentLoaded", function () {
    const signupForm = document.getElementById("signup-form");
    const loginForm = document.getElementById("login-form");

    if (signupForm) {
        signupForm.addEventListener("submit", function (event) {
            event.preventDefault();

            let formData = new FormData(this);
            let email = formData.get("email");
            let password = formData.get("password");

            // Validate email format
            if (!validateEmail(email)) {
                alert("Invalid email format!");
                return;
            }

            // Validate password format
            if (!validatePassword(password)) {
                alert("Password must be at least 8 characters long, contain at least one uppercase letter and one lowercase letter.");
                return;
            }

            formData.append("action", "signup");

            fetch("CSHlogin.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Signup successful! Please check your email for verification.");
                    signupForm.reset();
                    showLoginForm(); // Redirect to login form
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
        });
    }

    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            event.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "login");

            fetch("CSHlogin.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Login successful! Welcome, " + data.first_name);
                    window.location.href = "index.html";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred. Please try again.");
            });
        });
    }

    // Function to validate email format
    function validateEmail(email) {
        let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        return emailPattern.test(email);
    }

    // Function to validate password format
    function validatePassword(password) {
        let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
        return passwordPattern.test(password);
    }

    // Function to show the signup form
    window.showSignupForm = function() {
        let signupForm = document.getElementById("signup-form");
        let loginForm = document.getElementById("login-form");

        if (signupForm && loginForm) {
            signupForm.style.display = "block";
            loginForm.style.display = "none";
        } else {
            console.error("Signup or login form container not found!");
        }
    };

    // Function to show the login form
    window.showLoginForm = function() {
        let signupForm = document.getElementById("signup-form");
        let loginForm = document.getElementById("login-form");

        if (signupForm && loginForm) {
            signupForm.style.display = "none";
            loginForm.style.display = "block";
        } else {
            console.error("Signup or login form container not found!");
        }
    };
});



    </script>
</body>
</html>
