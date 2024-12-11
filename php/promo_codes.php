<?php
include 'database.php';

header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");  // Prevent caching
header("Pragma: no-cache");
header("Expires: 0");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);



$action = $_GET['action'] ?? '';
$conn = getDatabaseConnection();

try {
    if ($action === 'list') {
        $result = $conn->query("SELECT * FROM promotion_codes");
        if (!$result) {
            throw new Exception("Failed to fetch promo codes.");
        }
        $result = $conn->query("SELECT * FROM promotion_codes");
        $codes = $result->fetch_all(MYSQLI_ASSOC);

        // Cast `is_active` field to integer to ensure it's properly handled in JavaScript
        foreach ($codes as &$code) {
            $code['is_active'] = (int)$code['is_active']; // Ensure it's an integer
        }
        echo json_encode(['success' => true, 'codes' => $codes]);
    } 
    
    elseif ($action === 'add') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception("Invalid input data.");
        }
        $stmt = $conn->prepare("INSERT INTO promotion_codes (code, discount_percentage) VALUES (?, ?)");
        $stmt->bind_param("sd", $data['code'], $data['discount']);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } 
    
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception("Invalid input data.");
        }
        $stmt = $conn->prepare("UPDATE promotion_codes SET code = ?, discount_percentage = ? WHERE promo_code_id = ?");
        $stmt->bind_param("sdi", $data['code'], $data['discount'], $data['id']);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } 
    
    elseif ($action === 'delete') {
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM promotion_codes WHERE promo_code_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } 
    
    elseif ($action === 'toggle_status') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            throw new Exception("Invalid input data.");
        }
    
        // Update the promo code status
        $stmt = $conn->prepare("UPDATE promotion_codes SET is_active = ? WHERE promo_code_id = ?");
        $stmt->bind_param("ii", $data['is_active'], $data['id']);
        $stmt->execute();
    
        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            // Fetch the updated list of promo codes after status update
            $result = $conn->query("SELECT * FROM promotion_codes");
            if (!$result) {
                throw new Exception("Failed to fetch promo codes after status update.");
            }
    
            $codes = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'codes' => $codes]);
        } else {
            // Log failed update
            error_log("Failed to update promo code with ID: " . $data['id']);
            echo json_encode(['success' => false, 'message' => 'Failed to update promo code status.']);
        }
    }
    
    
    
    else {
        throw new Exception("Invalid action.");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
