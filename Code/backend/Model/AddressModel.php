<?php
require_once(__DIR__ . '/../../Database/db.php');

class AddressModel {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function getCustomerAddresses($customer_id) {
        $stmt = $this->con->prepare("
            SELECT address_id, street, city, state, postal_code, country, is_default 
            FROM customer_address 
            WHERE customer_id = ? 
            ORDER BY is_default DESC, created_at ASC
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $addresses = [];
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
        
        return $addresses;
    }

    public function addAddress($customer_id, $street, $city, $state, $postal_code, $country, $is_default = false) {
        // If this is set as default, remove default from other addresses
        if ($is_default) {
            $this->removeDefaultFromAll($customer_id);
        }

        $stmt = $this->con->prepare("
            INSERT INTO customer_address (customer_id, street, city, state, postal_code, country, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssssi", $customer_id, $street, $city, $state, $postal_code, $country, $is_default);
        
        if ($stmt->execute()) {
            return $this->con->insert_id;
        }
        
        return false;
    }

    public function getAddress($address_id) {
        $stmt = $this->con->prepare("
            SELECT address_id, customer_id, street, city, state, postal_code, country, is_default 
            FROM customer_address 
            WHERE address_id = ?
        ");
        $stmt->bind_param("i", $address_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function removeDefaultFromAll($customer_id) {
        $stmt = $this->con->prepare("UPDATE customer_address SET is_default = 0 WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
    }

    public function setDefaultAddress($customer_id, $address_id) {
        $this->removeDefaultFromAll($customer_id);
        
        $stmt = $this->con->prepare("
            UPDATE customer_address 
            SET is_default = 1 
            WHERE address_id = ? AND customer_id = ?
        ");
        $stmt->bind_param("ii", $address_id, $customer_id);
        return $stmt->execute();
    }
}