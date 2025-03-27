<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wp_leave_requests";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Admin user details
$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$admin_first_name = "Admin";
$admin_last_name = "User";
$admin_email = "yohanii725@gmail.com";
$admin_phone_number = "0778439871";
$admin_designation = "Administrator";
$admin_department = "Administration";
$admin_sub_office = "Head Office"; // Sub office for the admin
$admin_date_of_joining = "2023-01-01";
$admin_role = "Admin";

// SQL query to insert admin user into wp_users table
$sql = "INSERT INTO wp_pradeshiya_sabha_users (username, password, first_name, last_name, email, phone_number, designation, department, sub_office, date_of_joining, user_role)
        VALUES ('$admin_username', '$admin_password', '$admin_first_name', '$admin_last_name', '$admin_email', '$admin_phone_number', '$admin_designation', '$admin_department', '$admin_sub_office', '$admin_date_of_joining', '$admin_role')";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Admin user added successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the connection
$conn->close();
?>
