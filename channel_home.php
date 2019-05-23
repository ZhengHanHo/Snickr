<?php
/**
 * Created by PhpStorm.
 * User: Jiaqi Li
 * Date: 2019/4/19
 * Time: 17:53
 */
?>
<?php
header("Content-Type:text/html;charset=utf-8");//支持中文
include 'functions.php';
$invite = "Invite other";
$delete = "Delete channel";
$remove = "Remove member";
$view = "View member";
$quit = "Quit channel";
?>
<?php
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:                                     //用户已经登录
    $conn = custom_connect();//尝试连接数据库
if (isset($_GET['deleteChannel'])): //当用户点击delete按钮时重新加载页面
    $adminID = $_GET['admin'];
    $admin = getUsername($adminID);
    $workspaceID = $_GET['wname'];
    $wname = getWorkspaceName($workspaceID);
    $deleteChannelID = $_GET['deleteChannel'];
    $deleteChannelName = getChannelName($workspaceID, $deleteChannelID);
    $not_delete_remove = channelDeleted_memberRemoved($adminID, $workspaceID, $deleteChannelID);
    if ($not_delete_remove == true):
        $query_delete_channel = "delete from channels where wid = '$workspaceID' and cid = '$deleteChannelID';";
        $result_delete_channel = mysqli_query($conn, $query_delete_channel);
        if (!$result_delete_channel):
            echo '<script>alert("Error! Can\'t delete this channel!");</script>';
        endif;
        $url = "channel_home.php?admin=$adminID&wname=$workspaceID";
        echo "<script type='text/javascript'>";
        echo "alert(\"You deleted the channel $deleteChannelName successfully!\");";
        echo " location.href='$url';";
        echo "</script>";
    else:?>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "You were removed from this channel or this channel has been deleted! "?>
        </div>
        <meta http-equiv="refresh" content="1; url=<?php echo "channel_home.php?admin=$adminID&wname=$workspaceID"?>">
        <?php exit;
    endif;

    //header("Location: channel_home.php?admin=".$adminID."&wname=".$workspaceID);
    /************************************************************
             使用数据库将deleteChannelName从wname中删除
             删除ChannelsInviteLog中所有含wname和cname的记录
     **********************************************************/
    ?>
<?php
elseif (isset($_GET['quitChannel'])): //当用户点击quit按钮时重新加载页面
    $adminID = $_GET['admin'];
    $admin = getUsername($adminID);
    $workspaceID = $_GET['wname'];
    $wname = getWorkspaceName($workspaceID);
    $quitChannelID = $_GET['quitChannel'];
    $quitChannelName = getChannelName($workspaceID, $quitChannelID);
    $not_deleted_removed = channelDeleted_memberRemoved($adminID, $workspaceID, $quitChannelID);
    /********************************************************************************************
     * 数据库查询admin是否被移出quitChannelName或quitChannelName是否被删除，赋值给$deleted_removed
     ********************************************************************************************/
    if ($not_deleted_removed == false):?>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "You were removed from this channel or this channel has been deleted! "?>
        </div>
        <meta http-equiv="refresh" content="1; url=<?php echo "channel_home.php?admin=$adminID&wname=$workspaceID"?>">
        <?php exit; ?>
    <?php
    else:
        $query_quit_channel = "delete from cu where wid = '$workspaceID' and cid = '$quitChannelID' and uid = '$adminID';";
        $result_quit_channel = mysqli_query($conn, $query_quit_channel);
        if (!$result_quit_channel):
            echo '<script>alert("Error! Can\'t delete this channel!");</script>';
        endif;
        /************************************************************
          使用数据库将admin从wname中quitChannelName的人员名单中移除
         **********************************************************/

        $url = "channel_home.php?admin=$adminID&wname=$workspaceID";
        echo "<script type='text/javascript'>";
        echo "alert(\"You quited the channel $quitChannelName successfully!\");";
        echo " location.href='$url';";
        echo "</script>";
        //header("Location: channel_home.php?admin=".$adminID."&wname=".$workspaceID);
    endif;?>
<?php
else://页面第一次被加载，从dashboard中获得admin,wname参数
    $adminID = $_GET['admin'];
    $workspaceID = $_GET['wname'];
    $admin = getUsername($adminID);
    $wname = getWorkspaceName($workspaceID);
    //$admin = "Steve Rogers";$wname = "NYU";
    ?>
<?php endif;
mysqli_close($conn);
 endif; ?>
<?php
$conn = custom_connect();
$public_num = 0; $private_num = 0; $direct_num = 0;
$notification_num = notification_num($adminID);
$publicName = array();$publicCreator = array();$publicTime = array();
$privateName = array();$privateCreator = array();$privateTime = array();
$directName = array();$directCreator = array();$directTime = array();

if ($conn):
    $query_public = "with T1 as (
select  wid, cid, ccreatetime
from channels natural join cu
where wid = '$workspaceID' and ctype = 'PUBLIC'  and uid = '$adminID'
ORDER BY cname ASC
)
select cid, uid , ccreatetime
from T1 natural join cu
where cutype = 'CREATOR'
order by ccreatetime ASC ;";
    $result = mysqli_query($conn, $query_public);
    while ($row = mysqli_fetch_array($result)) {
        $publicCreator[] = $row['uid'];
        $publicName[] = $row['cid'];
        $publicTime[] = $row['ccreatetime'];
        $public_num += 1;
    }
    $query_private = "with T1 as (
select  wid, cid, ccreatetime
from channels natural join cu
where wid = '$workspaceID' and ctype = 'PRIVATE'  and uid = '$adminID'
ORDER BY cname ASC
)
select cid, uid , ccreatetime
from T1 natural join cu
where cutype = 'CREATOR'
order by ccreatetime ASC ;";
    $result = mysqli_query($conn, $query_private);
    while ($row = mysqli_fetch_array($result)) {
        $privateCreator[] = $row['uid'];
        $privateName[] = $row['cid'];
        $privateTime[] = $row['ccreatetime'];
        $private_num += 1;
    }
    $query_direct = "with T1 as (
select  wid, cid, ccreatetime
from channels natural join cu
where wid = '$workspaceID' and ctype = 'DIRECT'  and uid = '$adminID'
ORDER BY cname ASC
)
select cid, uid , ccreatetime
from T1 natural join cu
where cutype = 'CREATOR'
order by ccreatetime ASC;";
    $result = mysqli_query($conn, $query_direct);
    while ($row = mysqli_fetch_array($result)) {
        $directCreator[] = $row['uid'];
        $directName[] = $row['cid'];
        $directTime[] = $row['ccreatetime'];
        $direct_num += 1;
    }
    mysqli_close($conn);
else:?>
    <div class="alert alert-danger alert-dismissable fade show">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Fail!</strong> <?php echo "Can't connect to database, please refresh the web page!"?>
    </div>
    <meta http-equiv="refresh" content="2; url=<?php echo "index.php"?>">
<?php
    exit;
endif;
//$publicName = ['Homework', 'Lecture', 'Activity'];
//$publicCreator = ['Steve Rogers', 'Tony Stark', 'Thor'];
//$publicTime = ['2019-04-25', '2019-04-26', '2019-04-27'];
//$privateName = ['DB_discuss', 'ML_discuss'];
//$privateCreator = ['Bruce Banner', 'Natasha Romanova'];
//$privateTime = ['2019-04-28', '2019-04-29'];
//$directName = ['Out_for_Dinner'];
//$directCreator = ['Hawkeye'];
//$directTime = ['2019-04-30'];
//$public_num = 3; $private_num = 2; $direct_num = 1;

/************************************************************
 * 以上全部变量均由数据库查询得到,针对于特定的admin和wname
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
<!-- jQuery (Bootstrap 的 JavaScript 插件需要引入 jQuery) -->
<script src="https://code.jquery.com/jquery.js"></script>
<!-- 包括所有已编译的插件Bootstrap 的 下拉菜单需要引入 popper -->
<script src="https://unpkg.com/popper.js"></script>
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
                    <a href="<?php echo "dashboard.php?admin=".$adminID?>" style="display:block">Home</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="<?php echo "notification.php?admin=".$adminID?>" style="display: block">Notification</a>
                    <span class="badge badge-primary badge-pill"><?php echo $notification_num ?></span>
                </li>
            </ul>
        </div>
        <div class="col-10">
            <div class="tab-content" id="nav-tabContent">
                <!--<div class="tab-pane fade" id="list-Workspaces" role="tabpanel"-->
                <!--aria-labelledby="list-Workspaces-list">-->
                <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                    <ul class="navbar-nav">
                        <li class="nav-item active">
                            <a class="navbar-brand" href="#"> <?php echo $wname?> </a>
                        </li>
                    </ul>
                    <form class="form-inline my-2 my-lg-0">
                        <button type="button" class="btn btn-outline-success"
                                onclick="javascript:window.location.href='<?php echo "channel_join.php?admin=".$adminID,"&wname=".$workspaceID?>'"> Join
                        </button>
                        <button type="button" class="btn btn-outline-primary"
                                onclick="javascript:window.location.href='<?php echo "channel_create.php?admin=".$adminID,"&wname=".$workspaceID?>'"> Create
                        </button>
                    </form>
                </nav>
                <div class="card my-2">
                    <div class="card-body">
                        <h5 class="card-title">Public Channel</h5>
                        <p class="card-text"><?php echo "You currently join ",$public_num," public channel(s)"?></p>
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
                                <?php for($i = 0; $i < $public_num; $i++):?>
                                    <?php $cname=getChannelName($workspaceID, $publicName[$i]) ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo "chat.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$publicName[$i]?>">
                                                <?php echo $cname?>
                                            </a>
                                        </td>
                                        <td><?php echo $publicTime[$i]?></td>
                                        <td><?php echo getUsername($publicCreator[$i])?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false"> Manage
                                                </button>
                                                <div class="dropdown-menu">
                                                    <?php
                                                    if ($publicCreator[$i] == $adminID):
                                                        ?>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_invite.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$publicName[$i]?>">
                                                            <?php echo $invite?>
                                                        </a>
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                                onclick="javascript:window.location.href='<?php echo "channel_view.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$publicName[$i]?>'" >
                                                            <?php echo $view?>
                                                        </button>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_remove.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$publicName[$i]?>">
                                                            <?php echo $remove?>
                                                        </a>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_home.php?admin=".$adminID,"&deleteChannel=".$publicName[$i],"&wname=".$workspaceID?>">
                                                            <?php echo $delete?>
                                                        </a>
                                                    <?php
                                                    else:
                                                        ?>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_invite.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$publicName[$i]?>">
                                                            <?php echo $invite?>
                                                        </a>
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                                onclick="javascript:window.location.href='<?php echo "channel_view.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$publicName[$i]?>'" >
                                                            <?php echo $view?>
                                                        </button>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_home.php?admin=".$adminID,"&wname=".$workspaceID,"&quitChannel=".$publicName[$i]?>">
                                                            <?php echo $quit?>
                                                        </a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card my-2" >
                    <div class="card-body">
                        <h5 class="card-title">Private Channel</h5>
                        <p class="card-text"><?php echo "You currently join ",$private_num," private channel(s)"?></p>
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
                                <?php for($i = 0; $i < $private_num; $i++):?>
                                    <?php $cname=getChannelName($workspaceID, $privateName[$i]) ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo "chat.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$privateName[$i]?>">
                                                <?php echo $cname?>
                                            </a>
                                        </td>
                                        <td><?php echo $privateTime[$i]?></td>
                                        <td><?php echo getUsername($privateCreator[$i])?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false"> Manage
                                                </button>
                                                <div class="dropdown-menu">
                                                    <?php
                                                    if ($privateCreator[$i] == $adminID):
                                                        ?>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_invite.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$privateName[$i]?>">
                                                            <?php echo $invite?>
                                                        </a>
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                                onclick="javascript:window.location.href='<?php echo "channel_view.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$privateName[$i]?>'" >
                                                            <?php echo $view?>
                                                        </button>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_remove.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$privateName[$i]?>">
                                                            <?php echo $remove?>
                                                        </a>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_home.php?admin=".$adminID,"&deleteChannel=".$privateName[$i],"&wname=".$workspaceID?>">
                                                            <?php echo $delete?>
                                                        </a>
                                                    <?php
                                                    else:
                                                        ?>
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                                onclick="javascript:window.location.href='<?php echo "channel_view.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$privateName[$i]?>'" >
                                                            <?php echo $view?>
                                                        </button>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_home.php?admin=".$adminID,"&wname=".$workspaceID,"&quitChannel=".$privateName[$i]?>">
                                                            <?php echo $quit?>
                                                        </a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card my-2">
                    <div class="card-body">
                        <h5 class="card-title">Direct Channel</h5>
                        <!--<h6 class="card-subtitle mb-2 text-muted">Notifications that you received from others</h6>-->
                        <p class="card-text"><?php echo "You currently join ",$direct_num," direct channel(s)"?></p>
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
                                <?php for($i = 0; $i < $direct_num; $i++):?>
                                    <?php $cname=getChannelName($workspaceID, $directName[$i]) ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo "chat.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$directName[$i]?>">
                                                <?php echo $cname?>
                                            </a>
                                        </td>
                                        <td><?php echo $directTime[$i]?></td>
                                        <td><?php echo getUsername($directCreator[$i])?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false"> Manage
                                                </button>
                                                <div class="dropdown-menu">
                                                    <?php
                                                    if ($directCreator[$i] == $adminID):
                                                        ?>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_invite_temp.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$directName[$i]?>">
                                                            <?php echo $invite?>
                                                        </a>
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                                onclick="javascript:window.location.href='<?php echo "channel_view.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$directName[$i]?>'" >
                                                            <?php echo $view?>
                                                        </button>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_home.php?admin=".$adminID,"&deleteChannel=".$directName[$i],"&wname=".$workspaceID?>">
                                                            <?php echo $delete?>
                                                        </a>
                                                    <?php
                                                    else:
                                                        ?>
                                                        <button type="button" class="dropdown-item" data-toggle="modal"
                                                                onclick="javascript:window.location.href='<?php echo "channel_view.php?admin=".$adminID,"&wname=".$workspaceID,"&cname=".$directName[$i]?>'" >
                                                            <?php echo $view?>
                                                        </button>
                                                        <a class="dropdown-item"
                                                           href="<?php echo "channel_home.php?admin=".$adminID,"&wname=".$workspaceID,"&quitChannel=".$directName[$i]?>">
                                                            <?php echo $quit?>
                                                        </a>
                                                    <?php
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
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
<!-- Modal -->
<!--<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">-->
<!--    <div class="modal-dialog" role="document">-->
<!--        <div class="modal-content">-->
<!--            <div class="modal-header">-->
<!--                <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
<!--                    <span aria-hidden="true">&times;</span>-->
<!--                </button>-->
<!--            </div>-->
<!--            <div class="modal-body">-->
<!--                <div class="row pre-scrollable my-2">-->
<!--                    <table class="table table-hover">-->
<!--                        <thead>-->
<!--                        <tr>-->
<!--                            <th scope="col">User Name</th>-->
<!--                            <th scope="col">Email</th>-->
<!--                        </tr>-->
<!--                        </thead>-->
<!--                        <tbody>-->
<!--                        --><?php //for($i = 0; $i < $user_num; $i++):?>
<!--                            --><?php //$cname=$username.$i ?>
<!--                            <tr>-->
<!--                                <td>--><?php //echo $cname?><!--</td>-->
<!--                                <td>--><?php //echo $email?><!--</td>-->
<!--                            </tr>-->
<!--                        --><?php //endfor; ?>
<!--                        </tbody>-->
<!--                    </table>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="modal-footer">-->
<!--                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
</body>
</html>