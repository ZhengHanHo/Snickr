<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'Hyhezhenghan1997');
define('DB_NAME', 'snickr');

function custom_connect(){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if(!$conn){
        echo '<script>alert("Error! Can\'t connect to database!");</script>';
        return false;//连接数据库失败输出false
    }else{
        return $conn;
    }
};
function getUsername($uid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query_user = "select username as name from users where uid = '$uid';";
        $result = mysqli_query($conn, $query_user);
        if (mysqli_num_rows($result) == 1){
            $username = mysqli_fetch_array($result)['name'];
            mysqli_close($conn);
            return $username;
        }else{
            echo '<script>alert("Error! Can\'t find the user!");</script>';
            mysqli_close($conn);
            return "";
        }
    }
};
function getWorkspaceName($wid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query_workspace = "select wname as name from workspaces where wid = '$wid';";
        $result = mysqli_query($conn, $query_workspace);
        if (mysqli_num_rows($result) == 1){
            $workspaceName = mysqli_fetch_array($result)['name'];
            mysqli_close($conn);
            return $workspaceName;
        }else{
            echo '<script>alert("Error! Can\'t find the workspace!");</script>';
            mysqli_close($conn);
            return "";
        }
    }
};
function getChannelName($wid, $cid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query_channel = "select cname as name from channels where wid = '$wid' and cid = '$cid';";
        $result = mysqli_query($conn, $query_channel);
        if (mysqli_num_rows($result) == 1){
            $channelName = mysqli_fetch_array($result)['name'];
            mysqli_close($conn);
            return $channelName;
        }else{
            echo '<script>alert("Error! Can\'t find the channel!");</script>';
            mysqli_close($conn);
            return "";
        }
    }
};
function getUidbyEmail($email){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query_getUidbyEmail = "select uid from users where email = '$email';";
        $result = mysqli_query($conn, $query_getUidbyEmail);
        $userID = mysqli_fetch_array($result)['uid'];
        mysqli_close($conn);
        return $userID;
    }
};
function notification_num($adminID) {
    $conn = custom_connect();
    if (!$conn){
        echo '<script>alert("Error! Can\'t connect to database!");</script>';
        exit;
    }
    else{
        $query_notification_workspace = "with T1 as(
  select createuid, inviteduid, wid, max(wptime) as max_time
  from workspacesinvitelog  join users u1 join users u2 natural join workspaces
  where createuid = u1.uid and inviteduid = u2.uid and u2.uid = '$adminID'
  group by createuid, inviteduid, wid)
  select count(*) as cnt
  from T1 natural join workspacesinvitelog
  where wptime = max_time  and wstatus = 'SENT' 
    and wid not in 
        (select wid from wu where uid = '$adminID');";
        $query_notification_channel = "with T1 as(
  select createuid, inviteduid, wid, cid, max(cptime) as max_time
  from channelsinvitelog join users u1 join users u2 natural join channels natural join workspaces
  where createuid = u1.uid and inviteduid = u2.uid and u2.uid = '$adminID'
  group by createuid, inviteduid, wid, cid)
  select count(*) as cnt
  from T1 natural join ChannelsInviteLog
  where cptime = max_time  and cstatus = 'SENT' 
    and (cid, wid) not in 
        (select cid, wid from cu where uid = '$adminID');";
        $result = mysqli_query($conn, $query_notification_workspace);
        $notification_workspace_num = mysqli_fetch_array($result)['cnt'];
        $result = mysqli_query($conn, $query_notification_channel);
        $notification_channel_num = mysqli_fetch_array($result)['cnt'];
        mysqli_close($conn);
        return $notification_workspace_num + $notification_channel_num;
    }
};
function notification_received($adminID){
    $conn = custom_connect();
    if (!$conn){
        echo '<script>alert("Error! Can\'t connect to database!");</script>';
        exit;
    }
    else{
        $workspace_num_received = 0;$channel_num_received = 0;
        $workspace_username_received = array();$workspace_received = array();
        $channel_username_received = array();$channel_received = array();$channel_workspace_received = array();
        $query_workspace_received = "with T1 as(
  select createuid, inviteduid, wid, max(wptime) as max_time
  from workspacesinvitelog  join users u1 join users u2 natural join workspaces
  where createuid = u1.uid and inviteduid = u2.uid and u2.uid = '$adminID'
  group by createuid, inviteduid, wid)
  select createuid, wid
  from T1 natural join workspacesinvitelog
  where wptime = max_time  and wstatus = 'SENT' 
    and wid not in 
        (select wid from wu where uid = '$adminID');";
        $query_channel_received = "with T1 as(
  select createuid, inviteduid, wid, cid, max(cptime) as max_time
  from channelsinvitelog join users u1 join users u2 natural join channels natural join workspaces
  where createuid = u1.uid and inviteduid = u2.uid and u2.uid = '$adminID'
  group by createuid, inviteduid, wid, cid)
  select createuid, wid, cid
  from T1 natural join ChannelsInviteLog
  where cptime = max_time  and cstatus = 'SENT'
    and (cid, wid) not in
        (select cid, wid from cu where uid = '$adminID')
    and wid in (select wid from wu where uid = '$adminID');";
        $result_workspace_received = mysqli_query($conn, $query_workspace_received);
        while ($row = mysqli_fetch_array($result_workspace_received)) {
            $workspace_received[] = $row['wid'];
            $workspace_username_received[] = $row['createuid'];
            $workspace_num_received += 1;
        }
        $result_channel_received = mysqli_query($conn, $query_channel_received);
        while ($row = mysqli_fetch_array($result_channel_received)) {
            $channel_workspace_received[] = $row['wid'];
            $channel_received[] = $row['cid'];
            $channel_username_received[] = $row['createuid'];
            $channel_num_received += 1;
        }
        $multi_result = array();
        $multi_result[] = $workspace_num_received;//0
        $multi_result[] = $channel_num_received;//1
        $multi_result[] = $workspace_username_received;//2
        $multi_result[] = $workspace_received;//3
        $multi_result[] = $channel_username_received;//4
        $multi_result[] = $channel_workspace_received;//5
        $multi_result[] = $channel_received;//6
        mysqli_close($conn);
        return $multi_result;
    }
};
function notification_sent($adminID){
    $conn = custom_connect();
    if (!$conn){
        echo '<script>alert("Error! Can\'t connect to database!");</script>';
        exit;
    }else{
        $workspace_num_sent = 0;$channel_num_sent = 0;
        $workspace_username_sent = array();$workspace_sent = array();
        $workspace_time_sent = array();$workspace_status_sent = array();
        $channel_username_sent = array();$channel_workspace_sent = array();
        $channel_time_sent = array();$channel_status_sent = array();$channel_sent = array();
        $query_workspace_sent = "with T1 as(
  select createuid, inviteduid, wid, max(wptime) as max_time
  from workspacesinvitelog  natural join workspaces
  where createuid = '$adminID'
  group by createuid, inviteduid, wid)
  select inviteduid, wid, wstatus, wptime
  from T1 natural join workspacesinvitelog
  where wptime = max_time;";
        $query_channel_sent = "with T1 as(
  select createuid, inviteduid, wid, cid, max(cptime) as max_time
  from channelsinvitelog join users u1 join users u2 natural join channels natural join workspaces
  where createuid = u1.uid and inviteduid = u2.uid and u1.uid = '$adminID'
  group by createuid, inviteduid, wid, cid)
  select inviteduid, wid, cid, cstatus, cptime
  from T1 natural join ChannelsInviteLog
  where cptime = max_time;";
        $result_workspace_sent = mysqli_query($conn, $query_workspace_sent);
        while ($row = mysqli_fetch_array($result_workspace_sent)) {
            $workspace_username_sent[] = $row['inviteduid'];
            $workspace_sent[] = $row['wid'];
            $workspace_time_sent[] = $row['wptime'];
            $status = $row['wstatus'];
            if ($status == "SENT"){
                $workspace_status_sent[] = "Not Process";
            }elseif ($status == "ACCEPT"){
                $workspace_status_sent[] = "Approved";
            }else{
                $workspace_status_sent[] = "Refused";
            }
            $workspace_num_sent += 1;
        }
        $result_channel_sent = mysqli_query($conn, $query_channel_sent);
        while ($row = mysqli_fetch_array($result_channel_sent)) {
            $channel_username_sent[] = $row['inviteduid'];
            $channel_workspace_sent[] = $row['wid'];
            $channel_sent[] = $row['cid'];
            $channel_time_sent[] = $row['cptime'];
            $status = $row['cstatus'];
            if ($status == "SENT"){
                $channel_status_sent[] = "Not Process";
            }elseif ($status == "ACCEPT"){
                $channel_status_sent[] = "Approved";
            }else{
                $channel_status_sent[] = "Refused";
            }
            $channel_num_sent += 1;
        }
        $multi_result = array();
        $multi_result[] = $workspace_num_sent;//0
        $multi_result[] = $channel_num_sent;//1
        $multi_result[] = $workspace_username_sent;//2
        $multi_result[] = $workspace_sent;//3
        $multi_result[] = $workspace_time_sent;//4
        $multi_result[] = $workspace_status_sent;//5
        $multi_result[] = $channel_username_sent;//6
        $multi_result[] = $channel_workspace_sent;//7
        $multi_result[] = $channel_sent;//8
        $multi_result[] = $channel_time_sent;//9
        $multi_result[] = $channel_status_sent;//10
        mysqli_close($conn);
        return $multi_result;
    }
};
function channelDeleted($wid, $cid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $workspace_not_delete = true;
        $channel_not_delete = true;
        $query_workspace_delete = "select * from workspaces where wid = '$wid';";
        $query_channel_delete = "select * from channels where wid = '$wid' and cid = '$cid';";
        $result1 = mysqli_query($conn, $query_workspace_delete);
        if (mysqli_num_rows($result1) == 0){
            $workspace_not_delete = false;//该workspace被删除
        }else{
            $result2 = mysqli_query($conn, $query_channel_delete);
            if (mysqli_num_rows($result2) == 0){
                $channel_not_delete = false;
            }else{;}
        }
        mysqli_close($conn);
        return ($workspace_not_delete && $channel_not_delete);
    }
};
function workspaceDeleted($wid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        //$workspace_not_delete = true;
        $query_workspace_delete = "select * from workspaces where wid = '$wid';";
        $result = mysqli_query($conn, $query_workspace_delete);
        if (mysqli_num_rows($result) == 0){
            $workspace_not_delete = false;//该workspace被删除
        }else{
            $workspace_not_delete = true;
        }
        mysqli_close($conn);
        return $workspace_not_delete;
    }
};
function channelDeleted_memberRemoved($uid, $wid, $cid){
    $conn = custom_connect();
    if (!$conn){
        echo '<script>alert("Error! Can\'t connect to database!");</script>';
        exit;
    }
    else{
        $channel_not_delete = true;
        $workspace_not_delete = true;
        $member_not_remove_workspace = true;
        $member_not_remove_channel = true;
        $q_workspace_delete = "select * from workspaces where wid = '$wid';";
        $q_workspace_remove = "select * from wu where wid = '$wid' and uid = '$uid';";
        $q_channel_delete = "select * from channels where wid = '$wid' and cid = '$cid';";
        $q_channel_remove = "select * from cu where wid = '$wid' and uid = '$uid' and cid = '$cid';";
        $result1 = mysqli_query($conn, $q_workspace_delete);
        if (mysqli_num_rows($result1) == 0){
            $workspace_not_delete = false;//该workspace被删除
        }else{
            $result2 = mysqli_query($conn, $q_workspace_remove);
            if (mysqli_num_rows($result2) == 0){
                $member_not_remove_workspace = false;//该user被移出workspace
            }else{
                $result3 = mysqli_query($conn, $q_channel_delete);
                if (mysqli_num_rows($result3) == 0){
                    $channel_not_delete = false;//该workspace中的channel被删除
                }else{
                    $result4 = mysqli_query($conn, $q_channel_remove);
                    if (mysqli_num_rows($result4) == 0){
                        $member_not_remove_channel = false;//该user被移出workspace中的channel
                    }
                }
            }
        }
        mysqli_close($conn);
        return ($channel_not_delete && $workspace_not_delete && $member_not_remove_channel && $member_not_remove_workspace);
    }
};
function workspaceDeleted_memberRemoved($uid, $wid){
    $conn = custom_connect();
    if (!$conn){
        echo '<script>alert("Error! Can\'t connect to database!");</script>';
        exit;
    }
    else{
        $workspace_not_delete = true;
        $member_not_remove_workspace = true;
        $q_workspace_delete = "select * from workspaces where wid = '$wid';";
        $q_workspace_remove = "select * from wu where wid = '$wid' and uid = '$uid';";
        $result1 = mysqli_query($conn, $q_workspace_delete);
        if (mysqli_num_rows($result1) == 0){
            $workspace_not_delete = false;//该workspace被删除
        }else{
            $result2 = mysqli_query($conn, $q_workspace_remove);
            if (mysqli_num_rows($result2) == 0){
                $member_not_remove_workspace = false;//该user被移出workspace
            }else{
                ;
            }
        }
        mysqli_close($conn);
        return ( $workspace_not_delete && $member_not_remove_workspace);
    }
};
function invite_once($createuid, $inviteduid, $wid, $cid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query1 = "select *
from channelsinvitelog
where createuid = '$createuid' and inviteduid = '$inviteduid' and wid = '$wid' and cid = '$cid';";
        $query2 = "with T1 as(
  select createuid, inviteduid, wid, cid, max(cptime) as max_time
  from channelsinvitelog  natural join channels natural join workspaces
  where createuid = '$createuid' and inviteduid = '$inviteduid' and wid = '$wid' and cid = '$cid'
  group by createuid, inviteduid, wid, cid)
  select *
  from T1 natural join ChannelsInviteLog
  where cptime = max_time and cstatus = 'REFUSE';";
        $query3 = "select *
                  from cu
                  where wid = '$wid' and cid = '$cid' and  uid = '$inviteduid';";
        $result1 = mysqli_query($conn, $query1);
        if (mysqli_num_rows($result1) == 0){
            mysqli_close($conn);
            return true;
        }else{
            $result2 = mysqli_query($conn, $query2);
            if (mysqli_num_rows($result2) == 1){
                mysqli_close($conn);
                return true;
            }else{
                $result3 = mysqli_query($conn, $query3);
                if (mysqli_num_rows($result3) == 0){
                    mysqli_close($conn);
                    return true;
                }else{
                    mysqli_close($conn);
                    return false;
                }
            }
        }
    }
};
function invite_once2($createuid, $inviteduid, $wid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query1 = "select *
from workspacesinvitelog
where createuid = '$createuid' and inviteduid = '$inviteduid' and wid = '$wid';";
        $query2 = "with T1 as(
  select createuid, inviteduid, wid, max(wptime) as max_time
  from workspacesinvitelog  natural join workspaces
  where createuid = '$createuid' and inviteduid = '$inviteduid' and wid = '$wid'
  group by createuid, inviteduid, wid)
  select *
  from T1 natural join workspacesinvitelog
  where wptime = max_time and wstatus = 'REFUSE';";
        $query3 = "select *
                  from wu
                  where wid = '$wid' and uid = '$inviteduid';";
        $result1 = mysqli_query($conn, $query1);
        if (mysqli_num_rows($result1) == 0){
            mysqli_close($conn);
            return true;
        }else{
            $result2 = mysqli_query($conn, $query2);
            if (mysqli_num_rows($result2) == 1){
                mysqli_close($conn);
                return true;
            }else{
                $result3 = mysqli_query($conn, $query3);
                if (mysqli_num_rows($result3) == 0){
                    mysqli_close($conn);
                    return true;
                }else{
                    mysqli_close($conn);
                    return false;
                }
            }
        }
    }
};
function is_administrator($adminID, $wid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $administrator_num = 0; $administrator = array();
        $multi_result = array(0 => false, 1 => false);
        $query_admin = "select uid  from wu where wutype = 'SELECTED_ADMIN' and wid = '$wid';";
        $query_admin2 = "select *  from wu where wutype = 'ORIGINAL_ADMIN' and wid = '$wid' and uid = '$adminID';";
        $result1 = mysqli_query($conn, $query_admin);
        while ($row = mysqli_fetch_array($result1)) {
            $administrator[] = $row['uid'];
            $administrator_num += 1;
        }
        for ($i = 0; $i < $administrator_num; $i++){
            if ($adminID == $administrator[$i]){
                $multi_result[1] = true;//选中的管理员
                break;
            }else{
                continue;
            }
        }
        $result2 = mysqli_query($conn, $query_admin2);
        if (mysqli_num_rows($result2) == 1){
            $multi_result[0] = true;//原始管理员
        }
        mysqli_close($conn);
        return $multi_result;
    }
};
function is_still_member($adminID, $wid){
    $conn = custom_connect();
    if (!$conn){
        exit;
    }else{
        $query_admin = "select *  from wu where wutype = 'MEMBER' and wid = '$wid' and uid = '$adminID';";
        $result = mysqli_query($conn, $query_admin);
        if (mysqli_num_rows($result) == 1){
            mysqli_close($conn);
            return true;//原始管理员
        }else{
            mysqli_close($conn);
            return false;
        }
    }
};

?>
