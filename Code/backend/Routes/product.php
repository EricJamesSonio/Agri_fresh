<?php
require_once(__DIR__ . '/../Controller/ProductController.php');

// Make sure $con is available from db.php
$controller = new ProductController($con);
$controller->index();
