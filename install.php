<?php
// 创建目录
function createDir($path) {
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
        file_put_contents($path . '/.htaccess', "Deny from all");
    }
}

// 创建目录
createDir('pastes');
createDir('users');
createDir('admin');

// 默认配置
$config = [
    'PASTE_DIR' => 'pastes/',
    'USER_DIR' => 'users/',
    'MAX_PASTE_SIZE' => 1048576,    // 1MB
    'MIN_PASTE_LIFETIME' => 86400,  // 1天
    'SESSION_TIMEOUT' => 3600,      // 1小时
    'ALLOW_REGISTER' => 1
];

// 保存配置文件
file_put_contents('dynamic_config.json', json_encode($config, JSON_PRETTY_PRINT));

// 创建默认管理员账户
$adminUsers = [
    'adminstrator' => [
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'adminstrator',
        'created_at' => time()
    ]
];

// 保存管理员账户信息
file_put_contents('admin_users.dat', serialize($adminUsers));

// 创建安全文件
$securityFiles = [
    '.htaccess' => "Order deny,allow\nDeny from all",
    'index.html' => '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1></body></html>'
];

foreach ($securityFiles as $file => $content) {
    foreach (['pastes', 'users', 'admin'] as $dir) {
        file_put_contents("$dir/$file", $content);
    }
}

// 输出安装完成页面
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>系统安装完成</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-body text-center">
                <h2 class="text-success mb-4">✔️ 系统安装成功！</h2>
                <div class="alert alert-warning">
                    <h5>重要安全提示：</h5>
                    <ol class="text-start">
                        <li>立即删除本文件 (install.php)</li>
                        <li>修改站长默认密码 (默认密码: admin123)</li>
                        <li>检查目录权限：
                            <ul>
                                <li>pastes/ 目录应设置为 755</li>
                                <li>users/ 目录应设置为 700</li>
                            </ul>
                        </li>
                    </ol>
                </div>
                <div class="d-grid gap-2">
                    <a href="admin_login.php" class="btn btn-lg btn-primary">进入管理登录</a>
                    <a href="../" class="btn btn-lg btn-secondary">返回网站首页</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

// 自动删除安装文件（取消注释生效）
// unlink(__FILE__);
