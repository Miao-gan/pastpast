<?php
require_once 'config.php';

// 验证权限
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'adminstrator') {
    $_SESSION['password_change_error'] = '无操作权限';
    header("Location: admin_login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_panel.php?tab=admins");
    exit;
}
$username = trim($_POST['username'] ?? '');
$newPassword = $_POST['new_password'] ?? '';
if (empty($username) || empty($newPassword)) {
    $_SESSION['password_change_error'] = '用户名和新密码不能为空';
    header("Location: admin_panel.php?tab=admins");
    exit;
}

if (strlen($newPassword) < 8) {
    $_SESSION['password_change_error'] = '密码长度至少8位';
    header("Location: admin_panel.php?tab=admins");
    exit;
}

// 加载管理员数据
$adminUsersFile = ADMIN_USERS_FILE;
if (!file_exists($adminUsersFile)) {
    $_SESSION['password_change_error'] = '管理员数据文件丢失';
    header("Location: admin_panel.php?tab=admins");
    exit;
}

$adminUsers = unserialize(file_get_contents($adminUsersFile));
if (!isset($adminUsers[$username])) {
    $_SESSION['password_change_error'] = '指定的用户不存在';
    header("Location: admin_panel.php?tab=admins");
    exit;
}
$adminUsers[$username]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
if (file_put_contents($adminUsersFile, serialize($adminUsers), LOCK_EX)) {
    $_SESSION['password_change_success'] = '密码修改成功';
    if ($username === $_SESSION['user']) {
        $_SESSION = array();
        session_destroy();
        header("Location: admin_login.php");
        exit;
    }
} else {
    $_SESSION['password_change_error'] = '密码修改失败，请检查文件权限';
}

header("Location: admin_panel.php?tab=admins");
exit;
?>