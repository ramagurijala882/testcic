<?php
// data_analysis.php

include 'database.php'; // Include your database connection
header('Content-Type: application/json');
$conn = getDatabaseConnection();

define('DEFAULT_MONTH', date('m'));
define('DEFAULT_YEAR', date('Y'));

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'total_sales':
        fetchTotalSales($conn);
        break;
    case 'top_selling_products':
        fetchTopSellingProducts($conn);
        break;
    case 'sales_by_category':
        fetchSalesByCategory($conn);
        break;
    case 'daily_sales':
        fetchDailySales($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function fetchTotalSales($conn) {
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        $startDate = $_GET['start_date'];
        $endDate = $_GET['end_date'];

        // Prepare the query with placeholders for the dates
        $stmt = $conn->prepare("
            SELECT SUM(total_price) AS total_sales
            FROM orders
            WHERE order_date BETWEEN ? AND ?
        ");
        
        // Check if prepare was successful
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => $conn->error]);
            return;
        }

        // Bind the parameters to the placeholders
        $stmt->bind_param("ss", $startDate, $endDate);

    } else {
        // Prepare the query without the date filter
        $stmt = $conn->prepare("
            SELECT SUM(total_price) AS total_sales
            FROM orders
        ");
        
        // Check if prepare was successful
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => $conn->error]);
            return;
        }
    }

    // Execute the prepared statement
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
        return;
    }

    // Get the result
    $result = $stmt->get_result();
    // Fetch the data
    $data = $result->fetch_assoc();
    
    // Return the response as JSON
    echo json_encode(['success' => true, 'data' => $data]);
}


function fetchTopSellingProducts($conn) {
    $stmt = $conn->prepare("SELECT product_name, SUM(quantity) AS total_quantity FROM order_details GROUP BY product_name ORDER BY total_quantity DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
}

function fetchSalesByCategory($conn) {
    $stmt = $conn->prepare("SELECT p.product_category, SUM(od.quantity * od.price) AS total_sales FROM order_details od JOIN products p ON od.product_name = p.product_name GROUP BY p.product_category");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
}

function fetchDailySales($conn) {
    $stmt = $conn->prepare("SELECT DATE(order_date) AS sale_date, SUM(total_price) AS total_sales FROM orders GROUP BY sale_date");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
}
?>