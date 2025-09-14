<?php
require_once(__DIR__ . '/../../database/db.php');

class Product {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function all() {
        $sql = "SELECT p.product_id, p.name, p.description,
                       p.size1_value, p.size1_unit, p.price1,
                       p.size2_value, p.size2_unit, p.price2,
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
                'category_id' => $row['category_id'],
                'category' => $row['category'] ?? 'Uncategorized',
                'stock_quantity' => intval($row['stock_quantity']),
                'img' => $row['image_url'],

                // Sizes
                'size1_value' => floatval($row['size1_value']),
                'size1_unit' => $row['size1_unit'],
                'price1' => floatval($row['price1']),

                'size2_value' => $row['size2_value'] !== null ? floatval($row['size2_value']) : null,
                'size2_unit' => $row['size2_unit'],
                'price2' => $row['price2'] !== null ? floatval($row['price2']) : null,

                'tags' => $tags
            ];
        }

        return $products;
    }

    public function create($data) {
        $stmt = $this->con->prepare("
            INSERT INTO product 
            (name, description, stock_quantity, image_url, category_id,
             size1_value, size1_unit, price1,
             size2_value, size2_unit, price2,
             is_organic, is_seasonal) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
            $stmt->bind_param(
                "ssissi d s d d s d i i",  // spacing for clarity, actual string is below
                $data['name'],          // s
                $data['description'],   // s
                $data['stock_quantity'],// i
                $data['image_url'],     // s
                $data['category'],      // i
                $data['size1_value'],   // d
                $data['size1_unit'],    // s
                $data['price1'],        // d
                $data['size2_value'],   // d
                $data['size2_unit'],    // s
                $data['price2'],        // d
                $data['is_organic'],    // i
                $data['is_seasonal']    // i
            );


        $stmt->execute();
    }

    public function update($data) {
        $current = $this->find($data['id']);
        if (!$current) {
            throw new Exception("Product not found");
        }

        $updateFields = [];
        $types = "";
        $values = [];

        if (isset($data['name'])) {
            $updateFields[] = "name=?";
            $types .= "s";
            $values[] = $data['name'];
        }

        if (isset($data['description'])) {
            $updateFields[] = "description=?";
            $types .= "s";
            $values[] = $data['description'];
        }


        if (isset($data['stock_quantity'])) {
            $updateFields[] = "stock_quantity=?";
            $types .= "i";
            $values[] = $data['stock_quantity'];
        }

        if (isset($data['image_url']) && $data['image_url'] !== '') {
            $updateFields[] = "image_url=?";
            $types .= "s";
            $values[] = $data['image_url'];
        }

        if (isset($data['category'])) {
            $updateFields[] = "category_id=?";
            $types .= "i";
            $values[] = $data['category'];
        }

        if (isset($data['is_organic'])) {
            $updateFields[] = "is_organic=?";
            $types .= "i";
            $values[] = $data['is_organic'];
        }

        if (isset($data['is_seasonal'])) {
            $updateFields[] = "is_seasonal=?";
            $types .= "i";
            $values[] = $data['is_seasonal'];
        }

        // Size 1
        if (isset($data['size1_value'])) {
            $updateFields[] = "size1_value=?";
            $types .= "d";
            $values[] = $data['size1_value'];
        }
        if (isset($data['size1_unit'])) {
            $updateFields[] = "size1_unit=?";
            $types .= "s";
            $values[] = $data['size1_unit'];
        }
        if (isset($data['price1'])) {
            $updateFields[] = "price1=?";
            $types .= "d";
            $values[] = $data['price1'];
        }

        // Size 2
        if (isset($data['size2_value'])) {
            $updateFields[] = "size2_value=?";
            $types .= "d";
            $values[] = $data['size2_value'];
        }
        if (isset($data['size2_unit'])) {
            $updateFields[] = "size2_unit=?";
            $types .= "s";
            $values[] = $data['size2_unit'];
        }
        if (isset($data['price2'])) {
            $updateFields[] = "price2=?";
            $types .= "d";
            $values[] = $data['price2'];
        }

        if (empty($updateFields)) {
            return;
        }

        $types .= "i";
        $values[] = $data['id'];

        $sql = "UPDATE product SET " . implode(", ", $updateFields) . " WHERE product_id=?";
        $stmt = $this->con->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
    }

    public function find($id) {
        $stmt = $this->con->prepare("
            SELECT p.product_id, p.name, p.description, p.stock_quantity,
                   p.image_url,
                   p.size1_value, p.size1_unit, p.price1,
                   p.size2_value, p.size2_unit, p.price2,
                   c.category_id, c.category_name AS category,
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
            'category_id' => $result['category_id'],
            'category' => $result['category'] ?? 'Uncategorized',
            'stock_quantity' => intval($result['stock_quantity']),
            'img' => $result['image_url'],

            'size1_value' => floatval($result['size1_value']),
            'size1_unit' => $result['size1_unit'],
            'price1' => floatval($result['price1']),

            'size2_value' => $result['size2_value'] !== null ? floatval($result['size2_value']) : null,
            'size2_unit' => $result['size2_unit'],
            'price2' => $result['price2'] !== null ? floatval($result['price2']) : null,

            'tags' => $tags
        ];
    }

    public function delete($id) {
        $stmt = $this->con->prepare("DELETE FROM product WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deleteByName($name) {
        $stmt = $this->con->prepare("DELETE FROM product WHERE name = ?");
        $stmt->bind_param("s", $name);
        return $stmt->execute();
    }
}
