<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Replace with your RDS database credentials
$servername = "csicg4.czptxhzjxjrt.us-east-1.rds.amazonaws.com";
$username = "group4";
$password = "Groupfour";
$dbname = "ice_shop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$order_id = $_GET['order_id'] ?? '';

if ($order_id) {
    $orderQuery = "SELECT order_id, total_price, amount_paid, payment_status FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        $orderDetailsQuery = "SELECT od.product_name, od.quantity, od.price, od.toppings FROM order_details od WHERE od.order_id = ?";
        $stmt2 = $conn->prepare($orderDetailsQuery);
        $stmt2->bind_param("i", $order_id);
        $stmt2->execute();
        $orderDetailsResult = $stmt2->get_result();

        $orderDetails = [];
        while ($row = $orderDetailsResult->fetch_assoc()) {
            $orderDetails[] = $row;
        }

        echo json_encode([
            'order_id' => $order['order_id'],
            'total_price' => $order['total_price'],
            'amountPaid' => $order['amount_paid'],
            'payment_status' => $order['payment_status'],
            'orderDetails' => $orderDetails
        ]);
    } else {
        echo json_encode(['error' => 'Order not found']);
    }
}

$stmt->close();
$conn->close();
?>
