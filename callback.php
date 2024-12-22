<?php
require_once 'database.php';
$callbackData = json_decode(file_get_contents('php://input'), true);

$transactionId = $callbackData['Body']['stkCallback']['CheckoutRequestID'];
$resultCode = $callbackData['Body']['stkCallback']['ResultCode'];
$status = $resultCode == 0 ? 'success' : 'failed';

$conn = getConnection();
$stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
$stmt->bind_param('ss', $status, $transactionId);
$stmt->execute();
