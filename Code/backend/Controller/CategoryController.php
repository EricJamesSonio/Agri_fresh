<?php
require_once(__DIR__ . '/../Model/Category.php');

class CategoryController {
    private $model;

    public function __construct($con) {
        $this->model = new Category($con);
    }

    public function index() {
        header("Content-Type: application/json");
        $cats = $this->model->all();
        echo json_encode($cats);
    }
}
