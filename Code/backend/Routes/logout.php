<?php
session_start();
session_unset();
session_destroy();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
exit;
