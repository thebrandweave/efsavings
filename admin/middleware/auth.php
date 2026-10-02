<?php
require_once($menuPath . "../config/JWT.php");

function verifyAuth()
{
    global $menuPath;
    $loginRedirect = ($menuPath ?? "./") . "login.php";

    // Check for JWT token in cookie
    if (!isset($_COOKIE['admin_token'])) {
        if (!headers_sent()) {
            header("Location: " . $loginRedirect);
        } else {
            echo "<script>window.location.href='" . addslashes($loginRedirect) . "';</script>";
        }
        exit();
    }

    $token = $_COOKIE['admin_token'];
    $decoded = JWTManager::verifyToken($token);

    if (!$decoded) {
        // Token is invalid or expired
        if (!headers_sent()) {
            setcookie('admin_token', '', time() - 3600, '/');
            header("Location: " . $loginRedirect);
        } else {
            echo "<script>document.cookie='admin_token=; Max-Age=-3600; path=/;'; window.location.href='" . addslashes($loginRedirect) . "';</script>";
        }
        exit();
    }

    // Token is valid, refresh it if headers are not yet sent
    $newToken = JWTManager::refreshToken($token);
    if ($newToken && !headers_sent()) {
        setcookie(
            'admin_token',
            $newToken,
            time() + 3600,
            '/',
            '',
            true,
            true
        );
    }

    // Set session variables from token
    $_SESSION['admin_id'] = $decoded->admin_id;
    $_SESSION['admin_email'] = $decoded->email;
    $_SESSION['admin_role'] = $decoded->role;

    return true;
}
