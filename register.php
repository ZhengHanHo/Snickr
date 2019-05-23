<!doctype html>
<html lang="en">



<?php
if (isset($_POST['signup_sub'])):
    $email = $_POST['email'];
    $password = $_POST['password'];
    $username = $_POST['username'];
    $nickname = $_POST['nickname'];
    include('pdoconnect.php');
    if ($email == "" or $password == "" or $username == "" or $nickname == ""):?>
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
    <?php elseif (strlen($username) > 32):
        ?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><?php echo "Invalid Username. Please check your input!" ?></strong>
        </div>
    <?php elseif (strlen($nickname) > 32):
        ?>
        <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><?php echo "Invalid Nickname. Please check your input!" ?></strong>
        </div>
    <?php else:
        $conn = pdo_connect();//尝试连接数据库
        if ($conn)://若连接数据库成功
            $stmt = $conn->prepare("insert into Users(email,username,nickname,password,registertime) values (?,?,?,?,now())");//向数据库插入表单传来的值的sql
            $stmt->bindParam(1, $email, PDO::PARAM_STR, 32);
            $stmt->bindParam(2, $username, PDO::PARAM_STR, 32);
            $stmt->bindParam(3, $nickname, PDO::PARAM_STR, 32);
            $stmt->bindParam(4, $password, PDO::PARAM_STR, 32);
            $result = $stmt->execute();
            if($result == True):?>
                <div class="alert alert-success alert-dismissable fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong>Success!</strong>
                </div>
            <?php else: ?>
                <div class="alert alert-danger alert-dismissable fade show">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <strong><?php echo "User already exist! Please log in or try other email or username!" ?></strong>
                </div>
            <?php endif; ?>

        <?php else: //若数据库连接失败?>
            <div class="alert alert-danger alert-dismissable fade show">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong>Fail to connect database!</strong>
            </div>
            <meta http-equiv="refresh" content="2; url=<?php echo "index.php" ?>">
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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
                    <h3 class="card-title">Register an account</h3>
                </div>
                <div class="list-group list-group-flush">
                    <form accept-charset="UTF-8" role="form" action="register.php" method="post">
                        <fieldset>
                            <div class="list-group-item">
                                <input class="form-control" placeholder="E-mail" name="email" type="text">
                            </div>
                            <div class="list-group-item">
                                <input class="form-control" placeholder="Password" name="password" type="password"
                                       value="">
                            </div>
                            <div class="list-group-item">
                                <input class="form-control" placeholder="Username" name="username" type="text">
                            </div>
                            <div class="list-group-item">
                                <input class="form-control" placeholder="Nickname" name="nickname" type="text">
                            </div>
                            <div class="list-group-item">
                                <input class="btn btn-primary btn-user btn-block" type="submit" value="signup"
                                       name="signup_sub">
                                <hr>
                                <a class="small">Already have an account? </a><a class="small" href="index.php">Log
                                    in!</a>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>


