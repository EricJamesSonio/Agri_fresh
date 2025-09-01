<?php
require_once(__DIR__ . '/../../database/db.php');

class Product {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

public function all() {
    $sql = "SELECT p.product_id, p.name, p.description, p.price, 
                   IFNULL(p.stock_quantity, 0) AS stock_quantity, 
                   p.image_url,
                   c.category_id,
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
            'category_id' => $row['category_id'],      // <--- added
            'category' => $row['category'] ?? 'Uncategorized',
            'price' => floatval($row['price']),
            'stock_quantity' => intval($row['stock_quantity']),
            'img' => $row['image_url'],
            'tags' => $tags
        ];
    }

    return $products;
}

    public function create($data) {
    $stmt = $this->con->prepare("
        INSERT INTO product 
        (name, price, stock_quantity, image_url, category_id, is_organic, is_seasonal) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sdiisii",
        $data['name'], $data['price'], $data['stock_quantity'], 
        $data['image_url'], $data['category'], $data['is_organic'], $data['is_seasonal']
    );
    $stmt->execute();
}

public function update($data) {
    $stmt = $this->con->prepare("
        UPDATE product SET
        name=?, price=?, stock_quantity=?, image_url=?, category_id=?, is_organic=?, is_seasonal=?
        WHERE product_id=?
    ");
    $stmt->bind_param(
        "sdiisiii",
        $data['name'], $data['price'], $data['stock_quantity'],
        $data['image_url'], $data['category'], $data['is_organic'], $data['is_seasonal'], $data['id']
    );
    $stmt->execute();
}

public function find($id) {
    $stmt = $this->con->prepare("
        SELECT p.product_id, p.name, p.description, p.price, p.stock_quantity, p.image_url,
               c.category_id,
               c.category_name AS category,
               p.is_organic, p.is_seasonal
        FROM product p
        LEFT JOIN category c ON p.category_id = c.category_id
        WHERE p.product_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!$result) return null;

    $tags = [];
    if ($result['is_organic']) $tags[] = 'organic';
    if ($result['is_seasonal']) $tags[] = 'seasonal';

    return [
        'id' => $result['product_id'],
        'name' => $result['name'],
        'description' => $result['description'],
        'category_id' => $result['category_id'],    // <--- added
        'category' => $result['category'] ?? 'Uncategorized',
        'price' => floatval($result['price']),
        'stock_quantity' => intval($result['stock_quantity']),
        'img' => $result['image_url'],
        'tags' => $tags
    ];
}

}
