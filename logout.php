<?php
// logout.php
session_start();

// Saare session variables ko khali karein
$_SESSION = array();

// Session ko destroy karein
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Wapas login page par redirect karein
header("Location: login.php");
exit;
?>