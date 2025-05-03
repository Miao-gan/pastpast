<?php
require_once 'config.php';
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || 
   ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'adminstrator')) {
    header("Location: admin_login.php");
    exit;
}
$adminUsersFile = ADMIN_USERS_FILE;
if (!file_exists($adminUsersFile)) {
    die("管理员数据文件丢失，请联系系统管理员");
}

// 处理删除用户
if (isset($_GET['delete_user'])) {
    $username = $_GET['delete_user'];
    $userFile = USER_DIR . $username . '.dat';
    if (file_exists($userFile)) {
        unlink($userFile);
    }
    foreach (glob(PASTE_DIR . '*.json') as $pasteFile) {
        $pasteData = json_decode(file_get_contents($pasteFile), true);
        if ($pasteData['user'] === $username) {
            unlink($pasteFile);
        }
    }
    header("Location: admin_panel.php?tab=users");
    exit;
}

// 处理添加新管理员
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_admin'])) {
    if ($_SESSION['role'] === 'adminstrator') {
        $newAdmin = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        $adminUsers = unserialize(file_get_contents($adminUsersFile));
        if (!isset($adminUsers[$newAdmin])) {
            $adminUsers[$newAdmin] = [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'created_at' => time()
            ];
            file_put_contents($adminUsersFile, serialize($adminUsers));
        }
    }
    header("Location: admin_panel.php?tab=admins");
    exit;
}

// 处理删除管理员
if (isset($_GET['delete_admin'])) {
    if ($_SESSION['role'] === 'adminstrator') {
        $adminUsers = unserialize(file_get_contents($adminUsersFile));
        unset($adminUsers[$_GET['delete_admin']]);
        file_put_contents($adminUsersFile, serialize($adminUsers));
    }
    header("Location: admin_panel.php?tab=admins");
    exit;
}

// 加载数据
$adminUsers = unserialize(file_get_contents($adminUsersFile));
$currentTab = $_GET['tab'] ?? 'users';
$dynamicConfig = json_decode(file_get_contents('dynamic_config.json'), true);
$minValues = [
    'MAX_PASTE_SIZE' => 1000000,
    'MIN_PASTE_LIFETIME' => 3600,
    'SESSION_TIMEOUT' => 300
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>管理面板</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pre-content { 
            max-height: 400px; 
            overflow: auto;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .config-table td { vertical-align: middle; }
        .password-form { background: #f8f9fa; padding: 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>管理面板 <small class="text-muted">(当前角色: <?= $_SESSION['role'] ?>)</small></h2>
            <a href="admin_logout.php" class="btn btn-danger">退出登录</a>
        </div>

        <!-- 显示操作结果消息 -->
        <?php if (isset($_SESSION['password_change_error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['password_change_error'] ?></div>
            <?php unset($_SESSION['password_change_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['password_change_success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['password_change_success'] ?></div>
            <?php unset($_SESSION['password_change_success']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- 导航菜单 -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="?tab=users" class="list-group-item list-group-item-action <?= $currentTab === 'users' ? 'active' : '' ?>">
                        用户管理
                    </a>
                    <a href="?tab=pastes" class="list-group-item list-group-item-action <?= $currentTab === 'pastes' ? 'active' : '' ?>">
                        粘贴管理
                    </a>
                    <?php if ($_SESSION['role'] === 'adminstrator'): ?>
                        <a href="?tab=admins" class="list-group-item list-group-item-action <?= $currentTab === 'admins' ? 'active' : '' ?>">
                            管理员管理
                        </a>
                        <a href="?tab=config" class="list-group-item list-group-item-action <?= $currentTab === 'config' ? 'active' : '' ?>">
                            系统配置
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 内容区域 -->
            <div class="col-md-9">
                <?php if ($currentTab === 'users'): ?>
                    <!-- 用户管理 -->
                    <h4 class="mb-3">用户管理</h4>
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>用户名</th>
                                <th>注册时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (glob(USER_DIR . '*.dat') as $file): ?>
                                <?php $user = unserialize(file_get_contents($file)); ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= date('Y-m-d H:i', $user['created_at']) ?></td>
                                    <td>
                                        <a href="?delete_user=<?= $user['username'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('确定删除该用户及其所有粘贴？')">
                                           删除
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php elseif ($currentTab === 'admins' && $_SESSION['role'] === 'adminstrator'): ?>
                    <!-- 管理员管理 -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">管理员管理</h4>
                            
                            <!-- 添加管理员表单 -->
                            <form method="post" class="mb-4">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <input type="text" name="username" class="form-control" placeholder="用户名" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="password" name="password" class="form-control" placeholder="密码" required>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="role" class="form-select">
                                            <option value="admin">普通管理员</option>
                                            <option value="adminstrator">站长</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" name="new_admin" class="btn btn-primary w-100">添加</button>
                                    </div>
                                </div>
                            </form>

                            <!-- 管理员列表 -->
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>用户名</th>
                                        <th>角色</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($adminUsers as $username => $data): ?>
                                        <tr>
                                            <td><?= $username ?></td>
                                            <td><?= $data['role'] ?></td>
                                            <td><?= date('Y-m-d H:i', $data['created_at']) ?></td>
                                            <td>
                                                <?php if ($username !== $_SESSION['user']): ?>
                                                    <a href="?delete_admin=<?= $username ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('确定删除该管理员？')">
                                                       删除
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <!-- 修改密码表单 -->
                            <div class="password-form mt-4">
                                <h5 class="mb-3">修改管理员密码</h5>
                                <form method="post" action="admin_change_password.php">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <select name="username" class="form-select" required>
                                                <option value="">选择管理员</option>
                                                <?php foreach ($adminUsers as $username => $data): ?>
                                                    <option value="<?= $username ?>"><?= $username ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="password" name="new_password" class="form-control" placeholder="新密码" required minlength="8">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-warning w-100">修改密码</button>
                                        </div>
                                    </div>
                                    <small class="text-muted">密码至少需要8个字符</small>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif ($currentTab === 'config' && $_SESSION['role'] === 'adminstrator'): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">系统配置</h4>
                            
                            <?php if (isset($_SESSION['config_updated'])): ?>
                                <div class="alert alert-<?= $_SESSION['config_updated']['status'] ?>">
                                    <?= $_SESSION['config_updated']['message'] ?>
                                </div>
                                <?php unset($_SESSION['config_updated']); ?>
                            <?php endif; ?>

                            <form method="post" action="save_config.php">
                                <div class="row g-3 mb-4">
                                    <!-- 存储目录 -->
                                    <div class="col-md-6">
                                        <label class="form-label">粘贴存储目录</label>
                                        <input type="text" class="form-control" 
                                            name="PASTE_DIR" 
                                            value="<?= htmlspecialchars($dynamicConfig['PASTE_DIR'] ?? PASTE_DIR) ?>"
                                            required>
                                        <small class="text-muted">必须以斜杠结尾 (如: pastes/)</small>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">用户数据目录</label>
                                        <input type="text" class="form-control"
                                            name="USER_DIR"
                                            value="<?= htmlspecialchars($dynamicConfig['USER_DIR'] ?? USER_DIR) ?>"
                                            required>
                                    </div>

                                    <!-- 大小限制 -->
                                    <div class="col-md-4">
                                        <label class="form-label">最大粘贴大小 (字节)</label>
                                        <input type="number" class="form-control"
                                            name="MAX_PASTE_SIZE"
                                            value="<?= htmlspecialchars($dynamicConfig['MAX_PASTE_SIZE'] ?? MAX_PASTE_SIZE) ?>"
                                            min="<?= $minValues['MAX_PASTE_SIZE'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">最小存活时间 (秒)</label>
                                        <input type="number" class="form-control"
                                            name="MIN_PASTE_LIFETIME"
                                            value="<?= htmlspecialchars($dynamicConfig['MIN_PASTE_LIFETIME'] ?? MIN_PASTE_LIFETIME) ?>"
                                            min="<?= $minValues['MIN_PASTE_LIFETIME'] ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">会话超时 (秒)</label>
                                        <input type="number" class="form-control"
                                            name="SESSION_TIMEOUT"
                                            value="<?= htmlspecialchars($dynamicConfig['SESSION_TIMEOUT'] ?? SESSION_TIMEOUT) ?>"
                                            min="<?= $minValues['SESSION_TIMEOUT'] ?>" required>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">保存配置</button>
                            </form>
                        </div>
                    </div>

                <?php elseif ($currentTab === 'pastes'): ?>
                    <!-- 粘贴管理 -->
                    <h4 class="mb-3">粘贴管理</h4>
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>标题</th>
                                <th>用户</th>
                                <th>创建时间</th>
                                <th>过期时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (glob(PASTE_DIR . '*.json') as $file): ?>
                                <?php 
                                    $pasteData = json_decode(file_get_contents($file), true);
                                    $pasteId = basename($file, '.json');
                                    
                                    // 检查是否已过期
                                    if ($pasteData['expires_at'] > 0 && time() > $pasteData['expires_at']) {
                                        unlink($file);
                                        continue;
                                    }
                                ?>
                                <tr>
                                    <td><?= $pasteId ?></td>
                                    <td><?= htmlspecialchars($pasteData['title'] ?? '无标题') ?></td>
                                    <td><?= htmlspecialchars($pasteData['user'] ?? '匿名') ?></td>
                                    <td><?= date('Y-m-d H:i', $pasteData['created_at']) ?></td>
                                    <td>
                                        <?= $pasteData['expires_at'] > 0 ? date('Y-m-d H:i', $pasteData['expires_at']) : '永不过期' ?>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= $pasteId ?>" class="btn btn-sm btn-info" target="_blank">查看</a>
                                        <a href="delete.php?id=<?= $pasteId ?>" class="btn btn-sm btn-danger" onclick="return confirm('确定删除此粘贴？')">删除</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>