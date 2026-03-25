<?php
// ============================================================
//  VulnApp — register.php
//  NAMJERNO RANJIVA registracija — za edukativne svrhe
//  Ranjivosti: bez CSRF, plaintext lozinka, bez validacije,
//  bez sanitizacije, XSS u error poruci
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

    // RANJIVOST: nema sanitizacije ni validacije unosa
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // RANJIVOST: nema provjere jesu li polja prazna
    // RANJIVOST: nema provjere formata emaila
    // RANJIVOST: nema provjere snage lozinke

    // RANJIVOST: SQL Injection u INSERT upitu
    $sql = "INSERT INTO vuln_users (username, password, email)
            VALUES ('$username', '$password', '$email')";

    if (mysqli_query($conn, $sql)) {
        // RANJIVOST: prikazujemo korisničke podatke bez escapiranja — XSS
        $success = 'Registracija uspješna! Dobrodošli, ' . $username . '!';
    } else {
        // RANJIVOST: prikazujemo SQL grešku korisniku
        $error = 'Greška: ' . mysqli_error($conn) . '<br>SQL: ' . $sql;
    }
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VulnApp — Registracija</title>
  <LINK REL="STYLESHEET" HREF="../../css/normalize.css"/>
  <link rel="stylesheet" href="../../css/vulnapp_register.css"/>
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
        NOVI<br>
        <em>račun</em><br>
        KORISNIKA
      </h2>
    </div>

    <div class="left-bottom">
      <ul class="vuln-list">
        <li class="vuln-item">SQL injection u INSERT upitu</li>
        <li class="vuln-item">Lozinka u plaintextu</li>
        <li class="vuln-item">Nema validacije unosa</li>
        <li class="vuln-item">XSS u success poruci</li>
        <li class="vuln-item">SQL greška vidljiva korisniku</li>
      </ul>
    </div>
  </div>

  <!-- Desni panel -->
  <div class="right-panel">

    <div class="form-header">
      <div class="warn-badge">⚠ Nezaštićeno</div>
      <h1 class="form-title">REGISTRACIJA</h1>
      <p class="form-sub">
        Forma bez validacije i zaštite.<br>
        Lozinka se sprema kao plaintext.
      </p>
    </div>

    <?php if ($error): ?>
      <!-- RANJIVOST: $error se prikazuje bez htmlspecialchars -->
      <div class="msg msg-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <!-- RANJIVOST: $success sadrži korisnički unos bez escapiranja — XSS -->
      <div class="msg msg-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- RANJIVOST: nema CSRF tokena -->
    <form method="POST" action="register.php">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="username">Korisničko ime</label>
          <!-- RANJIVOST: nema maxlength, nema validacije -->
          <input
            class="form-input"
            type="text"
            id="username"
            name="username"
            placeholder="Korisničko ime"
            value="<?= $_POST['username'] ?? '' ?>"
          />
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input
            class="form-input"
            type="text"
            id="email"
            name="email"
            placeholder="Email adresa"
            value="<?= $_POST['email'] ?? '' ?>"
          />
          <!-- RANJIVOST: type="text" umjesto type="email" — nema browser validacije -->
        </div>
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
        <!-- RANJIVOST: nema zahtjeva za snagom lozinke -->
        <p class="input-hint">Nema zahtjeva za lozinkom — može biti i "a".</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="password2">Potvrda lozinke</label>
        <input
          class="form-input"
          type="password"
          id="password2"
          name="password2"
          placeholder="Ponovite lozinku"
        />
        <!-- RANJIVOST: polja se ne uspoređuju na serveru -->
        <p class="input-hint">Podudaranje lozinki se ne provjerava.</p>
      </div>

      <button type="submit" class="btn-submit">Registriraj se</button>

    </form>

    <!-- XSS hint za demonstraciju -->
    <div class="xss-hint">
      Probajte XSS u polju "Korisničko ime":
      <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code>
      <code>&lt;img src=x onerror=alert(1)&gt;</code>
    </div>

    <div class="form-footer">
      Već imate račun? <a href="login.php">Prijavite se</a>
    </div>

  </div>
</div>

</body>
</html>
