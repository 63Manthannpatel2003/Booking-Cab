<?php
/*
* Student Name: Manthan Patel
* Student ID: s105974663
* File: admin.php
* Description: Admin page for managing taxi bookings
*/

// Database connection

session_start();

// Set timezone to India Standard Time
date_default_timezone_set('Australia/Melbourne');

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

$bookings = array();
$message = "";
$show_bookings = false;

// Handle List All button
if (isset($_POST['list_all'])) {
    $show_bookings = true;
    // Get bookings with pick-up time within 2 hours from now and status 'unassigned'
    $current_time = date('Y-m-d H:i:s');
    $two_hours_later = date('Y-m-d H:i:s', strtotime('+2 hours'));
    
    // $sql = "SELECT b.booking_number, c.customer_name, b.passenger_name, b.passenger_phone, 
    //                b.unit_number, b.street_number, b.street_name, b.suburb, b.destination_suburb, 
    //                b.pickup_date, b.pickup_time 
    //         FROM booking b 
    //         JOIN customer c ON b.customer_email = c.email_address
    //         WHERE b.status = 'unassigned' 
    //         AND CONCAT(b.pickup_date, ' ', b.pickup_time) BETWEEN ? AND ?
    //         ORDER BY b.pickup_date, b.pickup_time";
    
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("ss", $current_time, $two_hours_later);
    // $stmt->execute();
    // $result = $stmt->get_result();

    $result = $conn->query("SELECT b.booking_number, c.customer_name, b.passenger_name, b.passenger_phone, 
                   b.unit_number, b.street_number, b.street_name, b.suburb, b.destination_suburb, 
                   b.pickup_date, b.pickup_time 
            FROM booking b 
            JOIN customer c ON b.customer_email = c.email_address
            WHERE b.status = 'unassigned' 
            AND CONCAT(b.pickup_date, ' ', b.pickup_time) BETWEEN '$current_time' AND '$two_hours_later'
            ORDER BY b.pickup_date, b.pickup_time");
    
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    // $stmt->close();
}

// Handle Assign button
if (isset($_POST['assign']) && !empty($_POST['booking_ref'])) {
    $booking_ref = trim($_POST['booking_ref']);
    
    // Update booking status from 'unassigned' to 'assigned'
    $update_sql = "UPDATE booking SET status = 'assigned' WHERE booking_number = ? AND status = 'unassigned'";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("s", $booking_ref);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $message = "The booking request " . htmlspecialchars($booking_ref) . " has been properly assigned.";
        } else {
            $message = "No unassigned booking found with reference number: " . htmlspecialchars($booking_ref);
        }
    } else {
        $message = "Error updating booking status.";
    }
    $stmt->close();

    // $update = $conn->query("UPDATE booking SET status = 'assigned' WHERE booking_number = '$booking_ref' AND status = 'unassigned'");

    // if ($update) {
    //     if ($conn->affected_rows > 0) {
    //         $message = "The booking request " . htmlspecialchars($booking_ref) . " has been properly assigned.";
    //     } else {
    //         $message = "No unassigned booking found with reference number: " . htmlspecialchars($booking_ref);
    //     }
    // } else {
    //     $message = "Error updating booking status.";
    // }
}

$conn->close();

function formatAddress($unit, $street_num, $street_name, $suburb) {
    $address = "";
    if (!empty($unit)) {
        $address .= $unit . "/";
    }
    $address .= $street_num . " " . $street_name . ", " . $suburb;
    return $address;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CabsOnline - Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .student-id { position: absolute; top: 10px; right: 20px; font-weight: bold; color: #666; }
        h1 { text-align: center; color: #333; margin-bottom: 30px; }
        .admin-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9; }
        .admin-section h2 { margin-top: 0; color: #555; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"] { 
            padding: 8px; border: 2px solid #ddd; border-radius: 4px; font-size: 14px; width: 200px; 
        }
        input[type="submit"] { 
            background-color: #17a2b8; color: white; padding: 10px 20px; border: none; border-radius: 4px; 
            cursor: pointer; font-size: 14px; margin-left: 10px;
        }
        input[type="submit"]:hover { background-color: #138496; }
        .list-btn { background-color: #28a745; }
        .list-btn:hover { background-color: #218838; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .success { color: green; background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { color: red; background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .bookings-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .bookings-table th, .bookings-table td { 
            border: 1px solid #ddd; padding: 12px; text-align: left; 
        }
        .bookings-table th { 
            background-color: #f8f9fa; font-weight: bold; color: #495057; 
        }
        .bookings-table tr:nth-child(even) { background-color: #f8f9fa; }
        .bookings-table tr:hover { background-color: #e9ecef; }
        .no-bookings { text-align: center; padding: 20px; color: #666; font-style: italic; }
        .back-link { margin-top: 30px; text-align: center; }
        .back-link a { color: #007bff; text-decoration: none; padding: 10px 20px; border: 1px solid #007bff; border-radius: 4px; }
        .back-link a:hover { background-color: #007bff; color: white; text-decoration: none; }
    </style>
</head>
<body>
    <div class="student-id">s105974663</div>
    
    <div class="container">
        <h1>CabsOnline Administration</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo (strpos($message, 'properly assigned') !== false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- List All Bookings Section -->
        <div class="admin-section">
            <h2>View Unassigned Bookings</h2>
            <p>Click the button below to list all unassigned bookings with pick-up time within 2 hours from now:</p>
            <form method="POST" action="admin.php" style="display: inline;">
                <input type="submit" name="list_all" value="List All" class="list-btn">
            </form>
        </div>
        
        <!-- Assign Booking Section -->
        <div class="admin-section">
            <h2>Assign Taxi to Booking</h2>
            <p>Enter the booking reference number to assign a taxi:</p>
            <form method="POST" action="admin.php" style="display: flex; align-items: center;">
                <div class="form-group" style="margin-bottom: 0; margin-right: 15px;">
                    <label for="booking_ref">Booking Reference Number:</label>
                    <input type="text" id="booking_ref" name="booking_ref" placeholder="Enter booking reference" required>
                </div>
                <input type="submit" name="assign" value="Update">
            </form>
        </div>
        
        <!-- Bookings Table -->
        <?php if ($show_bookings): ?>
            <div class="admin-section">
                <h2>Unassigned Bookings (Next 2 Hours)</h2>
                <?php if (count($bookings) > 0): ?>
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Booking Reference</th>
                                <th>Customer Name</th>
                                <th>Passenger Name</th>
                                <th>Passenger Phone</th>
                                <th>Pick-up Address</th>
                                <th>Destination Suburb</th>
                                <th>Pick-up Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['booking_number']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['passenger_phone']); ?></td>
                                    <td><?php echo htmlspecialchars(formatAddress($booking['unit_number'], $booking['street_number'], $booking['street_name'], $booking['suburb'])); ?></td>
                                    <td><?php echo htmlspecialchars($booking['destination_suburb']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($booking['pickup_date'] . ' ' . $booking['pickup_time']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-bookings">
                        No unassigned bookings found with pick-up time within the next 2 hours.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="booking.php">← Back to Booking</a>
        </div>
    </div>
</body>
</html>