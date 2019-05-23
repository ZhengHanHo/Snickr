<?php
/**
 * Created by PhpStorm.
 * User: Jiaqi Li
 * Date: 2019/5/8
 * Time: 23:45
 */
?>
<?php
header("Content-Type:text/html;charset=utf-8");//支持中文
include 'functions.php';
//$invite = "Invite other";
//$delete = "Delete workspace";
//$remove = "Remove member";
//$view = "View member";
//$quit = "Quit workspace";
//$administrator = "Choose administrator";
$invite = "Invite";
$delete = "Delete";
$remove = "Remove";
$view = "View";
$quit = "Quit";
$administrator = "Administrator";
?>
<?php
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:
    $conn = custom_connect();//尝试连接数据库
    if (isset($_GET['deleteWorkspace'])): //当用户点击delete按钮时重新加载页面
        $adminID = $_GET['admin'];
        $admin = getUsername($adminID);
        $deleteWorkspaceID = $_GET['deleteWorkspace'];
        $deleteWorkspaceName = getWorkspaceName($deleteWorkspaceID);
        //$deleteChannelID = $_GET['deleteChannel'];
        //$deleteChannelName = getChannelName($workspaceID, $deleteChannelID);
        $not_delete_remove = workspaceDeleted_memberRemoved($adminID, $deleteWorkspaceID);
        if ($not_delete_remove == true):
            $query_delete_workspace = "delete from workspaces where wid = '$deleteWorkspaceID';";
            $result_delete_workspace = mysqli_query($conn, $query_delete_workspace);
            if (!$result_delete_workspace):
                echo '<script>alert("Error! Can\'t delete this workspace!");</script>';
            endif;
            $url = "workspace_home.php?admin=$adminID";
            echo "<script type='text/javascript'>";
            echo "alert(\"You deleted the workspace $deleteWorkspaceName successfully!\");";
            echo " location.href='$url';";
            echo "</script>";
        else:?>
            <link href="css/bootstrap.min.css" rel="stylesheet">
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail!</strong> <?php echo "You were removed from this workspace or this workspace has been deleted! "?>
            </div>
            <meta http-equiv="refresh" content="1; url=<?php echo "workspace_home.php?admin=$adminID"?>">
            <?php exit;
        endif;

        //header("Location: channel_home.php?admin=".$adminID."&wname=".$workspaceID);
        /************************************************************
        使用数据库将deleteChannelName从wname中删除
        删除ChannelsInviteLog中所有含wname和cname的记录
         **********************************************************/
        ?>
    <?php
    elseif (isset($_GET['quitWorkspace'])): //当用户点击quit按钮时重新加载页面
        $adminID = $_GET['admin'];
        $admin = getUsername($adminID);
        $quitWorkspaceID = $_GET['quitWorkspace'];
        $quitWorkspaceName = getWorkspaceName($quitWorkspaceID);
        //$quitChannelID = $_GET['quitChannel'];
        //$quitChannelName = getChannelName($workspaceID, $quitChannelID);
        $not_deleted_removed = workspaceDeleted_memberRemoved($adminID, $quitWorkspaceID);
        /********************************************************************************************
         * 数据库查询admin是否被移出quitChannelName或quitChannelName是否被删除，赋值给$deleted_removed
         ********************************************************************************************/
        if ($not_deleted_removed == false):?>
            <link href="css/bootstrap.min.css" rel="stylesheet">
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail!</strong> <?php echo "You were removed from this workspace or this workspace has been deleted! "?>
            </div>
            <meta http-equiv="refresh" content="1; url=<?php echo "workspace_home.php?admin=$adminID"?>">
            <?php exit; ?>
        <?php
        else:
            $query_quit_workspace = "delete from wu where wid = '$quitWorkspaceID' and uid = '$adminID'; ";
            $query_quit_workspace_channel = "delete from cu where uid = '$adminID' and wid = '$quitWorkspaceID';";
            $result_quit_workspace = mysqli_query($conn, $query_quit_workspace);
            $result_quit_workspace_channel = mysqli_query($conn, $query_quit_workspace_channel);
            if (! ($result_quit_workspace && $result_quit_workspace_channel)):
                echo '<script>alert("Error! Can\'t delete this workspace!");</script>';
            endif;
            /************************************************************
            使用数据库将admin从wname中quitChannelName的人员名单中移除
             **********************************************************/

            $url = "workspace_home.php?admin=$adminID";
            echo "<script type='text/javascript'>";
            echo "alert(\"You quited the workspace $quitWorkspaceName successfully!\");";
            echo " location.href='$url';";
            echo "</script>";
            //header("Location: channel_home.php?admin=".$adminID."&wname=".$workspaceID);
        endif;?>
    <?php
    else://页面第一次被加载，从dashboard中获得admin参数
        $adminID = $_GET['admin'];
        //$workspaceID = $_GET['wname'];
        $admin = getUsername($adminID);
        //$wname = getWorkspaceName($workspaceID);
        //$admin = "Steve Rogers";$wname = "NYU";
        ?>
    <?php endif;
    mysqli_close($conn);
endif;?>
<?php
$conn = custom_connect();
$workspace_num = 0;
$notification_num = notification_num($adminID);
$workspaceName = array();$workspaceCreator = array();

if ($conn):
    $query_workspace = "select wid, uid
from wu
where wutype = 'ORIGINAL_ADMIN' and 
      wid in (select wid from wu where uid = '$adminID')
order by wid ASC ;";
    $result_workspace = mysqli_query($conn, $query_workspace);
    while ($row = mysqli_fetch_array($result_workspace)) {
        $workspaceCreator[] = $row['uid'];
        $workspaceName[] = $row['wid'];
        $workspace_num += 1;
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
                            <a class="navbar-brand" href="#"> <?php echo "Your Workspace"?> </a>
                        </li>
                    </ul>
                    <form class="form-inline my-2 my-lg-0">
                        <button type="button" class="btn btn-outline-primary"
                                onclick="javascript:window.location.href='<?php echo "workspace_create.php?admin=".$adminID ?>'">
                            Create
                        </button>
                    </form>
                </nav>
                <div class="card my-2">
                    <div class="card-body">
<!--                        <h5 class="card-title">Public Channel</h5>-->
<!--                        <p class="card-text">--><?php //echo "You currently join ",$public_num," public channel(s)"?><!--</p>-->
                        <div class="row pre-scrollable my-2">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th scope="col">Workspace Name</th>
                                    <th scope="col">Invite other</th>
                                    <th scope="col">Choose Admin</th>
                                    <th scope="col">Remove member</th>
                                    <th scope="col">Delete workspace</th>
                                    <th scope="col">View member</th>
                                    <th scope="col">Quit workspace</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php for($i = 0; $i < $workspace_num; $i++):?>
                                    <?php $wname=getWorkspaceName( $workspaceName[$i]) ?>
                                    <tr>
                                        <td>
                                            <p>
                                                <?php echo $wname?>
                                            </p>
                                        </td>
                                        <?php
                                        $multi_result = is_administrator($adminID, $workspaceName[$i]);
                                        if ($multi_result[0]):
                                            ?>
                                        <td>
                                            <button type="button" class="btn btn-primary"
                                                    onclick="javascript:window.location.href='<?php echo "workspace_invite.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" >
                                                <?php echo $invite?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary"
                                                    onclick="javascript:window.location.href='<?php echo "workspace_administrator.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" >
                                                <?php echo $administrator?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger"
                                                    onclick="javascript:window.location.href='<?php echo "workspace_remove.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'">
                                                <?php echo $remove?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger"
                                                    onclick="javascript:window.location.href='<?php echo "workspace_home.php?admin=".$adminID,"&deleteWorkspace=".$workspaceName[$i]?>'">
                                                <?php echo $delete?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary"
                                                    onclick="javascript:window.location.href='<?php echo "workspace_view.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" >
                                                <?php echo $view?>
                                            </button>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary"
                                                    onclick="javascript:window.location.href='<?php echo "workspace_home.php?admin=".$adminID,"&quitWorkspace=".$workspaceName[$i]?>'" disabled>
                                                <?php echo $quit?>
                                            </button>
                                        </td>
                                        <?php
                                        elseif($multi_result[1]):
                                            ?>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_invite.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" >
                                                    <?php echo $invite?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_administrator.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" disabled>
                                                    <?php echo $administrator?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_remove.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'">
                                                    <?php echo $remove?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_home.php?admin=".$adminID,"&deleteWorkspace=".$workspaceName[$i]?>'">
                                                    <?php echo $delete?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_view.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" >
                                                    <?php echo $view?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_home.php?admin=".$adminID,"&quitWorkspace=".$workspaceName[$i]?>'" disabled>
                                                    <?php echo $quit?>
                                                </button>
                                            </td>
                                        <?php
                                        else:?>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_invite.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" disabled>
                                                    <?php echo $invite?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_administrator.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" disabled>
                                                    <?php echo $administrator?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_remove.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" disabled>
                                                    <?php echo $remove?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_home.php?admin=".$adminID,"&deleteWorkspace=".$workspaceName[$i]?>'" disabled>
                                                    <?php echo $delete?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_view.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>'" >
                                                    <?php echo $view?>
                                                </button>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary"
                                                        onclick="javascript:window.location.href='<?php echo "workspace_home.php?admin=".$adminID,"&quitWorkspace=".$workspaceName[$i]?>'" >
                                                    <?php echo $quit?>
                                                </button>
                                            </td>
                                        <?php
                                        endif;
                                        ?>
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