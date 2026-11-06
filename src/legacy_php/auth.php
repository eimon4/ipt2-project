<?php
session_start();
require_once __DIR__ . '/db.php';

// Returns the currently logged in user, or null if not logged in
function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    return [
        'id'   => (int)$_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['role'],
    ];
}

// Redirects to the login page if the user is not logged in
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

// Blocks access if the user is not an admin
function require_admin(): void {
    require_login();
    
    if (($_SESSION['role'] ?? 'user') !== 'admin') {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}
