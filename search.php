<?php
//$userid = 1;
//$User_Name = "Tom";
////会跳用
//$wid;
//$cid = 1;
include('functions.php');
include('pdoconnect.php');
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
endif;
$userid = $_GET['admin'];
$wid = $_GET['wname'];
$cid = $_GET['cname'];
$User_Name = getUsername($userid);
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- 引入 Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
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


    <!-- Begin emoji-picker Stylesheets -->
    <link href="/lib/css/emoji.css" rel="stylesheet">
    <!-- End emoji-picker Stylesheets -->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">


</head>

<!-- 以下是导航栏-->
<nav class="navbar navbar-expand-sm bg-dark navbar-dark justify-content-between">
    <!-- Navbar content -->
    <ul class="navbar-nav">
        <li class="nav-item active">
            <a class="navbar-brand" href="#"><?php echo "Hello ".$User_Name."!"?></a>
        </li>
    </ul>
    <div class="form-inline">
        <form class="form-inline" role="form" action="<?php echo "search.php?admin=$userid&wname=$wid&cname=$cid"?>" method="post">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search"
                   name="searchtext">
            <button class="btn btn-outline-success my-2 mr-2" type="submit" name="searchsubmit">Search</button>
        </form>
        <button class="btn btn-outline-danger my-2 my-sm-0" type="reset" onclick="logout_js()">Logout</button>
    </div>
</nav>
<!-- 以上是导航栏 -->

<div class="container-fluid my-3">
    <div class="card">
        <!-- 以下是Search标题栏-->
        <div class="card-header">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="navbar-brand" href="#">Search result</a>
                </li>
            </ul>
            <form class="form-inline">
                <a href="<?php echo "chat.php?admin=$userid&wname=$wid&cname=$cid"?>" class="btn btn-primary">Back to Chat</a>
            </form>
        </div>
        <!-- 以上是Search标题栏-->
        <div class="card-body">
            <div class="row pre-scrollable" style="max-height: 430px">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Workspace Name</th>
                        <th scope="col">Channel Name</th>
                        <th scope="col">Message</th>
                    </tr>
                    </thead>
                    <tbody
                    <?php
                    $conn = pdo_connect();//尝试连接数据库
                    if (isset($_POST['searchsubmit'])):
                        $searchtext = $_POST['searchtext'];
                        if($searchtext == ""){
                            //查询字符串为空，跳回chat界面
                            echo '<script></script>';
                            $url = "chat.php?admin=$userid&wname=$wid&cname=$cid";
                            echo "<script type='text/javascript'>";
                            echo " location.href='$url';";
                            echo "</script>";
                        }
                        if ($conn)://若连接数据库成功
                            $stmt = $conn->prepare("select wname,cname,username,content,mtime
from MessageContent natural join MessageFrom natural join Users natural join Channels natural join Workspaces
where mid in
(select mid from MessageFrom where cid in (select cid from CU where uid = ?))
and locate(?,content)>0 and mtype = 'TEXT'
order by mtime desc;");
                            $stmt->bindParam(1, $userid, PDO::PARAM_INT, 11);
                            $stmt->bindParam(2, $searchtext, PDO::PARAM_STR, 128);
                            $result = $stmt->execute();
                            $rowarray = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $row_num = 0;
                            if (sizeof($rowarray) > 0) {
                                while ($row_num<sizeof($rowarray)) {
                                    $wname = $rowarray[$row_num]['wname'];
                                    $cname = $rowarray[$row_num]['cname'];
                                    $textuser = $rowarray[$row_num]['username'];
                                    $texttime = $rowarray[$row_num]['mtime'];
                                    $textcontent = $rowarray[$row_num]['content'];
                                    $row_num += 1;
                                    $row_num = htmlspecialchars($row_num);
                                    $wname = htmlspecialchars($wname);
                                    $cname= htmlspecialchars($cname);
                                    $texttime = htmlspecialchars($texttime);
                                    $textuser = htmlspecialchars($textuser);
                                    $textcontent = htmlspecialchars($textcontent);
                                    echo <<<EOL
<tr>
      <th scope="row">$row_num</th>
      <td>$wname</td>
      <td>$cname</td>
      <td>
      <div class="media-body">
        <h5 class="mt-0 mb-1">($texttime)$textuser:</h5>
            $textcontent
        </div>
       </td>
</tr>
                                 
EOL;
                                }
                            } else {
                                echo <<<EOL
                    <tr>
                        <div class="media-body">
                            <h5 class="mt-0 mb-1">No result Found!</h5>
                            <br>
                        </div>
                    </tr>
EOL;
                            }
                            ?>
                        <?php else: //若数据库连接失败?>
                            <div class="alert alert-danger alert-dismissable fade show">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>Fail to connect database!</strong>
                            </div>
                            <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</html>