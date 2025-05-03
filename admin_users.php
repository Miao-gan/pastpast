<?php
// 管理员and站长
$adminUsers = [
    'admin' => [
        'password' => password_hash('管理密码666', // 管理员密码
         PASSWORD_DEFAULT),
        'role' => 'admin'
    ],
    'adminstrator' => [
        'password' => password_hash('站长密码999'//站长密码
        , PASSWORD_DEFAULT), 
        'role' => 'adminstrator'
    ]
];
$adminUsersFile = 'admin_users.dat';
file_put_contents($adminUsersFile, serialize($adminUsers));