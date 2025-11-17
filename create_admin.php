<?php
$users = [
    ['username' => 'basnayaka',     'password' => 'JeWXkDX06z'],
    ['username' => 'dangolla',      'password' => 'FBorDl2sBh'],
    ['username' => 'karunarathna',  'password' => 'Wpx4C7IScL'],
];

foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    echo "-- {$u['username']}\n";
    echo "INSERT INTO wp_pradeshiya_sabha_users (username, password, first_name, last_name, gender, email, service_number, phone_number, designation, sub_office, date_of_joining, created_at, updated_at, leave_balance, duty_leave_count, casual_leave_balance, sick_leave_balance, department_id, designation_id) VALUES ('{$u['username']}', '$hash', ";
    
    if ($u['username'] === 'basnayaka') {
        echo "'B.M.R.N.', 'Basnayaka', 'Male', 'basnayaka@pannalaps.lk', '40', '712821259', ";
    } elseif ($u['username'] === 'dangolla') {
        echo "'P.D.M.G.', 'Dangolla', 'Male', 'dangolla@pannalaps.lk', '42', '776379009', ";
    } elseif ($u['username'] === 'karunarathna') {
        echo "'K.K.U.D.', 'Karunarathna', 'Male', 'karunarathna@pannalaps.lk', '36', '767923968', ";
    }
    
    echo "'Employee', 'Head Office', '2025-04-21', NOW(), NOW(), 45.0, 0, 21.0, 24.0, 4, 2);\n\n";
}
?>