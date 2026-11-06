<?php
require_once __DIR__ . '/auth.php';

// Log out
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
