<?php
session_start();
include "dbconfig.php";

class LoginController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $sub_office, $password) {
        $email = mysqli_real_escape_string($this->conn, $email);
        $sub_office = mysqli_real_escape_string($this->conn, $sub_office);

        $query = "SELECT * FROM wp_pradeshiya_sabha_users WHERE email = ? AND sub_office = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $email, $sub_office);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Store session variables
                $_SESSION['user'] = [
                    'id' => $user['ID'],
                    'email' => $user['email'],
                    'sub_office' => $user['sub_office'],
                    'role' => $user['user_role'],
                    'first_name' => $user['first_name'],  // Add first name to session
                    'last_name' => $user['last_name'],    // Add last name to session
                    'designation' => $user['designation'],  // Add designation to session
                ];

                // Redirect based on user role
                if ($user['user_role'] === 'Admin') {
                    header("Location: ./admin/admin-dashboard.php");
                } else {
                    header("Location: ./employee/user-dashboard.php");
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
