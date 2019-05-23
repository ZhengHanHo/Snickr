<?php
/**
 * Created by PhpStorm.
 * User: Jiaqi Li
 * Date: 2019/4/28
 * Time: 20:46
 */
include 'functions.php';
?>
<?php
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
endif;
if (isset($_GET['status'])):      //点击Accept或者Refuse按钮后刷新页面
$adminID = $_GET['admin']; $admin = getUsername($adminID);
$status = $_GET['status']; $conn = custom_connect();
    if (isset($_GET['cname'])):    //被邀请加入channel
        $userID = $_GET['uname'];
        $workspaceID = $_GET['wname'];
        $channelID = $_GET['cname'];
        $not_deleted_channel = channelDeleted($workspaceID, $channelID);
        if ($not_deleted_channel == false): //在用户选择accept或者refuse前一刹那，该channel已经被删除
        ?>
            <link href="css/bootstrap.min.css" rel="stylesheet">
            <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "This channel has been deleted or you have been removed from this channel! "?>
            </div>
<!--            延迟一秒中刷新页面-->
            <meta http-equiv="refresh" content="2; url=<?php echo "notification.php?admin=$adminID"?>">
        <?php
        else:
            if ($status == "accept"):
                //$cname = $_GET['cname'];$wname = $_GET['wname'];$uname = $_GET['uname'];
                $query_accept_channel = "INSERT INTO channelsinvitelog VALUES ('$userID', '$adminID', '$workspaceID', '$channelID', 'ACCEPT', now());";
                $result_accept_channel = mysqli_query($conn, $query_accept_channel);
                if (!$result_accept_channel):
                    echo '<script>alert("Error! Can\'t accept!");</script>';
                endif;
                $query_cu = "insert into cu values ('$workspaceID', '$channelID', '$adminID', 'MEMBER');";
                $result_cu = mysqli_query($conn, $query_cu);
                if (!$result_cu):
                    echo '<script>alert("Error! Can\'t join to the channel!");</script>';
                endif;
                $url = "notification.php?admin=$adminID";
                echo "<script type='text/javascript'>";
                echo "alert(\"Accept successfully!\");";
                echo " location.href='$url';";
                echo "</script>";
                //echo '<script>alert("Accept successfully!");</script>';
                //header("Location: notification.php?admin=".$adminID);
            /************************************************************
            数据库插入admin接受uname邀请admin加入wname中的cname的记录
                             在cu中加上相应的记录
             ************************************************************/
            else:
                //$cname = $_GET['cname'];$wname = $_GET['wname'];$uname = $_GET['uname'];
                $query_refuse_channel = "INSERT INTO channelsinvitelog VALUES ('$userID', '$adminID', '$workspaceID', '$channelID', 'REFUSE', now());";
                $result_refuse_channel = mysqli_query($conn, $query_refuse_channel);
                if (!$result_refuse_channel):
                    echo '<script>alert("Error! Can\'t insert to database!");</script>';
                endif;
                echo '<script>alert("Refuse successfully!");</script>';
                header("Location: notification.php?admin=".$adminID);
                /************************************************************
                数据库插入admin拒绝uname邀请admin加入wname中的cname的记录
                 ************************************************************/
            endif;
        endif;
    else:                          //被邀请加入workspace
        $userID = $_GET['uname']; $workspaceID = $_GET['wname'];
        $not_deleted_workspace = workspaceDeleted($workspaceID);
        if ($not_deleted_workspace == false): //在用户选择accept或者refuse前一刹那，该workspace已经被删除
            ?>
            <link href="css/bootstrap.min.css" rel="stylesheet">
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail!</strong> <?php echo "This workspace has been deleted or you have been removed from this workspace! "?>
            </div>
            <!--            延迟一秒中刷新页面-->
            <meta http-equiv="refresh" content="2; url=<?php echo "notification.php?admin=$adminID"?>">
        <?php
        else:
        if ($status == "accept"):
            //$wname = $_GET['wname'];$uname = $_GET['uname'];
            $query_accept_workspace = "INSERT INTO workspacesinvitelog VALUES ('$userID', '$adminID', '$workspaceID', 'ACCEPT', now());";
            $result_accept_workspace = mysqli_query($conn, $query_accept_workspace);
            if (!$result_accept_workspace):
                echo '<script>alert("Error! Can\'t insert to database!");</script>';
            endif;
            $query_wu = "insert into wu values ('$workspaceID', '$adminID', 'MEMBER');";
            $result_wu = mysqli_query($conn, $query_wu);
            if (!$result_wu):
                echo '<script>alert("Error! Can\'t join to the workspace!");</script>';
            endif;
            $url = "notification.php?admin=$adminID";
            echo "<script type='text/javascript'>";
            echo "alert(\"Accept successfully!\");";
            echo " location.href='$url';";
            echo "</script>";
            //echo '<script>alert("Accept successfully!");</script>';
            //header("Location: notification.php?admin=".$adminID);
        /************************************************************
               数据库插入admin接受uname邀请admin加入wname的记录
                            在wu中插入对应的记录
         ************************************************************/
        else:
            //$wname = $_GET['wname'];$uname = $_GET['uname'];
            $query_refuse_workspace = "INSERT INTO workspacesinvitelog VALUES ('$userID', '$adminID', '$workspaceID', 'REFUSE', now());";
            $result_refuse_workspace = mysqli_query($conn, $query_refuse_workspace);
            if (!$result_refuse_workspace):
                echo '<script>alert("Error! Can\'t insert to database!");</script>';
            endif;
            echo '<script>alert("Refuse successfully!");</script>';
            $url = "notification.php?admin=$adminID";
            echo "<script type='text/javascript'>";
            echo " location.href='$url';";
            echo "</script>";
            /************************************************************
                  数据库插入admin拒绝uname邀请admin加入wname的记录
             ************************************************************/
        endif;
        endif;
    endif;
mysqli_close($conn);
?>
<?php
else:                              //第一次进入页面
 $adminID= $_GET['admin'];
 $admin = getUsername($adminID);
?>
<?php endif; ?>

<?php
$notification_num = notification_num($adminID);
$multi_result = notification_received($adminID);
$workspace_num_received = $multi_result[0];
$channel_num_received = $multi_result[1];
$workspace_username_received = $multi_result[2];
$workspace_received = $multi_result[3];
$channel_username_received = $multi_result[4];
$channel_workspace_received = $multi_result[5];
$channel_received = $multi_result[6];

//$channel_username_received = ['Steve Rogers', 'Tony Stark'];
//$channel_received = ['DB_discuss', 'ML_discuss'];
//$channel_workspace_received = ['CMU', 'UCB'];
//$channel_num_received = 2;
//$workspace_username_received = ['Thor', 'Loki'];
//$workspace_received = ['NYU','MIT'];
//$workspace_num_received = 2;
$multi_result = notification_sent($adminID);
$workspace_num_sent = $multi_result[0];
$channel_num_sent = $multi_result[1];
$workspace_username_sent = $multi_result[2];
$workspace_sent = $multi_result[3];
$workspace_time_sent = $multi_result[4];
$workspace_status_sent = $multi_result[5];
$channel_username_sent = $multi_result[6];
$channel_workspace_sent = $multi_result[7];
$channel_sent = $multi_result[8];
$channel_time_sent = $multi_result[9];
$channel_status_sent = $multi_result[10];
//var_dump($multi_result);

//$channel_sent = ['Homework', 'Lecture'];
//$channel_workspace_sent = ['UCSD','USTC'];
//$channel_num_sent = 2;
//$channel_status_sent = ['Refused','Approved'];
//$channel_username_sent = ['Natasha Romanova','Hawkeye'];
//$channel_time_sent = ['2019-04-24 21:42:19','2019-04-26 23:42:00'];
//$workspace_sent = ['UCLA'];
//$workspace_num_sent = 1;
//$workspace_status_sent = ['Not Process'];
//$workspace_username_sent = ['Bruce Banner'];
//$workspace_time_sent = ['2019-04-28 11:25:06'];
/************************************************************
 * 以上全部变量均由数据库查询得到,针对于特定的admin
 **********************************************************/
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 引入 Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <!-- jQuery (Bootstrap 的 JavaScript 插件需要引入 jQuery) -->
    <script src="https://code.jquery.com/jquery.js"></script>
    <!-- 包括所有已编译的插件 -->
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" language="javascript">
        function logout_js()
        {
            window.location.href='logout.php';
        }
    </script>
    <!-- 以下是导航栏-->
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark justify-content-between">
        <!-- Navbar content -->
        <ul class="navbar-nav">
            <li class="nav-item active">
                <a class="navbar-brand" href="#"><?php echo "Hello, ".$admin?></a>
            </li>
        </ul>
        <form class="form-inline">
            <button class="btn btn-outline-danger my-2 my-sm-0" type="reset" onclick="logout_js()">Logout</button>
        </form>
    </nav>
    <!-- 以上是导航栏 -->
    <div class="container-fluid my-3">
        <div class = "row">
            <div class="col-2">
                <ul class="list-group">
                    <li class="list-group-item">
                        <a href="<?php echo "dashboard.php?admin=$admin"?>" style="display:block">Home</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="<?php echo "notification.php?admin=$admin"?>" style="display: block">Notification</a>
                        <span class="badge badge-primary badge-pill"><?php echo $notification_num ?></span>
                    </li>
                </ul>
            </div>
            <div class="col-10">
                <div class="tab-content" id="nav-tabContent">
                    <!--<div class="tab-pane fade" id="list-Workspaces" role="tabpanel"-->
                    <!--aria-labelledby="list-Workspaces-list">-->
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <!-- Navbar content -->
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">Notifications</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="card border-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Received</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Notifications that you received from others</h6>
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">Content</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for($i = 0; $i < $workspace_num_received; $i++):?>
                                    <?php $wname = getWorkspaceName($workspace_received[$i]);
                                          $uname = getUsername($workspace_username_received[$i]);?>
                                    <tr>
                                        <td><?php echo "User ".$uname," invites you to join Workspace ".$wname?></td>
                                        <td>
                                            <button class="btn btn-success my-2 my-sm-0" id="pfb" type="submit"
                                                    onclick="javascript:window.location.href='<?php echo "notification.php?admin=".$adminID,"&status=accept","&wname=".$workspace_received[$i],"&uname=".$workspace_username_received[$i]?>'">
                                                Accept
                                            </button>
                                            <button class="btn btn-danger my-2 my-sm-0" type="submit"
                                                    onclick="javascript:window.location.href='<?php echo "notification.php?admin=".$adminID,"&status=refuse","&wname=".$workspace_received[$i],"&uname=".$workspace_username_received[$i]?>'">
                                                Refuse
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                    <?php for($i = 0; $i < $channel_num_received; $i++):?>
                                    <?php $cname = getChannelName($channel_workspace_received[$i], $channel_received[$i]);
                                          $wname = getWorkspaceName($channel_workspace_received[$i]);
                                          $uname = getUsername($channel_username_received[$i]);?>
                                    <tr>
                                        <td><?php echo "User ".$uname," invites you to join Channel ".$cname," in Workspace ".$wname?></td>
                                        <td>
                                            <button class="btn btn-success my-2 my-sm-0" id="pfb" type="submit"
                                                    onclick="javascript:window.location.href='<?php echo "notification.php?admin=".$adminID,"&status=accept","&cname=".$channel_received[$i],"&wname=".$channel_workspace_received[$i],"&uname=".$channel_username_received[$i]?>'">
                                                Accept
                                            </button>
                                            <button class="btn btn-danger my-2 my-sm-0" type="submit"
                                                    onclick="javascript:window.location.href='<?php echo "notification.php?admin=".$adminID,"&status=refuse","&cname=".$channel_received[$i],"&wname=".$channel_workspace_received[$i],"&uname=".$channel_username_received[$i]?>'">
                                                Refuse
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card border-dark mb-3" >
                        <div class="card-body">
                            <h5 class="card-title">Sent</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Notifications that you sent to others</h6>
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">Content</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for($i = 0; $i < $workspace_num_sent; $i++):?>
                                    <?php $wname = getWorkspaceName($workspace_sent[$i]);
                                          $uname = getUsername($workspace_username_sent[$i]);
                                          $utime = $workspace_time_sent[$i];
                                          $ustatus = $workspace_status_sent[$i];
                                          ?>
                                    <tr>
                                        <td><?php echo "You invite user ".$uname," to join Workspace ".$wname?></td>
                                        <td><?php echo $utime?></td>
                                        <td>
                                            <p><?php echo $ustatus?></p>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                    <?php for($i = 0; $i < $channel_num_sent; $i++):?>
                                    <?php $cname = getChannelName($channel_workspace_sent[$i], $channel_sent[$i]);
                                          $wname = getWorkspaceName($channel_workspace_sent[$i]);
                                          $uname = getUsername($channel_username_sent[$i]);
                                          $utime = $channel_time_sent[$i];
                                          $ustatus = $channel_status_sent[$i];
                                    ?>
                                    <tr>
                                        <td><?php echo "You invite user ".$uname," to join Channel ".$cname," in Workspace ".$wname?></td>
                                        <td><?php echo $utime?></td>
                                        <td>
                                            <p><?php echo $ustatus?></p>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--</div>-->
                </div>
            </div>
        </div>
    </div>
</body>
</html>