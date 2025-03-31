<?php
session_start();
require "dbconfig.php";

class LoginController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $sub_office, $password) {
       
        $email = mysqli_real_escape_string($this->conn, $email);
        $sub_office = mysqli_real_escape_string($this->conn, $sub_office);


        // Use LOWER() to make comparison case-insensitive
        $query = "SELECT * FROM wp_pradeshiya_sabha_users WHERE email = ? AND LOWER(sub_office) = LOWER(?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $email, $sub_office);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                
                $_SESSION['user'] = [
                    'id' => $user['ID'],
                    'email' => $user['email'],
                    'sub_office' => $user['sub_office'],
                    'role' => $user['user_role'],
                    'first_name' => $user['first_name'],  
                    'last_name' => $user['last_name'],    
                    'designation' => $user['designation'],  
                ];

                // Redirect based on user role
                if ($user['user_role'] === 'Admin') {
                    header("Location: ./admin/admin-dashboard.php");
                } else {
                    header("Location: ./user/user-dashboard.php");
                }
                exit();
            } else {
                return "Invalid credentials";
            }
        } else {
            return "User does not exist or wrong sub office";
        }
    }

    public function logout() {
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
}
?>
