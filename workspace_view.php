<?php
include 'functions.php';
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:
    $conn = custom_connect();
    if (isset($_GET['wname'])): ////页面被第一次加载时，从channel_home页面获得admin和cname和wname的参数
        $adminID = $_GET['admin']; $admin = getUsername($adminID);
        $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
        //$channelID = $_GET['cname']; $cname = getChannelName($workspaceID, $channelID);
        $notification_num = notification_num($adminID);
        $deleted_removed = workspaceDeleted_memberRemoved($adminID, $workspaceID);
        if (! $deleted_removed){
            $url = "workspace_home.php?admin=$adminID";
            echo "<script type='text/javascript'>";
            echo "alert(\"You were removed from the workspace $wname or this workspace has been deleted!\");";
            echo " location.href='$url';";
            echo "</script>";
            exit;
        }
        $user_num = 0;$username = array(); $nickname = array();$email = array();$userType = array();
        $query = "select username, nickname, email, wutype
from users natural join wu
where wid = '$workspaceID'
order by wutype ASC ;";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_array($result)) {
            $username[] = $row['username'];
            $nickname[] = $row['nickname'];
            $email[] = $row['email'];
            $userType[] = $row['wutype'];
            $user_num += 1;
        }
//    $user_num = 3;//$deleted_removed = false;
//    $username = ['James', 'Linda', 'Michael'];
//    $nickname = ['JJ', 'Little Linda', 'Mike'];
//    $email = ['james@gmail.com', 'linda@gmail.com', 'mike@gmail.com'];
//    $userType = ['Creator', 'Member', 'Member'];
        mysqli_close($conn);
        /************************************************************
         * 数据库查询wname中cname所有人的姓名并赋给$name
         * 数据库查询wname中cname所有人的邮箱并赋给$email
         * 数据库查询wname中cname所有人的昵称并赋给$nickname
         * 数据库查询wname中cname所有人的身份并赋给$userType
         * 数据库查询admin未处理的通知的数量并赋给$notification_num
         * 数据库查询wname中cname的人员个数并赋给$user_num
         * 数据库查询admin是否被移出cname或cname是否被删除，赋值给$deleted_removed
         **********************************************************/
        ?>
    <?php endif; ?>
<?php endif; ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workspace</title>
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
                    <a href="<?php echo "dashboard.php?admin=".$adminID ?>" style="display:block">Home</a>
                </li>
                <li class="list-group-item">
                    <a href="<?php echo "workspace_home.php?admin=".$adminID ?>" style="display:block">Workspace</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="<?php echo "notification.php?admin=".$adminID?>" style="display: block">Notification</a>
                    <span class="badge badge-primary badge-pill"><?php echo $notification_num ?></span>
                </li>
            </ul>
        </div>
        <?php
        if ($deleted_removed == true):
            ?>
            <div class="col-10">
                <div class="tab-content" id="nav-tabContent">
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">View</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="card my-2">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $wname ?></h5>
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">User Name</th>
                                        <th scope="col">Nickname</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">User Type</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for($i = 0; $i < $user_num; $i++):?>
                                        <tr>
                                            <td><?php echo $username[$i]?></td>
                                            <td><?php echo $nickname[$i]?></td>
                                            <td><?php echo $email[$i]?></td>
                                            <td><?php echo $userType[$i]?></td>
                                        </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        else:?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail!</strong> <?php echo "You were removed from this workspace or this workspace has been deleted! "?>
            </div>
        <?php
        endif;
        ?>
    </div>
</div>
</body>
</html>
