<?php
session_start();

function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../frontend/html/index.php");
        exit();
    }
}

function requireLogin() {
    if (!isset($_SESSION['customer_id'])) {
        header("Location: ../../frontend/html/login.php");
        exit();
    }
}
