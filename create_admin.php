<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pannalaps_leave";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Users to be inserted
$users = [
    [
        "username" => "sub-office-admin",
        "password" => password_hash("sub123", PASSWORD_DEFAULT),
        "first_name" => "Sub Office",
        "last_name" => "Admin",
        "email" => "suboffice@example.com",
        "phone_number" => "0711111111",
        "designation" => "Sub-Office Admin",
        "department" => "Management",
        "sub_office" => "Head Office",
        "date_of_joining" => "2023-01-01",
        "role" => "Admin"
    ],
    [
        "username" => "makandura",
        "password" => password_hash("makandura123", PASSWORD_DEFAULT),
        "first_name" => "Makandura",
        "last_name" => "User",
        "email" => "makandura@example.com",
        "phone_number" => "0722222222",
        "designation" => "Officer",
        "department" => "Operations",
        "sub_office" => "Makandura Sub-Office",
        "date_of_joining" => "2022-06-15",
        "role" => "Employee"
    ],
    [
        "username" => "yakkwila",
        "password" => password_hash("yakkwila123", PASSWORD_DEFAULT),
        "first_name" => "Yakkwila",
        "last_name" => "User",
        "email" => "yakkwila@example.com",
        "phone_number" => "0733333333",
        "designation" => "Supervisor",
        "department" => "IT",
        "sub_office" => "Yakkwila Sub-Office",
        "date_of_joining" => "2021-03-10",
        "role" => "Employee"
    ],
    [
        "username" => "hamangalla",
        "password" => password_hash("hamangalla123", PASSWORD_DEFAULT),
        "first_name" => "Hamangalla",
        "last_name" => "User",
        "email" => "hamangalla@example.com",
        "phone_number" => "0744444444",
        "designation" => "Manager",
        "department" => "Finance",
        "sub_office" => "Hamangalla Sub-Office",
        "date_of_joining" => "2020-11-20",
        "role" => "Employee"
    ]
];

// Prepare the SQL statement
$check_query = "SELECT ID FROM wp_pradeshiya_sabha_users WHERE email = ?";
$insert_query = "INSERT INTO wp_pradeshiya_sabha_users (username, password, first_name, last_name, email, phone_number, designation, department, sub_office, date_of_joining, user_role) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$check_stmt = $conn->prepare($check_query);
$insert_stmt = $conn->prepare($insert_query);

foreach ($users as $user) {
    // Check if the user already exists
    $check_stmt->bind_param("s", $user["email"]);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows == 0) {
        // Insert the new user
        $insert_stmt->bind_param(
            "sssssssssss",
            $user["username"],
            $user["password"],
            $user["first_name"],
            $user["last_name"],
            $user["email"],
            $user["phone_number"],
            $user["designation"],
            $user["department"],
            $user["sub_office"],
            $user["date_of_joining"],
            $user["role"]
        );

        if ($insert_stmt->execute()) {
            echo "User " . $user["username"] . " added successfully!<br>";
        } else {
            echo "Error adding " . $user["username"] . ": " . $insert_stmt->error . "<br>";
        }
    } else {
        echo "User with email " . $user["email"] . " already exists!<br>";
    }
}

// Close statements and connection
$check_stmt->close();
$insert_stmt->close();
$conn->close();
?>
