<?php
session_start();
$logged_in = isset($_SESSION['user_id']);
$username  = $logged_in ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VulnApp — Dobrodošli</title>
  <link href="https://fonts.googleapis.com/css2?family=VT323&family=IBM+Plex+Mono:wght@400;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../styles/normalize.css" />
    <link rel="stylesheet" href="../../css/vulnapp_index.css" />
</head>
<body>
<!-- NAV -->
<nav>
  <div class="nav-logo">Vuln<span>App</span></div>
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
    <span class="nav-user">// <?= $username ?></span>
  <?php else: ?>
    <span class="nav-badge">⚠ Nezaštićeno</span>
  <?php endif; ?>
</nav>

<!-- ALERT BANNER -->
<div class="alert-banner">
  <div class="alert-dot"></div>
  <span>Upozorenje: Ova aplikacija je namjerno ranjiva — samo za edukativne svrhe</span>
</div>

<!-- HERO -->
<section class="hero">
  <div class="hero-grid-bg"></div>
  <p class="hero-tag">// vulnapp — ranjiva aplikacija</p>
  <h1>SUSTAV<br><em>BEZ</em><br>ZAŠTITE</h1>
  <p class="hero-desc">
    Ova aplikacija ne koristi nikakve sigurnosne mehanizme.<br>
    Ranjiva je na SQL injection, XSS, CSRF i druge napade.<br>
    Prijavite se ili registrirajte za pristup panelu.
  </p>
  <div class="hero-btns">
    <?php if ($logged_in): ?>
      <a href="dashboard.php" class="btn btn-red">Otvori panel</a>
      <a href="logout.php" class="btn btn-outline">Odjava</a>
    <?php else: ?>
      <a href="login.php"    class="btn btn-red">Prijava</a>
      <a href="register.php" class="btn btn-outline">Registracija</a>
    <?php endif; ?>
  </div>
</section>

<!-- RANJIVOSTI -->
<section class="vuln-section">
  <div class="section-label">Poznate ranjivosti</div>
  <div class="vuln-grid">

    <div class="vuln-card">
      <div class="vuln-id">CVE-001</div>
      <div class="vuln-name">SQL Injection</div>
      <p class="vuln-desc">Login forma direktno umeće korisnički unos u SQL upit bez sanitizacije.</p>
      <span class="vuln-severity sev-high">Visoki rizik</span>
    </div>

    <div class="vuln-card">
      <div class="vuln-id">CVE-002</div>
      <div class="vuln-name">XSS — Reflected</div>
      <p class="vuln-desc">Korisnički unos se prikazuje u HTML-u bez escapiranja — mogući script injection.</p>
      <span class="vuln-severity sev-high">Visoki rizik</span>
    </div>

    <div class="vuln-card">
      <div class="vuln-id">CVE-003</div>
      <div class="vuln-name">Plaintext lozinke</div>
      <p class="vuln-desc">Lozinke se pohranjuju u bazu bez ikakvog hashiranja ili saltanja.</p>
      <span class="vuln-severity sev-high">Visoki rizik</span>
    </div>

    <div class="vuln-card">
      <div class="vuln-id">CVE-004</div>
      <div class="vuln-name">Bez CSRF zaštite</div>
      <p class="vuln-desc">Forme ne koriste CSRF tokene — moguće cross-site request forgery napade.</p>
      <span class="vuln-severity sev-medium">Srednji rizik</span>
    </div>

    <div class="vuln-card">
      <div class="vuln-id">CVE-005</div>
      <div class="vuln-name">Session Fixation</div>
      <p class="vuln-desc">Session ID se ne regenerira nakon prijave — mogući session hijacking.</p>
      <span class="vuln-severity sev-medium">Srednji rizik</span>
    </div>

    <div class="vuln-card">
      <div class="vuln-id">CVE-006</div>
      <div class="vuln-name">Bez Rate Limitinga</div>
      <p class="vuln-desc">Nema ograničenja pokušaja prijave — aplikacija je ranjiva na brute force.</p>
      <span class="vuln-severity sev-medium">Srednji rizik</span>
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
      <p class="quick-sub">Ranjiva forma za prijavu. Pokušajte SQL injection: <code>' OR '1'='1</code></p>
      <div class="quick-arrow">→ login.php</div>
    </a>

    <a href="register.php" class="quick-card">
      <div class="quick-num">02</div>
      <div class="quick-title">Registracija</div>
      <p class="quick-sub">Registracija bez validacije. Lozinka se sprema u plaintext.</p>
      <div class="quick-arrow">→ register.php</div>
    </a>

    <a href="dashboard.php" class="quick-card">
      <div class="quick-num">03</div>
      <div class="quick-title">Korisnički panel</div>
      <p class="quick-sub">Panel s korisničkim podacima. Ranjiv na XSS u svim input poljima.</p>
      <div class="quick-arrow">→ dashboard.php</div>
    </a>

  </div>
</section>

<!-- FOOTER -->
<footer>
  <span class="footer-left">VulnApp &copy; <?= date('Y') ?> — Završni rad, Fakultet računarstva</span>
  <span class="footer-right">⚠ EDUKATIVNA SVRHA</span>
</footer>

</body>
</html>