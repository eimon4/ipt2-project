<?php
require_once __DIR__ . '/auth.php';

$error = '';

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        // Determine role: the very first user registered becomes an admin
        $countRes = db()->query('SELECT COUNT(*) AS total FROM users');
        $row = $countRes->fetch_assoc();
        
        if ($row['total'] == 0) {
            $role = 'admin';
        } else {
            $role = 'user';
        }

        // Check if email already exists in the database
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        
        if ($exists) {
            $error = 'Email is already registered';
        } else {
            // Hash the password securely and insert the new user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO users(name, email, password_hash, role) VALUES(?, ?, ?, ?)');
            $stmt->bind_param('ssss', $name, $email, $hash, $role);
            
            if ($stmt->execute()) {
                header('Location: index.php');
                exit;
            } else {
                $error = 'Failed to register';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SwiftTrack - Register</title>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <header class="absolute top-0 left-0 w-full bg-blue-900 text-white shadow">
    <div class="w-full px-4 py-3 flex items-center justify-between">
      <div class="font-bold text-xl">SwiftTrack.</div>
    </div>
  </header>
  
  <div class="w-full max-w-md bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>
    
    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700"><?= e($error) ?></div>
    <?php endif; ?>
    
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Full Name</label>
        <input name="name" type="text" class="w-full border rounded px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input name="email" type="email" class="w-full border rounded px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Password</label>
        <input name="password" type="password" class="w-full border rounded px-3 py-2" required />
      </div>
      <button class="w-full bg-blue-900 hover:bg-blue-800 text-white rounded px-3 py-2">Create Account</button>
    </form>
    
    <p class="mt-4 text-sm text-center">
      Already have an account? <a class="text-blue-600 hover:underline" href="index.php">Login</a>
    </p>
  </div>
  <script src="https://cdn.tailwindcss.com"></script>
</body>
</html>
