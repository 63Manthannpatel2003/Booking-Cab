<?php
/*
* Student Name: Manthan Patel
* Student ID: s105974663
* File: login.php
* Description: Login page for existing customers
*/

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your Mercury username
$password = ""; // Replace with your Mercury password
$dbname = "cabsonline"; // Replace with your database name

// $servername = "mercury.swin.edu.au";
// $username = "105974663@student.swin.edu.au "; 
// $password = "Mnp@060372";
// $dbname = "cabsonline";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error_message = "Both email and password are required.";
    } else {
        // Check credentials
        $sql = "SELECT email_address, customer_name, password FROM customer WHERE email_address = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // $result = $conn->query("SELECT email_address, customer_name, password FROM customer WHERE email_address = '$email'");

        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if ($password == $row['password']) {
                // Login successful
                session_start();
                $_SESSION['customer_email'] = $row['email_address'];
                $_SESSION['customer_name'] = $row['customer_name'];
                header("Location: booking.php");
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CabsOnline - Login</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .student-id { position: absolute; top: 10px; right: 20px; font-weight: bold; color: #666; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="email"], input[type="password"] { 
            width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; 
        }
        input[type="submit"] { 
            background-color: #28a745; color: white; padding: 12px 30px; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 16px; width: 100%; 
        }
        input[type="submit"]:hover { background-color: #218838; }
        .error { color: red; margin-bottom: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; }
        .register-link { text-align: center; margin-top: 20px; }
        .register-link a { color: #007bff; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="student-id">s105974663</div>
    
    <div class="container">
        <h1>CabsOnline Login</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <input type="submit" value="Login">
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>