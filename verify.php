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
    die("Database connection failed: " . $e->getMessage());
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = :token AND verified = 0");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mark the user as verified
        $updateStmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE verification_token = :token");
        $updateStmt->bindParam(':token', $token);
        $updateStmt->execute();

        echo "<h2>Email Verified Successfully!</h2>";
        echo "<p>You can now <a href='CSHlogin.php'>login</a> with your account.</p>";
    } else {
        echo "<h2>Invalid or Expired Token!</h2>";
        echo "<p>The verification link may be incorrect or already used.</p>";
    }
} else {
    echo "<h2>No verification token provided.</h2>";
}
?>
