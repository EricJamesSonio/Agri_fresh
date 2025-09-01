<?php
require_once(__DIR__ . '/../../database/db.php');

class Category {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    // Return array of categories: [ { id: int, name: string }, ... ]
    public function all() {
        $sql = "SELECT category_id, category_name FROM category ORDER BY category_name";
        $result = $this->con->query($sql);

        $cats = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $cats[] = [
                    'id' => (int)$row['category_id'],
                    'name' => $row['category_name']
                ];
            }
        }
        return $cats;
    }
}
