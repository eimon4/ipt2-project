<?php
require_once __DIR__ . '/auth.php';
require_admin();

$user = current_user();
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Handle approve or reject actions
if ($id > 0 && ($action === 'approve' || $action === 'reject')) {
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = db()->prepare('UPDATE incidents SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    
    header('Location: admin.php');
    exit;
}

// Fetch all incidents along with the user's name and email
$sql = 'SELECT i.id, i.title, i.description, i.status, i.created_at, u.name AS user_name, u.email AS user_email 
        FROM incidents i 
        JOIN users u ON u.id = i.user_id 
        ORDER BY i.id DESC';
$res = db()->query($sql);
$rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Incident Report System</title>
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

  <main class="max-w-5xl mx-auto p-4 mt-10">
    <h1 class="text-2xl font-bold mb-4">All Incidents</h1>
    <div class="overflow-x-auto bg-white shadow rounded">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="px-4 py-3">ID</th>
            <th class="px-4 py-3">Title</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">User</th>
            <th class="px-4 py-3">Email</th>
            <th class="px-4 py-3">Created</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-3"><?= (int)$r['id'] ?></td>
            <td class="px-4 py-3">
              <a href="view_report.php?id=<?= (int)$r['id'] ?>" class="text-gray-800 font-medium hover:text-blue-600 hover:underline">
                <?= e($r['title']) ?>
              </a>
            </td>
            <td class="px-4 py-3">
              <?php 
                $statusColor = 'text-yellow-600';
                if ($r['status'] === 'approved') $statusColor = 'text-green-600';
                if ($r['status'] === 'rejected') $statusColor = 'text-red-600';
              ?>
              <span class="<?= $statusColor ?>">
                <?= ucfirst(e($r['status'])) ?>
              </span>
            </td>
            <td class="px-4 py-3"><?= e($r['user_name']) ?></td>
            <td class="px-4 py-3"><?= e($r['user_email']) ?></td>
            <td class="px-4 py-3"><?= e($r['created_at']) ?></td>
            <td class="px-4 py-3 space-x-2">
              <?php if ($r['status'] === 'pending'): ?>
                <a class="text-green-600 hover:underline" href="admin.php?action=approve&id=<?= (int)$r['id'] ?>">Approve</a>
                <a class="text-red-600 hover:underline" href="admin.php?action=reject&id=<?= (int)$r['id'] ?>">Reject</a>
              <?php else: ?>
                <span class="text-gray-400 italic">Resolved</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          
          <?php if (!$rows): ?>
          <tr>
            <td colspan="7" class="px-4 py-3 text-center text-gray-500">No incidents yet.</td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
  <script src="https://cdn.tailwindcss.com"></script>
</body>
</html>
