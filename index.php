<?php
session_start();
require "db.php";

function h($t) {
    return htmlspecialchars($t, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $author = trim($_POST["author"] ?? "");
    $body = trim($_POST["body"] ?? "");

    if ($author !== "" && $body !== "") {
        $stmt = $pdo->prepare("INSERT INTO messages (author, body) VALUES (?, ?)");
        $stmt->execute([$author, $body]);
    }

    if (isset($_POST["remember"])) {
        setcookie("name", $author, time() + 60 * 60 * 24 * 30);
    } else {
        setcookie("name", "", time() - 3600);
    }

    header("Location: index.php");
    exit;
}

$_SESSION["visits"] = ($_SESSION["visits"] ?? 0) + 1;

$name = $_COOKIE["name"] ?? "";
$search = trim($_GET["search"] ?? "");

$stmt = $pdo->prepare(
    "SELECT * FROM messages WHERE body ILIKE ? ORDER BY created_at DESC, id DESC"
);
$stmt->execute(["%" . $search . "%"]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Class Wall</title>
<style>
    body { font-family: "Courier New", monospace; max-width: 480px; margin: 40px auto; padding: 0 16px; color: #000; background: #fff; }
    h1 { text-align: center; letter-spacing: 4px; margin-bottom: 4px; }
    .sub { text-align: center; margin-top: 0; }
    hr { border: 0; border-top: 2px dashed #000; margin: 20px 0; }
    input[type=text], textarea { width: 100%; box-sizing: border-box; font-family: inherit; border: 1px solid #000; padding: 8px; margin: 4px 0 12px; }
    button { font-family: inherit; background: #000; color: #fff; border: 0; padding: 10px 16px; cursor: pointer; }
    .row { display: flex; gap: 8px; }
    .row input { margin-bottom: 0; }
    .entry { margin-bottom: 20px; }
    .meta { display: flex; justify-content: space-between; font-size: 13px; }
    .text { margin: 6px 0 0; white-space: pre-wrap; word-wrap: break-word; }
</style>
</head>
<body>
<h1>CLASS WALL</h1>
<p class="sub">Visit #<?= h($_SESSION["visits"]) ?> in this session</p>
<hr>

<form method="POST">
    <label>Name</label>
    <input type="text" name="author" maxlength="50" value="<?= h($name) ?>" required>
    <label>Message</label>
    <textarea name="body" rows="3" required></textarea>
    <label><input type="checkbox" name="remember" <?= $name !== "" ? "checked" : "" ?>> Remember my name</label>
    <br><br>
    <button type="submit">PRINT</button>
</form>

<hr>

<form method="GET" class="row">
    <input type="text" name="search" placeholder="search..." value="<?= h($search) ?>">
    <button type="submit">FIND</button>
</form>

<hr>

<?php if (!$messages): ?>
    <p>Nothing printed yet.</p>
<?php endif; ?>

<?php foreach ($messages as $m): ?>
    <div class="entry">
        <div class="meta">
            <span>No. <?= str_pad($m["id"], 4, "0", STR_PAD_LEFT) ?> / <?= h($m["author"]) ?></span>
            <span><?= h(date("M d, H:i", strtotime($m["created_at"]))) ?></span>
        </div>
        <p class="text"><?= h($m["body"]) ?></p>
    </div>
<?php endforeach; ?>

<hr>
</body>
</html>
