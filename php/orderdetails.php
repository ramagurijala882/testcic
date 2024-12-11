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
        return $conn;
    }

    $conn = getDatabaseConnection();

    $search_query = "";
    $search_field = isset($_GET['search_field']) ? $_GET['search_field'] : '';
    $search_term = isset($_GET['search_term']) ? mysqli_real_escape_string($conn, $_GET['search_term']) : '';

    if ($search_term != '') {
        // Ensure a valid search field is chosen
        $search_query = "WHERE $search_field LIKE '%$search_term%'";
    }

    $query = "
        SELECT o.order_id, c.name AS customer_name, o.total_price, o.order_date, od.product_name, od.quantity, od.price, od.toppings, 
        o.amount_paid,
        CASE 
            WHEN o.amount_paid >= o.total_price THEN 'Paid'
            ELSE 'Pending'
        END AS payment_status
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN order_details od ON o.order_id = od.order_id
        $search_query
        ORDER BY o.order_date DESC
    ";

    $result = mysqli_query($conn, $query);

    echo "<h1>Search Orders</h1>";
    echo "<form method='GET'>
            <select name='search_field'>
                <option value='c.name' " . ($search_field == 'c.name' ? 'selected' : '') . ">Customer Name</option>
                <option value='o.order_id' " . ($search_field == 'o.order_id' ? 'selected' : '') . ">Order ID</option>
                <option value='od.order_detail_id' " . ($search_field == 'od.order_detail_id' ? 'selected' : '') . ">Order Detail ID</option>
                <option value='od.product_name' " . ($search_field == 'od.product_name' ? 'selected' : '') . ">Product Name</option>
            </select>
            <input type='text' name='search_term' value='$search_term' placeholder='Enter search term' />
            <input type='submit' value='Search' />
        </form>";

    echo "<table border='1'>
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Toppings</th>
            <th>Total Price</th>
            <th>Order Date</th>
            <th>Payment Status</th>
        </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['order_id']}</td>
                <td>{$row['customer_name']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['quantity']}</td>
                <td>{$row['price']}</td>
                <td>{$row['toppings']}</td>
                <td>{$row['total_price']}</td>
                <td>{$row['order_date']}</td>
                <td>{$row['payment_status']}</td>
            </tr>";
    }

    echo "</table>";
?>
