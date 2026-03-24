<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>WebSec Lab — Analiza Web Sigurnosti</title>
  <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Syne:wght@400;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="../styles/normalize.css" />
  <link rel="stylesheet" href="../css/main.css" />
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">Web<span>Sec</span>Lab<span>_</span></div>
  <ul class="nav-links">
    <li><a href="#aplikacije">Aplikacije</a></li>
    <li><a href="#napadi">Napadi</a></li>
    <li><a href="#o-projektu">O projektu</a></li>
  </ul>
  <a href="login.php" class="nav-cta">Prijava</a>
</nav>

<!-- HERO -->
<div class="hero">
  <p class="hero-tag">Završni rad — Web Sigurnost</p>
  <h1>
    Analiza<br>
    <span class="line2">web</span><br>
    <span class="line3">sigurnosti</span>
  </h1>
  <p class="hero-desc">
    Praktična usporedba zaštićene i nezaštićene web aplikacije.<br>
    Demonstracija SQL injection, XSS, CSRF i ostalih napada<br>
    na stvarnim primjerima.
  </p>
  <div class="hero-btns">
    <a href="#aplikacije" class="btn-primary">Istraži aplikacije</a>
    <a href="#napadi" class="btn-secondary">Pogledaj napade</a>
  </div>

  <!-- TERMINAL -->
  <div class="terminal">
    <div class="term-bar">
      <div class="term-dot r"></div>
      <div class="term-dot y"></div>
      <div class="term-dot g"></div>
      <span class="term-title">websec@lab ~ bash</span>
    </div>
    <div class="term-body">
      <div class="term-line"><span class="prompt">$</span> <span class="cmd">sqlmap -u "target/login.php"</span></div>
      <div class="term-line"><span class="out">[INFO] testing connection...</span></div>
      <div class="term-line"><span class="out">[INFO] parameter 'username' injectable</span></div>
      <div class="term-line"><span class="warn">[WARN] SQL injection FOUND!</span></div>
      <div class="term-line"><span class="out">[DATA] retrieved: admin:password123</span></div>
      <div class="term-line" style="margin-top:.5rem"><span class="prompt">$</span> <span class="cmd">run_xss_scan --target /profile</span></div>
      <div class="term-line"><span class="out">[INFO] scanning input fields...</span></div>
      <div class="term-line"><span class="warn">[WARN] reflected XSS detected</span></div>
      <div class="term-line" style="margin-top:.5rem"><span class="prompt">$</span> <span class="cmd">compare --secure vs --unsafe</span></div>
      <div class="term-line"><span class="out">[OK] secure app: 0 vulnerabilities</span></div>
      <div class="term-line"><span class="warn">[!!] unsafe app: 7 vulnerabilities</span></div>
      <div class="term-line" style="margin-top:.5rem"><span class="prompt">$</span> <span class="cursor"></span></div>
    </div>
  </div>
</div>

<!-- STATS -->
<div class="stats">
  <div class="stat">
    <div class="stat-num red">7</div>
    <div class="stat-label">Otkrivenih ranjivosti</div>
  </div>
  <div class="stat">
    <div class="stat-num">2</div>
    <div class="stat-label">Web aplikacije</div>
  </div>
  <div class="stat">
    <div class="stat-num">4</div>
    <div class="stat-label">Vrste napada</div>
  </div>
  <div class="stat">
    <div class="stat-num red">0</div>
    <div class="stat-label">Ranjivosti u zaštićenoj</div>
  </div>
</div>

<!-- APPS SECTION -->
<section id="aplikacije">
  <p class="section-tag">// Dvije aplikacije</p>
  <h2 class="section-title">Usporedba<br>aplikacija</h2>
  <p class="section-sub">
    Obje aplikacije nude iste funkcionalnosti — registraciju, prijavu i upravljanje podacima.
    Razlikuju se isključivo u implementaciji sigurnosnih mehanizama.
  </p>

  <div class="apps-grid">
    <!-- UNSAFE -->
    <div class="app-card card-unsafe">
      <div class="card-badge badge-unsafe">Nezaštićena</div>
      <h3 class="card-title">VulnApp</h3>
      <p class="card-desc">
        Namjerno ranjiva aplikacija bez zaštite.<br>
        Idealna meta za demonstraciju napada.
      </p>
      <ul class="card-features">
        <li class="vuln">SQL upiti bez parametrizacije</li>
        <li class="vuln">Korisnički unos bez sanitizacije</li>
        <li class="vuln">Lozinke pohranjene u plaintextu</li>
        <li class="vuln">Nema CSRF zaštite</li>
        <li class="vuln">Session management ranjivosti</li>
      </ul>
      <a href="vulnapp/index.php" class="card-link unsafe">Otvori VulnApp →</a>
      <div class="card-bg-num">01</div>
    </div>

    <!-- SECURE -->
    <div class="app-card card-secure">
      <div class="card-badge badge-secure">Zaštićena</div>
      <h3 class="card-title">SecureApp</h3>
      <p class="card-desc">
        Implementacija s primijenjenim sigurnosnim praksama.<br>
        Iste funkcionalnosti, drugačija implementacija.
      </p>
      <ul class="card-features">
        <li class="safe">Parametrizirani SQL upiti (PDO)</li>
        <li class="safe">Sanitizacija i validacija unosa</li>
        <li class="safe">bcrypt hashiranje lozinki</li>
        <li class="safe">CSRF tokeni na svim formama</li>
        <li class="safe">Sigurno upravljanje sesijama</li>
      </ul>
      <a href="secureapp/index.php" class="card-link secure">Otvori SecureApp →</a>
      <div class="card-bg-num">02</div>
    </div>
  </div>
</section>

<!-- ATTACKS SECTION -->
<section id="napadi" style="padding-top: 0;">
  <p class="section-tag">// Demonstrirani napadi</p>
  <h2 class="section-title">Vrste<br>napada</h2>
  <p class="section-sub">
    Svaki napad demonstriran je na nezaštićenoj aplikaciji i objašnjeno je
    kako ga zaštićena aplikacija blokira.
  </p>

  <div class="attacks-grid">
    <div class="attack-item">
      <div class="attack-icon">💉</div>
      <div class="attack-name">SQL Injection</div>
      <p class="attack-desc">Ubacivanje SQL koda kroz korisnički unos za neovlašteni pristup bazi podataka ili zaobilaženje autentikacije.</p>
      <span class="attack-tag tag-high">Visoki rizik</span>
    </div>
    <div class="attack-item">
      <div class="attack-icon">📜</div>
      <div class="attack-name">XSS napad</div>
      <p class="attack-desc">Cross-site scripting omogućuje ubacivanje malicioznih skripti koje se izvršavaju u pregledniku žrtve.</p>
      <span class="attack-tag tag-high">Visoki rizik</span>
    </div>
    <div class="attack-item">
      <div class="attack-icon">🎭</div>
      <div class="attack-name">CSRF napad</div>
      <p class="attack-desc">Cross-site request forgery prisiljava korisnika da nesvjesno izvrši neželjene radnje na stranici gdje je autentificiran.</p>
      <span class="attack-tag tag-high">Visoki rizik</span>
    </div>
    <div class="attack-item">
      <div class="attack-icon">🔑</div>
      <div class="attack-name">Brute Force</div>
      <p class="attack-desc">Automatsko isprobavanje kombinacija lozinki bez rate-limiting zaštite. Naglašava važnost jakih lozinki i zaključavanja računa.</p>
      <span class="attack-tag tag-med">Srednji rizik</span>
    </div>
    <div class="attack-item">
      <div class="attack-icon">🕵️</div>
      <div class="attack-name">Session Hijacking</div>
      <p class="attack-desc">Krađa session tokena za preuzimanje korisničke sesije. Demonstrira važnost sigurnih session mehanizama.</p>
      <span class="attack-tag tag-high">Visoki rizik</span>
    </div>
    <div class="attack-item">
      <div class="attack-icon">📂</div>
      <div class="attack-name">Path Traversal</div>
      <p class="attack-desc">Neovlašteni pristup datotekama izvan web direktorija manipulacijom putanje. Otkriva osjetljive sistemske datoteke.</p>
      <span class="attack-tag tag-med">Srednji rizik</span>
    </div>
  </div>
</section>

<!-- O PROJEKTU -->
<section id="o-projektu" style="border-top: 1px solid var(--border);">
  <p class="section-tag">// O projektu</p>
  <h2 class="section-title">Tehnologije<br>i alati</h2>

  <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-top:2rem;">
    <div>
      <p style="font-family:var(--mono); font-size:.75rem; color:var(--muted); line-height:1.9;">
        <span style="color:var(--accent);">// Stack</span><br>
        HTML5 + CSS3 + JavaScript<br>
        PHP 8.x<br>
        MySQL / MariaDB<br>
        XAMPP (Apache)<br><br>
        <span style="color:var(--accent);">// Alati za testiranje</span><br>
        Burp Suite<br>
        sqlmap<br>
        OWASP ZAP<br>
        Browser DevTools
      </p>
    </div>
    <div>
      <p style="font-family:var(--mono); font-size:.75rem; color:var(--muted); line-height:1.9;">
        <span style="color:var(--accent2);">// Zaštitni mehanizmi</span><br>
        PDO Prepared Statements<br>
        htmlspecialchars() / strip_tags()<br>
        password_hash() bcrypt<br>
        CSRF Token validacija<br>
        HTTP Security Headers<br>
        Input validacija (server-side)<br>
        Session regeneration<br>
        Rate limiting
      </p>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-left">
    <span>WebSecLab</span> — Završni rad, Fakultet elektrotehnike, strojarstva i brodogradnje, 2026.
  </div>
  <div class="footer-right">
    Ruđera Boškovića 32, 21000 Split<br>
  </div>
</footer>

</body>
</html>