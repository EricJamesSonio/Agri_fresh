<?php
require_once(__DIR__ . '/../Model/Product.php');

class ProductController {
    private $model;

    public function __construct($con) {
        $this->model = new Product($con);
    }

    public function get($id) {
    $product = $this->model->find($id);
    header("Content-Type: application/json");
    echo json_encode($product);
}


    public function index() {
        $products = $this->model->all();
        header("Content-Type: application/json");
        echo json_encode($products);
    }

    public function createOrUpdate() {
    $data = json_decode(file_get_contents('php://input'), true);

    if(isset($data['id']) && $data['id']) {
        $this->model->update($data);
        echo json_encode(["status" => "success", "message" => "Product updated"]);
    } else {
        $this->model->create($data);
        echo json_encode(["status" => "success", "message" => "Product created"]);
    }
}

    



    
}
