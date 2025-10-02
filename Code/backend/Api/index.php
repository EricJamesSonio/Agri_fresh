<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once(__DIR__ . '/../../database/db.php'); // DB connection

$request = $_GET['request'] ?? '';

switch ($request) {
    case 'login':
        require_once(__DIR__ . '/../Routes/auth.php');
        break;

    case 'products':
        require_once(__DIR__ . '/../Routes/product.php');
        break;

    case 'signup':
        require_once(__DIR__ . '/../Routes/signup.php');
        break;

    case 'cart':
        require_once(__DIR__ . '/../Routes/cart.php');
    break;

    case 'address':
        require_once(__DIR__ . '/../Routes/address.php');
    break;

    case 'orders':
        require_once(__DIR__ . '/../Routes/order.php');
    break;

    case "orderUpdate":
        require_once(__DIR__ . '/../Routes/orderUpdate.php');
        break;

    case 'category':
        require_once(__DIR__ . '/../Routes/category.php');
        break;

    case 'profile':
        require_once(__DIR__ . '/../Routes/profile.php');
        break;

    case 'voucher':
        require_once(__DIR__ . '/../Routes/voucher.php');
        break;

    case 'customer':
        require_once(__DIR__ . '/../Routes/customer.php');
        break;

    case 'get-notifications':
        require_once(__DIR__ . '/../Routes/get-notifications.php');
        break;

    case 'mark-notifications-read':
        require_once(__DIR__ . '/../Routes/mark-notif-read.php');
        break;

    case 'voucherAdd':
        require_once(__DIR__ . '/../Routes/voucherAdd.php');
        break;

    case 'voucherDelete':
        require_once(__DIR__ . '/../Routes/voucherDelete.php');
        break;

    case 'voucherUpdate':
        require_once(__DIR__ . '/../Routes/voucherUpdate.php');
        break;

    case 'voucherList':
        require_once(__DIR__ . '/../Routes/voucherList.php');
        break;

    case 'voucherValidate':
        require_once(__DIR__ . '/../Routes/voucherValidate.php');
        break;

    case 'logout':
        require_once(__DIR__ . '/../Routes/logout.php');
        break;

    case 'sendVoucher':
        require_once(__DIR__ . '/../Routes/send-voucher.php');
        break;

    case 'orderReturnRequest':
        require_once(__DIR__ . '/../Routes/orderReturnRequest.php');
        break;

    case 'otp':
        require_once(__DIR__ . '/../Routes/otp.php');
        break;



    default:
        echo json_encode([
            "status" => "error",
            "message" => "Unknown or missing API request"
        ]);
        break;
}
