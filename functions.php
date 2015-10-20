<?
include_once 'mysql.php';

function execute($q){
	$conn = connect();
    $result = mysqli_query($conn, $q) or die(mysqli_error()." DB Error at line ". __LINE__ . " in file " . __FILE__);
    mysqli_close($conn);
	return $result;
}

function executeSelect($q, $id = NULL){
        $conn = connect();
        $result = mysqli_query($conn, $q) or die(mysqli_error($conn)." DB Error at line ". __LINE__ . " in file " . __FILE__);
        $res = mysqli_query($conn, "select found_rows() as total");
		$row = mysqli_fetch_array($res,MYSQL_ASSOC);
        mysqli_close($conn);

        $results = array();
        $results['row_count'] = mysqli_num_rows($result);
        $results['found_rows'] = $row['total'];

        $i=0;
        while ($line = mysqli_fetch_array($result, MYSQL_ASSOC)) {
        		if($id == NULL)
                	$results['r'][$i]=$line;
                else
                	$results['r'][$line[$id]]=$line;
                $i++;
        }
        mysqli_free_result($result);
        return $results;
}

function executeSingleSelect($q){
        $conn = connect();
        $result = mysqli_query($conn, $q) or die(mysqli_error($conn)." DB Error at line ". __LINE__ . " in file " . __FILE__);
        mysqli_close($conn);
        $results = array();
		$value = "";
        $row_count = mysqli_num_rows($result);
        if($row_count ==0)
        	return NULL;
		foreach (mysqli_fetch_array($result, MYSQL_ASSOC) as $key => $value) {
			break;
		}
        mysqli_free_result($result);
        return $value;
}

function executeUpdate($q){
        $conn = connect();
        $result = mysqli_query($conn, $q) or die(mysqli_error($conn)." DB Error at line ". __LINE__ . " in file " . __FILE__);
		$value = mysqli_affected_rows($conn);
        mysqli_close($conn);
        return $value;
}

/**************** DONT USE ***********************************
function executeSingleRowSelect($q){
        $conn = connect();
        $result = mysqli_query($conn, $q) or die(mysqli_error($conn)." DB Error at line ". __LINE__ . " in file " . __FILE__);
        mysqli_close($conn);
        $results = array();
		$value = "";
		$i=0;
		while ($line = mysqli_fetch_array($result, MYSQL_ASSOC)) {
			$i++;
			break;
        }

        if(!isset($line))
        	$line = null;
        mysqli_free_result($result);
        return $line;
}
************************************************************/


function df($dt){
	if(is_null($dt)) return null;
	return date('d-M-Y', strtotime($dt));
}

function toMonthName($monthNum){
	if(is_null($monthNum)) return 'No Month';
	return date('F', mktime(0, 0, 0, $monthNum, 10));
}

function startsWith($haystack, $needle){return $needle === "" || strpos($haystack, $needle) === 0;}

function endsWith($haystack, $needle){return $needle === "" || substr($haystack, -strlen($needle)) === $needle;}

function print_a($a){echo "<pre>"; print_r($a); echo "</pre>";}

function nf($a, $zeroReturn = false){
	if($a == 0 || is_null($a) || empty($a))
		if($zeroReturn == true)
			return 0;
		else return "";
	return number_format($a);
}

function convertdatetime($gmttime, $pattern=null, $timezoneRequired ='Asia/Calcutta'){
	if($pattern == null) $pattern = $_SESSION['DATE_FORMAT'];
    $system_timezone = date_default_timezone_get();
    $local_timezone = $timezoneRequired;
    date_default_timezone_set($local_timezone);
    $local = date("Y-m-d h:i:s A");

    date_default_timezone_set("GMT");
    $gmt = date("Y-m-d h:i:s A");
    date_default_timezone_set($system_timezone);
    $diff = (strtotime($gmt) - strtotime($local));

    $date = new DateTime($gmttime);
    $date->modify("-$diff seconds");
    $timestamp = $date->format($pattern);
    return $timestamp;
}

function validEmail($email){
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex){
      $isValid = false;
   }
   else{
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64){
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255){
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.'){
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local)){
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)){
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain)){
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))){
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))){
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))){
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

function titleCase($string, $delimiters = array(" ", "-", ".", "'", "O'", "Mc"), $exceptions = array("and", "to", "of", "das", "dos", "I", "II", "III", "IV", "V", "VI")){
    /*
     * Exceptions in lower case are words you don't want converted
     * Exceptions all in upper case are any words you don't want converted to title case
     *   but should be converted to upper case, e.g.:
     *   king henry viii or king henry Viii should be King Henry VIII
     */
    $string = mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
    foreach ($delimiters as $dlnr => $delimiter) {
        $words = explode($delimiter, $string);
        $newwords = array();
        foreach ($words as $wordnr => $word) {
            if (in_array(mb_strtoupper($word, "UTF-8"), $exceptions)) {
                // check exceptions list for any words that should be in upper case
                $word = mb_strtoupper($word, "UTF-8");
            } elseif (in_array(mb_strtolower($word, "UTF-8"), $exceptions)) {
                // check exceptions list for any words that should be in upper case
                $word = mb_strtolower($word, "UTF-8");
            } elseif (!in_array($word, $exceptions)) {
                // convert to uppercase (non-utf8 only)
                $word = ucfirst($word);
            }
            array_push($newwords, $word);
        }
        $string = join($delimiter, $newwords);
   }//foreach
   return $string;
}


function printHeader($title = 'Loksuvidha Reports', $menu='Y'){
//    $task = isset($_REQUEST['task']) ? trim(strtolower($_REQUEST['task'])) : null;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?=$title?></title>
<link rel="stylesheet" type="text/css" href="css/general.css" />

<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

<script type="text/javascript" src="js/index.js"></script>
</head>
<body id="minwidth-body">
	<div class="timestamp">As On: <?=date('d-M-Y h:i:s A')?></div><div class="clr"></div>

	<div id="border-top" class="h_green">
		<div>
			<div>
				<span class="title"><img src='images/android-icon-96x96.png'/>LokSuvidha</span>
			</div>
		</div>
	</div>
	<?if(isset($_SESSION['user_id'])){?>
	<div id="header-box">
		<div id="module-status" style="background:none";>
			<ul>
				<li><a><?=$_SESSION['user_name']?></a></li>
				<li><a href="index.php?task=chpass"><img src="/images/icon_certificate.png" width="16" title="Change Password"/></a></li>
				<li><a href="index.php?task=logout"><img src="/images/icon_sign-out.png" width="16" title="Sign Out"/></a></li>
			</ul>
			<input type="hidden" id="partid" name="partid" value="<?=titleCase($_SESSION['user_id'])?>" />
		</div>
        <? if($menu=='Y'){?>
        	<div id="module-menu">
				<ul id="menu">
					<li class="node "><a>Deals</a>
						<ul style="width: 144px;">
							<li style="width: 144px;"><a class="icon-16-cpanel" href="index.php?task=deallist">Deal List</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-user" href="index.php?task=generic&index=12">NOC Report</a></li>
							<li style="width: 144px;"><a class="icon-16-user" href="index.php?task=generic&index=13">Bank Posting</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-user" href="index.php?task=generic&index=16">Seized Vehicles</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-user" href="index.php?task=generic&index=21">Employee List</a></li>
							<li style="width: 144px;"><a class="icon-16-user" href="index.php?task=generic&index=22">Due Date List</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<?if(isset($_SESSION['is_admin_login']) && $_SESSION['is_admin_login']){?>
								<li style="width: 144px;"><a class="icon-16-user" href="index.php?task=dashboard">Dashboard</a></li>
							<?}?>
						</ul>
					</li>
					<li class="node "><a>Sales</a>
						<ul style="width: 118px;">
							<li style="width: 118px;"><a class="icon-16-menu" href="index.php?task=se_report">Sales Report</a></li>
							<li style="width: 118px;"><a class="icon-16-menu" href="index.php?task=dl_report">Dealer Report</a></li>
							<li style="width: 118px;"><a class="icon-16-menu" href="index.php?task=generic&index=40">Proposals</a></li>
							<li style="width: 118px;"><a class="icon-16-menu" href="index.php?task=generic&index=6">Disbursements</a></li>
							<li style="width: 118px;"><a class="icon-16-trash" href="index.php?task=generic&index=10">Pending Vehicles</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 118px;"><a class="icon-16-trash" href="index.php?task=generic&index=0">Pay Instruments</a></li>
							<li style="width: 118px;"><a class="icon-16-trash" href="index.php?task=generic&index=39">NACH/ECS Report</a></li>
							<li style="width: 118px;"><a class="icon-16-trash" href="index.php?task=generic&index=11">Due List Report</a></li>
						</ul>
					</li>
					<li class="node "><a>Recovery</a>
						<ul style="width: 144px;">
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=od_report">OD Report</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=lastpayment">Last Payment</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-article" href="index.php?task=per_field">Field Performance</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=per_caller">Caller Performance</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-article" href="index.php?task=generic&index=7">Daily Recovery</a></li>
							<li style="width: 144px;"><a class="icon-16-article" href="index.php?task=generic&index=1">Monthly Recovery</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=9">EMI Recovery</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=4">Tags</a></li>
							<!--li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=5">SRA Tag Entry</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=2">Caller Tag Summary</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=3">SRA Tag Summary</a></li-->
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=15">Regular Recovery</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=18">Bouncing Report</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=20">Non-Starter Cases</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=28">Cash Receipts</a></li>
							<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=35">Closed Vehicles</a></li>
							<li class="separator" style="width: 144px;"><span></span></li>
							<?if(isset($_SESSION['is_admin_login']) && $_SESSION['is_admin_login']){?>
								<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=32">Collections</a></li>
								<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=29">Mobile View</a></li>
								<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=23">Comissions</a></li>
								<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=37">Bank Repayments</a></li>
								<li style="width: 144px;"><a class="icon-16-trash" href="index.php?task=generic&index=24">Customer Repayments</a></li>
							<?}?>

						</ul>
					</li>
				</ul>
			</div>
			<script type="text/javascript">initializeMenu();</script>
		<?}?>
	        <div class="clr"></div>
	</div>
	<?}?>
<? }

function printLoginBody(){?>
<body onload="javascript:document.getElementById('userName').focus();">
<div id="loginpage">
<div id="loginMain">
<form id="loginForm" name="loginForm" method="post" onsubmit="return validateForm();">
<table width="100%">
	<tr><td height="37" colspan="2" align="center"><label class="textstsss">Login - Loksuvidha Reports</label></td></tr>
	<tr><td width="37%" height="30" align="right"><label class="textsts">User Name : </label></td><td width="63%"><input type="text" id="userName" name="userName" /></td></tr>
	<tr><td height="30" align="right"><label class="textsts">Password : </label></td><td><input type="password" id="passW" name="passW" /></td></tr>
	<tr><td height="37">&nbsp;</td><td><input type="submit" class="button_new" value="LOGIN" id="log" name="log" /></td></tr>
</table>
</form>
</div>
</div>
<?
printFooter();

}

function printFooter(){?>
<noscript>Warning! JavaScript must be enabled for proper operation of the Administrator back-end.</noscript>
<div id="border-bottom">
<div><div></div></div></div>
<div id="footer"></div>
<div style="position: absolute; top: 0px; left: 0px;" class="tip-wrap"><div class="tip-top"></div><div class="tip"></div><div class="tip-bottom"></div></div>
<div class="timestamp_f">As On: <?=date('d-M-Y h:i:s A')?></div><div class="clr"></div>
</body>
</html>
<?}

function callMasterJavascript(){?>
    <!--script type="javascript" src="js/index.js"></script-->
<?
}

function changePassword(){
printHeader();?>
<body onload="javascript:document.getElementById('old_pass').focus();" >
<div id="header-box">
    <div id="module-menu" class="PageHeader">Loksuvidha Password Change</div>
    <div align="right" style="float:right;text-align:right;">

    </div>
    <div class="clr"></div>
</div>
<div id="content-box">
    <div class="border">
        <div class="padding">
            <div class="col width-90">
                <table width="100%">
                    <tr><td align="center" width="100%">
                        <div id="changePass">
                            <form id="changepsw" name="changepsw" method="post" onsubmit="return validateChpassForm();">
                                <table class="admintable" width="100%" align="center">

                                    <tbody>
                                    <tr>
                                        <td class="keys" width="40%" height="30" align="right"><label class="textsts">Old password : </label></td>
                                        <td width="60%"><input type="password" id="old_pass" name="old_pass" /></td>
                                    </tr>
                                    <tr>
                                        <td class="keys" height="30" align="right"><label class="textsts">New Password : </label></td>
                                        <td><input type="password" id="new_pass1" name="new_pass1" /></td>
                                    </tr>
                                    <tr>
                                        <td class="keys" height="30" align="right"><label class="textsts">Re-type Password : </label></td>
                                        <td><input type="password" id="new_pass2" name="new_pass2" /></td>
                                    </tr>
                                    <tr>
                                        <td height="37">&nbsp;</td>
                                        <td><input type="submit" class="button_new" value=" CHANGE " id="chpass" name="chpass" /></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </td></tr>
                </table></div>
        </div>
        <div class="clr"></div>
    </div>
    <div class="clr"></div>
</div>
</div>
<?printFooter();
}


function throwError($error){?>
	<div class="error"><?=$error?></div>
<?}


function printBox($task=""){?>
	<div id="content-box">
		<div class="border">
			<div class="padding">
				<div class="stripeMe" id="content-table">
					<? include_once "$task.php";?>
				</div>
				<div class="clr"></div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
<?}

function printMHeader($task, $type = 0){?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html> <head>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<!-- Meta tags that set up the page as a mobile page   -->
	<meta name = "viewport" content = "user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=device-width">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />
	<link rel="apple-touch-icon" href="../images/iphone.png" />
	<meta name="format-detection" content="telephone=no" />
	<!--  A free Google web font embed because android does not have the browser safe fonts -->
	<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Allerta">
	<!--link rel="stylesheet" href="../css/zomato.css" media="screen" /-->
	<!--link rel="stylesheet" href="https://www.buxfer.com/css-1435124065/core.css" media="screen"-->
	<!--link rel="stylesheet" href="https://www.buxfer.com/css-1435124065/content.css" media="screen"-->
	<style><?printMobileCSS();?></style>
	<!--Script that scrolls page up to the top -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
	<!--script type="text/javascript" src="index.js"></script-->
	<script type="text/javascript"><?=printMobileJS();?></script>

	<script type="text/javascript">
	window.scrollTo(0,1);
	</script>
	<!-- Fill out the Title for each page -->
	<title>LKS Mobile</title>
	</head>
	<body>
		<div class="profilebar">
			<div class="photo">
				<!--img src="/images/app/<?=$_SESSION['userid']?>.png" width="40" height="40"/-->
				<img src="/images/user.png" width="40" height="40"/>
			</div>
			<div class='info'>
				<div class="name"><?=titleCase($_SESSION['username'])?></div>
				<div class="dtl"><?=titleCase($_SESSION['centre'])?>  &#8226; <?=$_SESSION['designation']?></div>
			</div>
			<div class="dt"><?=date('d M')?></div>
			<div class="clear"></div>
		</div>
		<!--center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center-->
		<div id ="content-box">
		<div id="module-menu">
			<ul id="menu">
				<li class="<?=($task=="dashboard" ? 'selected' : '')?>"><a href="#" onclick="javascript:callDashboard();"><img height="17" src="../images/app/icon_pie-chart.png"/></a></li>
				<li class="<?=($task=="deallist" ? ($type==0 ? 'selected' : '') : '')?>"><a href="#" onclick="javascript:callDealList(0);">Assigned</a></li>
				<li class="<?=($task=="deallist" ? ($type==1 ? 'selected' : '') : '')?>"><a href="#" onclick="javascript:callDealList(1);">Pending</a></li>
				<li class="<?=($task=="deallist" ? ($type==2 ? 'selected' : '') : '')?>"><a href="#" onclick="javascript:callDealList(2);">Recovered</a></li>
				<li class="<?=($task=="deal" ? 'selected' : '')?>"><a href="#" onclick="javascript:callDeal(0);"><img height="17" src="../images/app/icon_suitcase.png"/></a></li>
			</ul>
		</div>
		<div class="clear"></div>

<?}

function printMFooter(){?>
		</div>
<div class="clear"></div>
<br><br><br>
<div class="footer">As On: <?=date('d-M-y h:i:s')?></div>
</body>
</html>

<?}

function printMobileCSS(){?>

body{background-color: #f5f5f5;font-family: Arial,sans-serif;font-size: 13px;margin:0px;margin-top: 3px}
a{text-decoration:none;color:#000}
hr{height:1px;background: #eee;border:0px;}
fieldset{margin-bottom:10px;border:1px #ccc solid;padding:5px;text-align:left;background-color:#fff;box-shadow: 0 0 2px rgba(0,0,0,.12),0 2px 4px rgba(0,0,0,.24);}
fieldset table{border:0px;}
fieldset p{margin:10px 0px;}
legend{color:#0B55C4;font-size:12px;font-weight:bold;}
table.details tr{vertical-align: top;}
table.admintable td{padding: 3px;}
table.admintable td.keys, table.adminlist td.keys, table.admintable td.shortkeys {background-color: #f6f6f6;text-align: right;width: 150px;color: #666;font-weight: bold;border-bottom: 1px solid #e9e9e9;border-right: 1px solid #e9e9e9;}
table.admintable td.shortkeys {width: 120px;}
table#summary, table.tbucket{text-align:center;margin:auto;}
table{border: 1px solid #eee;border-width: 0px 0px 1px 1px;border-collapse: collapse;}
table#summary{width:100%;}
table td.filler{width:10%;}
table.tbucket td{border: 1px solid #eee;border-width: 1px 1px 0px 0px;padding:10px 12px;width:16%;text-align: center;font-weight:bold}
table.tcommission {margin:3px;}
table.tcommission thead, table.tcommission tfoot{background-color:#EEE0E5; font-weight:bold}
table.tcommission td{border: 1px solid #ccc;padding:5px 12px;width:16%;text-align:center;}
tr.ass{background-color:#FFC1C1;}
tr.ass td{padding:5px 10px;}
tr.rec{font-size:20px;background-color:#C1FFC1;}
tr.headers{font-size:12px;}

#module-menu{margin-top:3px;width:100%;}
#menu li{border-left: 1px solid #fff;border-right: 1px solid #d8d8d8;}
#menu li{float: left;position: relative;list-style: none;display: inline;width: 20%;text-align: center;border:1px solid #000;border-width:0 0 1px 0;}
#menu li.selected{margin: -1px;background-color: #f5f5f5;border:1px solid #000;border-width:1px 1px 0px 1px;border-bottom: 1px solid #f5f5f5;}
#menu li.selected a{margin: 3px 2px 3px 2px;background-color: #777;color:#fff;}
#menu, #menu ul, #menu li{/*margin: 0;*/padding: 0;/*border: 0 none;*/}
#menu li a{display: block;white-space: nowrap;}
#menu a, #menu div{padding: 3px;margin: 3px;color: #000;line-height: 1.6em;vertical-align: middle;font-size: 11px;font-weight: bold;text-decoration: none;cursor: default;background-repeat: no-repeat;background-position: left 50%;background-color: #ccc;}

.b{font-weight:bold;}
.cash{color:green;}
.red{color:red;}
.green{color:green;}
.footer{text-align:center;margin-bottom:2px;}
.clear{clear:both}
.half{float:left;width:50%;}
.tleft, .textleft {text-align:left !important;}
.tright, .textright{text-align:right !important;}
.tcenter{text-align:center !important;}
.left{float:left;}
.right{float:right;}
.textsts{font-size: 12px;color: #444;font-weight: bold;}
.profilebar{height:36px;border:1px solid #ccc;border-width: 1px 0px 1px 0px;background-color:#fff;padding:10px;}
.profilebar .name{font-size: 15px;font-weight:bold;margin-bottom:8px;}
.dt{font-size: 12px;margin:10px 2px 0 0;float:right;}
.dtl{font-size: 14px;font-weight:normal;color:#aaa;}
.photo{width:47px;float:left;}
.photo img{border-radius:50%;}
.info{float:left;}
.empty{text-align:centre;font-weight:bold;font-size:15px;margin-top:10px;}
.filterbar{padding:6px 2px 10px 2px;}
.deallist, .dashboard{background:#f5f5f5;margin:0px;/* border:1px solid #ccc;border-width: 0px 1px 1px 1px;*/}
.deallist .recovered{background-color:#fffacc;}
.deallist .pending{background-color:#fff;}
.deallist .seized{background-color:#FFB6C1;}
.deallist .recovered_by{float:left;/*font-weight:bold;*/padding:9px 5px 5px 0px;}
.deallist .deal{margin:4px 2px 5px 2px;padding: 5px;border: 1px solid #ddd;}
.cname{font-weight:bold;font-size:15px;width:70%;float:left;}
.deal .cname a{color:#cb202d;}
.deal .odamt{float:right;margin-right:2px;padding:3px 4px;background-color:#5ba829;color:#fff;font-size:14px;}
.deal .area, .deal .vehicle{color:#777;font-size:13px;margin:1px;line-height:22px;}
.deal .dealattributes{float:left;}
.telb, .telg, .bucket, .status-ico, .logs-ico, .comment-ico{margin-right:10px;padding:0px 5px;float:left;border-radius: 50%;padding: 8px;/* margin: 20px 20px 5px 0px;*/}
.telb{background-color:green;}
.status-ico, .logs-ico{background-color: lightseagreen;}
.telg{background-color:orange;}
.comment-ico{background-color:#BDB76B;}
.bucket{background-color: #EEA2AD;border-radius:20%;}
.telb a, .telg a{color:#fff;font-size:18px;}
.bucket a{color:red;font-size: 15px;vertical-align: top;font-weight: bold;}
.dashboard{padding:5px 2px 5px 2px;}
.dashboard .half{font-weight:bold;text-align:center;}
.dashboard .tag{margin:2px 2px -2px 2px;padding:3px;border:1px solid #ccc;color:#8B5A2B;font-weight:bold;border-bottom:0px;/* float:left;*/}
.dashboard .committed, .dashboard .collection{font-weight: bold;background-color: #777;/*#8B5A00;*/padding: 4px;margin: -1px 2px;color: #fff;text-align: center;}
.dashboard #collection .committed{background-color:#458B74;}
.dashboard #collection .collection{background-color:green;}
.dashboard .number{padding:10px;font-size:24px;}
.dashboard #collection .number{margin:0 2px;border:1px solid #ccc; text-align:center;font-size:18px;padding:7px;}
.dashboard .recovered, .dashboard .assigned{border:1px solid #ccc;/*float:left;*/ margin:0px 2px;min-width:20%;}
.dashboard .recovered .title{color:#fff;background-color:green;padding:3px;}
.dashboard .assigned .title{color:#fff;background-color:red;padding:3px;}
.bh{float:left;margin:36px 2px;background-color:red;color:#fff;text-align:centre;font-size:30px}
.dashboard .alert{font-weight: bold;background-color:#fffacc;padding: 7px;margin: -1px 2px;color: red;text-align: center;border:1px solid #ddd;border-radius:10px;margin:0px 2px 2px 2px;}
.dashboard .commission{font-weight: bold;background-color:brown;padding: 7px;margin: -1px 2px;color:#fff;text-align: center;border-radius:10px;margin:0px 2px 2px 2px;}
.dashboard .commission a{color:#fff;}

.dealstatus{background-color:#fff;padding:1px 1px 1px 2px;}
.dealstsitem{/*border:1px solid #ccc;margin:2px 0px;*/}
.dealstsitem.today{background-color: #F4A460;border-bottom:1px solid #ccc;colr: #fff;font-weight:bold;}
.dealstsitem.alternate{background-color: #EEE0E5;}
.dealstsitem .rowdt{text-align:center;}
.dealstsitem .due{text-align:right;}
.dealstsitem .received{text-align:right;font-weight:bold;}
.dealstsitem .balance{text-align:right;font-weight:bold;}
.dealstsitem .rowdt, .dealstsitem .due, .dealstsitem .received, .dealstsitem .balance{border:1px solid #ccc;border-width:1px 0px 0px 0px;/* float:left;*/ padding:2px;width:20%;padding-right:4px;}
.dealstsitem .status, .dealstsitem .sr{border:1px solid #ccc;border-width:1px 0px 0px 0px;/* float:left;*/ width:5%;text-align:center;padding:2px;font-size:12px;}
.dealstsitem .remark{/*border-top:1px solid #fff;*/ border-bottom:1px solid #ccc;text-align:right;padding:2px;font-size:12px;color: #666;}
.nav{float:left;margin:3px;padding:3px 5px;border-radius:3px;border:2px solid orange;background: #000;color:#fff;font-weight:bold;}
.nav a{color:#fff;}
.noresults{margin:2px;background-color:#EEE0E5;color:red;border:1px solid #ccc;padding:5px;text-align:center;font-weight:bold;}
.legends{font-weight: bold;line-height: 20px;margin: 3px 2px;}
.deal.header{margin:4px 2px 5px 2px;padding: 5px;border: 1px solid #ddd;background-color: #fff;}
.deal.header .telb, .deal.header .telg, .deal.header .bucket, .deal.header .status-ico, .deal.header .logs-ico, .deal.header .comment-ico{border-radius: 50%;padding:10px;margin:20px 20px 5px 0px;}
.logitem{margin:4px 2px 5px 2px;padding: 5px;border: 1px solid #ddd;background-color: #fff;}
.logitem .logdt{width:15%;float:left;margin-right:7px;}
.logitem .dd{background-color: lightgreen;text-align: center;color:green;font-weight:bold;padding: 2px;}
.logitem .mm{text-align: center;border:1px solid #ccc;padding: 8px 2px;font-size:12px;}
.logitem .logby{float:left;font-weight:bold;color:#666;font-size: smaller;}
.logitem .lognfd{font-weight:bold;color:#666;float:right;font-size: smaller;}
.logitem .logcomments{margin:5px 2px;font-size:smaller;}
#tagid {width:92%;}
.header{font-weight: bold;font-size: 13px;color: beige;margin: 0px 0px 5px 0px;padding: 6px 0 6px 2px;background-color: brown;}
<?}

function printMobileJS(){?>
	// JavaScript Document
	var j_query = jQuery.noConflict();
	function ge(ele){
		return document.getElementById(ele);
	}
	function sort(field){
		if(ge('sval').value == field){
			if(ge('stype').value == 'asc') ge('stype').value = 'desc';else ge('stype').value = 'asc';
		}else{ge('sval').value = field;}
	}
	function saveStatus(dealid){
		if(ge('rec')) rec = ge('rec').value; else rec = 0;
		if(ge('tagid')) tagid = ge('tagid').value; else tagid = 0;
		if(ge('comment')) comment = ge('comment').value.trim(); else comment = '';

		if(rec==0 && tagid == 0){
			alert("Please choose problem");
			return false;
		}
		if(rec==0 && tagid == -1 && (comment == '' ||  comment == 'Comments')){
			alert("Please write comments");
				return false;
		}

		if(comment == 'Comments') comment = '';

		j_query("#content").empty().html('<center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
		//alert("dealid = "+dealid+ "rec=" + rec + "&tagid=" + tagid + "&comment="+ comment);
		var url = btoa("submit=1&rec=" + rec + "&tagid=" + tagid + "&comment="+ escape(comment));
		window.location.assign("index.php?task=dealcomment&dealid="+dealid+"&url="+url);
	}
	function callDeal(dealid){
		j_query("#content").empty().html('<center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
		window.location.assign("index.php?task=deal&dealid="+dealid);
	}
	function callDealComments(dealid){
		j_query("#content").empty().html('<center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader3.gif" style="border:none;" /><br>&nbsp;</center>');
		window.location.assign("index.php?task=dealcomment&dealid="+dealid);
	}
	function callDashboard(){
		j_query("#content").empty().html('<center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
		window.location.assign("index.php?task=dashboard");
	}
	function callCommission(){
		j_query("#content").empty().html('<center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
		window.location.assign("index.php?task=commission");
	}
	function callDealList(type, dd, bucket,yr){
		if(typeof dd !== 'undefined') ; else if(ge('dd')) dd = ge('dd').value; else dd=0;
		if(typeof bucket !== 'undefined') ;	else if(ge('bucket'))bucket = ge('bucket').value; else bucket = -1;
		if(typeof yr !== 'undefined') ;	else if(ge('yr')) yr = ge('yr').value; else yr = '';

		if(ge('search')) search = ge('search').value; else search = '';
		if(ge('sval')) 	sval = ge('sval').value; else sval = 'pkid';
		if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
		if(ge('limit')) 	limit = ge('limit').value; else limit = 15;
		if(ge('page'))	page = ge('page').value; else page =1;

		j_query("#content").empty().html('<center>&nbsp;<br><img src="<?=$_SESSION['LKS_ROOT']?>/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
		var url = btoa("type=" + type + "&bucket=" + bucket + "&dd="+ dd + "&yr=" + yr + "&search=" + search +"&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
		window.location.assign("index.php?task=deallist&url="+url);
	}
<?
}