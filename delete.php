<?php
require_once 'config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $pasteId = $_POST['id'];
    $filename = PASTE_DIR . $pasteId . '.json';
    if (preg_match('/^[a-z0-9]+$/', $pasteId) && file_exists($filename)) {
        $pasteData = json_decode(file_get_contents($filename), true);
        
        if ($_SESSION['user'] === $pasteData['user']) {
            unlink($filename);
        }
    }
}

header("Location: create.php");
exit;
?>