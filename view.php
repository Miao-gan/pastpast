<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    die("缺少粘贴ID");
}

$pasteId = $_GET['id'];
$filename = PASTE_DIR . $pasteId . '.json';
if (!preg_match('/^[a-z0-9]+$/', $pasteId) || !file_exists($filename)) {
    die("粘贴不存在或已过期");
}
$pasteData = json_decode(file_get_contents($filename), true);
if ($pasteData['expires_at'] > 0 && time() > $pasteData['expires_at']) {
    unlink($filename);
    die("粘贴已过期");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>查看粘贴 <?php echo htmlspecialchars($pasteId); ?></title>
    <h1><?php echo !empty($pasteData['title']) ? htmlspecialchars($pasteData['title']) : '未命名粘贴'; ?></h1>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { 
            background: #f5f5f5; 
            padding: 15px; 
            border-radius: 5px; 
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .meta { color: #666; margin-bottom: 15px; }
        .actions { margin: 15px 0; }
        .actions a, .actions button { 
            margin-right: 10px;
            color: #06c;
            text-decoration: none;
        }
        button {
            background: none;
            border: none;
            color: #06c;
            cursor: pointer;
            padding: 0;
            font-size: inherit;
        }
    </style>
</head>
<body>
    <h1>粘贴 <?php echo htmlspecialchars($pasteId); ?></h1>
    
    <?php if (isset($_SESSION['user'])): ?>
        <p>当前用户: <?php echo htmlspecialchars($_SESSION['user']); ?> (<a href="logout.php">退出</a>)</p>
    <?php else: ?>
        <p><a href="login.php">登录</a> | <a href="register.php">注册</a></p>
    <?php endif; ?>
    
    <div class="meta">
        <strong>创建时间:</strong> <?php echo date('Y-m-d H:i:s', $pasteData['created_at']); ?><br>
        <?php if ($pasteData['expires_at'] > 0): ?>
            <strong>过期时间:</strong> <?php echo date('Y-m-d H:i:s', $pasteData['expires_at']); ?>
        <?php else: ?>
            <strong>过期时间:</strong> 永不过期
        <?php endif; ?>
    </div>
    
    <div class="actions">
        <a href="create.php">创建新粘贴</a>
        <a href="raw.php?id=<?php echo $pasteId; ?>" target="_blank">查看原始文本(Raw)</a>
        <?php if (isset($_SESSION['user']) && $_SESSION['user'] === $pasteData['user']): ?>
            <form method="post" action="delete.php" style="display: inline;" onsubmit="return confirm('确定要删除这个粘贴吗？');">
                <input type="hidden" name="id" value="<?php echo $pasteId; ?>">
                <button type="submit">删除</button>
            </form>
        <?php endif; ?>
    </div>
    
    <pre><?php 
    if (isset($_GET['highlight'])) {
        echo highlight_search_term($pasteData['content'], $_GET['highlight']);
    } else {
        echo htmlspecialchars($pasteData['content']); 
    }
?></pre>
</body>
</html>