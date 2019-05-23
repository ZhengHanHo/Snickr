<?php
/**
 * Created by PhpStorm.
 * User: Jiaqi Li
 * Date: 2019/5/8
 * Time: 23:56
 */
include 'functions.php';
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:                                     //用户已登录
    if (! isset($_GET['invite'])):
        $adminID = $_GET['admin']; $admin = getUsername($adminID);
        $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
        $channelID = $_GET['cname']; $cname = getChannelName($workspaceID, $channelID);
        $conn = custom_connect();
        $query_num_people = "select *
    from cu
    where wid = '$workspaceID' and cid = '$channelID';";
        $query_last_notification = "with T1 as(
    select createuid, wid, cid, max(cptime) as max_time
    from channelsinvitelog  natural join channels natural join workspaces
    where createuid = '$adminID' and wid = '$workspaceID' and cid = '$channelID')
    select *
    from T1 natural join ChannelsInviteLog
    where cptime = max_time and cstatus = 'SENT';";
        if ($conn):  //数据库连接成功
            $result = true;
            $result1 = mysqli_query($conn, $query_num_people);
            if (mysqli_num_rows($result1) >= 2){
                $result = false;
            }else{
                $result2 = mysqli_query($conn, $query_last_notification);
                if (mysqli_num_rows($result2) == 1){
                    $result = false;
                }else{
                    ;
                }
            }
            if ($result == true){
                $url1 = "channel_invite_temp.php?admin=$adminID&wname=$workspaceID&cname=$channelID&invite=once";
                echo "<script type='text/javascript'>";
                echo " location.href='$url1';";
                echo "</script>";
            }else{
                $url2 = "channel_home.php?admin=$adminID&wname=$workspaceID";
                echo "<script type='text/javascript'>";
                echo "alert(\"You can not invite others to join $cname now!\");";
                echo " location.href='$url2';";
                echo "</script>";
            }
        else://数据库连接失败?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail!</strong> <?php echo "Can't connect to database, please refresh the web page!"?>
            </div>
            <meta http-equiv="refresh" content="1; url=<?php echo "index.php"?>">
        <?php endif;

    mysqli_close($conn);
    else://加载invite界面仿照channel_invite.php
        $conn = custom_connect();
        if (isset($_GET['uname'])): //当用户点击invite按钮时重新加载页面
            $adminID = $_GET['admin']; $admin = getUsername($adminID);
            $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
            $channelID = $_GET['cname']; $cname = getChannelName($workspaceID, $channelID);
            $invitedUserNameID = $_GET['uname']; $invitedUserName = getUsername($invitedUserNameID);
            //$invite_once = invite_once($adminID, $invitedUserNameID, $workspaceID, $channelID);//判断是否只邀请过一次
            $not_deleted_removed = channelDeleted_memberRemoved($adminID, $workspaceID, $channelID);
            /************************************************************
             * 数据库查询admin邀请uname加入cname的请求若没有出现过或者出现过但是最近一次被拒绝
             * 则将invite_once设为true；否则为false
             * 获得当前用户名admin、频道名cname和工作空间名wname
             * 获得被邀请用户名invitedUserName
             * 数据库查询admin是否被移出cname或cname是否被删除，赋值给$deleted_removed
             **********************************************************/
            ?>
            <?php
            if ($not_deleted_removed == true):
                ?>

                    <div class="alert alert-success alert-dismissable fade show">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>Success!</strong> <?php echo "You invited user ".$invitedUserName," successfully"?>
                    </div>
                    <?php
                    $query_invite_channel = "insert into channelsinvitelog values ('$adminID','$invitedUserNameID','$workspaceID','$channelID','SENT',NOW());";
                    $result_invite_channel = mysqli_query($conn, $query_invite_channel);
                    if (!$result_invite_channel):
                        echo '<script>alert("Error! Can\'t sent the invitation!");</script>';
                    endif;
                    /************************************************************
                    数据库插入admin邀请uname加入wname中的cname的记录
                     ************************************************************/
                    ?>
                <meta http-equiv="refresh" content="2; url=<?php echo"channel_home.php?admin=$adminID&wname=$workspaceID"?>">
            <?php
            else:
                //重定向浏览器
                header("Location: channel_home.php?admin=$adminID&wname=$workspaceID");
                //确保重定向后，后续代码不会被执行
                exit;
                ?>
            <?php
            endif;?>
        <?php
        else: //页面被第一次加载时，从channel_home页面获得admin和cname和wname的参数
            $adminID = $_GET['admin']; $admin = getUsername($adminID);
            $workspaceID = $_GET['wname']; $wname = getWorkspaceName($workspaceID);
            $channelID = $_GET['cname']; $cname = getChannelName($workspaceID, $channelID);
            $not_deleted_removed = channelDeleted_memberRemoved($adminID, $workspaceID, $channelID);
            //$deleted_removed = false;
            /************************************************************
             * 数据库查询admin是否被移出cname或cname是否被删除，赋值给$deleted_removed
             **********************************************************/
            if ($not_deleted_removed == false):
                //重定向
                header("Location: channel_home.php?admin=$adminID&wname=$workspaceID");
                //确保重定向后，后续代码不会被执行
                exit;
            endif;
            ?>
        <?php endif; ?>
        <?php
        $notification_num = notification_num($adminID);
        $user_num = 0;$username = array();$email = array();
        if ($conn)://若连接数据库成功
            $query_invite = "select uid, email
from wu natural join users
where wid = '$workspaceID' and uid not in
                  (select uid
                  from cu
                  where cu.wid = '$workspaceID' and cu.cid = '$channelID');";
            $result = mysqli_query($conn, $query_invite);
            while ($row = mysqli_fetch_array($result)) {
                $username[] = $row['uid'];
                $email[] = $row['email'];
                $user_num += 1;
            }
            mysqli_close($conn);?>
        <?php else: //若数据库连接失败?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail!</strong> <?php echo "Can't connect to database, please refresh the web page!"?>
            </div>
            <meta http-equiv="refresh" content="2; url=<?php echo "index.php"?>">
        <?php endif;?>
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
                            <a href="<?php echo "dashboard.php?admin=$adminID"?>" style="display:block">Home</a>
                        </li>
                        <li class="list-group-item">
                            <a href="<?php echo "channel_home.php?admin=$adminID&wname=$workspaceID"?>" style="display:block">Channel</a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?php echo "notification.php?admin=".$adminID?>" style="display: block">Notification</a>
                            <span class="badge badge-primary badge-pill"><?php echo $notification_num ?></span>
                        </li>
                    </ul>
                </div>
                <?php
                if ($not_deleted_removed == true):
                    ?>
                    <div class="col-10">
                        <div class="tab-content" id="nav-tabContent">
                            <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                                <ul class="navbar-nav">
                                    <li class="nav-item active">
                                        <a class="navbar-brand" href="#">Invite</a>
                                    </li>
                                </ul>
                            </nav>
                            <div class="card my-2">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $cname ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?php echo "You can only invite users in the same workspace ".$wname," to ".$cname?></h6>
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
                                                        <button type="button" class="btn btn-outline-success"
                                                                onclick="javascript:window.location.href='<?php echo "channel_invite_temp.php?admin=".$adminID,"&cname=".$channelID,"&uname=".$username[$i],"&wname=".$workspaceID,"&invite=once"?>'">
                                                            invite
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
                        <strong>Fail!</strong> <?php echo "You were removed from this channel or this channel has been deleted! "?>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </div>
        </body>
        </html>

    <?php endif;?>
<?php
endif;
?>
