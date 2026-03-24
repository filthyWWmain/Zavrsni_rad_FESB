<?php
// ============================================================
//  SecureApp — register.php
//  Zaštićena registracija: CSRF, PDO, bcrypt, server validacija,
//  sanitizacija unosa, provjera snage lozinke
// ============================================================
session_start();

if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Ako je već prijavljen, preusmjeri
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../db_connect.php';

$errors  = [];
$success = '';
$old     = []; // čuvamo stare vrijednosti za refill forme

// ── Generiraj CSRF token ─────────────────────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Obrada POST zahtjeva ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. CSRF provjera
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = 'Nevažeći zahtjev. Osvježite stranicu i pokušajte ponovo.';
    } else {

        // 2. Sanitizacija unosa
        $username  = trim(htmlspecialchars($_POST['username']  ?? '', ENT_QUOTES, 'UTF-8'));
        $email     = trim(htmlspecialchars($_POST['email']     ?? '', ENT_QUOTES, 'UTF-8'));
        $password  = $_POST['password']  ?? '';
        $password2 = $_POST['password2'] ?? '';

        // Čuvamo za refill (bez lozinke!)
        $old = ['username' => $username, 'email' => $email];

        // 3. Server-side validacija

        // Korisničko ime
        if (empty($username)) {
            $errors[] = 'Korisničko ime je obavezno.';
        } elseif (strlen($username) < 3 || strlen($username) > 30) {
            $errors[] = 'Korisničko ime mora imati između 3 i 30 znakova.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Korisničko ime smije sadržavati samo slova, brojeve i podvlaku (_).';
        }

        // Email
        if (empty($email)) {
            $errors[] = 'Email adresa je obavezna.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Unesite ispravnu email adresu.';
        } elseif (strlen($email) > 150) {
            $errors[] = 'Email adresa je predugačka.';
        }

        // Lozinka
        if (empty($password)) {
            $errors[] = 'Lozinka je obavezna.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Lozinka mora imati najmanje 8 znakova.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Lozinka mora sadržavati najmanje jedno veliko slovo.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Lozinka mora sadržavati najmanje jedan broj.';
        }

        // Potvrda lozinke
        if ($password !== $password2) {
            $errors[] = 'Lozinke se ne podudaraju.';
        }

        // 4. Provjeri jedinstvenost u bazi (PDO prepared statement)
        if (empty($errors)) {

            $chk = $pdo->prepare(
                "SELECT id FROM secure_users
                 WHERE username = ? OR email = ?
                 LIMIT 1"
            );
            $chk->execute([$username, $email]);

            if ($chk->fetch()) {
                $errors[] = 'Korisničko ime ili email adresa su već zauzeti.';
            }
        }

        // 5. Sve prošlo — registriraj korisnika
        if (empty($errors)) {

            // Hashiraj lozinku bcrypt algoritmom
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            $ins = $pdo->prepare(
                "INSERT INTO secure_users (username, password_hash, email)
                 VALUES (?, ?, ?)"
            );
            $ins->execute([$username, $hash, $email]);

            // Regeneriraj CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            $success = 'Registracija uspješna! Možete se prijaviti.';
            $old = []; // počisti stare vrijednosti
        }

        // Regeneriraj CSRF token nakon svakog POST-a
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="X-Content-Type-Options" content="nosniff">
  <meta http-equiv="X-Frame-Options" content="DENY">
  <title>SecureApp — Registracija</title>
  <link rel="stylesheet" href="../../css/normalize.css"/>
  <link rel="stylesheet" href="../../css/secureapp_register.css"/>
</head>
<body>

<div class="page-wrap">

  <!-- ── Lijevi panel ── -->
  <div class="left-panel">
    <div class="left-top">
      <div class="brand-logo">Secure<span>App</span></div>
      <div class="brand-tag">// zaštićena aplikacija</div>
    </div>

    <div class="left-middle">
      <h2 class="left-tagline">
        Novi<br>
        <em>korisnički</em><br>
        račun
      </h2>
    </div>

    <div class="left-bottom">
      <!-- Zahtjevi za lozinku — ažuriraju se JS-om -->
      <ul class="requirements-list" id="req-list">
        <li class="req-item" id="req-len">
          <div class="req-dot"></div> Najmanje 8 znakova
        </li>
        <li class="req-item" id="req-upper">
          <div class="req-dot"></div> Jedno veliko slovo
        </li>
        <li class="req-item" id="req-num">
          <div class="req-dot"></div> Jedan broj
        </li>
        <li class="req-item" id="req-match">
          <div class="req-dot"></div> Lozinke se podudaraju
        </li>
      </ul>
    </div>
  </div>

  <!-- ── Desni panel — forma ── -->
  <div class="right-panel">

    <div class="form-header">
      <div class="shield-icon">🛡</div>
      <h1 class="form-title">Registracija</h1>
      <p class="form-sub">Izradite novi račun. Svi podaci su sigurno pohranjeni.</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="msg msg-error">
        <?php if (count($errors) === 1): ?>
          <?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?>
        <?php else: ?>
          Molimo ispravite sljedeće greške:
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="msg msg-success">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        <br><a href="login.php" style="color:inherit;font-weight:500;">→ Idi na prijavu</a>
      </div>
    <?php endif; ?>

    <form class="register-form" method="POST" action="register.php" autocomplete="off" novalidate>

      <!-- CSRF token -->
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="username">Korisničko ime</label>
          <input
            class="form-input"
            type="text"
            id="username"
            name="username"
            maxlength="30"
            autocomplete="off"
            placeholder="npr. marko_95"
            value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          />
          <p class="input-hint" id="hint-username">Slova, brojevi i _ (3–30 znakova)</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email adresa</label>
          <input
            class="form-input"
            type="email"
            id="email"
            name="email"
            maxlength="150"
            autocomplete="off"
            placeholder="npr. marko@email.com"
            value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
          />
          <p class="input-hint" id="hint-email"></p>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Lozinka</label>
        <input
          class="form-input"
          type="password"
          id="password"
          name="password"
          maxlength="128"
          autocomplete="new-password"
          placeholder="Minimalno 8 znakova"
        />
        <!-- Snaga lozinke — live indikator -->
        <div class="strength-wrap">
          <div class="strength-bar">
            <div class="strength-seg" id="seg1"></div>
            <div class="strength-seg" id="seg2"></div>
            <div class="strength-seg" id="seg3"></div>
            <div class="strength-seg" id="seg4"></div>
          </div>
          <span class="strength-label" id="strength-label">Unesite lozinku</span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password2">Potvrda lozinke</label>
        <input
          class="form-input"
          type="password"
          id="password2"
          name="password2"
          maxlength="128"
          autocomplete="new-password"
          placeholder="Ponovite lozinku"
        />
        <p class="input-hint" id="hint-match"></p>
      </div>

      <button type="submit" class="btn-submit">Registriraj se</button>

    </form>

    <div class="form-footer">
      Već imate račun? <a href="login.php">Prijavite se</a>
    </div>

  </div>
</div>

<script>
// ── Live validacija i snaga lozinke ────────────────────────
const passInput  = document.getElementById('password');
const pass2Input = document.getElementById('password2');
const segs       = [document.getElementById('seg1'), document.getElementById('seg2'),
                    document.getElementById('seg3'), document.getElementById('seg4')];
const strengthLbl = document.getElementById('strength-label');
const hintMatch   = document.getElementById('hint-match');

const reqLen   = document.getElementById('req-len');
const reqUpper = document.getElementById('req-upper');
const reqNum   = document.getElementById('req-num');
const reqMatch = document.getElementById('req-match');

function calcStrength(p) {
  let score = 0;
  if (p.length >= 8)  score++;
  if (p.length >= 12) score++;
  if (/[A-Z]/.test(p) && /[0-9]/.test(p)) score++;
  if (/[^a-zA-Z0-9]/.test(p)) score++;
  return score;
}

const labels = ['', 'Slaba', 'Osrednja', 'Dobra', 'Jaka'];
const cls    = ['', 'w', 'f', 'g', 's'];

passInput.addEventListener('input', () => {
  const p = passInput.value;
  const score = p.length === 0 ? 0 : Math.max(1, calcStrength(p));

  segs.forEach((s, i) => {
    s.className = 'strength-seg' + (i < score ? ' ' + cls[score] : '');
  });
  strengthLbl.textContent = p.length === 0 ? 'Unesite lozinku' : labels[score];

  // Ažuriraj zahtjeve na lijevoj strani
  reqLen.classList.toggle('met',   p.length >= 8);
  reqUpper.classList.toggle('met', /[A-Z]/.test(p));
  reqNum.classList.toggle('met',   /[0-9]/.test(p));

  checkMatch();
});

pass2Input.addEventListener('input', checkMatch);

function checkMatch() {
  const p1 = passInput.value;
  const p2 = pass2Input.value;
  if (p2.length === 0) {
    hintMatch.textContent = '';
    hintMatch.className = 'input-hint';
    reqMatch.classList.remove('met');
    return;
  }
  if (p1 === p2) {
    hintMatch.textContent = '✓ Lozinke se podudaraju';
    hintMatch.className = 'input-hint hint-success';
    reqMatch.classList.add('met');
  } else {
    hintMatch.textContent = '✕ Lozinke se ne podudaraju';
    hintMatch.className = 'input-hint hint-error';
    reqMatch.classList.remove('met');
  }
}

// ── Live validacija emaila ───────────────────────────────────
const emailInput = document.getElementById('email');
const hintEmail  = document.getElementById('hint-email');

emailInput.addEventListener('blur', () => {
  const v = emailInput.value.trim();
  if (v.length === 0) { hintEmail.textContent = ''; return; }
  const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  hintEmail.textContent = valid ? '✓ Ispravna email adresa' : '✕ Neispravna email adresa';
  hintEmail.className   = 'input-hint ' + (valid ? 'hint-success' : 'hint-error');
  emailInput.className  = 'form-input ' + (valid ? 'input-valid' : 'input-invalid');
});

// ── Live validacija korisničkog imena ───────────────────────
const usernameInput = document.getElementById('username');
const hintUsername  = document.getElementById('hint-username');

usernameInput.addEventListener('input', () => {
  const v = usernameInput.value;
  if (v.length === 0) {
    hintUsername.textContent = 'Slova, brojevi i _ (3–30 znakova)';
    hintUsername.className = 'input-hint';
    return;
  }
  const valid = /^[a-zA-Z0-9_]{3,30}$/.test(v);
  hintUsername.textContent = valid ? '✓ Ispravno korisničko ime' : '✕ Samo slova, brojevi i _ (3–30 zn.)';
  hintUsername.className   = 'input-hint ' + (valid ? 'hint-success' : 'hint-error');
  usernameInput.className  = 'form-input ' + (valid ? 'input-valid' : 'input-invalid');
});
</script>

</body>
</html>
