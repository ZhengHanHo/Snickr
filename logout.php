<?php
/**
 * Created by PhpStorm.
 * User: Jiaqi Li
 * Date: 2019/5/6
 * Time: 10:30
 */
// 初始化会话。
session_start();
$_SESSION = array ();
if (isset ( $_COOKIE [session_name ()] )) {
    setcookie ( session_name (), '', time () - 3600, '/' );
}
session_destroy (); // 最后彻底销毁session.
//var_dump($_SESSION);
//echo "logout";
echo '<script>alert("You\'ve logged out successfully!");location.href="index.php"</script>';
// 重置会话中的所有变量
//$_SESSION = array();
//session_destroy();
//header("Location: index.php");