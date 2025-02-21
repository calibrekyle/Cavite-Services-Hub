<?php
// Start the session to handle login status
session_start();

// Database credentials (replace with your own)
$host = 'localhost';
$dbname = 'userDB';
$dbusername = 'root'; // Change to your MySQL username
$dbpassword = '';     // Change to your MySQL password

// Create a PDO connection to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    header("Location: index.html"); // Redirect to index.html if already logged in
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Query the database for the user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $inputUsername);
    $stmt->execute();

    // Fetch user data from the database
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user found, verify password
    if ($user && password_verify($inputPassword, $user['password'])) {
        $_SESSION['username'] = $inputUsername; // Set session variable
        header("Location: index.html"); // Redirect to customer/index.html
        exit();
    } else {
        $errorMessage = "Invalid username or password!";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            width: 300px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <?php if (isset($errorMessage)): ?>
                <p id="error-message" style="color: red;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

<?php
// The following is the SQL to create the database and the "users" table
// Run this query in your MySQL database to set it up:

/* 
    CREATE DATABASE userDB;
    USE userDB;

    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL
    );
*/

// To insert a user (make sure to hash the password):
// $password = password_hash('password123', PASSWORD_DEFAULT);
// INSERT INTO users (username, password) VALUES ('admin', '$password');
?>
