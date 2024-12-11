<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../php/database.php';

header('Content-Type:application/json');

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!isset($data['orderId'], $data['paymentAmount'])) {
        throw new Exception('Order ID and payment amount are required.');
    }

    $orderId = intval($data['orderId']);
    $paymentAmount = floatval($data['paymentAmount']);

    $conn = getDatabaseConnection();

    // Fetch the latest total price and amount paid
    $stmt = $conn->prepare("SELECT total_price, amount_paid FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->bind_result($totalPrice, $amountPaid);
    $stmt->fetch();
    $stmt->close();

    // Calculate remaining balance and update amount paid
    $remainingDue = max(0, $totalPrice - $amountPaid);
    $paymentApplied = min($paymentAmount, $remainingDue);
    $newAmountPaid = $amountPaid + $paymentApplied;

    // Update payment info
    $paymentStatus = ($newAmountPaid >= $totalPrice) ? 'Paid' : 'Pending';
    $stmt = $conn->prepare("UPDATE orders SET amount_paid = ?, payment_status = ? WHERE order_id = ?");
    $stmt->bind_param("dsi", $newAmountPaid, $paymentStatus, $orderId);
    $stmt->execute();

    // Calculate change
    $change = ($paymentAmount > $remainingDue) ? $paymentAmount - $remainingDue : 0;

    echo json_encode([
        'success' => true,
        'remainingBalance' => max(0, $remainingDue - $paymentApplied),
        'change' => $change,
        'paymentStatus' => $paymentStatus
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
