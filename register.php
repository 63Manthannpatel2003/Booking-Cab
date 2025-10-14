<?php
/*
* Student Name: Manthan Patel
* Student ID: s105974663
* File: register.php
* Description: Registration page for new customers - PHP 5.4 Compatible
*/

// Change your creds here
$servername = "localhost";
$username = "root"; // Replace with your Mercury username
$password = ""; // Replace with your Mercury password
$dbname = "cabsonline"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = trim($_POST['customer_name']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validation
    if (empty($customer_name) || empty($password) || empty($confirm_password) || empty($email) || empty($phone)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check_email_sql = "SELECT email_address FROM customer WHERE email_address = ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // $result = $conn->query("SELECT email_address FROM customer WHERE email_address = '$email'");
        
        if ($result->num_rows > 0) {
            $error_message = "Email address already exists. Please use a different email.";
        } else {
            // Insert new customer
            $insert_sql = "INSERT INTO customer (email_address, customer_name, password, phone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ssss", $email, $customer_name, $password, $phone);
            
            if ($stmt->execute()) {
                // Registration successful, redirect to booking page with email
                session_start();
                $_SESSION['customer_email'] = $email;
                $_SESSION['customer_name'] = $customer_name;
                header("Location: booking.php");
                exit();
            } else {
                $error_message = "Error registering customer. Please try again.";
            }

            // $result = $conn->query("INSERT INTO customer (email_address, customer_name, password, phone) VALUES ('$email', '$customer_name', '$password', '$phone')");
            
            // if ($result) {
            //     // Registration successful, redirect to booking page with email
            //     session_start();
            //     $_SESSION['customer_email'] = $email;
            //     $_SESSION['customer_name'] = $customer_name;
            //     header("Location: booking.php");
            //     exit();
            // } else {
            //     $error_message = "Error registering customer. Please try again.";
            // }
        }
        // if not using a stmt please comment this line 
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
    <title>CabsOnline - Register</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .student-id { position: absolute; top: 10px; right: 20px; font-weight: bold; color: #666; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] { 
            width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; 
        }
        input[type="submit"] { 
            background-color: #007bff; color: white; padding: 12px 30px; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 16px; width: 100%; 
        }
        input[type="submit"]:hover { background-color: #0056b3; }
        .error { color: red; margin-bottom: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #007bff; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="student-id">s105974663</div>
    
    <div class="container">
        <h1>CabsOnline Registration</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Re-type Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Contact Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
            
            <input type="submit" value="Register">
        </form>
        
        <div class="login-link">
            <p>Already have an account? <a href="./login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>