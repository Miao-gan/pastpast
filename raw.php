<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("HTTP/1.0 404 Not Found");
    die("缺少粘贴ID");
}

$pasteId = $_GET['id'];
$filename = PASTE_DIR . $pasteId . '.json';
if (!preg_match('/^[a-z0-9]+$/', $pasteId) || !file_exists($filename)) {
    header("HTTP/1.0 404 Not Found");
    die("粘贴不存在或已过期");
}
$pasteData = json_decode(file_get_contents($filename), true);
if ($pasteData['expires_at'] > 0 && time() > $pasteData['expires_at']) {
    unlink($filename);
    header("HTTP/1.0 404 Not Found");
    die("粘贴已过期");
}
header("Content-Type: text/plain");
echo $pasteData['content'];
?>