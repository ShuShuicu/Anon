<?php

/**
 * Anon Database
 */
if (!defined('ANON_ALLOWED_ACCESS')) exit;

class Anon_Database
{
    private $pdo;

    /**
     * 构造函数：初始化数据库连接
     */
    public function __construct()
    {
        $this->conn = new mysqli(
            ANON_DB_HOST,
            ANON_DB_USER,
            ANON_DB_PASSWORD,
            ANON_DB_DATABASE,
            ANON_DB_PORT
        );

        if ($this->conn->connect_error) {
            die("数据库连接失败: " . $this->conn->connect_error);
        }

        $this->conn->set_charset(ANON_DB_CHARSET);
    }
    /**
     * 执行查询并返回结果
     * @param string $sql SQL 查询语句
     * @return array 查询结果
     */
    public function query($sql)
    {
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL 查询错误: " . $this->conn->error);
        }

        // 如果是 SELECT 查询，返回结果数组
        if ($result instanceof mysqli_result) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }

        // 返回受影响的行数
        return $this->conn->affected_rows;
    }

    /**
     * 准备并执行预处理语句
     * @param string $sql SQL 查询语句
     * @param array $params 参数数组
     * @return bool|mysqli_stmt 预处理语句对象
     */
    public function prepare($sql, $params = [])
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("SQL 预处理错误: " . $this->conn->error);
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // 默认所有参数为字符串
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * 获取用户信息
     * @param int $uid 用户 ID
     * @return array 用户信息
     */
    public function getUserInfo($uid)
    {
        $sql = "SELECT * FROM " . ANON_DB_PREFIX . "users WHERE uid = ?";
        $stmt = $this->prepare($sql, [$uid]);
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $name, $password, $email, $group);
            $stmt->fetch();
            return [
                'uid' => $userId,
                'name' => $name,
                'email' => $email,
                'group' => $group
            ];
        }

        return null; // 用户不存在
    }

    /**
     * 关闭数据库连接
     */
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
