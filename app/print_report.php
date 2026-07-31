<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM records ORDER BY id ASC");
$records = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>System Summary Report</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, -apple-system, sans-serif; padding: 2rem; color: #000; background: #fff; }
    .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 1rem; margin-bottom: 1.5rem; }
    .logo-container { display: flex; align-items: center; gap: 12px; }
    .logo { height: 48px; width: auto; object-fit: contain; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
    th { background-color: #f8f9fa; font-weight: bold; }
    .avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
    .no-print-btn { background: #6366f1; color: white; border: none; padding: 10px 18px; cursor: pointer; border-radius: 6px; font-weight: bold; margin-bottom: 1rem; }
    
    @media print {
      .no-print-btn { display: none !important; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

  <button type="button" class="no-print-btn" onclick="window.print()">Print Document</button>
  
  <div class="header">
    <div class="logo-container">
      <img src="assets/logo.png" class="logo" alt="Logo" onerror="this.style.display='none'">
      <div>
        <h2>System Audit & Summary Report</h2>
        <p>Generated: <?= date('F j, Y, g:i a') ?></p>
      </div>
    </div>
    <div>
      <p><strong>Total Records:</strong> <?= count($records) ?></p>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Photo</th>
        <th>Code</th>
        <th>Name</th>
        <th>Role</th>
        <th>Status</th>
        <th>Last Updated</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($records)): ?>
        <tr><td colspan="6" style="text-align:center;">No records found.</td></tr>
      <?php else: ?>
        <?php foreach ($records as $row): ?>
          <?php $imgPath = (!empty($row['image']) && file_exists('uploads/' . $row['image'])) ? 'uploads/' . $row['image'] : 'https://via.placeholder.com/32?text=U'; ?>
          <tr>
            <td><img src="<?= $imgPath ?>" class="avatar" alt="Avatar"></td>
            <td><strong><?= htmlspecialchars($row['record_code']) ?></strong></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['role']) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= htmlspecialchars($row['updated_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <script>
    // Reliable auto-print trigger once page DOM is ready
    document.addEventListener("DOMContentLoaded", function() {
      setTimeout(function() {
        window.print();
      }, 300);
    });
  </script>
</body>
</html>