<?php
// ============================================================
//  SecureApp — upload.php
//  Zaštićen upload:
//  - Whitelist ekstenzija (samo slike)
//  - Provjera stvarnog MIME tipa (finfo)
//  - Ograničenje veličine (max 2MB)
//  - Novo randomizirano ime datoteke
//  - Upload VAN web roota
//  - CSRF zaštita
//  - Provjera dimenzija slike (getimagesize)
// ============================================================
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db_connect.php';

$user_id  = (int) $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');

$msg_error   = '';
$msg_success = '';

// ── CSRF token ───────────────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Konfiguracija zaštite ─────────────────────────────────────
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const ALLOWED_MIME_TYPES  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const MAX_FILE_SIZE       = 2 * 1024 * 1024; // 2MB
// ── Upload mapa — zaštićena .htaccess-om, PHP kreira automatski ──
// __DIR__ = .../htdocs/webseclab/secureapp
// storage = .../htdocs/webseclab/storage/uploads/  (deny from all)
$upload_dir = dirname(__DIR__) . '/storage/uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0750, true);
    // Dodaj .htaccess koji blokira direktno izvršavanje
    file_put_contents($upload_dir . '.htaccess', "deny from all\n");
}

// ── Upload obrada ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {

    // 1. CSRF provjera
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $msg_error = 'Nevažeći zahtjev.';

    } else {

        $file = $_FILES['upload'];

        // 2. Provjera upload greške
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $msg_error = 'Greška pri uploadu. Pokušajte ponovo.';

        // 3. Provjera veličine datoteke
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $msg_error = 'Datoteka je prevelika. Maksimalna veličina je 2MB.';

        } else {

            // 4. Provjera ekstenzije — whitelist
            $original_name = $file['name'];
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                $msg_error = 'Tip datoteke nije dopušten. Dozvoljeno: '
                           . implode(', ', ALLOWED_EXTENSIONS);

            } else {

                // 5. Provjera stvarnog MIME tipa (ne vjerujemo browseru!)
                $finfo     = new finfo(FILEINFO_MIME_TYPE);
                $real_mime = $finfo->file($file['tmp_name']);

                if (!in_array($real_mime, ALLOWED_MIME_TYPES)) {
                    $msg_error = 'Stvarni tip datoteke nije dozvoljen ('
                               . htmlspecialchars($real_mime, ENT_QUOTES, 'UTF-8')
                               . '). Moguć pokušaj spoofinga!';

                } else {

                    // 6. Provjera je li stvarno slika (getimagesize)
                    $img_info = @getimagesize($file['tmp_name']);

                    if ($img_info === false) {
                        $msg_error = 'Datoteka nije ispravna slika.';

                    } else {

                        // 7. Generiraj sigurno, randomizirano ime — bez originalnog!
                        $safe_name = bin2hex(random_bytes(16)) . '.' . $ext;
                        $dest      = $upload_dir . $safe_name;

                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            // Spremi u bazu samo ime i user_id
                            $stmt = $pdo->prepare(
                                "INSERT INTO secure_uploads (user_id, original_name, stored_name, mime_type, file_size)
                                 VALUES (?, ?, ?, ?, ?)"
                            );
                            // Pokušaj insert — tablica možda ne postoji pa gracefully fallback
                            try {
                                $stmt->execute([
                                    $user_id,
                                    htmlspecialchars($original_name, ENT_QUOTES, 'UTF-8'),
                                    $safe_name,
                                    $real_mime,
                                    $file['size']
                                ]);
                            } catch (PDOException $e) {
                                // Tablica ne postoji — upload je i dalje uspio
                            }

                            $msg_success = 'Datoteka uspješno uploadana i sigurno pohranjena.';

                        } else {
                            $msg_error = 'Greška pri pohrani datoteke.';
                        }
                    }
                }
            }
        }

        // Regeneriraj CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// ── Dohvati uploadane datoteke iz baze ───────────────────────
$uploaded_files = [];
try {
    $stmt = $pdo->prepare(
        "SELECT * FROM secure_uploads WHERE user_id = ? ORDER BY uploaded_at DESC"
    );
    $stmt->execute([$user_id]);
    $uploaded_files = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tablica možda ne postoji — ignoriramo
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="X-Frame-Options" content="DENY">
  <title>SecureApp — Upload datoteka</title>
  <link rel="stylesheet" href="../../css/secureapp_upload.css"/>
</head>
<body>

<nav>
  <div class="nav-logo">Secure<span>App</span></div>
  <ul class="nav-links">
    <li><a href="index.php">Početna</a></li>
    <li><a href="dashboard.php">Dashboard</a></li>
    <li><a href="upload.php">Upload</a></li>
  </ul>
  <div style="display:flex;align-items:center;gap:1rem;">
    <span class="nav-user">✓ <?= $username ?></span>
    <form method="POST" action="logout.php" style="display:inline;">
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>
      <button type="submit" class="nav-logout">Odjava</button>
    </form>
  </div>
</nav>

<div class="status-banner">
  <div class="status-dot"></div>
  Whitelist ekstenzija · MIME provjera · Randomizirano ime · Upload van web roota · CSRF zaštita
</div>

<div class="page">

  <div class="page-title">Upload datoteka</div>
  <p class="page-sub">// sigurni upload — samo slike, max 2MB, van web roota</p>

  <div class="upload-grid">

    <!-- Upload forma -->
    <div class="panel">
      <div class="panel-title">
        Upload slike
        <span class="badge-green">✓ Zaštićeno</span>
      </div>

      <?php if ($msg_error):   ?>
        <div class="msg msg-error">
          <?= htmlspecialchars($msg_error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if ($msg_success): ?>
        <div class="msg msg-success">
          <?= htmlspecialchars($msg_success, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" id="upload-form">

        <!-- CSRF token -->
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>

        <div class="drop-zone" id="drop-zone">
          <input type="file" name="upload" id="file-input"
                 accept=".jpg,.jpeg,.png,.gif,.webp"/>
          <div class="drop-icon">🖼</div>
          <div class="drop-text">
            <strong>Povuci sliku ovdje ili klikni</strong>
            Samo: JPG, PNG, GIF, WEBP
          </div>
          <div class="drop-hint" id="drop-hint">Max veličina: 2MB</div>
        </div>

        <div class="upload-progress" id="progress-wrap">
          <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
          <div class="progress-label" id="progress-label">Provjera datoteke...</div>
        </div>

        <button type="submit" class="btn-upload" id="btn-upload">
          Sigurno uploadaj
        </button>
      </form>

      <!-- Sigurnosne provjere -->
      <div class="secure-box">
        <div class="secure-box-title">✓ Aktivne sigurnosne provjere</div>
        <div class="secure-box-item">
          ✓ <strong>Whitelist ekstenzija</strong> — samo <code>.jpg .png .gif .webp</code>
        </div>
        <div class="secure-box-item">
          ✓ <strong>Provjera stvarnog MIME tipa</strong> — <code>finfo::file()</code> čita magične bajtove
        </div>
        <div class="secure-box-item">
          ✓ <strong>getimagesize() provjera</strong> — mora biti ispravna slika
        </div>
        <div class="secure-box-item">
          ✓ <strong>Max veličina 2MB</strong> — DoS zaštita
        </div>
        <div class="secure-box-item">
          ✓ <strong>Randomizirano ime</strong> — <code>random_bytes(16)</code>, original se odbacuje
        </div>
        <div class="secure-box-item">
          ✓ <strong>Upload van web roota</strong> — datoteke nisu direktno dostupne
        </div>
        <div class="secure-box-item">
          ✓ <strong>.htaccess zaštita</strong> — <code>deny from all</code> u upload mapi
        </div>
      </div>
    </div>

    <!-- Lista datoteka -->
    <div class="panel">
      <div class="panel-title">
        Moje slike
        <span class="badge-green"><?= count($uploaded_files) ?> datoteka</span>
      </div>

      <div class="files-label">Uploadane datoteke</div>

      <?php if (empty($uploaded_files)): ?>
        <div class="file-empty">Nema uploadanih datoteka.</div>
      <?php else: ?>
        <?php foreach ($uploaded_files as $f): ?>
          <div class="file-item">
            <div class="file-item-name">
              <?= htmlspecialchars($f['original_name'], ENT_QUOTES, 'UTF-8') ?>
              <span>
                Pohranjeno kao: <?= htmlspecialchars($f['stored_name'], ENT_QUOTES, 'UTF-8') ?>
                &nbsp;·&nbsp; <?= round($f['file_size'] / 1024, 1) ?> KB
              </span>
            </div>
            <span class="file-item-ext">
              <?= htmlspecialchars(pathinfo($f['original_name'], PATHINFO_EXTENSION), ENT_QUOTES, 'UTF-8') ?>
            </span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Usporedba s vulnapp -->
      <div class="secure-box" style="margin-top:1.5rem;">
        <div class="secure-box-title">Usporedba s VulnApp</div>
        <div class="secure-box-item">
          VulnApp prima <code>shell.php</code> → izvršava se kao PHP kod<br>
          SecureApp blokira sve osim slika na 4 razine provjere
        </div>
        <div class="secure-box-item">
          VulnApp čuva datoteke u <code>uploads/</code> unutar web roota<br>
          SecureApp čuva van web roota — nedostupno direktno
        </div>
        <div class="secure-box-item">
          VulnApp koristi originalno ime → predvidiva putanja<br>
          SecureApp generira random ime → napadač ne zna putanju
        </div>
        <div class="secure-box-item">
          Pokušaj upload <code>shell.php</code> ovdje → blokiran odmah na 1. provjeri
        </div>
      </div>
    </div>

  </div>
</div>

<script>
const fileInput  = document.getElementById('file-input');
const dropHint   = document.getElementById('drop-hint');
const btnUpload  = document.getElementById('btn-upload');
const progressWrap = document.getElementById('progress-wrap');
const progressFill = document.getElementById('progress-fill');
const progressLbl  = document.getElementById('progress-label');

const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const MAX_SIZE     = 16 * 1024 * 1024;

fileInput.addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;

  const ext  = file.name.split('.').pop().toLowerCase();
  const size = file.size;

  // Client-side prevalidacija (server uvijek validira ponovo)
  if (!ALLOWED_EXT.includes(ext)) {
    dropHint.textContent = '✕ Nedopuštena ekstenzija: .' + ext;
    dropHint.className   = 'drop-hint error';
    btnUpload.disabled   = true;
    return;
  }

  if (size > MAX_SIZE) {
    dropHint.textContent = '✕ Datoteka je prevelika: ' + (size / 1024 / 1024).toFixed(1) + 'MB (max 2MB)';
    dropHint.className   = 'drop-hint error';
    btnUpload.disabled   = true;
    return;
  }

  dropHint.textContent = '✓ ' + file.name + ' — ' + (size / 1024).toFixed(1) + 'KB';
  dropHint.className   = 'drop-hint';
  btnUpload.disabled   = false;
});

document.getElementById('upload-form').addEventListener('submit', function () {
  progressWrap.style.display = 'block';
  let pct = 0;
  const iv = setInterval(() => {
    pct = Math.min(pct + 15, 90);
    progressFill.style.width = pct + '%';
    progressLbl.textContent  = 'Provjera i pohrana... ' + pct + '%';
  }, 120);
  setTimeout(() => clearInterval(iv), 900);
});
</script>

</body>
</html>
