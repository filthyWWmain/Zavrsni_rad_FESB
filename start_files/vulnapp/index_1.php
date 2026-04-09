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
  <title>VulnApp — Početna</title>
  <link rel="stylesheet" href="../../css/vulnapp_index_1.css"/>
</head>
<body>

<nav>
  <a href="index.php" class="nav-logo">Vuln<span>App</span></a>
  <ul class="nav-links">
    <li><a href="index.php" class="active">Početna</a></li>
    <li><a href="login.php">Prijava</a></li>
    <li><a href="register.php">Registracija</a></li>
    <?php if ($logged_in): ?>
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="upload.php">Upload</a></li>
    <?php endif; ?>
  </ul>
  <div class="nav-right">
    <?php if ($logged_in): ?>
      <span class="nav-user"><?= $username ?></span>
      <a href="logout.php" class="nav-logout">Odjava</a>
    <?php else: ?>
      <span class="nav-badge">⚠ Nezaštićeno</span>
    <?php endif; ?>
  </div>
</nav>

<div class="warn-strip">
  <div class="warn-strip-dot"></div>
  Ova aplikacija je namjerno ranjiva — isključivo u edukativne svrhe
</div>

<!-- Hero -->
<div class="hero anim-1">
  <p class="hero-tag">// vulnapp — ranjiva aplikacija</p>
  <h1>Sustav<br>bez <em>zaštite</em></h1>
  <p class="hero-desc">
    Namjerno ranjiva aplikacija za demonstraciju sigurnosnih propusta.
    Prijavite se ili registrirajte za pristup panelu.
  </p>
  <div class="hero-btns">
    <?php if ($logged_in): ?>
      <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
      <a href="upload.php"    class="btn btn-secondary">Upload</a>
    <?php else: ?>
      <a href="login.php"    class="btn btn-primary">Prijava</a>
      <a href="register.php" class="btn btn-secondary">Registracija</a>
    <?php endif; ?>
  </div>
</div>

<!-- Stats -->
<div class="stats-row anim-2">
  <div class="stat">
    <div class="stat-num">7</div>
    <div class="stat-label">Ranjivosti</div>
  </div>
  <div class="stat">
    <div class="stat-num">0</div>
    <div class="stat-label">Zaštitnih mehanizama</div>
  </div>
  <div class="stat">
    <div class="stat-num">4</div>
    <div class="stat-label">Vrste napada</div>
  </div>
  <div class="stat">
    <div class="stat-num">plaintext</div>
    <div class="stat-label">Pohrana lozinki</div>
  </div>
</div>

<!-- Ranjivosti -->
<div class="page">
  <div class="page-header anim-3">
    <h1>Poznate ranjivosti</h1>
    <p>// svaka je namjerno implementirana za demonstraciju</p>
  </div>

  <div class="vuln-grid anim-3">
    <div class="vuln-card high">
      <div class="vuln-card-id">CVE-001</div>
      <div class="vuln-card-name">SQL Injection</div>
      <p class="vuln-card-desc">Login forma direktno umeće korisnički unos u SQL upit bez parametrizacije.</p>
      <span class="badge badge-danger">Visoki rizik</span>
    </div>
    <div class="vuln-card high">
      <div class="vuln-card-id">CVE-002</div>
      <div class="vuln-card-name">XSS — Reflected & Stored</div>
      <p class="vuln-card-desc">Korisnički unos se ispisuje bez htmlspecialchars() — script injection moguć.</p>
      <span class="badge badge-danger">Visoki rizik</span>
    </div>
    <div class="vuln-card high">
      <div class="vuln-card-id">CVE-003</div>
      <div class="vuln-card-name">Plaintext lozinke</div>
      <p class="vuln-card-desc">Lozinke se pohranjuju u bazu kao čisti tekst, bez hashiranja ili saltanja.</p>
      <span class="badge badge-danger">Visoki rizik</span>
    </div>
    <div class="vuln-card high">
      <div class="vuln-card-id">CVE-004</div>
      <div class="vuln-card-name">File Upload RCE</div>
      <p class="vuln-card-desc">Upload bez provjere tipa — .php webshell se može uploadati i izvršiti.</p>
      <span class="badge badge-danger">Visoki rizik</span>
    </div>
    <div class="vuln-card medium">
      <div class="vuln-card-id">CVE-005</div>
      <div class="vuln-card-name">CSRF</div>
      <p class="vuln-card-desc">Forme bez CSRF tokena — moguće cross-site request forgery napade.</p>
      <span class="badge badge-warning">Srednji rizik</span>
    </div>
    <div class="vuln-card medium">
      <div class="vuln-card-id">CVE-006</div>
      <div class="vuln-card-name">IDOR</div>
      <p class="vuln-card-desc">Brisanje bilješki bez provjere vlasništva — brišeš i tuđe zapise.</p>
      <span class="badge badge-warning">Srednji rizik</span>
    </div>
    <div class="vuln-card medium">
      <div class="vuln-card-id">CVE-007</div>
      <div class="vuln-card-name">Session Fixation</div>
      <p class="vuln-card-desc">Session ID se ne regenerira nakon prijave — moguć session hijacking.</p>
      <span class="badge badge-warning">Srednji rizik</span>
    </div>
  </div>

  <!-- Quick access -->
  <div class="page-header anim-4" style="margin-top:3rem;">
    <h1>Brzi pristup</h1>
    <p>// sve stranice aplikacije</p>
  </div>

  <div class="quick-grid anim-4">
    <a href="login.php" class="quick-card">
      <div class="quick-num">01</div>
      <div class="quick-title">Prijava</div>
      <p class="quick-sub">Ranjiva forma. Pokušajte SQL injection: <code>' OR '1'='1' --</code></p>
      <div class="quick-arrow">→ login.php</div>
    </a>
    <a href="register.php" class="quick-card">
      <div class="quick-num">02</div>
      <div class="quick-title">Registracija</div>
      <p class="quick-sub">Bez validacije. Lozinka ide u bazu kao plaintext.</p>
      <div class="quick-arrow">→ register.php</div>
    </a>
    <a href="upload.php" class="quick-card">
      <div class="quick-num">03</div>
      <div class="quick-title">File Upload</div>
      <p class="quick-sub">Upload bez provjere. Pokušajte uploadati shell.php webshell.</p>
      <div class="quick-arrow">→ upload.php</div>
    </a>
  </div>
</div>

<footer>
  <span class="footer-left">VulnApp &copy; <?= date('Y') ?> — Završni rad, Fakultet računarstva</span>
  <span class="footer-right">⚠ Edukativna svrha</span>
</footer>

</body>
</html>
