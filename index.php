<?php
ob_start();
session_start();
include_once 'functions.php';
include_once "mysql.php";
$task = isset($_REQUEST['task']) ? trim(strtolower($_REQUEST['task'])) : null;
if(isset($_REQUEST['url'])){
	parse_str(base64_decode($_REQUEST['url']), $_PARAMS);
	$_REQUEST = array_merge($_REQUEST, $_PARAMS);
}
if(isset($_SESSION['user_id'])){
    if(!isset($task) || $task=="login"){
       $task="deallist";
    }
}else{
    $task="login";
}

switch($task){
    case "deallist":
    case "deal":
	case "show_receipts":
	case "dashboard":

	case "se_report":
	case "dl_report";
	case "lastpayment":

	case "od_report":
    case "per_field":
    case "per_caller":
	case "generic":
	case "dealcomment":
		printHeader();
		printBox($task);
		printFooter();
		break;

	case "refreshdashbord":
		require_once "refreshDashboard.php";
		echo "done";
		break;

	case "login":
        $error=null;
        if(isset($_REQUEST['log'])){
            $userName = isset($_REQUEST['userName']) ? $_REQUEST['userName'] : "";
            $passW = isset($_REQUEST['passW']) ? $_REQUEST['passW'] : "";
            $pass=$passW;
            $qry="select UserId, UserLogin, RealName, UserType from ".$_SESSION['USER_DB_PREFIX'].".tbmuser where UserLogin = '$userName' and UserPsswrd = password('$pass') and active = 2";
            $users = executeSelect($qry);
			$t1 = executeSelect($qry);
			if($t1['row_count'] > 0){
				$user = $t1['r'][0];
                $_SESSION['user_id']= $user['UserId'];
                $_SESSION['user_name'] = $user['RealName'];
                $_SESSION['UserType'] = $user['UserType'];
				$_SESSION['DATE_FORMAT'] = "d-M-Y H:i:s";

                if($_SESSION['UserType'] == 2)
					$_SESSION['is_admin_login'] = true;
				else
					$_SESSION['is_admin_login'] = false;

				header("location: index.php?task=slist");
            } else{
            	$error="User Name & Password do not match..!";
                echo "<script>alert('User Name & Password do not match..!');</script>";
                echo "<script>window.location = 'index.php?task=login';</script>";
                die();
			}
        }
        printHeader("Loksuvidha Reports - Login");
        printLoginBody();
        break;


    case "chpass":
        $error=null;
        if(isset($_REQUEST['chpass'])){
            $done = 0;
            $old_pass = isset($_REQUEST['old_pass']) ? $_REQUEST['old_pass'] : "";
            $new_pass1 = isset($_REQUEST['new_pass1']) ? $_REQUEST['new_pass1'] : "";
            $new_pass2 = isset($_REQUEST['new_pass2']) ? $_REQUEST['new_pass2'] : "";
            if($new_pass1==$new_pass2 && $old_pass!=""){
                $pass_old= $old_pass;
                $pass_new1= $new_pass1;
                $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                $qry="update ".$_SESSION['USER_DB_PREFIX'].".tbmuser set UserPasswd=password('$pass_new1') where password = password('$pass_old') and UserId='$userId' and active = 2";

				$result = executeUpdate($qry);

                if($result){
                  $done=1;
                }
            }
            if($done == 1){
                echo "<script>alert('Password changed..!');</script>";
                echo "<script>window.close();</script>";
            }else{
                echo "<script>alert('Password do not match..!');</script>";
                die();
            }
        }
        changePassword();
        break;

    case "logout":
//    	ob_start();
//		session_start();
		unset($_SESSION['user_id']);
		session_destroy();
		header("location: index.php");
		break;

	case "clear":
		if($_SESSION['is_admin_login']){
			$user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : null;
			$test_instance_id = isset($_REQUEST['tid']) ? $_REQUEST['tid'] : null;
			$return = clearTestInstance($user_id, $test_instance_id);
			//$return =1;
			/*
			0://No Access
			1://Success
			2://User Not Linked
			3://Query Failed
			4: //Not a demo user
			*/
			echo $return;
			break;
		}
		break;

    default:
        header("location: index.php?task=deallist");
		break;
}
?>
