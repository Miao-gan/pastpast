<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || preg_match('/[\/\\\\]/', $username)) {
        $error = "用户名包含非法字符";
    } elseif (file_exists(USER_DIR . $username . '.dat')) {
        $error = "用户名已存在";
    } elseif (strlen($password) < 6) {
        $error = "密码至少6位";
    } else {
        $userData = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => time()
        ];
        $filePath = USER_DIR . $username . '.dat';
        if (file_put_contents($filePath, serialize($userData), LOCK_EX)) {
            header('Location: login.php');
            exit;
        } else {
            $error = "注册失败，请检查目录权限";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户注册</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: white;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background: #e9ecef;
            border-radius: 3px;
        }
        .strength-0 { width: 20%; background: #dc3545; }
        .strength-1 { width: 40%; background: #fd7e14; }
        .strength-2 { width: 60%; background: #ffc107; }
        .strength-3 { width: 80%; background: #28a745; }
        .strength-4 { width: 100%; background: #20c997; }
    </style>
</head>
<body style="background-color: #f8f9fc;">
    <div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>用户注册</h2>
            
            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">用户名</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">电子邮箱</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">密码</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="password-strength strength-0" id="passwordStrength"></div>
                    <small class="text-muted">至少8个字符，包含大小写字母和数字</small>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">确认密码</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                    <i class="fas fa-user-plus me-2"></i>立即注册
                </button>
                
                <div class="text-center">
                    <a href="login.php" class="text-decoration-none">已有账号？立即登录</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.getElementById('password').addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            const strengthBar = document.getElementById('passwordStrength');
            strengthBar.className = 'password-strength strength-' + strength;
        });
        
        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            return Math.min(strength, 4);
        }
    </script>
</body>
</html>