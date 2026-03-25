<?php
// ============================================================
//  SecureApp — login.php
//  Zaštićena prijava: CSRF token, PDO, bcrypt, rate limiting,
//  session regeneration, bez izlaganja detalja greške
// ============================================================
session_start();

// Regeneriraj session ID ako nije inicijaliziran
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// Ako je već prijavljen, preusmjeri na dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Konekcija na bazu (PDO) ──────────────────────────────────
require_once 'db_connect.php';

$error   = '';
$success = '';

// ── Generiraj CSRF token ako ne postoji ─────────────────────
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
        $error = 'Nevažeći zahtjev. Pokušajte ponovo.';
    } else {

        // 2. Dohvati i sanitiziraj unos
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3. Osnovna validacija — ne otkrivamo što je krivo
        if (empty($username) || empty($password)) {
            $error = 'Unesite korisničko ime i lozinku.';
        } else {

            // 4. Dohvati korisnika iz baze — PDO prepared statement
            $stmt = $pdo->prepare(
                "SELECT id, username, password_hash, login_attempts, locked_until
                 FROM secure_users
                 WHERE username = ?
                 LIMIT 1"
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 5. Provjeri je li račun zaključan (rate limiting)
            if ($user && $user['locked_until'] && new DateTime() < new DateTime($user['locked_until'])) {
                $remaining = (new DateTime($user['locked_until']))->diff(new DateTime());
                $error = 'Račun je privremeno zaključan. Pokušajte za ' . $remaining->i . ' min ' . $remaining->s . ' s.';

            // 6. Provjeri lozinku — password_verify() s bcrypt hashom
            } elseif ($user && password_verify($password, $user['password_hash'])) {

                // Uspješna prijava — resetiraj pokušaje
                $reset = $pdo->prepare(
                    "UPDATE secure_users SET login_attempts = 0, locked_until = NULL WHERE id = ?"
                );
                $reset->execute([$user['id']]);

                // Regeneriraj session ID (sprječava session fixation)
                session_regenerate_id(true);

                // Postavi session varijable
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // novi CSRF token

                header('Location: dashboard.php');
                exit;

            } else {
                // Neuspješna prijava — povećaj brojač pokušaja
                if ($user) {
                    $attempts = $user['login_attempts'] + 1;
                    $locked   = null;

                    // Nakon 5 neuspješnih pokušaja — zaključaj 15 minuta
                    if ($attempts >= 5) {
                        $locked   = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');
                        $attempts = 0;
                    }

                    $upd = $pdo->prepare(
                        "UPDATE secure_users SET login_attempts = ?, locked_until = ? WHERE id = ?"
                    );
                    $upd->execute([$attempts, $locked, $user['id']]);
                }

                // Generička poruka — ne otkrivamo postoji li korisnik
                $error = 'Neispravno korisničko ime ili lozinka.';
            }
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
  <title>SecureApp — Prijava</title>
  <link rel="stylesheet" href="../../normalize.css"/>
  <link rel="stylesheet" href="../../css/secureapp_login.css"/>
</head>
<body>

<div class="page-wrap">

  <!-- ── Lijevi panel — branding ── -->
  <div class="left-panel">
    <div class="left-top">
      <div class="brand-logo">Secure<span>App</span></div>
      <div class="brand-tag">// zaštićena aplikacija</div>
    </div>

    <div class="left-middle">
      <h2 class="left-tagline">
        Sigurna<br>
        <em>prijava</em><br>
        korisnika
      </h2>
    </div>

    <div class="left-bottom">
      <div class="security-chips">
        <div class="chip"><div class="chip-dot"></div> CSRF token zaštita</div>
        <div class="chip"><div class="chip-dot"></div> PDO Prepared Statements</div>
        <div class="chip"><div class="chip-dot"></div> bcrypt verifikacija</div>
        <div class="chip"><div class="chip-dot"></div> Rate limiting — 5 pokušaja</div>
        <div class="chip"><div class="chip-dot"></div> Session regeneration</div>
      </div>
    </div>
  </div>

  <!-- ── Desni panel — forma ── -->
  <div class="right-panel">

    <div class="form-header">
      <div class="lock-icon">🔐</div>
      <h1 class="form-title">Prijava</h1>
      <p class="form-sub">
        Unesite podatke za pristup korisničkom panelu.
      </p>
    </div>

    <?php if ($error): ?>
      <div class="msg msg-error">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="msg msg-success">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="login.php" autocomplete="off">

      <!-- CSRF token — skriveno polje -->
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"/>

      <div class="form-group">
        <label class="form-label" for="username">Korisničko ime</label>
        <input
          class="form-input"
          type="text"
          id="username"
          name="username"
          maxlength="100"
          autocomplete="username"
          placeholder="Unesite korisničko ime"
          value="<?= isset($_POST['username'])
            ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8')
            : '' ?>"
          required
        />
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Lozinka</label>
        <input
          class="form-input"
          type="password"
          id="password"
          name="password"
          maxlength="128"
          autocomplete="current-password"
          placeholder="Unesite lozinku"
          required
        />
        <p class="input-hint">Račun se zaključava nakon 5 neuspješnih pokušaja.</p>
      </div>

      <button type="submit" class="btn-submit">Prijava</button>

    </form>

    <div class="form-footer">
      Nemate račun? <a href="register.php">Registrirajte se</a>
    </div>

  </div>
</div>

</body>
</html>
