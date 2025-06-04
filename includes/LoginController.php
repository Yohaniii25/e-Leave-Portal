<?php
session_start();
require "dbconfig.php";

class LoginController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function login($email, $sub_office, $password)
    {
        $email = mysqli_real_escape_string($this->conn, $email);
        $sub_office = mysqli_real_escape_string($this->conn, $sub_office);

        $query = "
        SELECT u.ID, u.first_name, u.last_name, u.email, u.password, 
               u.sub_office, u.department_id, d.designation_id, d.designation_name
        FROM wp_pradeshiya_sabha_users u
        LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
        WHERE u.email = ? AND LOWER(u.sub_office) = LOWER(?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

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
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'designation_id' => $user['designation_id'],
                    'designation' => trim($user['designation_name']),
                    'department_id' => $user['department_id'] ?? null,
                ];

                $designation = strtolower(trim($user['designation_name']));

                if ($designation === 'admin') {
                    header("Location: ./admin/admin-dashboard.php");
                } elseif ($designation === 'employee') {
                    header("Location: ./user/user-dashboard.php");
                } else {
                    header("Location: ./admin/dashboard.php");
                }
                exit();
            } else {
                return "Invalid credentials";
            }
        } else {
            return "User does not exist or wrong sub office";
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
}
