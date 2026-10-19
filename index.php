<?php
declare(strict_types=1);

// आपका बाकी PHP कोड इसके नीचे रहेगा

$settingsFile = __DIR__ . '/settings.php';

$settings = is_file($settingsFile)
    ? require $settingsFile
    : [];

$whatsapp = preg_replace('/\D+/', '', (string)($settings['whatsapp'] ?? '919876543210'));
$seconds = max(3, min(120, (int)($settings['redirect_seconds'] ?? 10)));
$autoRedirect = (bool)($settings['auto_redirect'] ?? true);
$messageTemplate = (string)($settings['whatsapp_message'] ?? 'Hello LAVIE WORLD! Please share your latest handbag collection and prices.');
$messageTemplate = (string)($settings['whatsapp_message'] ?? 'Hello LAVIE WORLD! Please share your latest handbag collection and prices.');

$products = [
    ['name' => 'Classic Black Bag', 'category' => 'Shoulder Bags', 'tag' => 'BESTSELLER', 'image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=85'],
    ['name' => 'Elegant Cream Bag', 'category' => 'Mini Bags', 'tag' => 'TRENDING', 'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=800&q=85'],
    ['name' => 'Everyday Style Bag', 'category' => 'Handbags', 'tag' => 'NEW', 'image' => 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?auto=format&fit=crop&w=800&q=85'],
    ['name' => 'Luxury Mini Bag', 'category' => 'Crossbody Bags', 'tag' => 'POPULAR', 'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=85'],
    ['name' => 'Elegant Chain Bag', 'category' => 'Party Bags', 'tag' => 'TRENDING', 'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=85'],
    ['name' => 'Chic Daily Bag', 'category' => 'Daily Wear', 'tag' => 'NEW', 'image' => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?auto=format&fit=crop&w=800&q=85'],
];

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function whatsappUrl(string $number, string $message): string {
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
}

$generalUrl = whatsappUrl(
    $whatsapp,
    $messageTemplate
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#17130f">
<title>LAVIE WORLD — Bags, Style & Elegance</title>
<meta name="description" content="Explore stylish handbags and discover your next favourite bag. Ask about products directly on WhatsApp.">

<style>
:root {
    --ink:#19150f;
    --gold:#c39a5b;
    --cream:#fbf6ee;
    --muted:#857a6a;
    --green:#18b95d;
}
* { box-sizing:border-box; }
html { scroll-behavior:smooth; }
body {
    margin:0;
    background:var(--cream);
    color:var(--ink);
    font-family:Arial,Helvetica,sans-serif;
    padding-bottom:84px;
}
a { text-decoration:none; color:inherit; }
button { font:inherit; cursor:pointer; }
.promo {
    background:var(--ink);
    color:#e7c58e;
    text-align:center;
    padding:9px 5px;
    font-size:10px;
    letter-spacing:1.5px;
}
.header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:17px 16px;
}
.logo {
    font-family:Georgia,serif;
    font-size:26px;
    font-weight:bold;
    letter-spacing:2px;
}
.logo small {
    display:block;
    text-align:center;
    color:#a57a3b;
    font:10px Arial,sans-serif;
    letter-spacing:5px;
    margin-top:4px;
}
.pill {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    background:var(--green);
    color:white;
    font-size:12px;
    font-weight:bold;
    border-radius:30px;
    padding:12px 14px;
}
.hero {
    padding:34px 20px 28px;
    color:white;
    background:
        linear-gradient(90deg,rgba(18,15,11,.98) 0%,rgba(18,15,11,.88) 52%,rgba(18,15,11,.25) 100%),
        url('https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=1200&q=90') center/cover;
    min-height:390px;
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    justify-content:center;
}
.eyebrow {
    color:#e4bd7c;
    font-size:10px;
    letter-spacing:2.5px;
    margin-bottom:15px;
}
.hero h1 {
    font:normal clamp(42px,11vw,58px)/1.04 Georgia,serif;
    margin:0;
}
.hero h1 em { color:#dfb775; }
.hero p {
    max-width:260px;
    font-size:13px;
    line-height:1.8;
    color:#e3d9c8;
    margin:16px 0 21px;
}
.gold-line {
    width:48px;
    height:2px;
    background:var(--gold);
    margin-bottom:17px;
}
.section-head {
    text-align:center;
    padding:29px 12px 15px;
}
.kicker {
    color:#a67d42;
    font-size:10px;
    letter-spacing:3px;
    font-weight:bold;
    margin-bottom:8px;
}
.section-head h2 {
    font:normal 30px Georgia,serif;
    margin:0;
}
.section-head p {
    color:var(--muted);
    font-size:12px;
    line-height:1.6;
    margin:9px 0 0;
}
.filters {
    display:flex;
    gap:8px;
    padding:10px 14px 18px;
    overflow-x:auto;
    scrollbar-width:none;
}
.filters::-webkit-scrollbar { display:none; }
.filter {
    flex-shrink:0;
    padding:10px 14px;
    border:1px solid #e5d8c4;
    border-radius:30px;
    background:#fffaf4;
    color:#655640;
    font-size:10px;
    font-weight:bold;
}
.filter.active {
    background:var(--ink);
    border-color:var(--ink);
    color:#f0cf96;
}
.grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
    padding:0 13px;
}
.card {
    min-width:0;
    overflow:hidden;
    border:1px solid #efe5d7;
    border-radius:12px;
    background:white;
    box-shadow:0 5px 18px #39271008;
}
.photo {
    position:relative;
    aspect-ratio:4/4.4;
    background:#e9dfd0;
    overflow:hidden;
}
.photo img {
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}
.tag {
    position:absolute;
    top:9px;
    left:8px;
    padding:5px 7px;
    border-radius:3px;
    background:#1b1712;
    color:#edca8e;
    font-size:8px;
    letter-spacing:.6px;
}
.info { padding:11px 9px 10px; }
.cat {
    color:#a17b46;
    font-size:9px;
    letter-spacing:.5px;
    text-transform:uppercase;
    margin-bottom:7px;
}
.name {
    font-size:13px;
    line-height:1.4;
    min-height:36px;
    margin:0;
}
.order {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    background:var(--green);
    color:white;
    padding:11px 4px;
    border-radius:6px;
    font-size:10px;
    font-weight:bold;
}
.trust {
    margin:25px 13px;
    padding:20px 8px;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:7px;
    text-align:center;
    border-radius:12px;
    color:#f4e5cd;
    background:#1b1712;
}
.trust div { font-size:10px; line-height:1.6; }
.trust b {
    display:block;
    color:#e0b879;
    font-size:22px;
    font-weight:normal;
    margin-bottom:5px;
}
.bottom {
    text-align:center;
    margin:0 13px 24px;
    padding:23px 15px;
    border:1px solid #e4d0ad;
    border-radius:13px;
    background:#f0e2cd;
}
.bottom h2 {
    font:normal 25px Georgia,serif;
    margin:0;
}
.bottom p {
    font-size:12px;
    color:#685846;
    line-height:1.7;
    margin:10px 0 16px;
}
.bottom .pill { width:100%; padding:15px; }
footer {
    text-align:center;
    padding:4px 12px 22px;
    color:var(--muted);
    font-size:10px;
    line-height:1.8;
}
.sticky {
    position:fixed;
    z-index:5;
    left:0;
    right:0;
    bottom:0;
    padding:10px 13px calc(10px + env(safe-area-inset-bottom));
    background:#fbf6eef2;
    border-top:1px solid #e7ddce;
    backdrop-filter:blur(10px);
}
.sticky a {
    display:flex;
    justify-content:center;
    align-items:center;
    gap:8px;
    background:var(--green);
    color:white;
    border-radius:9px;
    padding:15px 8px;
    font-size:13px;
    font-weight:bold;
}
@media(min-width:700px) {
    body { max-width:1000px; margin:auto; padding-bottom:20px; }
    .hero { padding:60px 55px; min-height:440px; }
    .grid { grid-template-columns:repeat(3,minmax(0,1fr)); padding:0 24px; gap:18px; }
    .sticky { left:50%; right:auto; width:min(100%,1000px); transform:translateX(-50%); }
}
</style>
</head>
<body>

<div class="promo">THE NEW COLLECTION · BAGS THAT DEFINE YOUR STYLE</div>

<header class="header">
    <a href="#" class="logo">LAVIE<small>W O R L D</small></a>
    <a class="pill" href="<?= e($generalUrl) ?>">WhatsApp ↗</a>
</header>

<section class="hero">
    <div class="eyebrow">THE HANDBAG EDIT</div>
    <div class="gold-line"></div>
    <h1>Elegance in<br><em>Every Detail.</em></h1>
    <p>Discover your next favourite bag. Beautiful styles for everyday moments and special occasions.</p>
    <a class="pill" href="<?= e($generalUrl) ?>">EXPLORE COLLECTION&nbsp; →</a>
</section>

<section class="section-head">
    <div class="kicker">CURATED FOR YOU</div>
    <h2>Find Your Style</h2>
    <p>Little details. Big statement.</p>
</section>

<nav class="filters" aria-label="Product categories">
    <button class="filter active" data-filter="all">ALL BAGS</button>
    <button class="filter" data-filter="Shoulder Bags">SHOULDER</button>
    <button class="filter" data-filter="Mini Bags">MINI BAGS</button>
    <button class="filter" data-filter="Handbags">HANDBAGS</button>
    <button class="filter" data-filter="Party Bags">PARTY</button>
    <button class="filter" data-filter="Daily Wear">DAILY WEAR</button>
</nav>

<main class="grid">
<?php foreach ($products as $p):
    $msg = str_replace(
        ['{product}', '{category}'],
        [$p['name'], $p['category']],
        $messageTemplate
    );
    $productUrl = whatsappUrl($whatsapp, $msg);
?>
    <article class="card" data-category="<?= e($p['category']) ?>">
        <div class="photo">
            <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
            <span class="tag"><?= e($p['tag']) ?></span>
        </div>
        <div class="info">
            <div class="cat"><?= e($p['category']) ?></div>
            <h3 class="name"><?= e($p['name']) ?></h3>
            <a class="order" href="<?= e($productUrl) ?>">WhatsApp · ENQUIRE</a>
        </div>
    </article>
<?php endforeach; ?>
</main>

<section class="trust">
    <div><b>✧</b>Curated<br>Styles</div>
    <div><b>♡</b>Latest<br>Collection</div>
    <div><b>◉</b>WhatsApp<br>Assistance</div>
</section>

<section class="bottom">
    <h2>Your Next Favourite Bag.</h2>
    <p>Want more photos, details or availability?<br>Talk to us directly on WhatsApp.</p>
    <a class="pill" href="<?= e($generalUrl) ?>">CHAT WITH US&nbsp; →</a>
</section>

<footer>
    <strong>LAVIE WORLD</strong><br>
    Bags · Style · Elegance<br>
    Availability and details are confirmed on WhatsApp.
</footer>

<div class="sticky">
    <a href="<?= e($generalUrl) ?>">☏ &nbsp; SHOP / ENQUIRE ON WHATSAPP</a>
</div>



<script>
(function () {
    const autoRedirect = <?= $autoRedirect ? 'true' : 'false' ?>;
    const delay = <?= $seconds ?>;
    const whatsappUrl = <?= json_encode($generalUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // Silent redirect: no popup or countdown dialog.
    if (autoRedirect) {
        window.setTimeout(function () {
            window.location.href = whatsappUrl;
        }, delay * 1000);
    }

    // Product enquiry buttons open WhatsApp with the selected product details.
    document.querySelectorAll('.filter').forEach(function (button) {
        button.addEventListener('click', function () {
            const selected = this.dataset.filter;

            document.querySelectorAll('.filter').forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            document.querySelectorAll('.card').forEach(function (card) {
                card.hidden = selected !== 'all' &&
                    card.dataset.category !== selected;
            });
        });
    });
})();
</script>
</body>
</html>
