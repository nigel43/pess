<?php
header('Content-Type: application/json');
require_once 'database.php'; // Include DB connection

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);
$phoneNumber = $data['phoneNumber'];
$amount = $data['amount'];

// Daraja API credentials
$consumerKey = 'kawfXeAFIzD253hPnX1bAFuPPjz4enHwDCc3tkJ0okN7chc2';
$consumerSecret = 'imZD6Pb8sNc52wkvwyUoGeDTGEGOaehPRyGq5HvI6NW8Ovv4LgZdB4HbXeGB16Cm';
$shortCode = '3122608';
$passKey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';

// Generate access token
$authUrl = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$credentials = base64_encode("$consumerKey:$consumerSecret");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic $credentials"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$accessToken = json_decode($response)->access_token;

// Initiate STK Push
$timestamp = date('YmdHis');
$password = base64_encode($shortCode . $passKey . $timestamp);
$stkUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

$stkData = [
    'BusinessShortCode' => $shortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phoneNumber,
    'PartyB' => $shortCode,
    'PhoneNumber' => $phoneNumber,
    'CallBackURL' => 'https://nigel43.github.io/pess/callback.php',
    'AccountReference' => 'Shopping',
    'TransactionDesc' => 'Payment for 100GB'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $stkUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stkData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Save transaction
$conn = getConnection();
$stmt = $conn->prepare("INSERT INTO transactions (phone_number, amount, status) VALUES (?, ?, ?)");
$stmt->bind_param('sds', $phoneNumber, $amount, $status);
$status = 'pending';
$stmt->execute();

echo json_encode(['message' => 'Payment request sent. Please complete on your phone.']);
