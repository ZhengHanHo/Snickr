<?php
/*需要传入的变量*/
include('pdoconnect.php');
include('functions.php');
session_start();
if ( false ):    //用户尚未登录，必须重新登录
    header("Location: index.php");
    exit;
else:
    //echo $_SESSION['username']; echo $_GET['admin'];echo $_SESSION['chat_admin'];
    if (isset($_GET['admin']) && ($_SESSION['username'] == $_GET['admin']) ) {
        $userid = $_GET['admin'];
        $workspace_id = $_GET['wname'];
        $channel_id = $_GET['cname'];
        $_SESSION['chat_admin'] = $userid;
        $_SESSION['chat_wname'] = $workspace_id;
        $_SESSION['chat_cname'] = $channel_id;
    }elseif( !isset($_GET['admin']) || (isset($_GET['admin']) && $_SESSION['username'] == $_GET['admin']) ){
        $userid = $_SESSION['chat_admin'];
        $workspace_id = $_SESSION['chat_wname'];
        $channel_id = $_SESSION['chat_cname'];
    }else{
        header("Location: index.php");
        exit;
    }
    $User_Name = getUsername($userid);
    $Channel_Name = getChannelName($workspace_id, $channel_id);
//$channel_id = 1;
//$workspace_id = 1;
//$userid = 1;
//$Channel_Name = "NYU_CS";
//$User_Name ="Tom";
/*需要传入的变量*/
endif;
//替换超链接函数
function autolink($str)
{
    $regex = '@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))@';
    return preg_replace($regex, '<a href="$1">$1</a>', $str);
}
/*发送消息模块*/
$conn = pdo_connect();
if (isset($_POST['SendMessage'])):
    $messagecontent = $_POST['inputtext'];
    $validate = channelDeleted_memberRemoved($userid, $workspace_id, $channel_id);
    if ($conn && $validate):
        //需要做是否存在channel的验证
        //
        //
        //
        $stmt = $conn->prepare("insert into MessageContent (uid,mtype,content) values (?,'TEXT',?);");//向数据库插入表单传来的值的sql
        $stmt->bindParam(1, $userid, PDO::PARAM_INT, 11);
        $stmt->bindParam(2, $messagecontent, PDO::PARAM_STR, 128);
        $result1 = $stmt->execute();

        $stmt = $conn->prepare("select max(mid) as mid from MessageContent where uid = ? and mtype = 'TEXT' and content = ?;");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT, 11);
        $stmt->bindParam(2, $messagecontent, PDO::PARAM_STR, 128);
        $result2 = $stmt->execute();
        $newmid = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['mid'];


        $stmt = $conn->prepare("insert into MessageFrom (wid, cid, mid, mtime) values (?,?,?,now());");//向数据库插入表单传来的值的sql
        $stmt->bindParam(1, $workspace_id, PDO::PARAM_INT,11);
        $stmt->bindParam(2, $channel_id, PDO::PARAM_INT,11);
        $stmt->bindParam(3, $newmid, PDO::PARAM_INT,11);
        $result3 = $stmt->execute();
        //语句执行出错
        if ($result1 == False or $result2 == False or $result3 == False):?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Unknow Problem! Database crash!!!</strong>
            </div>
            <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>">
        <?php endif;?>

    <?php else: //若数据库连接失败?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail to connect database!</strong>
        </div>
        <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>">
    <?php endif; ?>
<?php endif; ?>


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
    <link href="lib/css/emoji.css" rel="stylesheet">
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
        <form class="form-inline" role = "form" action = "<?php echo "search.php?admin=$userid&wname=$workspace_id&cname=$channel_id"?>" method="post">
            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" name="searchtext">
            <button class="btn btn-outline-success my-2 mr-2" type="submit" name = "searchsubmit">Search</button>
        </form>
        <button class="btn btn-outline-danger my-2 my-sm-0" type="reset" onclick="logout_js()" >Logout</button>
    </div>
</nav>
<!-- 以上是导航栏 -->

<div class="container-fluid my-3">
    <div class="card">
        <!-- 以下是Channel标题栏-->
        <div class="card-header">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="navbar-brand" href="#"><?php echo $Channel_Name?></a>
                </li>
            </ul>
            <form class="form-inline">
                <a href="<?php echo "channel_home.php?admin=$userid&wname=$workspace_id"?>" class="btn btn-primary">Back to list</a>
            </form>
        </div>
        <!-- 以上是Channel标题栏-->

        <div class="card-body" id = "refresh">
            <!-- 以下是聊天记录list-->
            <div class="row pre-scrollable" style="max-height: 430px" id = "rollrollroll">
                <ul class="list-group" style="width: 100%">
                    <!--以下是插入聊天记录                    --><?php
                    $conn = custom_connect();
                    if ($conn)://若连接数据库成功
                        $q = "select username,mtype,content,mtime
from MessageFrom natural join MessageContent natural join Users
Where wid = '$workspace_id' and cid = '$channel_id'
order by mtime asc;";
                        $result = $conn->query($q);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $messagefromuser = $row["username"];
                                $messagetype = $row["mtype"];
                                $messagecontent = $row["content"];
                                $messagetime = $row['mtime'];
                                if ($messagetype == 'TEXT') {
                                    $messagecontent = htmlspecialchars($messagecontent);
                                    $messagecontent = autolink($messagecontent);
                                    echo <<<EOL
                    <li class="list-group-item">
                        <div class="media-body">
                            <h5 class="mt-0 mb-1">($messagetime)$messagefromuser:</h5>
                            $messagecontent
                        </div>
                    </li>
EOL;
                                } else if ($messagetype == 'IMAGE') {
                                    echo <<<EOL
                    <li class="list-group-item">
                        <div class="media-body">
                            <h5 class="mt-0 mb-1">($messagetime)$messagefromuser:</h5>
                            <img class="align-self-center mr-3" src="$messagecontent" height="250" width="400"
                                 alt="Generic placeholder image">

                        </div>
                    </li>
EOL;
                                }
                            }
                        } else {
                            echo <<<EOL
                    <li class="list-group-item">
                        <div class="media-body">
                            <h5 class="mt-0 mb-1">No message!</h5>
                        </div>
                    </li>
EOL;
                        }
                    else: //若数据库连接失败
                        ?>
                        <div class="alert alert-danger alert-dismissable fade show">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>Fail to connect database!</strong>
                        </div>
                        <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>">
                    <?php endif; ?>
                    <!--以上是插入聊天记录                    -->
                </ul>
            </div>
            <!-- 以上是聊天记录list-->
            <br>
        </div>

        <div class="row align-items-start ml-2">
<!--            12 8 6 4-->
            <div class="col-10 col-md-6">
                <!-- 以下是文本输入emoji框-->
                <form accept-charset="UTF-8" role="form" action="<?php echo "chat.php?admin=".$userid,"&wname=".$workspace_id,"&cname=".$channel_id?>" method="post">
                    <div class="input-group mb-3">
                        <!--                                <p class="lead emoji-picker-container">-->
                        <input type="text" class="form-control" placeholder="Input field" data-emojiable="true"
                               contenteditable="true" name="inputtext">
                        <!--                                </p>-->
                    </div>
                    <div class="input-group-append mb-2">
                        <button class="btn btn-success" type="submit" name="SendMessage"
                        ">Send</button>
                    </div>
                </form>

                <!-- 以上是文本输入emoji框-->
            </div>
            <div class="col-6 col-md-4">
                <!-- 以下是文件选择框-->
                <form action="<?php echo "upload_file.php?admin=".$userid,"&wname=".$workspace_id,"&cname=".$channel_id?>" method="post" enctype="multipart/form-data">
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="inputGroupFile" name="file">
                            <label class="custom-file-label" for="inputGroupFile">Choose file</label>
                        </div>
                        <div class="input-group-append">
                            <button class="btn btn-info" type="submit" name="submit" value="Submit">Upload</button>
                        </div>
                    </div>
                </form>
                <!-- 以上是文件选择框-->
            </div>
        </div>
    </div>
</div>


<script>
    $('#inputGroupFile').on('change', function () {
        //get the file name
        var fileName = $(this).val();
        //replace the "Choose a file" label
        $(this).next('.custom-file-label').html(fileName);
    })
</script>


<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="js/plugins.js"></script>


<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>

<!-- Begin emoji-picker JavaScript -->
<script src="lib/js/config.js"></script>
<script src="lib/js/util.js"></script>
<script src="lib/js/jquery.emojiarea.js"></script>
<script src="lib/js/emoji-picker.js"></script>
<!-- End emoji-picker JavaScript -->
<!--自动刷新局部页面-->
<!--自动刷新局部页面-->
<script type="text/javascript">
    // window.onload = setupRefresh;
    // function setupRefresh() {
    //     setInterval("refreshBlock();", 1000);
    // }
    // function refreshBlock() {
    //     document.getElementById('rollrollroll').scrollTop = document.getElementById('rollrollroll').scrollHeight
    //
    //     document.load("chattest.php #refresh");
    //     //$("#refresh").load("mescontent.php");
    //
    // }
    // $('#rollrollroll').scroll(function() {
    //     if ($('#rollrollroll').html().length) {
    //         scroll_l = $('#rollrollroll').scrollLeft();
    //         scroll_t = $('#rollrollroll').scrollTop();
    //     }
    // });
    i = 1;
    var auto_refresh = setInterval(
        (function () {
            if(i == 1) {
                $("#refresh").load("chat.php #refresh",function () {
                    $('#rollrollroll').scrollTop(100000000);
                }); //Load the content into the div
            }
            else {
                var scroll_t = $('#rollrollroll').scrollTop();
                var height = document.getElementById('rollrollroll').scrollHeight
                if(scroll_t+430 != height){
                    $("#refresh").load("chat.php #refresh",function () {
                        $('#rollrollroll').scrollTop(scroll_t);
                    }); //Load the content into the div
                }
                else {
                    $("#refresh").load("chat.php #refresh",function () {
                        var height = document.getElementById('rollrollroll').scrollHeight
                        $('#rollrollroll').scrollTop(height);
                    }); //Load the content into the div
                }
            }
            i += 1;
            if(i == 5){
                i = 2;
            }

        }), 100);
</script>
<script>
    //保证滚动条在最下面
    document.getElementById('rollrollroll').scrollTop = document.getElementById('rollrollroll').scrollHeight
</script>
<script>
    $(function () {
        // Initializes and creates emoji set from sprite sheet
        window.emojiPicker = new EmojiPicker({
            emojiable_selector: '[data-emojiable=true]',
            assetsPath: 'lib/img/',
            popupButtonClasses: 'fa fa-smile-o'
        });
        // Finds all elements with `emojiable_selector` and converts them to rich emoji input fields
        // You may want to delay this step if you have dynamically created input fields that appear later in the loading process
        // It can be called as many times as necessary; previously converted input fields will not be converted again
        window.emojiPicker.discover();
    });
</script>
<script>
    // Google Analytics
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-49610253-3', 'auto');
    ga('send', 'pageview');
</script>
