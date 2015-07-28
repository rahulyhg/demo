<? date_default_timezone_set('Asia/Calcutta');

$_SESSION['DB_PREFIX'] = "lksa";
$_SESSION['USER_DB_PREFIX'] = "ob_sa";
$_SESSION['ROWS_IN_TABLE'] = 30;
$_SESSION['MOBILE_ROWS_IN_TABLE'] = 15;
$_SESSION['LKS_ROOT'] = 'http://r.loksuvidha.com:81';
$_SESSION['PAY_MODE'] = array(0=>'Unknown', 1=> 'Cash', 2=> 'PDC',3=> 'Other', 4=> '4', 5=>'5', 6=> 'ECS',7=> '7');

function connect(){
	return mysqli_connect("192.168.1.150","root","admin", $_SESSION['DB_PREFIX']);
	//return mysqli_connect("localhost","root","",$_SESSION['DB_PREFIX']);
}
?>