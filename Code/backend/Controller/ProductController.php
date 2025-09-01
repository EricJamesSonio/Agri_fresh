<?php
require_once(__DIR__ . '/../Model/Product.php');

class ProductController {
    private $model;

    public function __construct($con) {
        $this->model = new Product($con);
    }

    public function index() {
        $products = $this->model->all();
        header("Content-Type: application/json");
        echo json_encode($products);
    }

    



    
}
