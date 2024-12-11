<?php
session_start();
include 'database.php';

$conn = getDatabaseConnection();

if (isset($_POST['name'], $_POST['phone'], $_POST['cart'], $_POST['grandTotal'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $cart = json_decode($_POST['cart'], true);
    $grandTotal = $_POST['grandTotal']; // Fetch the grand total from the request
    $conn = getDatabaseConnection();

    // Insert customer details
    $stmt = $conn->prepare("INSERT INTO customers (name, contact) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $phone);
    $stmt->execute();
    $customer_id = $stmt->insert_id;

    // Insert order details
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, order_date, total_price) VALUES (?, NOW(), ?)");
    $stmt->bind_param("id", $customer_id, $grandTotal);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert order items and toppings
    foreach ($cart as $item) {
        // Get the price of the toppings
        $total_topping_price = 0;
        foreach ($item['selectedToppings'] as $topping) {
            $stmt = $conn->prepare("SELECT price FROM toppings WHERE topping_name = ?");
            $stmt->bind_param("s", $topping);
            $stmt->execute();
            $result = $stmt->get_result();
            $topping_price = $result->fetch_assoc()['price'];
            $total_topping_price += $topping_price;
        }

        $total_price = ($item['productPrice'] + $total_topping_price) * $item['quantity'];

        // Insert into order_details table
        $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_name, quantity, price, toppings) 
                                VALUES (?, ?, ?, ?, ?)");
        $toppings = implode(", ", $item['selectedToppings']);
        $stmt->bind_param("ssdis", $order_id, $item['productName'], $item['quantity'], $total_price, $toppings);
        $stmt->execute();
    }

    // Return order ID in the response
    echo json_encode(['success' => true, 'order_id' => $order_id]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
}
?>
