<?php
session_start();

// Sigurno regeneriranje session ID-a ako nije postavljeno
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

$logged_in = isset($_SESSION['user_id']);
// htmlspecialchars štiti od XSS-a pri ispisu korisničkih podataka
$username = $logged_in ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <!-- Content Security Policy header -->
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' https://fonts.googleapis.com 'unsafe-inline'; font-src https://fonts.gstatic.com; script-src 'self'">
  <title>SecureApp — Dobrodošli</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../styles/normalize.css" />
    <link rel="stylesheet" href="../../css/secureapp_index.css" />
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">Secure<span>App</span></div>
  <ul class="nav-links">
    <li><a href="index.php">Početna</a></li>
    <li><a href="login.php">Prijava</a></li>
    <li><a href="register.php">Registracija</a></li>
    <?php if ($logged_in): ?>
    <li><a href="dashboard.php">Panel</a></li>
    <li><a href="logout.php">Odjava</a></li>
    <?php endif; ?>
  </ul>
  <?php if ($logged_in): ?>
    <span class="nav-user">✓ <?= $username ?></span>
  <?php else: ?>
    <span class="nav-badge">✓ Zaštićeno</span>
  <?php endif; ?>
</nav>

<!-- STATUS BANNER -->
<div class="status-banner">
  <div class="status-dot"></div>
  <span>Svi sigurnosni mehanizmi aktivni — PDO, bcrypt, CSRF zaštita, validacija unosa</span>
</div>

<!-- HERO -->
<section class="hero">
  <div class="hero-left">
    <p class="hero-tag">// secureapp — zaštićena aplikacija</p>
    <h1>Sustav s<br><em>potpunom</em><br>zaštitom</h1>
    <p class="hero-desc">
      Ista funkcionalnost kao ranjiva verzija, ali s<br>
      implementiranim sigurnosnim standardima.<br>
      Prijavite se ili registrirajte za pristup panelu.
    </p>
    <div class="hero-btns">
      <?php if ($logged_in): ?>
        <a href="dashboard.php" class="btn btn-teal">Otvori panel</a>
        <a href="logout.php" class="btn btn-outline">Odjava</a>
      <?php else: ?>
        <a href="login.php"    class="btn btn-teal">Prijava</a>
        <a href="register.php" class="btn btn-outline">Registracija</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="hero-right">
    <div class="sec-card">
      <div class="sec-card-title">Aktivni mehanizmi zaštite</div>

      <div class="sec-check">
        <div class="check-icon">✓</div>
        <div>
          <div class="check-text">PDO Prepared Statements</div>
          <div class="check-sub">Sprječava SQL injection napade</div>
        </div>
      </div>

      <div class="sec-check">
        <div class="check-icon">✓</div>
        <div>
          <div class="check-text">bcrypt hashiranje lozinki</div>
          <div class="check-sub">password_hash() s BCRYPT algoritmom</div>
        </div>
      </div>

      <div class="sec-check">
        <div class="check-icon">✓</div>
        <div>
          <div class="check-text">XSS zaštita</div>
          <div class="check-sub">htmlspecialchars() na svim ispisima</div>
        </div>
      </div>

      <div class="sec-check">
        <div class="check-icon">✓</div>
        <div>
          <div class="check-text">CSRF Tokeni</div>
          <div class="check-sub">Token na svakoj formi, provjera na backendu</div>
        </div>
      </div>

      <div class="sec-check">
        <div class="check-icon">✓</div>
        <div>
          <div class="check-text">Session regeneration</div>
          <div class="check-sub">Novi ID nakon svake prijave</div>
        </div>
      </div>

      <div class="sec-check">
        <div class="check-icon">✓</div>
        <div>
          <div class="check-text">Validacija unosa</div>
          <div class="check-sub">Server-side provjera svih polja</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features-section">
  <div class="section-label">Implementirani sigurnosni mehanizmi</div>
  <div class="features-grid">

    <div class="feature-card">
      <div class="feature-icon">🛡</div>
      <div class="feature-name">SQL Injection zaštita</div>
      <p class="feature-desc">Svi upiti koriste PDO prepared statements. Korisnički unos nikad nije dio SQL stringa.</p>
      <div class="feature-code">$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");</div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">🔐</div>
      <div class="feature-name">Sigurno hashiranje</div>
      <p class="feature-desc">Lozinke se nikad ne pohranjuju kao plaintext. Koristi se bcrypt s automatskim saltanjem.</p>
      <div class="feature-code">password_hash($pass, PASSWORD_BCRYPT);</div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">✏</div>
      <div class="feature-name">XSS prevencija</div>
      <p class="feature-desc">Svaki ispis korisničkih podataka prolazi kroz htmlspecialchars() prije renderiranja.</p>
      <div class="feature-code">htmlspecialchars($val, ENT_QUOTES, 'UTF-8');</div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">🔑</div>
      <div class="feature-name">CSRF tokeni</div>
      <p class="feature-desc">Svaka forma sadrži jedinstven CSRF token koji se provjerava pri submitanju.</p>
      <div class="feature-code">bin2hex(random_bytes(32));</div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">🔄</div>
      <div class="feature-name">Session sigurnost</div>
      <p class="feature-desc">Session ID se regenerira nakon prijave. HttpOnly i Secure zastavice su postavljene.</p>
      <div class="feature-code">session_regenerate_id(true);</div>
    </div>

    <div class="feature-card">
      <div class="feature-icon">✅</div>
      <div class="feature-name">Input validacija</div>
      <p class="feature-desc">Svi korisnički unosi se validiraju i sanitiziraju na server strani prije obrade.</p>
      <div class="feature-code">filter_var($email, FILTER_VALIDATE_EMAIL);</div>
    </div>

  </div>
</section>

<!-- QUICK ACCESS -->
<section class="quick-section">
  <div class="section-label">Brzi pristup</div>
  <div class="quick-grid">

    <a href="login.php" class="quick-card">
      <div class="quick-num">01</div>
      <div class="quick-title">Prijava</div>
      <p class="quick-sub">Sigurna forma za prijavu. SQL injection i brute force su blokirani.</p>
      <div class="quick-arrow">→ login.php</div>
    </a>

    <a href="register.php" class="quick-card">
      <div class="quick-num">02</div>
      <div class="quick-title">Registracija</div>
      <p class="quick-sub">Registracija s validacijom. Lozinka se hashira bcrypt algoritmom.</p>
      <div class="quick-arrow">→ register.php</div>
    </a>

    <a href="dashboard.php" class="quick-card">
      <div class="quick-num">03</div>
      <div class="quick-title">Korisnički panel</div>
      <p class="quick-sub">Zaštićen panel. Svi ispisi sanitizirani, CSRF tokeni na svim formama.</p>
      <div class="quick-arrow">→ dashboard.php</div>
    </a>

  </div>
</section>

<!-- FOOTER -->
<footer>
  <span class="footer-left">SecureApp &copy; <?= date('Y') ?> — Završni rad, Fakultet računarstva</span>
  <span class="footer-right">✓ Zaštićeno</span>
</footer>

</body>
</html>