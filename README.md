# Anon PHP Framework

## 状态管理

### 检查登录状态
```php
if (Anon_Check::isLoggedIn()) {
    // 用户已登录
} else {
    // 用户未登录
}
```

### 登录后设置Cookie

```php
Anon_Check::setAuthCookies(123, 'username');
```

### 注销登录
```php
Anon_Check::logout();
```
