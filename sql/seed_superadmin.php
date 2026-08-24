<?php
// One-off seeder for the initial Super Admin account. Safe to re-run (skips if username exists).
$mysqli = new mysqli('localhost', 'root', 'root', 'multi_purpose_profiling_db');
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Connect failed: {$mysqli->connect_error}\n");
    exit(1);
}

$username = 'superadmin';
$email = 'superadmin@example.com';
$password = 'ChangeMe@123';

$stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "Super admin already exists, skipping.\n";
    exit(0);
}
$stmt->close();

$role = $mysqli->query("SELECT id FROM roles WHERE name = 'super_admin'")->fetch_assoc();
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare('INSERT INTO users (role_id, username, email, password, first_name, last_name, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())');
$firstName = 'System';
$lastName = 'Administrator';
$stmt->bind_param('isssss', $role['id'], $username, $email, $hash, $firstName, $lastName);
$stmt->execute();

echo "Super admin created.\n";
echo "Username: $username\n";
echo "Password: $password\n";
