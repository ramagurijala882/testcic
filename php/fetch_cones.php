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


$sql = "SELECT p.product_id, p.product_name AS name, p.price, p.image_path, 
               GROUP_CONCAT(t.topping_name SEPARATOR ', ') AS toppings
        FROM products p
        LEFT JOIN product_toppings pt ON p.product_id = pt.product_id
        LEFT JOIN toppings t ON pt.topping_id = t.topping_id
       WHERE p.product_category = 'cones' 
        GROUP BY p.product_id";




$result = $conn->query($sql);

$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);

$conn->close();
?>
