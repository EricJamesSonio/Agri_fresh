<?php
require_once(__DIR__ . '/../../database/db.php');

class Product {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function all() {
        $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image_url,
                       c.category_name AS category,
                       p.is_organic, p.is_seasonal
                FROM product p
                LEFT JOIN category c ON p.category_id = c.category_id";
        $result = $this->con->query($sql);

        $products = [];
        while($row = $result->fetch_assoc()) {
            $tags = [];
            if ($row['is_organic']) $tags[] = 'organic';
            if ($row['is_seasonal']) $tags[] = 'seasonal';

            $products[] = [
                'id' => $row['product_id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'category' => $row['category'] ?? 'Uncategorized',
                'price' => floatval($row['price']),
                'img' => $row['image_url'],
                'tags' => $tags
            ];
        }

        return $products;
    }
}
