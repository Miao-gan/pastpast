<?php
require_once 'config.php';
$adminUsersFile = 'admin_users.dat';

if (!file_exists($adminUsersFile)) {
    die("管理员信息文件不存在，请联系网站管理员");
}

$adminUsers = unserialize(file_get_contents($adminUsersFile));

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (isset($adminUsers[$username])) {
        if (password_verify($password, $adminUsers[$username]['password'])) {
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $adminUsers[$username]['role'];
            header('Location: admin_panel.php');
            exit;
        } else {
            $error = "密码错误";
        }
    } else {
        $error = "用户名不存在";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
        }
        .form-floating label {
            color: #6c757d;
        }
        .btn-login {
            background: linear-gradient(to right, #4e73df, #224abe);
            border: none;
            padding: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body style="background-color: #f8f9fc;">
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt me-2"></i>管理员登录</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="用户名" required>
                    <label for="username">用户名</label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="密码" required>
                    <label for="password">密码</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>登录
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>