<?php
// This script generates SQL to insert admin users with properly hashed passwords
// Run this script once to generate the SQL, then execute the SQL in your database

// Admin users to create
$admins = [
    ['username' => 'admin1', 'password' => 'admin123', 'email' => 'admin1@sistema.com'],
    ['username' => 'admin2', 'password' => 'secure456', 'email' => 'admin2@sistema.com'],
    ['username' => 'admin3', 'password' => 'manager789', 'email' => 'admin3@sistema.com']
];

// Generate SQL
echo "-- SQL to insert admin users with hashed passwords\n";
echo "INSERT INTO users (username, password, email, role) VALUES\n";

$values = [];
foreach ($admins as $admin) {
    $hashed_password = password_hash($admin['password'], PASSWORD_DEFAULT);
    $values[] = "('{$admin['username']}', '{$hashed_password}', '{$admin['email']}', 'admin')";
}

echo implode(",\n", $values) . ";\n";
echo "\n-- Passwords in plaintext for reference (remove in production):\n";
foreach ($admins as $admin) {
    echo "-- {$admin['username']}: {$admin['password']}\n";
}
?>