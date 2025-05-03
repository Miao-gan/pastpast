<?php
define('PASTE_DIR', 'pastes/');          // 存储粘贴内容的目录
define('USER_DIR', 'users/');            // 存储用户数据的目录
define('MAX_PASTE_SIZE', 1000000);        // 最大粘贴大小(字节)
define('MIN_PASTE_LIFETIME', 86400);     // 最小存活时间(1天)
define('SESSION_TIMEOUT', 3600);         // 会话超时时间(1小时)
define('ADMIN_USERS_FILE', 'admin_users.dat');
if (!file_exists(PASTE_DIR)) {
    mkdir(PASTE_DIR, 0755, true);
}
if (!file_exists(USER_DIR)) {
    mkdir(USER_DIR, 0755, true);
}
define('BASE_PATH', realpath(__DIR__));
function validatePath($path) {
    if (strpos(realpath($path), BASE_PATH) !== 0) {
        die("非法路径访问!");
    }
}
function highlight_search_term($text, $term) {
    return preg_replace(
        '/(' . preg_quote($term, '/') . ')/i',
        '<span class="search-highlight">$1</span>',
        htmlspecialchars($text)
    );
}
session_start();
?>