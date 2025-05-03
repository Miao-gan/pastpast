<?php
require_once 'config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    $lifetime = isset($_POST['expiry']) ? (int)$_POST['expiry'] : 0;
    $pasteData['expires_at'] = $lifetime > 0 ? time() + $lifetime : 0;
    if (strlen($content) > MAX_PASTE_SIZE) {
        $error = "粘贴内容过长，最大允许 " . MAX_PASTE_SIZE . " 字节";
    } elseif ($lifetime > 0 && $lifetime < MIN_PASTE_LIFETIME) {
        $error = "存活时间不能小于1天";
    } else {
        $pasteId = uniqid();
        $filename = PASTE_DIR . $pasteId . '.json';
        $pasteData = [
            'content' => $content,
            'created_at' => time(),
            'expires_at' => $lifetime > 0 ? time() + $lifetime : 0,
            'user' => $_SESSION['user']
        ];
        $title = isset($_POST['title']) ? substr($_POST['title'], 0, 100) : '';
        $pasteData['title'] = $title;
        if (file_put_contents($filename, json_encode($pasteData))) {
            header("Location: view.php?id=" . $pasteId);
            exit;
        } else {
            $error = "创建粘贴失败，请重试";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>创建新粘贴</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-title {
            color: #0d6efd;
            margin-bottom: 25px;
            text-align: center;
        }
        .expiry-options .form-check {
            margin-right: 15px;
        }
        #content {
            min-height: 300px;
            font-family: 'Courier New', monospace;
        }
        .btn-submit {
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-paste me-2"></i>创建新粘贴</h2>
            
            <?php if (isset($_SESSION['user'])): ?>
                <div class="alert alert-info mb-4">
                    当前用户: <?php echo htmlspecialchars($_SESSION['user']); ?> 
                    (<a href="logout.php" class="alert-link">退出</a>)
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-4">
                    您尚未登录 (<a href="login.php" class="alert-link">登录</a> | 
                    <a href="register.php" class="alert-link">注册</a>)
                </div>
            <?php endif; ?>
            
            <form action="create.php" method="post">
                <div class="mb-4">
                    <label for="title" class="form-label">标题 (可选)</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="输入粘贴标题...">
                </div>
                
                <div class="mb-4">
                    <label for="content" class="form-label">内容 <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" required placeholder="在此输入您要粘贴的内容..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">过期时间</label>
                    <div class="expiry-options d-flex flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="expiry" id="expiry1h" value="3600" checked>
                            <label class="form-check-label" for="expiry1h">1小时</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="expiry" id="expiry1d" value="86400">
                            <label class="form-check-label" for="expiry1d">1天</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="expiry" id="expiry1w" value="604800">
                            <label class="form-check-label" for="expiry1w">1周</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="expiry" id="expiry1m" value="2592000">
                            <label class="form-check-label" for="expiry1m">1个月</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="expiry" id="expiryNever" value="0">
                            <label class="form-check-label" for="expiryNever">永不过期</label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="syntax" class="form-label">语法高亮 (可选)</label>
                    <select class="form-select" id="syntax" name="syntax">
                        <option value="none" selected>无</option>
                        <option value="php">PHP</option>
                        <option value="javascript">JavaScript</option>
                        <option value="html">HTML</option>
                        <option value="css">CSS</option>
                        <option value="sql">SQL</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fas fa-save me-2"></i>创建粘贴
                </button>
            </form>
            <a href="index.php" class="btn btn-secondary mt-3">返回主页</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
