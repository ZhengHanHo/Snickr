<?php
/*需要传入的变量*/
include 'functions.php';
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
endif;
$adminID = $_GET['admin']; $admin = getUsername($adminID);
$uid = $adminID;$notification_num = notification_num($adminID);
$deleted_removed_workspace = true;
//$uid = 1;
/*需要传入的变量*/
if (isset($_POST['create_w'])):
    $wname = $_POST['workspace_name'];
    $wdescription = $_POST['workspace_description'];
    include('pdoconnect.php');
    if ($wname == "" or $wdescription == ""):?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "Please complete the table!" ?>
        </div>
    <?php elseif (strlen($wname) > 32 or strlen($wdescription) > 32):
        ?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><?php echo "Invalid input. Exceed length limit" ?></strong>
        </div>
    <?php else:
        $conn = pdo_connect();//尝试连接数据库
        $stmt = $conn->prepare("select now() as time");
        $stmt->execute();
        $now = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['time'];

        $stmt = $conn->prepare("insert into Workspaces (wname, description, wcreatetime) values (?,?,?);");//向数据库插入表单传来的值的sql
        $stmt->bindParam(1, $wname, PDO::PARAM_STR, 32);
        $stmt->bindParam(2, $wdescription, PDO::PARAM_STR, 32);
        $stmt->bindParam(3, $now, PDO::PARAM_STR, 20);
        $result1 = $stmt->execute();

        $stmt = $conn->prepare("select wid from Workspaces where wname = ? and description = ? and wcreatetime = ?;");
        $stmt->bindParam(1, $wname, PDO::PARAM_STR, 32);
        $stmt->bindParam(2, $wdescription, PDO::PARAM_STR, 32);
        $stmt->bindParam(3, $now, PDO::PARAM_STR, 20);
        $result2 = $stmt->execute();
        $newwid = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['wid'];

        $stmt = $conn->prepare("insert into WU (wid, uid, wutype) values (?,?,'ORIGINAL_ADMIN');");//向数据库插入表单传来的值的sql
        $stmt->bindParam(1, $newwid, PDO::PARAM_INT,11);
        $stmt->bindParam(2, $uid, PDO::PARAM_INT,11);
        $result3 = $stmt->execute();
        if ($result1 != False and $result2 != False and $result3 != False):?>
            <div class="alert alert-success alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Success!</strong>
            </div>
            <meta http-equiv="refresh" content="2; url=<?php echo "workspace_home.php?admin=$adminID" ?>">
        <?php else: ?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong><?php echo "Workspace already exist!" ?></strong>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>


<head>
    <meta charset="UTF-8">
    <title>Create Workspace</title>
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
<!doctype html>
<html lang="en">
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
                    <a href="<?php echo "workspace_home.php?admin=".$adminID ?>" style="display:block">Workspace</a>
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
                                <a class="navbar-brand" href="#">Create Channel</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-xl-6 col-lg-10 col-md-9">
                                <div class="card text-center my-5">
                                    <div class="card-header">
                                        <h3 class="card-title">Create a workspace</h3>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        <form accept-charset="UTF-8" role="form" action="<?php echo "workspace_create.php?admin=".$adminID ?>" method="post">
                                            <fieldset>
                                                <div class="list-group-item">
                                                    <input class="form-control" placeholder="Workspace name" name="workspace_name"
                                                           type="text">
                                                </div>
                                                <div class="list-group-item">
                                                    <input class="form-control" placeholder="Workspace description"
                                                           name="workspace_description" type="text"
                                                           value="">
                                                </div>
                                                <div class="list-group-item">
                                                    <input class="btn btn-primary btn-user btn-block" type="submit" value="Create"
                                                           name="create_w">
                                                    <hr>
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







