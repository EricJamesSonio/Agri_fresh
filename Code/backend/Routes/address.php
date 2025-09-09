<?php
require_once(__DIR__ . '/../Controller/AddressController.php');
require_once(__DIR__ . '/../../Database/db.php');


$method = $_SERVER['REQUEST_METHOD'];
$controller = new AddressController($con);

switch($method) {
    case 'GET':
        $customer_id = $_GET['customer_id'] ?? null;
        if (!$customer_id) {
            echo json_encode(["status"=>"error","message"=>"Missing customer_id"]);
            exit;
        }
        
        $response = $controller->getAddresses($customer_id);
        echo json_encode($response);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $customer_id = $data['customer_id'] ?? null;
        $action = $data['action'] ?? 'add';

        if (!$customer_id) {
            echo json_encode(["status"=>"error","message"=>"Missing customer_id"]);
            exit;
        }

        if ($action === 'add') {
            $response = $controller->addAddress($customer_id, $data);
        } elseif ($action === 'set_default') {
            $address_id = $data['address_id'] ?? null;
            if (!$address_id) {
                echo json_encode(["status"=>"error","message"=>"Missing address_id"]);
                exit;
            }
            $response = $controller->setDefault($customer_id, $address_id);
        } else {
            $response = ["status"=>"error","message"=>"Invalid action"];
        }

        echo json_encode($response);
        break;

    default:
        echo json_encode(["status"=>"error","message"=>"Method not allowed"]);
        break;
}
