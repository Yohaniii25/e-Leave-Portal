<?php
// create_suboffice_admins.php
// Run this ONCE from browser or CLI: php create_suboffice_admins.php

require_once 'includes/dbconfig.php'; // Adjust path if needed

// Define the 3 sub-office admins
$admins = [
    [
        'username'     => 'makandura-admin',
        'plain_pass'   => 'Makandura@2025',
        'first_name'   => 'Makandura',
        'last_name'    => 'Admin',
        'email'        => 'makandura.admin@pannalaps.lk',
        'sub_office'   => 'Makandura Sub-Office',
        'phone'        => '076-1111111',
        'service_no'   => 'SO-MAK-001'
    ],
    [
        'username'     => 'yakkwila-admin',
        'plain_pass'   => 'Yakkwila@2025',
        'first_name'   => 'Yakkwila',
        'last_name'    => 'Admin',
        'email'        => 'c',
        'sub_office'   => 'Yakkwila Sub-Office',
        'phone'        => '076-2222222',
        'service_no'   => 'SO-YAK-001'
    ],
    [
        'username'     => 'hamangalla-admin',
        'plain_pass'   => 'Hamangalla@2025',
        'first_name'   => 'Hamangalla',
        'last_name'    => 'Admin',
        'email'        => 'hamangalla.admin@pannalaps.lk',
        'sub_office'   => 'Hamangalla Sub-Office',
        'phone'        => '076-3333333',
        'service_no'   => 'SO-HAM-001'
    ]
];

$inserted = 0;
foreach ($admins as $admin) {
    // Check if already exists
    $check = $conn->prepare("SELECT ID FROM wp_pradeshiya_sabha_users WHERE username = ?");
    $check->bind_param("s", $admin['username']);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        echo "Already exists: {$admin['username']}<br>";
        $check->close();
        continue;
    }
    $check->close();

    // Generate proper bcrypt hash
    $hash = password_hash($admin['plain_pass'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO wp_pradeshiya_sabha_users (
        username, password, first_name, last_name, gender, email,
        service_number, phone_number, designation, sub_office,
        date_of_joining, leave_balance, casual_leave_balance, sick_leave_balance,
        department_id, designation_id
    ) VALUES (
        ?, ?, ?, ?, 'Male', ?,
        ?, ?, 'Sub-Office Admin', ?,
        '2025-04-01', 45.0, 21.0, 24.0,
        7, 7
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssss",
        $admin['username'],
        $hash,
        $admin['first_name'],
        $admin['last_name'],
        $admin['email'],
        $admin['service_no'],
        $admin['phone'],
        $admin['sub_office']
    );

    if ($stmt->execute()) {
        echo "Created: {$admin['username']} → Password: {$admin['plain_pass']}<br>";
        $inserted++;
    } else {
        echo "Failed: {$admin['username']}<br>";
    }
    $stmt->close();
}

echo "<hr><strong>Done! Created $inserted sub-office admins.</strong><br>";
echo "You can now login with:<br>";
echo "• makandura-admin / Makandura@2025<br>";
echo "• yakkwila-admin   / Yakkwila@2025<br>";
echo "• hamangalla-admin / Hamangalla@2025<br>";
?>