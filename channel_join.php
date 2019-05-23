<?php
include 'functions.php';
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:
    $conn = custom_connect();
if (isset($_GET['joinChannelName'])): //当用户点击join按钮时重新加载页面
    $adminID = $_GET['admin']; $admin = getUsername($adminID);
    $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
    $channelID = $_GET['joinChannelName']; $cname = getChannelName($workspaceID, $channelID);
    //$admin = $_GET['admin'];$cname = $_GET['joinChannelName'];$wname = $_GET['wname'];
    $deleted_removed =channelDeleted($workspaceID, $channelID);
    //$deleted_removed = false;
    /************************************************************
     * 数据库查询wname中的cname是否被删除，赋值给$deleted_removed
     **********************************************************/
    ?>
    <?php
    if ($deleted_removed == true):
    ?>
        <?php
        $query_cu = "insert into cu values ('$workspaceID', '$channelID', '$adminID', 'MEMBER');";
        $result_cu = mysqli_query($conn, $query_cu);
        if (!$result_cu):
            echo '<script>alert("Error! Can\'t join to the channel!");</script>';
        endif;
        $url = "channel_join.php?admin=$adminID&wname=$workspaceID";
        echo "<script type='text/javascript'>";
        echo "alert(\"You joined the public channel $cname successfully!\");";
        echo " location.href='$url';";
        echo "</script>";
          /************************************************************
                  数据库插入admin加入wname中的cname的记录
          ************************************************************/
            ?>
    <?php
    else:
        //重定向浏览器
        header("Location: channel_join.php?admin=$adminID&wname=$workspaceID");
        //确保重定向后，后续代码不会被执行
        exit;
        ?>
    <?php
    endif;?>
<?php
else: //页面被第一次加载时，从channel_home页面获得admin和wname的参数
    //$admin = $_GET['admin'];$wname = $_GET['wname'];
    $adminID = $_GET['admin']; $admin = getUsername($adminID);
    $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
    $deleted_removed_workspace = workspaceDeleted($workspaceID);//检查workspace是否被删除
    ?>
<?php endif;
mysqli_close($conn);
 endif; ?>
<?php
    $conn = custom_connect();
    $notification_num = notification_num($adminID);$channel_num = 0;
    $channelName = array();$channelCreator = array(); $channelTime = array();
    $query_public_channel = "select wid, cid, uid, ccreatetime
from channels natural join cu
where cutype = 'CREATOR' and ctype ='PUBLIC' and wid = '$workspaceID' and (wid, cid) not in (
  select wid, cid
  from cu
  where wid = '$workspaceID' and uid = '$adminID');";
    $result_public_channel = mysqli_query($conn, $query_public_channel);
while ($row = mysqli_fetch_array($result_public_channel)) {
    $channelName[] = $row['cid'];
    $channelCreator[] = $row['uid'];
    $channelTime[] = $row['ccreatetime'];
    $channel_num += 1;
}
mysqli_close($conn);
//    $channel_num = 3;
//    $channelName=['Reading', 'Writing', 'Talking'];
//    $channelCreator = ['Steve Rogers', 'Tony Stark', 'Thor'];
//    $channelTime = ['2019-04-25', '2019-04-26', '2019-04-27'];
    /************************************************************
     * 数据库查询wname中所有admin不在其中的public channel的名字并赋给$channelName
     * 数据库查询wname中所有admin不在其中的public channel的创建时间并赋给$channelTime
     * 数据库查询wname中所有admin不在其中的public channel的创建者并赋给$channelCreator
     * 数据库查询wname中所有admin不在其中的public channel的个数并赋给$channel_num
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
                    <a href="<?php echo "dashboard.php?admin=".$adminID?>" style="display:block">Home</a>
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
        <?php
        if ($deleted_removed_workspace == true):
        ?>
        <div class="col-10">
            <div class="tab-content" id="nav-tabContent">
                <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                    <ul class="navbar-nav">
                        <li class="nav-item active">
                            <a class="navbar-brand" href="#">Join</a>
                        </li>
                    </ul>
                </nav>
                <div class="card my-2">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo "Public Channel" ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo "You can only join public channels in ".$wname?></h6>
                        <div class="row pre-scrollable my-2">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">Channel Name</th>
                                    <th scope="col">Created Time</th>
                                    <th scope="col">Creator</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php for($i = 0; $i < $channel_num; $i++):?>
                                    <tr>
                                        <td><?php echo getChannelName($workspaceID, $channelName[$i])?></td>
                                        <td><?php echo $channelTime[$i]?></td>
                                        <td><?php echo getUsername($channelCreator[$i])?></td>
                                        <td>
                                            <button type="button" class="btn btn-outline-success"
                                                    onclick="javascript:window.location.href='<?php echo "channel_join.php?admin=".$adminID,"&joinChannelName=".$channelName[$i],"&wname=".$workspaceID?>'">
                                                join
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
        <?php
        else:?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "This workspace has been deleted!"?>
        </div>
        <?php
        endif;
        ?>
    </div>
</div>
</body>
</html>