<?php
// Include Access token
global $access_token;
include 'accessToken.php';
date_default_timezone_set('Africa/Nairobi');

// Set up the request URL and other parameters
$processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
$callbackurl = 'https://9287-41-80-114-231.ngrok-free.app/Simpleav/callback.php';
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$BusinessShortCode = "174379";
$Timestamp = date('YmdHis');

// ENCRYPT DATA TO GET PASSWORD
$Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
$PhoneNumber = '254791905576'; // Phone number to receive the STK push
$money = '2000';
$PartyA = $PhoneNumber;
$PartyB = $BusinessShortCode;
$AccountReference = "SIMPLEAV";
$Amount = $money;
$TransactionDesc = "stk push test";

// Prepare headers for the STK Push request
$stkpushheader = [
  'Content-Type: application/json',
  'Authorization: Bearer ' . $access_token // Ensure this token is valid
];

// INITIATE CURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader);

// Prepare the data to be sent in the request
$curl_post_data = array(
  'BusinessShortCode' => $BusinessShortCode,
  'Password' => $Password,
  'Timestamp' => $Timestamp,
  'TransactionType' => 'CustomerPayBillOnline',
  'Amount' => $Amount,
  'PartyA' => $PartyA,
  'PartyB' => $PartyB,
  'PhoneNumber' => $PartyA,
  'CallBackUrl' => $callbackurl,
  'AccountReference' => $AccountReference,
  'TransactionDesc' => $TransactionDesc
);

// Convert the data to JSON
$data_string = json_encode($curl_post_data);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, TRUE);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

// Execute the CURL request and get the response
$curl_response = curl_exec($curl);

// Check for cURL errors
if (curl_errno($curl)) {
  echo 'Curl error: ' . curl_error($curl);
} else {
  // Print the response from the API
  echo $curl_response;
}

// Close the cURL session
curl_close($curl);

// Additional code from the second block
echo "Daraja API by Ayana ";

// Second cURL request (not needed since it's already handled above)
$ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Authorization: Bearer ' . $access_token, // Use the same access token
  'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  "BusinessShortCode" => $BusinessShortCode,
  "Password" => $Password, // Use the same password generated above
  "Timestamp" => $Timestamp,
  "TransactionType" => "CustomerPayBillOnline",
  "Amount" => $Amount,
  "PartyA" => $PartyA,
  "PartyB" => $BusinessShortCode,
  "PhoneNumber" => $PartyA,
  "CallBackURL" => $callbackurl,
  "AccountReference" => $AccountReference,
  "TransactionDesc" => $TransactionDesc
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

if (curl_errno($curl)) {
  echo 'Curl error: ' . curl_error($curl);
} else {
  // Print the response from the API
  echo $curl_response;

  // Redirect to the thank you page
  header("Location: thank you.php");
  exit();
}

// Output the response from the second request (if needed)
echo $response;

