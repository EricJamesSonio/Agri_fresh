<?php
// ensure your db connection is available as $con (same way you do for products)
require_once(__DIR__ . '/../Controller/CategoryController.php');

$controller = new CategoryController($con);
$controller->index();
