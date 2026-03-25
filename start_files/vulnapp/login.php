<?php
// ============================================================
//  VulnApp — login.php
//  NAMJERNO RANJIVA prijava — za edukativne svrhe
//  Ranjivosti: SQL injection, bez CSRF, plaintext usporedba,
//  bez rate limitinga, bez session regeneracije
// ============================================================
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'db_connect.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username']; // RANJIVOST: nema sanitizacije
    $password = $_POST['password']; // RANJIVOST: nema sanitizacije

    // RANJIVOST: SQL Injection — korisnički unos direktno u upit
    // Napad: username = ' OR '1'='1' -- 
    $sql    = "SELECT * FROM vuln_users WHERE username = '$username' AND password = '$password'";
    $result = $pdo->query($sql);
    $user   = $result->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // RANJIVOST: nema session_regenerate_id()
        // RANJIVOST: pohranjujemo plaintext lozinku u session
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['password'] = $user['password']; // nikad ne raditi ovo!

        header('Location: dashboard.php');
        exit;
    } else {
        // RANJIVOST: otkrivamo postoji li korisnik
        $check = $pdo->query("SELECT id FROM vuln_users WHERE username = '$username'");
        if ($check->fetch()) {
            $error = 'Pogrešna lozinka za korisnika: ' . $username;
        } else {
            $error = 'Korisnik "' . $username . '" ne postoji.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>VulnApp — Prijava</title>
    <link rel="stylesheet" href="../styles/normalize.css" />
    <link rel="stylesheet" href="../../css/vulnapp_login.css"/>
</head>
<body>

<div class="page-wrap">

  <!-- Lijevi panel -->
  <div class="left-panel">
    <div class="left-top">
      <div class="brand-logo">Vuln<span>App</span></div>
      <div class="brand-tag">// ranjiva aplikacija</div>
    </div>

    <div class="left-middle">
      <h2 class="left-tagline">
        PRIJAVA<br>
        <em>bez</em><br>
        ZAŠTITE
      </h2>
    </div>

    <div class="left-bottom">
      <ul class="vuln-list">
        <li class="vuln-item">SQL injection u login formi</li>
        <li class="vuln-item">Nema CSRF tokena</li>
        <li class="vuln-item">Plaintext lozinka u bazi</li>
        <li class="vuln-item">Nema rate limitinga</li>
        <li class="vuln-item">Otkriva postojanje korisnika</li>
      </ul>
    </div>
  </div>

  <!-- Desni panel — forma -->
  <div class="right-panel">

    <div class="form-header">
      <div class="warn-badge">⚠ Nezaštićeno</div>
      <h1 class="form-title">PRIJAVA</h1>
      <p class="form-sub">
        Forma bez ikakve zaštite.<br>
        Ranjiva na SQL injection i brute force.
      </p>
    </div>

    <?php if ($error): ?>
      <!-- RANJIVOST: prikazujemo $error bez escapiranja — XSS mogući -->
      <div class="msg msg-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="msg msg-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- RANJIVOST: nema CSRF tokena u formi -->
    <form method="POST" action="login.php">

      <div class="form-group">
        <label class="form-label" for="username">Korisničko ime</label>
        <!-- RANJIVOST: nema maxlength, nema validacije -->
        <input
          class="form-input"
          type="text"
          id="username"
          name="username"
          placeholder="Unesite korisničko ime"
          value="<?= $_POST['username'] ?? '' ?>"
        />
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Lozinka</label>
        <input
          class="form-input"
          type="password"
          id="password"
          name="password"
          placeholder="Unesite lozinku"
        />
        <p class="input-hint">Nema ograničenja pokušaja prijave.</p>
      </div>

      <button type="submit" class="btn-submit">Prijava</button>

    </form>

    <!-- SQL hint za demonstraciju -->
    <div class="sql-hint">
      Probajte SQL injection u polje "Korisničko ime":
      <code>' OR '1'='1' -- </code>
      <code>' OR 1=1 -- </code>
    </div>

    <div class="form-footer">
      Nemate račun? <a href="register.php">Registrirajte se</a>
    </div>

  </div>
</div>

</body>
</html>