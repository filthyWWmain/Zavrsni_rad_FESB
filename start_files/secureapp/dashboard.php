<?php
// ============================================================
//  SecureApp — dashboard.php
//  Zaštićen dashboard: CSRF, PDO, htmlspecialchars,
//  provjera vlasništva (anti-IDOR), session validacija
// ============================================================
session_start();

// Stroga provjera prijave
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connect.php';

// Sanitizirani podaci iz sesije
$user_id  = (int) $_SESSION['user_id']; // cast na int — sprječava injection
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

$msg_error   = '';
$msg_success = '';

// ── CSRF token ───────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Dodaj bilješku ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_note') {

    // CSRF provjera
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $msg_error = 'Nevažeći zahtjev.';
    } else {
        // Sanitizacija i validacija unosa
        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            $msg_error = 'Sadržaj bilješke ne može biti prazan.';
        } elseif (strlen($content) > 1000) {
            $msg_error = 'Bilješka ne smije biti dulja od 1000 znakova.';
        } else {
            // PDO prepared statement — nema SQL injection
            $stmt = $pdo->prepare(
                "INSERT INTO secure_notes (user_id, title, content) VALUES (?, 'Bilješka', ?)"
            );
            $stmt->execute([$user_id, $content]);
            $msg_success = 'Bilješka uspješno dodana.';
        }

        // Regeneriraj CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// ── Briši bilješku ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_note') {

    // CSRF provjera
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $msg_error = 'Nevažeći zahtjev.';
    } else {
        $note_id = (int) ($_POST['note_id'] ?? 0);

        if ($note_id > 0) {
            // Anti-IDOR: WHERE user_id = ? osigurava da korisnik može brisati SAMO svoje bilješke
            $stmt = $pdo->prepare(
                "DELETE FROM secure_notes WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$note_id, $user_id]);

            if ($stmt->rowCount() > 0) {
                $msg_success = 'Bilješka obrisana.';
            } else {
                $msg_error = 'Bilješka nije pronađena ili nemate pravo brisanja.';
            }
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// ── Dohvati bilješke (PDO) ────────────────────────────────────
$stmt  = $pdo->prepare("SELECT * FROM secure_notes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();

// ── Dohvati podatke korisnika (PDO) ──────────────────────────
$stmt_user = $pdo->prepare("SELECT id, username, email, created_at FROM secure_users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

$note_count = count($notes);
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="X-Frame-Options" content="DENY">
  <title>SecureApp — Dashboard</title>
  <link rel="stylesheet" href="../../css/secureapp_dashboard.css"/>
</head>
<body>

<nav>
  <div class="nav-logo">Secure<span>App</span></div>
  <ul class="nav-links">
    <li><a href="index.php">Početna</a></li>
    <li><a href="upload.php">Upload</a></li>
    <li><a href="dashboard.php">Dashboard</a></li>
  </ul>
  <div style="display:flex;align-items:center;gap:1rem;">
    <!-- Siguran ispis: htmlspecialchars -->
    <span class="nav-user">✓ <?= $username ?></span>
    <!-- Sigurna odjava: POST forma s CSRF tokenom -->
    <form method="POST" action="logout.php" style="display:inline;">
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>
      <button type="submit" class="nav-logout">Odjava</button>
    </form>
  </div>
</nav>

<div class="status-banner">
  <div class="status-dot"></div>
  CSRF zaštita aktivna &nbsp;·&nbsp; PDO prepared statements &nbsp;·&nbsp; XSS sanitizacija &nbsp;·&nbsp; IDOR zaštita
</div>

<div class="page">

  <!-- Welcome -->
  <div class="welcome-bar">
    <div>
      <div class="welcome-title">Dobrodošli, <span><?= $username ?></span></div>
      <div class="welcome-sub">// zaštićeni korisnički panel</div>
    </div>
    <div class="session-badge">
      <span class="badge-secure">✓ Sesija aktivna</span>
      <span style="font-size:.62rem;color:var(--muted);">Session ID nije izložen</span>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat">
      <div class="stat-num"><?= $note_count ?></div>
      <div class="stat-label">Bilješke</div>
    </div>
    <div class="stat">
      <div class="stat-num">✓</div>
      <div class="stat-label">CSRF zaštita</div>
    </div>
    <div class="stat">
      <div class="stat-num">✓</div>
      <div class="stat-label">XSS zaštita</div>
    </div>
    <div class="stat">
      <div class="stat-num muted">bcrypt</div>
      <div class="stat-label">Lozinka hashirana</div>
    </div>
  </div>

  <!-- Main grid -->
  <div class="main-grid">

    <!-- Bilješke -->
    <div class="panel">
      <div class="panel-title">
        Moje bilješke
        <span class="badge-green">✓ XSS zaštićeno</span>
      </div>

      <?php if ($msg_error):   ?><div class="msg msg-error"><?= htmlspecialchars($msg_error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($msg_success): ?><div class="msg msg-success"><?= htmlspecialchars($msg_success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <form class="note-form" method="POST">
        <input type="hidden" name="action" value="add_note"/>
        <!-- CSRF token na svakoj formi -->
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>
        <textarea
          name="content"
          maxlength="1000"
          placeholder="Unesite bilješku... (max 1000 znakova)"
        ></textarea>
        <button type="submit" class="btn-add">+ Dodaj bilješku</button>
      </form>

      <div class="notes-list">
        <?php if (empty($notes)): ?>
          <div class="note-empty">Nema bilješki. Dodajte prvu!</div>
        <?php else: ?>
          <?php foreach ($notes as $note): ?>
            <div class="note-item">
              <!-- Siguran ispis: htmlspecialchars štiti od XSS -->
              <div class="note-content">
                <?= htmlspecialchars($note['content'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <div class="note-meta">
                <?= htmlspecialchars($note['created_at'], ENT_QUOTES, 'UTF-8') ?>
              </div>
              <!-- Anti-IDOR: server provjerava pripada li bilješka korisniku -->
              <form method="POST" style="display:inline;">
                <input type="hidden" name="action" value="delete_note"/>
                <input type="hidden" name="note_id"
                       value="<?= htmlspecialchars($note['id'], ENT_QUOTES, 'UTF-8') ?>"/>
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>
                <button type="submit" class="btn-delete">[obriši]</button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Profil -->
    <div class="panel">
      <div class="panel-title">
        Korisnički profil
        <span class="badge-green">✓ Zaštićeno</span>
      </div>

      <div>
        <div class="profile-row">
          <span class="profile-key">Korisničko ime</span>
          <span class="profile-val teal">
            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Email</span>
          <span class="profile-val">
            <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Lozinka</span>
          <span class="profile-val teal">••••••••••• (bcrypt)</span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Registriran</span>
          <span class="profile-val">
            <?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8') ?>
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-key">Session ID</span>
          <span class="profile-val teal">Nije izložen u HTML-u</span>
        </div>
      </div>

      <div class="security-status">
        <div class="sec-status-title">Aktivni mehanizmi zaštite</div>
        <div class="sec-check">
          ✓ htmlspecialchars() na svim ispisima<br>
          ✓ PDO prepared statements (anti SQL injection)<br>
          ✓ CSRF token na svakoj formi<br>
          ✓ Anti-IDOR: provjera vlasništva bilješki<br>
          ✓ Lozinka nikad nije prikazana<br>
          ✓ Session ID nije izložen u HTML-u
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
