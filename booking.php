<?php
/*
* Student Name: Manthan Patel
* Student ID: s105974663
* File: booking.php
* Description: Booking page for taxi reservations - PHP 5.4 Compatible
*/

session_start();

// Set timezone to Australia/Melbourne
date_default_timezone_set('Australia/Melbourne');

// Check if user is logged in
if (!isset($_SESSION['customer_email'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cabsonline";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";
$success_message = "";
$booking_ref = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $passenger_name = trim($_POST['passenger_name']);
    $passenger_phone = trim($_POST['passenger_phone']);
    $unit_number = trim($_POST['unit_number']); // Can be empty
    $street_number = trim($_POST['street_number']);
    $street_name = trim($_POST['street_name']);
    $suburb = trim($_POST['suburb']);
    $destination_suburb = trim($_POST['destination_suburb']);
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    
    // Validation
    if (empty($passenger_name) || empty($passenger_phone) || empty($street_number) || 
        empty($street_name) || empty($suburb) || empty($destination_suburb) || 
        empty($pickup_date) || empty($pickup_time)) {
        $error_message = "All fields except unit number are required.";
    } else {
        // PHP 5.4 compatible date/time validation
        $pickup_datetime_str = $pickup_date . ' ' . $pickup_time;
        $pickup_timestamp = strtotime($pickup_datetime_str);
        $current_timestamp = time();
        $min_required_timestamp = $current_timestamp + (40 * 60); // Add 40 minutes
        
        if ($pickup_timestamp < $min_required_timestamp) {
            $current_time_str = date('H:i', $current_timestamp);
            $error_message = "Pick-up date/time must be at least 40 minutes from current time. Current time: " . $current_time_str;
        } else {
            // Generate unique booking reference number
            $booking_ref = 'BRN' . date('YmdHis') . rand(100, 999);
            $booking_datetime = date('Y-m-d H:i:s');
            $status = 'unassigned';
            
            // Insert booking
            $sql = "INSERT INTO booking (booking_number, customer_email, passenger_name, passenger_phone, 
                    unit_number, street_number, street_name, suburb, destination_suburb, pickup_date, 
                    pickup_time, booking_datetime, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $unit_param = empty($unit_number) ? null : $unit_number;
            $stmt->bind_param("sssssssssssss", $booking_ref, $_SESSION['customer_email'], $passenger_name, 
                            $passenger_phone, $unit_param, $street_number, $street_name, $suburb, 
                            $destination_suburb, $pickup_date, $pickup_time, $booking_datetime, $status);
            
            // First, prepare variables properly
            // $customer_email = $_SESSION['customer_email'];
            // $unit_param = empty($unit_number) ? 'NULL' : "'$unit_number'"; // Handle NULL properly

            // $result = $conn->query("INSERT INTO booking (booking_number, customer_email, passenger_name, passenger_phone, 
            // unit_number, street_number, street_name, suburb, destination_suburb, pickup_date, 
            // pickup_time, booking_datetime, status) 
            // VALUES ('$booking_ref', '$customer_email', '$passenger_name', 
            //         '$passenger_phone', $unit_param, '$street_number', '$street_name', '$suburb', 
            //         '$destination_suburb', '$pickup_date', '$pickup_time', '$booking_datetime', '$status')");
            // if ($result) {

            if ($stmt->execute()) {
                $success_message = "Thank you! Your booking reference number is " . $booking_ref . 
                                 ". We will pick up the passengers in front of your provided address at " . 
                                 date('H:i', strtotime($pickup_time)) . " on " . date('d/m/Y', strtotime($pickup_date)) . ".";
                
                // Send confirmation email
                $to = $_SESSION['customer_email'];
                $subject = "Your booking request with CabsOnline!";
                $message = "Dear " . $_SESSION['customer_name'] . ",\r\n\r\n";
                $message .= "Thanks for booking with CabsOnline! Your booking reference number is " . $booking_ref . ".\r\n\r\n";
                $message .= "We will pick up the passengers in front of your provided address at " . date('H:i', strtotime($pickup_time)) . " on " . date('d/m/Y', strtotime($pickup_date)) . ".\r\n\r\n";
                $message .= "Best regards,\r\n";
                $message .= "CabsOnline Team";
                
                // Mail headers
                $headers = "From: booking@cabsonline.com.au\r\n";
                
                // Send email
                if (mail($to, $subject, $message, $headers,"-r 105974663@student.swin.edu.au")) {
                    error_log("Booking confirmation email sent to: " . $to);
                } else {
                    error_log("Failed to send booking confirmation email to: " . $to);
                    // Don't show error to user as booking was successful
                }
                
                // Clear form data after successful booking
                $_POST = array();
            } else {
                $error_message = "Error processing booking. Please try again.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CabsOnline - Book a Taxi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .student-id { position: absolute; top: 10px; right: 20px; font-weight: bold; color: #666; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h1 { color: #333; margin: 0; }
        .user-info { font-size: 14px; color: #666; }
        .form-group { margin-bottom: 20px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="tel"], input[type="date"], input[type="time"] { 
            width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 4px; font-size: 16px; box-sizing: border-box; 
        }
        input[type="submit"] { 
            background-color: #ffc107; color: #212529; padding: 12px 30px; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 16px; width: 100%; font-weight: bold;
        }
        input[type="submit"]:hover { background-color: #e0a800; }
        .error { color: red; margin-bottom: 15px; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; }
        .success { color: green; margin-bottom: 15px; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; }
        .logout-link { margin-top: 20px; text-align: center; }
        .logout-link a { color: #dc3545; text-decoration: none; }
        .logout-link a:hover { text-decoration: underline; }
        .required { color: red; }
    </style>
</head>
<body>
    <div class="student-id">s105974663</div>
    
    <div class="container">
        <div class="header">
            <h1>Book a Taxi</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!<br>
                <small><?php echo htmlspecialchars($_SESSION['customer_email']); ?></small>
            </div>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="booking.php">
            <div class="form-group">
                <label for="passenger_name">Passenger Name: <span class="required">*</span></label>
                <input type="text" id="passenger_name" name="passenger_name" value="<?php echo isset($_POST['passenger_name']) ? htmlspecialchars($_POST['passenger_name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="passenger_phone">Contact Phone of Passenger: <span class="required">*</span></label>
                <input type="tel" id="passenger_phone" name="passenger_phone" value="<?php echo isset($_POST['passenger_phone']) ? htmlspecialchars($_POST['passenger_phone']) : ''; ?>" required>
            </div>
            
            <fieldset style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <legend style="color: #555; font-weight: bold;">Pick-up Address</legend>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="unit_number">Unit Number:</label>
                        <input type="text" id="unit_number" name="unit_number" value="<?php echo isset($_POST['unit_number']) ? htmlspecialchars($_POST['unit_number']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="street_number">Street Number: <span class="required">*</span></label>
                        <input type="text" id="street_number" name="street_number" value="<?php echo isset($_POST['street_number']) ? htmlspecialchars($_POST['street_number']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="street_name">Street Name: <span class="required">*</span></label>
                    <input type="text" id="street_name" name="street_name" value="<?php echo isset($_POST['street_name']) ? htmlspecialchars($_POST['street_name']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="suburb">Suburb: <span class="required">*</span></label>
                    <input type="text" id="suburb" name="suburb" value="<?php echo isset($_POST['suburb']) ? htmlspecialchars($_POST['suburb']) : ''; ?>" required>
                </div>
            </fieldset>
            
            <div class="form-group">
                <label for="destination_suburb">Destination Suburb: <span class="required">*</span></label>
                <input type="text" id="destination_suburb" name="destination_suburb" value="<?php echo isset($_POST['destination_suburb']) ? htmlspecialchars($_POST['destination_suburb']) : ''; ?>" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="pickup_date">Pick-up Date: <span class="required">*</span></label>
                    <input type="date" id="pickup_date" name="pickup_date" value="<?php echo isset($_POST['pickup_date']) ? $_POST['pickup_date'] : ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="pickup_time">Pick-up Time: <span class="required">*</span></label>
                    <input type="time" id="pickup_time" name="pickup_time" value="<?php echo isset($_POST['pickup_time']) ? $_POST['pickup_time'] : ''; ?>" required>
                </div>
            </div>
            
            <input type="submit" value="Book Now">
        </form>
        
        <div class="logout-link">
            <p><a href="login.php">Logout</a> | <a href="admin.php">Admin Panel</a></p>
        </div>
    </div>
</body>
</html>