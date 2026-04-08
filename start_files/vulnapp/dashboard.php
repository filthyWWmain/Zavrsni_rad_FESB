<?php
// ============================================================
//  VulnApp — dashboard.php
//  NAMJERNO RANJIVO:
//  - XSS: korisnički unos se ispisuje bez escapiranja
//  - SQL injection: nema prepared statements
//  - Nema CSRF zaštite na formama
//  - Nema provjere vlasništva bilješki (IDOR)
//  - Session podaci se ispisuju direktno
// ============================================================
session_start();

// Minimalna provjera prijave — nema provjere valjanosti sesije
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connect.php';

// RANJIVO: direktno iz session bez sanitizacije
$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

$msg_error   = '';
$msg_success = '';

// ── Dodaj bilješku ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_note') {
    // RANJIVO: nema CSRF provjere, nema sanitizacije
    $content = $_POST['content'];

    // RANJIVO: SQL injection moguć
    $sql = "INSERT INTO vuln_notes (user_id, content, title) VALUES ($user_id, '$content', 'Bilješka')";

    if (mysqli_query($conn, $sql)) {
        $msg_success = 'Bilješka dodana!';
    } else {
        // RANJIVO: otkriva SQL grešku
        $msg_error = 'Greška: ' . mysqli_error($conn);
    }
}

// ── Briši bilješku ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_note') {
    $note_id = $_POST['note_id'];

    // RANJIVO: IDOR — nema provjere pripada li bilješka korisniku
    // RANJIVO: SQL injection u note_id
    $sql = "DELETE FROM vuln_notes WHERE id = $note_id";
    mysqli_query($conn, $sql);
    $msg_success = 'Bilješka obrisana.';
}

// ── Dohvati bilješke ──────────────────────────────────────────
// RANJIVO: nema prepared statements
$sql   = "SELECT * FROM vuln_notes WHERE user_id = $user_id ORDER BY created_at DESC";
$notes = mysqli_query($conn, $sql);

// ── Dohvati podatke korisnika ─────────────────────────────────
$sql_user = "SELECT * FROM vuln_users WHERE id = $user_id";
$res_user = mysqli_query($conn, $sql_user);
$user     = mysqli_fetch_assoc($res_user);

// Broj bilješki
$count_sql = "SELECT COUNT(*) as cnt FROM vuln_notes WHERE user_id = $user_id";
$count_res = mysqli_query($conn, $count_sql);
$note_count = mysqli_fetch_assoc($count_res)['cnt'];
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VulnApp — Dashboard</title>
  <link rel="stylesheet" href="../../css/vulnapp_dashboard.css"/>
</head>
<body>

<nav>
  <div class="nav-logo">Vuln<span>App</span></div>
  <ul class="nav-links">
    <li><a href="index.php">Početna</a></li>
    <li><a href="upload.php">Upload</a></li>
    <li><a href="dashboard.php">Dashboard</a></li>
  </ul>
  <div style="display:flex;align-items:center;gap:1rem;">
    <!-- RANJIVO: username bez escapiranja -->
    <span class="nav-user">// <?= $username ?></span>
    <a href="logout.php" class="nav-logout">Odjava</a>
  </div>
</nav>

<div class="alert-banner">
  <div class="alert-dot"></div>
  Ranjiva aplikacija — XSS, IDOR, SQL Injection demonstracija
</div>

<div class="page">

  <!-- Welcome -->
  <div class="welcome-bar">
    <div>
      <!-- RANJIVO: username ispisan bez htmlspecialchars — XSS -->
      <div class="welcome-title">DOBRODOŠLI, <span><?= $username ?></span></div>
      <div class="welcome-sub">// korisnički panel — bez zaštite</div>
    </div>
    <div class="session-info">
      <div>Session ID: <strong><?= session_id() ?></strong></div>
      <div>User ID: <strong><?= $user_id ?></strong></div>
      <div>Prijava: <strong><?= date('H:i:s') ?></strong></div>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat">
      <div class="stat-num"><?= $note_count ?></div>
      <div class="stat-label">Bilješke</div>
    </div>
    <div class="stat">
      <div class="stat-num red">0</div>
      <div class="stat-label">CSRF zaštita</div>
    </div>
    <div class="stat">
      <div class="stat-num red">0</div>
      <div class="stat-label">XSS zaštita</div>
    </div>
    <div class="stat">
      <div class="stat-num amber">plaintext</div>
      <div class="stat-label">Lozinka u bazi</div>
    </div>
  </div>

  <!-- Main grid -->
  <div class="main-grid">

    <!-- Bilješke -->
    <div class="panel">
      <div class="panel-title">
        Moje bilješke
        <span>// XSS ranjivo</span>
      </div>

      <?php if ($msg_error):   ?><div class="msg msg-error"><?= $msg_error ?></div><?php endif; ?>
      <?php if ($msg_success): ?><div class="msg msg-success"><?= $msg_success ?></div><?php endif; ?>

      <!-- RANJIVO: nema CSRF tokena -->
      <form class="note-form" method="POST">
        <input type="hidden" name="action" value="add_note"/>
        <textarea
          name="content"
          placeholder="Unesite bilješku... (pokušajte: <script>alert('XSS')</script>)"
        ></textarea>
        <button type="submit" class="btn-add">+ Dodaj bilješku</button>
      </form>

      <div class="notes-list">
        <?php if (mysqli_num_rows($notes) === 0): ?>
          <div class="note-empty">Nema bilješki. Dodajte prvu!</div>
        <?php else: ?>
          <?php while ($note = mysqli_fetch_assoc($notes)): ?>
            <div class="note-item">
              <!-- RANJIVO: direktan ispis bez escapiranja — XSS se izvršava -->
              <div class="vuln-note-content"><?= $note['content'] ?></div>
              <div class="note-meta"><?= $note['created_at'] ?> &nbsp;·&nbsp; ID: <?= $note['id'] ?></div>
              <!-- RANJIVO: nema CSRF + nema provjere vlasništva (IDOR) -->
              <form method="POST" style="display:inline;margin-top:.4rem;">
                <input type="hidden" name="action" value="delete_note"/>
                <input type="hidden" name="note_id" value="<?= $note['id'] ?>"/>
                <button type="submit" style="background:transparent;border:none;color:#555;font-size:.65rem;cursor:pointer;font-family:var(--mono);letter-spacing:.06em;">
                  [obriši]
                </button>
              </form>
            </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Profil -->
    <div class="panel">
      <div class="panel-title">Korisnički profil <span>// podaci iz baze</span></div>

      <div class="profile-section">
        <div class="profile-row">
          <span class="profile-key">ID</span>
          <!-- RANJIVO: direktan ispis -->
          <span class="profile-val"><?= $user['id'] ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Korisničko ime</span>
          <span class="profile-val"><?= $user['username'] ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Email</span>
          <span class="profile-val"><?= $user['email'] ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Lozinka (plaintext!)</span>
          <!-- RANJIVO: prikazuje plaintext lozinku -->
          <span class="profile-val red"><?= $user['password'] ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Registriran</span>
          <span class="profile-val"><?= $user['created_at'] ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Session ID</span>
          <span class="profile-val amber"><?= session_id() ?></span>
        </div>
      </div>

      <div class="vuln-warning">
        <strong>⚠ OTKRIVENE RANJIVOSTI</strong>
        Lozinka je vidljiva u plaintextu. Session ID je izložen u HTML-u.
        Korisnički unos u bilješkama nije sanitiziran — pokušajte XSS payload.
        Brisanje bilješki nema CSRF zaštitu niti provjeru vlasništva (IDOR).
      </div>
    </div>

  </div>
</div>

</body>
</html>
