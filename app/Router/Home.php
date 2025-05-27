<?php
if (!defined('ANON_ALLOWED_ACCESS')) exit;
// 开启会话，确保全局会话已启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = ''; // 用于存储错误信息

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 使用 filter_input 获取和过滤输入数据，防止 XSS 和空值情况
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    if (empty($username) || empty($password)) {
        $error = "用户名和密码均不能为空";
    } else {
        // 创建 DB 实例
        global $anon_Db;
        $db = new Anon_Database($anon_Db);

        // 查询用户信息
        $sql = "SELECT uid, name, password, email FROM " . ANON_DB_PREFIX . "users WHERE name = ?";
        $stmt = $db->prepare($sql, [$username]);

        if ($stmt) {
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // 绑定返回字段
                $stmt->bind_result($uid, $name, $hashedPassword, $email);
                $stmt->fetch();

                // 验证密码
                if (password_verify($password, $hashedPassword)) {
                    // 登录成功，重置会话ID以防会话固定攻击
                    session_regenerate_id(true);

                    // 设置会话变量
                    $_SESSION['user_id'] = $uid;
                    $_SESSION['username'] = $name;

                    // 设置安全性更高的 Cookie，确保 HttpOnly
                    $cookieOptions = [
                        'expires'  => time() + 86400,
                        'path'     => '/',
                        'httponly' => true,
                        'secure'   => true,
                        'samesite' => 'Lax'
                    ];
                    setcookie('user_id', $uid, $cookieOptions);
                    setcookie('username', $name, $cookieOptions);

                    // 重定向到首页
                    header("Location: /");
                    exit;
                } else {
                    $error = "用户名或密码错误";
                }
            } else {
                $error = "用户名或密码错误";
            }
        } else {
            $error = "数据库查询错误";
        }
    }
}
?>
<?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (Anon_Check::isLoggedIn()) {
    echo '欢迎  ' . htmlspecialchars($_SESSION['username']) . ';
    <a href="/anon/logout">注销</a>';
} else {
?>
    <form method="post" action="">
        <input type="text" name="username" placeholder="User Name" />
        <input type="password" name="password" placeholder="Password" />
        <button type="submit">登录</button>
    </form>
<?php
} ?>