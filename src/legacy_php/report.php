<?php
require_once __DIR__ . '/auth.php';
require_login();

$user = current_user();
$info = '';
$error = '';

// Handle form submission to create a new incident report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if ($title === '' || $description === '') {
        $error = 'All fields are required';
    } else {
        $stmt = db()->prepare('INSERT INTO incidents(user_id, title, description) VALUES(?, ?, ?)');
        $stmt->bind_param('iss', $user['id'], $title, $description);
        
        if ($stmt->execute()) {
            $info = 'Report submitted. Status: pending';
        } else {
            $error = 'Failed to submit report';
        }
    }
}

// Fetch the current user's incidents
$stmt = db()->prepare('SELECT id, title, status, created_at FROM incidents WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$myIncidents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SwiftTrack - Report</title>
</head>
<body class="bg-gray-50 min-h-screen">
  <header class="bg-blue-900 text-white shadow relative">
    <div class="hidden lg:block absolute left-4 top-1/2 -translate-y-1/2 font-bold text-xl">SwiftTrack.</div>
    <div class="w-full max-w-5xl mx-auto px-4 py-3 flex flex-col sm:flex-row items-center justify-between">
      <div class="lg:hidden font-bold text-xl mb-2 sm:mb-0 sm:mr-4">SwiftTrack.</div>
      <div class="w-full flex items-center justify-between flex-1">
        <div class="font-semibold">Hello, <?= e($user['name']) ?></div>
        <nav class="space-x-4 text-sm">
          <a href="report.php" class="text-blue-200 hover:text-white">Report</a>
          <?php if (($user['role'] ?? 'user') === 'admin'): ?>
            <a href="admin.php" class="text-blue-200 hover:text-white">Admin</a>
          <?php endif; ?>
          <a href="logout.php" class="text-blue-200 hover:text-white">Logout</a>
        </nav>
      </div>
    </div>
  </header>

  <main class="max-w-5xl mx-auto p-4 mt-10">
    <div class="grid md:grid-cols-2 gap-6">
      <section class="bg-white shadow rounded p-6">
        <h2 class="text-xl font-semibold mb-4">Submit Incident</h2>
        
        <?php if ($info): ?>
          <div class="mb-4 p-3 rounded bg-green-100 text-green-700"><?= e($info) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
          <div class="mb-4 p-3 rounded bg-red-100 text-red-700"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="post" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input name="title" type="text" class="w-full border rounded px-3 py-2" required />
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="5" class="w-full border rounded px-3 py-2" required></textarea>
          </div>
          <button class="bg-blue-900 hover:bg-blue-800 text-white rounded px-3 py-2">Submit</button>
        </form>
      </section>

      <section class="bg-white shadow rounded p-6">
        <h2 class="text-xl font-semibold mb-4">My Reports</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b">
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Created</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($myIncidents as $r): ?>
              <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3"><?= (int)$r['id'] ?></td>
                <td class="px-4 py-3"><?= e($r['title']) ?></td>
                <td class="px-4 py-3">
                  <?php 
                    $statusColor = 'text-yellow-600';
                    if ($r['status'] === 'approved') $statusColor = 'text-green-600';
                    if ($r['status'] === 'rejected') $statusColor = 'text-red-600';
                  ?>
                  <span class=" <?= $statusColor ?>">
                    <?= ucfirst(e($r['status'])) ?>
                  </span>
                </td>
                <td class="px-4 py-3"><?= e($r['created_at']) ?></td>
              </tr>
              <?php endforeach; ?>
              
              <?php if (!$myIncidents): ?>
              <tr>
                <td colspan="4" class="px-4 py-3 text-center text-gray-500">No reports yet.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
  <script src="https://cdn.tailwindcss.com"></script>
</body>
</html>
