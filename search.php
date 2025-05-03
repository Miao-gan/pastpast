<?php
require_once 'config.php';

$searchQuery = trim($_GET['q'] ?? '');
$searchType = $_GET['type'] ?? 'content';
$results = [];

if (!empty($searchQuery)) {
    $files = glob(PASTE_DIR . '*.json');
    
    foreach ($files as $file) {
        $pasteData = json_decode(file_get_contents($file), true);
        $pasteId = basename($file, '.json');
        
        if ($pasteData['expires_at'] > 0) {
            echo date('Y-m-d H:i:s', $pasteData['expires_at']);
        } else {
            echo '永不过期';
        }
        
        // 根据搜索类型匹配
        $match = false;
        switch ($searchType) {
            case 'title':
                $match = stripos($pasteData['title'] ?? '', $searchQuery) !== false;
                break;
            case 'user':
                $match = stripos($pasteData['user'] ?? '', $searchQuery) !== false;
                break;
            default:
                $match = stripos($pasteData['content'], $searchQuery) !== false;
        }
        
        if ($match) {
            $results[] = [
                'id' => $pasteId,
                'title' => $pasteData['title'] ?? '',
                'content' => $pasteData['content'],
                'created_at' => $pasteData['created_at'],
                'user' => $pasteData['user'] ?? '匿名',
                'preview' => substr($pasteData['content'], 0, 100)
            ];
        }
    }
    
    // 按创建时间排序
    usort($results, function($a, $b) {
        return $b['created_at'] - $a['created_at'];
    });
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>搜索粘贴 - pastpast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .search-highlight {
            background-color: yellow;
            font-weight: bold;
        }
        .result-card {
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }
        .preview-text {
            color: #6c757d;
            font-size: 0.9em;
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
            <?php if (isset($_SESSION['user'])): ?>
                <a href="logout.php">退出</a>
            <?php else: ?>
                <a href="login.php">登录</a>
                <a href="register.php">注册</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container main-content mt-4">
        <h2>搜索结果</h2>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="search.php" class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="q" class="form-control" placeholder="输入关键词..." value="<?= htmlspecialchars($searchQuery) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="content" <?= $searchType === 'content' ? 'selected' : '' ?>>内容</option>
                            <option value="title" <?= $searchType === 'title' ? 'selected' : '' ?>>标题</option>
                            <option value="user" <?= $searchType === 'user' ? 'selected' : '' ?>>用户</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">搜索</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (!empty($searchQuery)): ?>
            <div class="mb-3">
                <p>找到 <?= count($results) ?> 个匹配 "<?= htmlspecialchars($searchQuery) ?>" 的结果</p>
            </div>
            
            <?php if (!empty($results)): ?>
                <div class="list-group">
                    <?php foreach ($results as $result): ?>
                        <div class="card result-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="view.php?id=<?= $result['id'] ?>">
                                        <?= !empty($result['title']) ? htmlspecialchars($result['title']) : '未命名粘贴' ?>
                                    </a>
                                </h5>
                                <p class="card-text preview-text">
                                    <?= $searchType === 'content' ? 
                                        highlight_search($result['preview'], $searchQuery) : 
                                        htmlspecialchars($result['preview']) ?>
                                </p>
                                <div class="text-muted small">
                                    由 <?= htmlspecialchars($result['user']) ?> 创建于 
                                    <?= date('Y-m-d H:i', $result['created_at']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">没有找到匹配的结果</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning">请输入搜索关键词</div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> pastpast</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function highlight_search($text, $query) {
    $highlighted = preg_replace(
        '/(' . preg_quote($query, '/') . ')/i',
        '<span class="search-highlight">$1</span>',
        htmlspecialchars($text)
    );
    return $highlighted;
}
?>