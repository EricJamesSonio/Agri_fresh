<?php
require_once(__DIR__ . '/../Model/AddressModel.php');

class AddressController {
    private $addressModel;

    public function __construct($con) {
        $this->addressModel = new AddressModel($con);
    }

    public function getAddresses($customer_id) {
        try {
            $addresses = $this->addressModel->getCustomerAddresses($customer_id);
            return [
                'status' => 'success',
                'data' => $addresses
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to fetch addresses: ' . $e->getMessage()
            ];
        }
    }

    public function addAddress($customer_id, $addressData) {
        // Validate required fields
        $required = ['street', 'city', 'country'];
        foreach ($required as $field) {
            if (empty($addressData[$field])) {
                return [
                    'status' => 'error',
                    'message' => "Missing required field: $field"
                ];
            }
        }

        try {
            $address_id = $this->addressModel->addAddress(
                $customer_id,
                $addressData['street'],
                $addressData['city'],
                $addressData['state'] ?? '',
                $addressData['postal_code'] ?? '',
                $addressData['country'],
                $addressData['is_default'] ?? false
            );

            if ($address_id) {
                return [
                    'status' => 'success',
                    'message' => 'Address added successfully',
                    'address_id' => $address_id
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to add address'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error adding address: ' . $e->getMessage()
            ];
        }
    }

    public function setDefault($customer_id, $address_id) {
        try {
            // Verify address belongs to customer
            $address = $this->addressModel->getAddress($address_id);
            if (!$address || $address['customer_id'] != $customer_id) {
                return [
                    'status' => 'error',
                    'message' => 'Address not found or access denied'
                ];
            }

            $success = $this->addressModel->setDefaultAddress($customer_id, $address_id);
            
            if ($success) {
                return [
                    'status' => 'success',
                    'message' => 'Default address updated'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to update default address'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error updating address: ' . $e->getMessage()
            ];
        }
    }

    public function getAddress($address_id) {
        try {
            $address = $this->addressModel->getAddress($address_id);
            
            if ($address) {
                return [
                    'status' => 'success',
                    'data' => $address
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Address not found'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error fetching address: ' . $e->getMessage()
            ];
        }
    }
}