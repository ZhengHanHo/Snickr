<?php
/**
 * Created by PhpStorm.
 * User: Jiaqi Li
 * Date: 2019/5/9
 * Time: 23:06
 */
?>
<?php
include('functions.php');
include('pdoconnect.php');
session_start();
if (isset($_GET['wname']) && isset($_GET['admin'])){
    if($_GET['admin'] != $_SESSION['username']){
        header("Location: index.php");
        exit;
    }
    //session_start();
    $_SESSION['admin'] = $_GET['admin'];
    $_SESSION['wname'] = $_GET['wname'];
    $workspaceID = $_SESSION['wname'] ;
    $adminID = $_SESSION['admin'];
    $admin = getUsername($adminID);
    $notification_num  = notification_num($adminID);
}else{
    //session_start();
    $workspaceID = $_SESSION['wname'] ;
    $adminID = $_SESSION['admin'];
    $admin = getUsername($adminID);
    $notification_num  = notification_num($adminID);
}

if (isset($_POST['login_sub']))://用户点击invite按钮
    $email = $_POST['email'];
    //$password = $_POST['password'];
    if ($email == "" ):?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "Please enter E-mail address!" ?>
        </div>
    <?php elseif (filter_var($email, FILTER_VALIDATE_EMAIL) == false or strlen($email) > 32):
        ?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><?php echo "Invalid Email address. Please check your input!" ?></strong>
        </div>
    <?php else://输入的合法性检查通过

        $connn = pdo_connect();//尝试连接数据库
        $conn = custom_connect();
        if ($connn and $conn)://若连接数据库成功
            $stmt = $connn->prepare("select uid from Users where email = ?;");
            $stmt->bindParam(1,$email,PDO::PARAM_STR,32);
            $result1 = $stmt->execute();
            $count1 = sizeof($stmt->fetchAll(PDO::FETCH_ASSOC));
            //echo $count1;
            if ($result1 == true):
                //echo "<script> alert("<?php echo $count1>">)"
                if ($count1== 1)://登陆成功
                    //echo "<script> alert('cscsc')</script>";
                    $result1 = $stmt->execute();
                    $userID = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['uid'];
                //echo $userID;
                    $uuname = getUsername($userID);
                    $q2 = "select * from wu where  uid = '$userID' and wid = '$workspaceID';";
                    $result2 = mysqli_query($conn, $q2);
                    if (mysqli_num_rows($result2) == 0):
                        $invite_once = invite_once2($adminID, $userID, $workspaceID);
                        if ($invite_once == true):
                            $query_insert_invitation = "insert into workspacesinvitelog values ('$adminID', '$userID', '$workspaceID', 'SENT', now());";
                            $result_insert_invitation = mysqli_query($conn, $query_insert_invitation);
                            if (!$result_insert_invitation):
                                echo '<script>alert("Error! Can\'t invite this user!");</script>';
                            endif;
                            $url = "workspace_invite.php?admin=$adminID&wname=$workspaceID";
                            echo "<script type='text/javascript'>";
                            echo "alert(\"You invited the user $uuname successfully!\");";
                            echo " location.href='$url';";
                            echo "</script>";
                        else://此人已经邀请过一次?>
                            <div class="alert alert-danger alert-dismissable fade show">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>You've already invited this user once!</strong>
                            </div>
                        <?php endif;
                    else://此人已经加入workspace?>
                        <div class="alert alert-danger alert-dismissable fade show">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>This user is already in the workspace!</strong>
                        </div>
                    <?php endif;
                else: //查无此人?>
                    <div class="alert alert-danger alert-dismissable fade show">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <strong>No such user!</strong>
                    </div>
                <?php
                endif;?>
            <?php endif;?>
        <?php else: //若数据库连接失败?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail to connect database!</strong>
            </div>
            <meta http-equiv="refresh" content="2; url=<?php echo "index.php"?>">
        <?php endif;
        mysqli_close($conn);
    endif; ?>
<?php endif; ?>



<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 引入 Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
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
        <div class="col-10">
            <div class="tab-content" id="nav-tabContent">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-xl-6 col-lg-10 col-md-9">
                            <div class="card text-center my-5">
                                <div class="card-header">
                                    <h3 class="card-title">invite</h3>
                                </div>
                                <div class="list-group list-group-flush">
                                    <form accept-charset="UTF-8" role="form" action="workspace_invite.php" method="post">
                                        <fieldset>
                                            <div class="list-group-item">
                                                <input class="form-control" placeholder="E-mail" name="email" type="text">
                                            </div>
                                            <div class="list-group-item">
                                                <input class="btn btn-primary btn-user btn-block" type="submit" value="Invite"
                                                       name="login_sub">
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>