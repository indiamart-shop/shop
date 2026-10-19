<?php
declare(strict_types=1);

// आपका बाकी PHP कोड इसके नीचे रहेगा

session_name('lavie_admin_session');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Strict',
    'path' => '/'
]);
session_start();

$settingsFile = __DIR__ . '/settings.php';

function readSettings(string $file): array {
    if (!is_file($file)) {
        return [];
    }

    $data = require $file;
    return is_array($data) ? $data : [];
}

function writeSettings(string $file, array $settings): bool {
    $content = "<?php\n// Private LAVIE WORLD settings.\nreturn "
        . var_export($settings, true) . ";\n";

    $tempFile = $file . '.tmp';
    $written = file_put_contents($tempFile, $content, LOCK_EX);

    if ($written === false) {
        return false;
    }

    @chmod($tempFile, 0600);

    if (!rename($tempFile, $file)) {
        @unlink($tempFile);
        return false;
    }

    @chmod($file, 0600);
    return true;
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): bool {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

$settings = readSettings($settingsFile);
$firstSetup = empty($settings['admin_password_hash']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) {
        $error = 'Security token expired. Refresh the page and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'setup' && $firstSetup) {
            $password = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters. Numbers-only or mixed passwords are allowed.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $settings = [
                    'admin_password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'whatsapp' => '919876543210',
                    'redirect_seconds' => 10,
                    'auto_redirect' => true,
                    'whatsapp_message' => 'Hello LAVIE WORLD! Please share your latest handbag collection and prices.'
                ];

                if (writeSettings($settingsFile, $settings)) {
                    session_regenerate_id(true);
                    $_SESSION['lavie_admin_logged_in'] = true;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    header('Location: admin.php');
                    exit;
                }

                $error = 'Could not save settings. Check folder permissions.';
            }
        } elseif ($action === 'login' && !$firstSetup) {
            $password = (string)($_POST['password'] ?? '');
            $hash = (string)($settings['admin_password_hash'] ?? '');

            if ($hash !== '' && password_verify($password, $hash)) {
                session_regenerate_id(true);
                $_SESSION['lavie_admin_logged_in'] = true;
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: admin.php');
                exit;
            }

            $error = 'Incorrect password. Please try again.';
        } elseif ($action === 'logout' && !empty($_SESSION['lavie_admin_logged_in'])) {
            $_SESSION = [];
            session_destroy();
            header('Location: admin.php');
            exit;
        } elseif ($action === 'save' && !empty($_SESSION['lavie_admin_logged_in'])) {
            $number = preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
            $seconds = filter_var(
                $_POST['redirect_seconds'] ?? null,
                FILTER_VALIDATE_INT
            );
            $auto = isset($_POST['auto_redirect']);
            $newPassword = (string)($_POST['new_password'] ?? '');
            $messageTemplate = trim((string)($_POST['whatsapp_message'] ?? ''));
            $messageTemplate = trim((string)($_POST['whatsapp_message'] ?? ''));

            if (strlen($number) < 10 || strlen($number) > 15) {
                $error = 'Enter a valid WhatsApp number with country code.';
            } elseif ($seconds === false || $seconds < 3 || $seconds > 120) {
                $error = 'Timer must be between 3 and 120 seconds.';
            } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
                $error = 'New password must be at least 6 characters.';
            } elseif ($messageTemplate === '' || strlen($messageTemplate) > 1500) {
                $error = 'WhatsApp message is required and must be 1500 characters or less.';
            } else {
                $settings['whatsapp'] = $number;
                $settings['redirect_seconds'] = $seconds;
                $settings['auto_redirect'] = $auto;
                $settings['whatsapp_message'] = $messageTemplate;
                $settings['whatsapp_message'] = $messageTemplate;

                if ($newPassword !== '') {
                    $settings['admin_password_hash'] =
                        password_hash($newPassword, PASSWORD_DEFAULT);
                }

                if (writeSettings($settingsFile, $settings)) {
                    $success = 'Settings saved successfully.';
                    if ($newPassword !== '') {
                        session_regenerate_id(true);
                        $success .= ' Your admin password has also been changed.';
                    }
                } else {
                    $error = 'Could not save settings. Check folder permissions.';
                }
            }
        }
    }
}

$settings = readSettings($settingsFile);
$firstSetup = empty($settings['admin_password_hash']);
$loggedIn = !empty($_SESSION['lavie_admin_logged_in']);

$number = (string)($settings['whatsapp'] ?? '919876543210');
$seconds = (int)($settings['redirect_seconds'] ?? 10);
$auto = (bool)($settings['auto_redirect'] ?? true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#17140f">
<title>LAVIE WORLD | Admin Panel</title>
<style>
* { box-sizing:border-box; }
body {
    margin:0;
    min-height:100vh;
    font-family:Arial,Helvetica,sans-serif;
    background:#f6f1e8;
    color:#211b13;
}
.top {
    padding:23px 20px;
    background:#17140f;
    color:white;
    border-bottom:3px solid #c49a56;
}
.brand {
    font: bold 25px Georgia,serif;
    letter-spacing:2px;
}
.brand span {
    display:block;
    margin-top:5px;
    color:#d7b477;
    font:10px Arial,sans-serif;
    letter-spacing:4px;
}
.top small {
    display:block;
    margin-top:15px;
    color:#cfc4b1;
    font-size:12px;
}
.wrap {
    max-width:760px;
    margin:30px auto;
    padding:0 15px 35px;
}
.card {
    background:white;
    border:1px solid #eadfce;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 35px #2b1b0a0b;
}
.card-head {
    padding:22px;
    background:linear-gradient(135deg,#242018,#17140f);
    color:white;
}
.card-head .eyebrow {
    color:#d4ae71;
    text-transform:uppercase;
    font-size:10px;
    letter-spacing:2px;
}
.card-head h1 {
    margin:10px 0 6px;
    font:normal 29px Georgia,serif;
}
.card-head p {
    margin:0;
    color:#d0c5b3;
    font-size:12px;
    line-height:1.6;
}
.card-body { padding:22px; }
.status {
    padding:12px 13px;
    border-radius:8px;
    margin-bottom:17px;
    font-size:13px;
    line-height:1.5;
}
.error { background:#fff0ef; color:#9a2c23; }
.success { background:#eaf9ef; color:#176f3b; }
label {
    display:block;
    font-weight:bold;
    font-size:12px;
    margin:18px 0 8px;
}
input[type="text"],
input[type="password"],
input[type="number"], textarea {
    width:100%;
    padding:14px 13px;
    border:1px solid #ddd1bf;
    background:#fffdf9;
    border-radius:8px;
    outline:none;
    font-size:15px;
    font-family:Arial,Helvetica,sans-serif;
}
input:focus, textarea:focus {
    border-color:#b18a4b;
    box-shadow:0 0 0 3px #c49a561c;
}
.help {
    color:#847969;
    font-size:11px;
    line-height:1.6;
    margin-top:7px;
}
.switch {
    display:flex;
    align-items:center;
    gap:12px;
    margin-top:22px;
    padding:15px;
    background:#faf6ef;
    border:1px solid #eee3d2;
    border-radius:9px;
}
.switch input { width:19px; height:19px; accent-color:#188d4a; }
.switch label { margin:0; font-size:13px; }
.switch small {
    display:block;
    margin-top:4px;
    font-weight:normal;
    color:#847969;
    line-height:1.5;
}
.btn {
    width:100%;
    margin-top:22px;
    padding:15px;
    border:0;
    border-radius:8px;
    background:#b68a48;
    color:white;
    font-size:13px;
    font-weight:bold;
    cursor:pointer;
}
.btn:hover { background:#987039; }
.logout {
    display:block;
    width:100%;
    padding:12px;
    margin-top:12px;
    border:1px solid #dfd1bc;
    border-radius:8px;
    color:#4f4332;
    text-align:center;
    background:white;
    font-weight:bold;
    cursor:pointer;
}
.stats {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
    margin-bottom:17px;
}
.stat {
    padding:15px;
    border-radius:10px;
    border:1px solid #e7dbc8;
    background:#fffdf9;
}
.stat small { color:#8a7a64; font-size:10px; }
.stat strong {
    display:block;
    margin-top:8px;
    font-size:19px;
    overflow-wrap:anywhere;
}
hr { border:0; border-top:1px solid #eee4d5; margin:24px 0; }
.footer {
    text-align:center;
    color:#8b7e6b;
    font-size:11px;
    padding:0 15px 24px;
}
.dev-whatsapp {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    margin-top:12px; padding:9px 14px; border-radius:999px;
    background:#25D366; color:#fff; text-decoration:none; font-size:12px;
    font-weight:700; box-shadow:0 4px 12px rgba(37,211,102,.18);
}
.dev-whatsapp svg { width:17px; height:17px; fill:currentColor; }
@media(min-width:600px) {
    .top { padding:27px max(24px,calc((100% - 700px)/2)); }
    .card-body { padding:28px; }
}
</style>
</head>
<body>
<header class="top">
    <div class="brand">LAVIE WORLD<span>ADMIN CONTROL CENTRE</span></div>
    <small>Manage your storefront and WhatsApp settings</small>
</header>

<main class="wrap">
<section class="card">
    <div class="card-head">
        <div class="eyebrow">Private dashboard</div>
        <h1>
            <?= $firstSetup ? 'Create Admin Password' : ($loggedIn ? 'Store Settings' : 'Welcome Back') ?>
        </h1>
        <p>
            <?= $firstSetup
                ? 'Set a strong password to secure your store administration.'
                : ($loggedIn
                    ? 'Control WhatsApp enquiries and your redirect timer.'
                    : 'Sign in to manage your LAVIE WORLD landing page.') ?>
        </p>
    </div>

    <div class="card-body">
        <?php if ($error !== ''): ?>
            <div class="status error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="status success"><?= h($success) ?></div>
        <?php endif; ?>

        <?php if ($firstSetup): ?>

            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="setup">

                <label for="new_password">Create Admin Password</label>
                <input id="new_password" type="password" name="new_password"
                       minlength="6" required autocomplete="new-password"
                       placeholder="At least 6 characters">

                <label for="confirm_password">Confirm Password</label>
                <input id="confirm_password" type="password" name="confirm_password"
                       minlength="6" required autocomplete="new-password"
                       placeholder="Enter password again">

                <p class="help">At least 6 characters. Numbers-only or mixed passwords are allowed.</p>
                <button class="btn" type="submit">CREATE SECURE ADMIN ACCOUNT</button>
            </form>

        <?php elseif (!$loggedIn): ?>

            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="login">

                <label for="password">Admin Password</label>
                <input id="password" type="password" name="password"
                       required autocomplete="current-password"
                       placeholder="Enter your admin password">

                <button class="btn" type="submit">LOGIN TO DASHBOARD →</button>
            </form>

        <?php else: ?>

            <div class="stats">
                <div class="stat">
                    <small>WHATSAPP NUMBER</small>
                    <strong><?= h($number) ?></strong>
                </div>
                <div class="stat">
                    <small>REDIRECT DELAY</small>
                    <strong><?= $seconds ?> seconds</strong>
                </div>
            </div>

            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="save">

                <label for="whatsapp">WhatsApp Number with Country Code</label>
                <input id="whatsapp" type="text" name="whatsapp"
                       inputmode="numeric" pattern="[0-9 +()-]{10,25}"
                       maxlength="25" required value="<?= h($number) ?>"
                       placeholder="Example: 919876543210">
                <p class="help">Enter country code + number. Do not include a leading plus sign in the saved number.</p>

                <label for="redirect_seconds">Automatic Redirect Timer</label>
                <input id="redirect_seconds" type="number" name="redirect_seconds"
                       min="3" max="120" step="1" required value="<?= $seconds ?>">
                <p class="help">Choose between 3 and 120 seconds.</p>

                <div class="switch">
                    <input id="auto_redirect" type="checkbox"
                           name="auto_redirect" value="1" <?= $auto ? 'checked' : '' ?>>
                    <label for="auto_redirect">
                        Enable Automatic WhatsApp Redirect
                        <small>When enabled, visitors are redirected after the selected delay. No popup is shown.</small>
                    </label>
                </div>

                <label for="whatsapp_message">WhatsApp Message Template</label>
                <textarea id="whatsapp_message" name="whatsapp_message" rows="5" maxlength="1500" required><?= h((string)($settings['whatsapp_message'] ?? 'Hello LAVIE WORLD! Please share your latest handbag collection and prices.')) ?></textarea>
                <p class="help">This message will be prefilled in WhatsApp. For product enquiries, use {product} and {category} as placeholders. The customer must press Send in WhatsApp.</p>

                <label for="whatsapp_message">WhatsApp Message Template</label>
                <textarea id="whatsapp_message" name="whatsapp_message" rows="5" maxlength="1500" required><?= h((string)($settings['whatsapp_message'] ?? 'Hello LAVIE WORLD! Please share your latest handbag collection and prices.')) ?></textarea>
                <p class="help">This message will be prefilled in WhatsApp. For product enquiries, use {product} and {category} as placeholders. The customer must press Send in WhatsApp.</p>

                <hr>

                <label for="new_password">Change Admin Password (Optional)</label>
                <input id="new_password" type="password" name="new_password"
                       minlength="6" autocomplete="new-password"
                       placeholder="Leave blank to keep current password">
                <p class="help">If you change the password, use at least 6 characters. Numbers-only or mixed passwords are allowed.</p>

                <button class="btn" type="submit">SAVE ALL SETTINGS ✓</button>
            </form>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <input type="hidden" name="action" value="logout">
                <button class="logout" type="submit">LOG OUT</button>
            </form>

        <?php endif; ?>
    </div>
</section>
</main>

<footer class="footer">
    LAVIE WORLD · PRIVATE ADMIN AREA<br>
    <a class="dev-whatsapp" href="https://wa.me/917027395027?text=Hello%2C%20I%20need%20developer%20support%20for%20LAVIE%20WORLD." target="_blank" rel="noopener noreferrer" aria-label="Contact developer on WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.85 11.85 0 0 0 12.08 0C5.5 0 .15 5.35.15 11.93c0 2.1.55 4.15 1.6 5.96L0 24l6.27-1.64a11.9 11.9 0 0 0 5.8 1.48h.01c6.58 0 11.93-5.35 11.93-11.93 0-3.19-1.24-6.19-3.49-8.43ZM12.08 21.8a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.72.98.99-3.63-.24-.37a9.84 9.84 0 0 1-1.51-5.26c0-5.46 4.44-9.9 9.9-9.9 2.65 0 5.14 1.03 7.01 2.9a9.84 9.84 0 0 1 2.9 7c0 5.46-4.44 9.9-9.9 9.9Zm5.43-7.41c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.08 4.5.71.31 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.3.17-1.42-.07-.12-.27-.2-.57-.35Z"/></svg>
        Developer WhatsApp Support
    </a>
</footer>
</body>
</html>
