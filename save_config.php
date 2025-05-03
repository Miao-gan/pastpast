<?php
require_once 'config.php';

// 权限验证
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'adminstrator') {
    die(json_encode(['status' => 'error', 'message' => '无操作权限']));
}

// 配置文件路径
$configPath = __DIR__ . '/dynamic_config.json';
$minValues = [
    'MAX_PASTE_SIZE' => 100000,
    'MIN_PASTE_LIFETIME' => 3600,
    'SESSION_TIMEOUT' => 300
];

// 加载现有配置
$currentConfig = json_decode(file_get_contents($configPath), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 接收并验证参数
        $newConfig = [
            'PASTE_DIR' => rtrim($_POST['PASTE_DIR'] ?? PASTE_DIR, '/') . '/',
            'USER_DIR' => rtrim($_POST['USER_DIR'] ?? USER_DIR, '/') . '/',
            'MAX_PASTE_SIZE' => max(
                $minValues['MAX_PASTE_SIZE'],
                intval($_POST['MAX_PASTE_SIZE'] ?? MAX_PASTE_SIZE)
            ),
            'MIN_PASTE_LIFETIME' => max(
                $minValues['MIN_PASTE_LIFETIME'],
                intval($_POST['MIN_PASTE_LIFETIME'] ?? MIN_PASTE_LIFETIME)
            ),
            'SESSION_TIMEOUT' => max(
                $minValues['SESSION_TIMEOUT'],
                intval($_POST['SESSION_TIMEOUT'] ?? SESSION_TIMEOUT)
            )
        ];

        // 验证目录路径
        $dirChecks = [
            'PASTE_DIR' => $newConfig['PASTE_DIR'],
            'USER_DIR' => $newConfig['USER_DIR']
        ];
        
        foreach ($dirChecks as $key => $path) {
            if (!is_dir($path) && !mkdir($path, 0755, true)) {
                throw new Exception("目录创建失败: $path");
            }
            if (!is_writable($path)) {
                throw new Exception("目录不可写: $path");
            }
        }

        // 保存配置
        file_put_contents($configPath, json_encode($newConfig, JSON_PRETTY_PRINT));
        
        // 更新Session配置
        ini_set('session.gc_maxlifetime', $newConfig['SESSION_TIMEOUT']);
        
        $_SESSION['config_updated'] = [
            'status' => 'success',
            'message' => '配置更新成功，部分变更需要重启服务生效'
        ];
    } catch (Exception $e) {
        $_SESSION['config_updated'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
    
    header("Location: admin_panel.php?tab=config");
    exit;
}

// 获取当前配置
$config = array_merge([
    'PASTE_DIR' => PASTE_DIR,
    'USER_DIR' => USER_DIR,
    'MAX_PASTE_SIZE' => MAX_PASTE_SIZE,
    'MIN_PASTE_LIFETIME' => MIN_PASTE_LIFETIME,
    'SESSION_TIMEOUT' => SESSION_TIMEOUT
], $currentConfig);
?>