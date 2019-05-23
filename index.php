<?php
include('pdoconnect.php');
session_start();
if (isset($_SESSION['username'])) {
    //保持用户上一次的登录状态，直接进入dashboard界面
    $username = $_SESSION['username'];
    header("Location: dashboard.php?admin=".$username);
    exit;
}

if (isset($_POST['login_sub']))://用户点击Login按钮
    $email = $_POST['email'];
    $password = $_POST['password'];
    if ($email == "" or $password == ""):?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail!</strong> <?php echo "Please complete the table!" ?>
        </div>
    <?php elseif (filter_var($email, FILTER_VALIDATE_EMAIL) == false or strlen($email) > 32):
        ?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><?php echo "Invalid Email address. Please check your input!" ?></strong>
        </div>
    <?php elseif (strlen($password) > 32):
        ?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><?php echo "Invalid Password. Please check your input!" ?></strong>
        </div>
    <?php else://输入的合法性检查通过
        $conn = pdo_connect();//尝试连接数据库
        if ($conn)://若连接数据库成功
            $stmt = $conn->prepare("select uid from Users where email = ? and password = ?");
            $stmt->bindParam(1, $email, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $password, PDO::PARAM_STR, 32);
            //$q = "select username from Users where email = '$email' and password = '$password'";//向数据库插入表单传来的值的sql
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($result) == 1)://登陆成功
                $userid = $result[0]['uid'];
                session_start();
                $lifeTime = 1 * 3600;
                setcookie(session_name(), session_id(), time() + $lifeTime, "/");
                $_SESSION['username'] = $userid;
                header("location: dashboard.php?admin=$userid");
                exit;
            else: //登录失败
                ?>
                <div class="alert alert-danger alert-dismissable fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>Wrong email or password!</strong>
                </div>
            <?php
            endif; ?>
        <?php else: //若数据库连接失败?>
            <div class="alert alert-danger alert-dismissable fade show">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>Fail to connect database!</strong>
            </div>
            <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>">
        <?php endif; ?>
    <?php endif; ?>
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

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-10 col-md-9">
            <div class="card text-center my-5">
                <div class="card-header">
                    <h3 class="card-title">Log in</h3>
                </div>
                <div class="list-group list-group-flush">
                    <form accept-charset="UTF-8" role="form" action="index.php" method="post">
                        <fieldset>
                            <div class="list-group-item">
                                <input class="form-control" placeholder="E-mail" name="email" type="text">
                            </div>
                            <div class="list-group-item">
                                <input class="form-control" placeholder="Password" name="password" type="password"
                                       value="">
                            </div>
                            <div class="list-group-item">
                                <input class="btn btn-primary btn-user btn-block" type="submit" value="Login"
                                       name="login_sub">
                                <hr>
                                <a class="small" href="register.php">Create an Account!</a>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>