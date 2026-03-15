<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

date_default_timezone_set("Asia/Kolkata");

$response = [
    "status" => "success",
    "timestamp" => date("Y-m-d H:i:s"),
    "message" => "This is a live GET API response"
];

echo json_encode($response);
exit;

