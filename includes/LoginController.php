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
        $email      = trim($email);
        $sub_office = trim($sub_office);
        $password   = trim($password);

        if (empty($email) || empty($sub_office) || empty($password)) {
            return "All fields are required.";
        }

        $query = "
            SELECT 
                u.ID, u.first_name, u.last_name, u.email, u.password, 
                u.sub_office, u.department_id, u.designation_id,
                COALESCE(d.designation_name, u.designation) AS designation_name
            FROM wp_pradeshiya_sabha_users u
            LEFT JOIN wp_designations d ON u.designation_id = d.designation_id
            WHERE u.email = ? AND LOWER(TRIM(u.sub_office)) = LOWER(TRIM(?))
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) return "Database error.";

        $stmt->bind_param("ss", $email, $sub_office);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return "Invalid email or sub-office.";
        }

        $user = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($password, $user['password'])) {
            return "Incorrect password.";
        }

        // Store basic user info
        $_SESSION['user'] = [
            'id'            => $user['ID'],
            'email'         => $user['email'],
            'first_name'    => $user['first_name'],
            'last_name'     => $user['last_name'],
            'full_name'     => trim($user['first_name'] . ' ' . $user['last_name']),
            'sub_office'    => $user['sub_office'],
            'department_id' => $user['department_id'] ?? null,
            'designation_id'=> $user['designation_id'],
            'designation'   => trim($user['designation_name'] ?? $user['designation']),
            'show_special_menu' => false   // We'll set this below
        ];

        $designation = strtolower(trim($user['designation_name'] ?? ''));

        // Special flag: only for Head of PS and Head Office Authorized Officer
        if (strpos($designation, 'head of pradeshiya sabha') !== false || 
            strpos($designation, 'head office authorized officer') !== false) {
            $_SESSION['user']['show_special_menu'] = true;
        }

        // ====================== ROUTING ======================
        if ($designation === 'admin') {
            header("Location: ./admin/admin-dashboard.php");
        } 
        elseif (strpos($designation, 'head of department') !== false || 
                strpos($designation, 'hod') !== false) {
            header("Location: ./admin/dashboard.php");
        } 
        elseif (strpos($designation, 'leave officer') !== false) {
            header("Location: ./admin/dashboard.php");
        }
        elseif (strpos($designation, 'head of pradeshiya sabha') !== false || 
                strpos($designation, 'head office authorized officer') !== false) {
            header("Location: ./admin/dashboard.php");
        } 
        elseif (strpos($designation, 'head of suboffice') !== false || 
                strpos($designation, 'suboffice leave officer') !== false) {
            header("Location: /e-Leave-Portal/admin/dashboard.php");
        }
        else {
            header("Location: ./user/user-dashboard.php");
        }
        exit();
    }

    public function logout()
    {
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
}