<?php
require_once(__DIR__ . '/../model/UserModel.php');

class AuthController {
    private $userModel;

    public function __construct($con) {
        $this->userModel = new UserModel($con);
    }

    public function login($email, $password) {
        $user = $this->userModel->findUser($email, $password);

        if ($user) {
            return [
                "status" => "success",
                "role" => $user["role"],
                "id" => $user["id"],
                "name" => $user["first_name"] . " " . $user["last_name"],
                "email" => $user["email"]
            ];
        } else {
            return ["status" => "error", "message" => "Invalid email or password"];
        }
    }
}
