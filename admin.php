<?php
// Lists saved registrations. Protected with HTTP Basic auth.
// Set ADMIN_PASSWORD as an environment variable (username: admin).
require_once __DIR__ . "/db.php";

$pass = getenv("ADMIN_PASSWORD");
if (!$pass) {
    http_response_code(503);
    exit("Admin page disabled: set the ADMIN_PASSWORD environment variable.");
}

$u = $_SERVER["PHP_AUTH_USER"] ?? "";
$p = $_SERVER["PHP_AUTH_PW"] ?? "";
if ($u !== "admin" || !hash_equals($pass, $p)) {
    header('WWW-Authenticate: Basic realm="Admin"');
    http_response_code(401);
    exit("Authentication required.");
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, "UTF-8"); }

$pdo = db();
$rows = $pdo ? $pdo->query("SELECT * FROM registrations ORDER BY id DESC")->fetchAll() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrations</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .container.wide { max-width: 1100px; }
    .scroll { overflow-x: auto; }
    .result-table th { width: auto; }
  </style>
</head>
<body>
  <main class="container wide">
    <section class="card">
      <h1>Registrations</h1>
      <?php if ($rows === null): ?>
        <p>No database connected (DATABASE_URL is not set).</p>
      <?php else: ?>
        <p class="subtitle"><?= count($rows) ?> total</p>
        <div class="scroll">
          <table class="result-table">
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Gender</th><th>Course</th><th>Interests</th><th>Address</th><th>Registered</th></tr>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= e($r["id"]) ?></td>
                <td><?= e($r["fullname"]) ?></td>
                <td><?= e($r["email"]) ?></td>
                <td><?= e($r["phone"]) ?></td>
                <td><?= e($r["dob"]) ?></td>
                <td><?= e($r["gender"]) ?></td>
                <td><?= e($r["course"]) ?></td>
                <td><?= e($r["interests"]) ?></td>
                <td><?= nl2br(e($r["address"])) ?></td>
                <td><?= e($r["created_at"]) ?></td>
              </tr>
            <?php endforeach; ?>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
