<?php
require_once __DIR__ . '/auth.php';

$error = '';

// Handle user login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required';
    } else {
        $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            // Verify password matches the hash in the database
            if (password_verify($password, $row['password_hash'])) {
                // Set session variables to log the user in
                $_SESSION['user_id'] = (int)$row['id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['role'] = $row['role'];
                
                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: report.php');
                }
                exit;
            }
        }
        $error = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SwiftTrack - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <header class="absolute top-0 left-0 w-full bg-blue-900 text-white shadow">
    <div class="w-full px-4 py-3 flex items-center justify-between">
      <div class="font-bold text-xl">SwiftTrack.</div>
    </div>
  </header>
  
  <div class="w-full max-w-md bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
    
    <?php if ($error): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700"><?= e($error) ?></div>
    <?php endif; ?>
    
    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input name="email" type="email" class="w-full border rounded px-3 py-2" required />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Password</label>
        <input name="password" type="password" class="w-full border rounded px-3 py-2" required />
      </div>
      <button class="w-full bg-blue-900 hover:bg-blue-800 text-white rounded px-3 py-2">Sign In</button>
    </form>
    
    <p class="mt-4 text-sm text-center">
      No account? <a class="text-blue-600 hover:underline" href="register.php">Register</a>
    </p>
  </div>
</body>
</html>
