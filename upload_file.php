<?php
/*需要传入的变量*/
include 'functions.php';
session_start();
if ( !(isset( $_SESSION['username']) && isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
endif;
$userid = $_GET['admin'];
$workspace_id = $_GET['wname'];
$channel_id = $_GET['cname'];
//$userid = 1;
//$workspace_id = 1;
//$channel_id = 1;
/*需要传入的变量*/
/*以下为文件上传代码*/
// 允许上传的图片后缀
$allowedExts = array("gif", "jpeg", "jpg", "png");
$temp = explode(".", $_FILES["file"]["name"]);
//echo $_FILES["file"]["size"];
$extension = end($temp);     // 获取文件后缀名
if ((($_FILES["file"]["type"] == "image/gif")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/jpg")
        || ($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/x-png")
        || ($_FILES["file"]["type"] == "image/png"))
    && ($_FILES["file"]["size"] < 204800000)
    && in_array($extension, $allowedExts)) {
    if ($_FILES["file"]["error"] > 0) {
        echo "错误：: " . $_FILES["file"]["error"] . "<br>";
    } else {
//        echo "上传文件名: " . $_FILES["file"]["name"] . "<br>";
//        echo "文件类型: " . $_FILES["file"]["type"] . "<br>";
//        echo "文件大小: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
//        echo "文件临时存储的位置: " . $_FILES["file"]["tmp_name"] . "<br>";
        $uniqid = md5(uniqid(microtime(true), true)); //随机文件名
        $append = explode('.', $_FILES["file"]["name"])[1];
        $uniqid = $uniqid . '.' . $append;
        // 判断当期目录下的 upload 目录是否存在该文件
        // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
        if (file_exists("upload/" . $uniqid)) {
            echo $uniqid . " 文件已经存在。 ";
        } else {

            // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $uniqid);
//            echo "文件存储在: " . "upload/" . $uniqid;
            /*以下为插入数据库代码*/
            //include('functions.php');

            $messagecontent = "upload/" . $uniqid;

            $conn = custom_connect();
            $validate = channelDeleted_memberRemoved($userid, $workspace_id, $channel_id);
            if ($conn && $validate):
                //需要做是否存在channel的验证
                //
                //
                //

                $q = "insert into MessageContent (uid,mtype,content) values ('$userid','IMAGE','$messagecontent');";//向数据库插入表单传来的值的sql
                mysqli_real_query($conn, $q);

                $q = "select max(mid) as mid from MessageContent where uid = '$userid' and mtype = 'IMAGE' and content = '$messagecontent';";
                $result = mysqli_query($conn, $q);
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $insertmid = $row['mid'];

                $q = "insert into MessageFrom (wid, cid, mid, mtime) values ('$workspace_id','$channel_id','$insertmid',now());";//向数据库插入表单传来的值的sql
                mysqli_real_query($conn, $q);
                ?><?php else: //若数据库连接失败?>
                <div class="alert alert-danger alert-dismissable fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>Fail to connect database!</strong>
                </div>
                <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>"><?php endif;
        }
    }
} else {?>
     <script> alert("Illegal File!") </script>;
    <?php $url = "chat.php?admin=$userid&wname=$workspace_id&cname=$channel_id";
    echo "<script type='text/javascript'>";
    echo " location.href='$url';";
    echo "</script>";?>
<?php }
/*以上为文件上传代码*/
//header("location: chat.php");
$url = "chat.php?admin=$userid&wname=$workspace_id&cname=$channel_id";
echo "<script type='text/javascript'>";
echo " location.href='$url';";
echo "</script>";

