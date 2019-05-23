<?php
include 'functions.php';
session_start();
if ( isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin'] ) {
    //页面第一次被加载，从login或者notification中获得admin参数
    $adminID = $_GET['admin']; $admin = getUsername($adminID);
} else {
    // Redirect them to the login page
    header("Location: index.php");
    exit;
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 引入 Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
<!-- jQuery (Bootstrap 的 JavaScript 插件需要引入 jQuery) -->
<script src="https://code.jquery.com/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script type="text/javascript">
    function logout_js()
    {
        window.location.href='logout.php';
    }
</script>

<?php
$notification_num = notification_num($adminID);
$workspace_num = 0;$workspaceName = array();$createdTime = array();$shortDescription = array();
$conn = custom_connect();//尝试连接数据库
if ($conn)://若连接数据库成功
$query_workspace = "select wid, description, wcreatetime
from users natural join wu natural join workspaces
where uid = '$adminID'
ORDER BY wname ASC;";
$result = mysqli_query($conn, $query_workspace);
while ($row = mysqli_fetch_array($result)) {
    $workspaceName[] = $row['wid'];
    $createdTime[] = $row['wcreatetime'];
    $shortDescription[] = $row['description'];
    $workspace_num += 1;
}
    mysqli_close($conn);?>
<?php else: //若数据库连接失败?>
    <div class="alert alert-danger alert-dismissable fade show">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Fail!</strong> <?php echo "Can't connect to database, please refresh the web page!"?>
    </div>
    <meta http-equiv="refresh" content="2; url=<?php echo "index.php"?>">
<?php endif;?>

<nav class="navbar navbar-expand-sm bg-dark navbar-dark justify-content-between">
    <!-- Navbar content -->
    <ul class="navbar-nav">
        <li class="nav-item active">
            <a class="navbar-brand" href="#">Hello, <?php echo $admin?></a>
        </li>
    </ul>
    <form class="form-inline">
        <button class="btn btn-outline-danger my-2 my-sm-0" type="reset" onclick="logout_js()">Logout</button>
    </form>
</nav>
<div class="container-fluid my-3">
    <div class="row">
        <div class="col-2">
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="<?php echo "dashboard.php?admin=$adminID"?>" style="display:block">Home</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="<?php echo "notification.php?admin=$adminID"?>" style="display: block">Notification</a>
                    <span class="badge badge-primary badge-pill"><?php echo $notification_num ?></span>
                </li>
            </ul>
        </div>
        <div class="col-10">
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="list-home" role="tabpanel"
                     aria-labelledby="list-home-list">
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <!-- Navbar content -->
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">Home</a>
                            </li>
                        </ul>
                        <form class="form-inline my-2 my-lg-0">
                        </form>
                    </nav>
                    <div class="card my-2">
                        <div class="card-body">
                            <h5 class="card-title">Workspaces</h5>
                            <p class="card-text"><?php echo "You currently join $workspace_num workspace(s)"?></p>
                            <!--<a href="#list-Workspaces" class="btn btn-outline-success">Manage My Workspace(s)</a>-->
                            <a href="<?php echo "workspace_home.php?admin=".$adminID?>" class="btn btn-outline-success">Manage My Workspace</a>
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">Workspace Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Created Time</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php for ($i = 0; $i < $workspace_num; $i++):?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo "channel_home.php?admin=".$adminID,"&wname=".$workspaceName[$i]?>">
                                                <?php echo getWorkspaceName($workspaceName[$i])?>
                                            </a>
                                        </td>
                                        <td><p> <?php echo $shortDescription[$i]?> </p></td>
                                        <td><p> <?php echo $createdTime[$i]?> </p></td>
                                    </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
                <div class="tab-pane fade" id="list-Workspaces" role="tabpanel"
                     aria-labelledby="list-Workspaces-list">
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <!-- Navbar content -->
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">Workspaces</a>
                            </li>
                        </ul>
                        <form class="form-inline">
                            <button class="btn btn-success my-2 my-sm-0" type="submit">Create</button>
                        </form>
                    </nav>
                    <div class="card my-2">
                        <div class="card-body">
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">WorkspaceID</th>
                                        <th scope="col">Workspace Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Role</th>
                                        <th scope="col">Manage</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>11111111</td>
                                        <td>Wname1</td>
                                        <td>Wdescription</td>
                                        <td>Creater</td>
                                        <td>
                                            <button class="btn btn-outline-success my-2 my-sm-0"
                                                    type="submit">Invite</button>
                                            <button class="btn btn-outline-danger my-2 my-sm-0"
                                                    type="submit">Leave</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="list-Channels" role="tabpanel" aria-labelledby="list-Channels-list">
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <!-- Navbar content -->
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">Channels</a>
                            </li>
                        </ul>
                        <form class="form-inline">
                            <button class="btn btn-success my-2 my-sm-0" type="submit">Create</button>
                        </form>
                    </nav>
                </div>
                <div class="tab-pane fade" id="list-Notifications" role="tabpanel"
                     aria-labelledby="list-Notifications-list">
                    <nav class="navbar navbar-expand-sm navbar-light bg-light justify-content-between">
                        <!-- Navbar content -->
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <a class="navbar-brand" href="#">Notifications</a>
                            </li>
                        </ul>
                    </nav>
                    <div class="card my-2">
                        <div class="card-body">
                            <div class="row pre-scrollable my-2">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th scope="col">Content</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>User A invites you to join Workspace W1</td>
                                        <td>
                                            <button class="btn btn-success my-2 my-sm-0"
                                                    type="submit">Accept</button>
                                            <button class="btn btn-danger my-2 my-sm-0"
                                                    type="submit">Refuse</button>
                                            <button class="btn btn-warning my-2 my-sm-0"
                                                    type="submit">Dismiss</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>User B invites you to join Channel C1</td>
                                        <td>
                                            <button class="btn btn-success my-2 my-sm-0"
                                                    type="submit">Accept</button>
                                            <button class="btn btn-danger my-2 my-sm-0"
                                                    type="submit">Refuse</button>
                                            <button class="btn btn-warning my-2 my-sm-0"
                                                    type="submit">Dismiss</button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

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
