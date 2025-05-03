<?php
require_once 'config.php';
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'adminstrator');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>pastpast</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        header {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        nav {
            background-color: #444;
            padding: 10px;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .main-content {
            padding: 20px 0;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: white;
        }
        .admin-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .admin-section h3 {
            color: #007bff;
            margin-top: 0;
        }
        .admin-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .admin-links a {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
        }
        .admin-links a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>pastpast</h1>
        </div>
    </header>
    
    <nav>
        <div class="container">
            <a href="index.php">首页</a>
            <a href="create.php">创建粘贴</a>
            <?php if ($isLoggedIn): ?>
                <?php if ($isAdmin): ?>
                    <a href="admin_panel.php">管理面板</a>
                <?php endif; ?>
                <a href="logout.php">退出</a>
            <?php else: ?>
                <a href="login.php">登录</a>
                <a href="register.php">注册</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container main-content">
        <h2>欢迎使用PastPast</h2>
        <p>这是一个Pastebin的替代品</p>
        
        <?php if ($isLoggedIn): ?>
            <p>当前登录用户: <?php echo htmlspecialchars($_SESSION['user']); ?>
            <?php if (isset($_SESSION['role'])): ?>
                (角色: <?php echo htmlspecialchars($_SESSION['role']); ?>)
            <?php endif; ?>
            </p>
            <p><a href="create.php" class="button">创建新粘贴</a></p>
        <?php else: ?>
            <p>请<a href="login.php">登录</a>或<a href="register.php">注册</a>后创建粘贴</p>
        <?php endif; ?>
        
        <?php if ($isAdmin && $_SESSION['role'] === 'adminstrator'): ?>
            <div class="admin-section">
                <h3>站长功能</h3>
                <div class="admin-links">
                    <a href="admin_panel.php">管理面板</a>
                    <a href="admin_login.php">管理员登录</a>
                    <a href="admin_logout.php">管理员退出</a>
                    <a href="install.php">系统安装</a>
                </div>
            </div>
        <?php elseif ($isAdmin): ?>
            <div class="admin-section">
                <h3>管理员功能</h3>
                <div class="admin-links">
                    <a href="admin_panel.php">管理面板</a>
                    <a href="admin_logout.php">管理员退出</a>
                </div>
            </div>
        <?php endif; ?>
        <div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">搜索粘贴</h5>
        <form method="get" action="search.php" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="q" class="form-control" placeholder="输入关键词..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="content" <?= ($_GET['type'] ?? 'content') === 'content' ? 'selected' : '' ?>>内容</option>
                    <option value="title" <?= ($_GET['type'] ?? '') === 'title' ? 'selected' : '' ?>>标题</option>
                    <option value="user" <?= ($_GET['type'] ?? '') === 'user' ? 'selected' : '' ?>>用户</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">搜索</button>
            </div>
        </form>
    </div>
</div>
        <h3>最近粘贴</h3>
        <ul>
            <?php
            $files = glob(PASTE_DIR . '*.json');
            if ($files) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $count = 0;
                foreach ($files as $file) {
                    if ($count >= 20) break;
                    
                    $pasteData = json_decode(file_get_contents($file), true);
                    $pasteId = basename($file, '.json');
                    if ($pasteData['expires_at'] > 0 && time() > $pasteData['expires_at']) {
                        unlink($file);
                        continue;
                    }
                    
                    echo '<li><a href="view.php?id=' . $pasteId . '">' . 
                         htmlspecialchars(substr($pasteData['content'], 0, 50)) . '...</a> - ' .
                         date('Y-m-d H:i', $pasteData['created_at']) . '</li>';
                    $count++;
                }
            } else {
                echo '<li>暂无粘贴</li>';
            }
            ?>
        </ul>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> pastpast - By_MPGF</p>
        </div>
    </footer>
</body>
</html>