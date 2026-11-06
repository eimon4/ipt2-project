<?php
require_once __DIR__ . '/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare('
    SELECT i.*, u.name AS user_name, u.email AS user_email 
    FROM incidents i 
    JOIN users u ON u.id = i.user_id 
    WHERE i.id = ?
');
$stmt->bind_param('i', $id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    die('Report not found.');
}

$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View Report - SwiftTrack.</title>
</head>
<body class="bg-gray-50 min-h-screen">
  <header class="bg-blue-900 text-white shadow relative">
    <div class="hidden lg:block absolute left-4 top-1/2 -translate-y-1/2 font-bold text-xl">SwiftTrack.</div>
    <div class="w-full max-w-5xl mx-auto px-4 py-3 flex flex-col sm:flex-row items-center justify-between">
      <div class="lg:hidden font-bold text-xl mb-2 sm:mb-0 sm:mr-4">SwiftTrack.</div>
      <div class="w-full flex items-center justify-between flex-1">
        <div class="font-semibold">Admin: <?= e($user['name']) ?></div>
        <nav class="space-x-4 text-sm">
          <a href="admin.php" class="text-blue-200 hover:text-white">Admin</a>
          <a href="logout.php" class="text-blue-200 hover:text-white">Logout</a>
        </nav>
      </div>
    </div>
  </header>

  <main class="max-w-3xl mx-auto p-4 mt-6">
    <div class="mb-4">
      <a href="admin.php" class="text-blue-600 hover:underline">&larr; Back to Dashboard</a>
    </div>
    
    <div class="bg-white shadow rounded p-8">
      <h1 class="text-2xl font-bold mb-2"><?= e($report['title']) ?></h1>
      
      <div class="text-sm text-gray-500 mb-8 flex space-x-6">
        <span><strong>Reported by:</strong> <?= e($report['user_name']) ?> (<?= e($report['user_email']) ?>)</span>
        <span><strong>Date:</strong> <?= e($report['created_at']) ?></span>
        <span>
          <strong>Status:</strong> 
          <?php 
            $statusColor = 'text-yellow-600';
            if ($report['status'] === 'approved') $statusColor = 'text-green-600';
            if ($report['status'] === 'rejected') $statusColor = 'text-red-600';
          ?>
          <span class=" <?= $statusColor ?>"><?= ucfirst(e($report['status'])) ?></span>
        </span>
      </div>

      <div class="border-t pt-6">
        <h3 class="text-sm text-gray-700 mb-3 uppercase tracking-wider">Description</h3>
        <p class="whitespace-pre-wrap text-gray-800 leading-relaxed"><?= e($report['description']) ?></p>
      </div>
      
      <?php if ($report['status'] === 'pending'): ?>
      <div class="border-t mt-8 pt-6 space-x-4">
        <a class="text-green-600 hover:underline" href="admin.php?action=approve&id=<?= (int)$report['id'] ?>">Approve Report</a>
        <a class="text-red-600 hover:underline" href="admin.php?action=reject&id=<?= (int)$report['id'] ?>">Reject Report</a>
      </div>
      <?php endif; ?>
    </div>
  </main>
  <script src="https://cdn.tailwindcss.com"></script>
</body>
</html>
