<?php

function pdo_connect(){
    $servername = "127.0.0.1";
    $username = "root";
    $password = "Hyhezhenghan1997";
    $dbname = "snickr";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    if(!$conn){
        return false;//连接数据库失败输出false
    }else{
        return $conn;
    }
};



