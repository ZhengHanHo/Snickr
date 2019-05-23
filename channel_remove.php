<?php
include 'functions.php';
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:
    $conn = custom_connect();
if (isset($_GET['uname'])): //当用户点击remove按钮时重新加载页面
    //$admin = $_GET['admin'];$cname = $_GET['cname'];$wname = $_GET['wname'];
    $adminID = $_GET['admin']; $admin = getUsername($adminID);
    $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
    $channelID = $_GET['cname']; $cname = getChannelName($workspaceID, $channelID);
    $deleteUserNameID = $_GET['uname']; $deleteUserName = getUsername($deleteUserNameID);
    ?>
    <?php
    $query_remove_channel = "delete from cu where uid = '$deleteUserNameID' and wid = '$workspaceID' and cid = '$channelID';";
    $result_remove_channel = mysqli_query($conn, $query_remove_channel);
    if (!$result_remove_channel):
        echo '<script>alert("Error! Can\'t remove the user from channel!");</script>';
    endif;
    $url = "channel_remove.php?admin=$adminID&wname=$workspaceID&cname=$channelID";
    echo "<script type='text/javascript'>";
    echo "alert(\"You removed user $deleteUserName successfully!\");";
    echo " location.href='$url';";
    echo "</script>";
    /************************************************************-->
                 数据库将deleteUserName从wname中的cname删除
    **********************************************************/
    ?>


<?php
else: //页面被第一次加载时，从channel_home页面获得admin和cname的参数
    //$admin = $_GET['admin'];$cname = $_GET['cname'];$wname = $_GET['wname'];
    $adminID = $_GET['admin']; $admin = getUsername($adminID);
    $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
    $channelID = $_GET['cname']; $cname = getChannelName($workspaceID, $channelID);
    ?>
<?php endif;
mysqli_close($conn);
 endif; ?>
<?php
$conn = custom_connect();
$notification_num = notification_num($adminID); $user_num = 0;
$username = array(); $email = array();
$query_remove_user = "select uid, email
from cu natural join users
where wid = '$workspaceID' and cid = '$channelID' and uid != '$adminID';";
$result_remove_user = mysqli_query($conn, $query_remove_user);
while ($row = mysqli_fetch_array($result_remove_user)) {
    $username[] = $row['uid'];
    $email[] = $row['email'];
    $user_num += 1;
}
mysqli_close($conn);
//$username=['James', 'Linda', 'Michael'];
//$email=['james@gmail.com', 'linda@gmail.com', 'michael@gmail.com'];
/************************************************************
 * 数据库查询wname中cname所有人的姓名(除了admin自己)并赋给$username
 * 数据库查询wname中cname所有人邮箱(除了admin自己)并赋给$email
 * 数据库查询wname中cname所有人的个数(除了admin自己)并赋给$user_num
 * 数据库查询admin未处理的通知的数量并赋给$notification_num
 **********************************************************/
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Channel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 引入 Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Bootstrap 的 JavaScript 插件需要引入 jQuery -->
<script src="https://code.jquery.com/jquery.js"></script>
<!-- 插件Bootstrap 的 下拉菜单需要引入 popper -->
<script src="https://unpkg.com/popper.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript" language="javascript">
    function logout_js()
    {
        window.location.href='logout.php';
    }
</script>
<nav class="navbar navbar-expand-sm bg-dark navbar-dark justify-content-between">
    <ul class="navbar-nav">
        <li class="nav-item active">
            <a class="navbar-brand" href="#"><?php echo "Hello, ".$admin?></a>
        </li>
    </ul>
    <form class="form-inline">
        <button class="btn btn-outline-danger my-2 my-sm-0" type="reset" onclick="logout_js()">Logout</button>
    </form>
</nav>
<div class="container-fluid my-3">
    <div class = "row">
        <div class="col-2">
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="dashboard.html" style="display:block">Home</a>
                </li>
                <li class="list-group-item">
                    <a href="<?php echo "channel_home.php?admin=".$adminID,"&wname=".$workspaceID?>" style="display:block">Channel</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="<?php echo "notification.php?admin=".$adminID?>" style="display: block">Notification</a>
                    <span class="badge badge-primary badge-pill"><?php echo $notification_num ?></span>
                </li>
            </ul>
        </div>
            <div class="col-10">
                <div class="tab-content" id="nav-tabContent">
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">Remove</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="card my-2">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $cname ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo "You can only remove members in ".$cname?></h6>
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">User Name</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for($i = 0; $i < $user_num; $i++):?>
                                        <tr>
                                            <td><?php echo getUsername($username[$i])?></td>
                                            <td><?php echo $email[$i]?></td>
                                            <td>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="javascript:window.location.href='<?php echo "channel_remove.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$channelID,"&uname=".$username[$i]?>'"> remove
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
</body>
</html>
