<?
if(!isset($task) || $task!="generic"){
    echo "Invalid Access in Generic Report";
    die();
}
else generic();

function generic(){
//	print_a($_REQUEST);
    $DEFAULT_SORT_TYPE = 'desc';
    $BUCKET_SIZE = 6;

	$index = isset($_REQUEST['index']) ? $_REQUEST['index'] : 1;

	$mm = date('m');; $yy = date('Y');

	$fy = ""; $last_fy = "";
	if(date('n') < 4){ //lastyear-thisyear
		$fy = date('y',  strtotime('-1 year'))."-".date('y');
		$last_fy = date('y',  strtotime('-2 year'))."-".date('y',  strtotime('-1 year'));
	}
	else {//thisyear-nextyear
		$fy = date('y')."-".date('y',  strtotime('+1 year'));
		$last_fy = date('y',  strtotime('-1 year'))."-".date('y');
	}

    $dbPrefix = $_SESSION['DB_PREFIX'];
	$dbPrefix_curr = "$dbPrefix".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));
	$dbPrefix_last = "$dbPrefix".($mm < 4 ? ($yy - 1)."".substr($yy-1,-2) : ($yy-1)."".(substr($yy-1,-2)+1));

	$fd = date('Y-M-01');

    //Get all centers
    $q1 = "SELECT tcase(centrenm) as centre from ".$dbPrefix.".tbmcentre order by centre";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){$centres = $t1['r'];}

    //Get all Dealer
    $q1 = "SELECT tcase(brkrnm) as dealer from ".$dbPrefix.".tbmbroker where brkrtyp = 1 and active = 2 order by active, brkrnm ";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){$dealer = $t1['r'];}

	//Get all Salesman
	$q1 = "SELECT tcase(salesmannm) as salesmannm from ".$dbPrefix.".tbmsalesman where active = 2 order by active, salesmannm";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){$salesman = $t1['r'];}

	//Get all Salesman
	$q1 = "SELECT tcase(salesmannm) as salesmannm from ".$dbPrefix.".tbmsalesman where active = 2 order by active, salesmannm";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){$salesman = $t1['r'];}

	//Get all banks
	$q1 = "SELECT bankid, banknm FROM $dbPrefix.tbmsourcebank where sourcebank = 1";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){$banks = $t1['r'];}

	//Get all bank branches
	$q1 = "SELECT b.bankBrnchid AS bankBrnchid, CONCAT(s.banknm, '-', tcase(b.bankBrnchNm)) AS branch FROM $dbPrefix.tbmsourcebank s JOIN $dbPrefix.tbmsourcebankbrnch b ON s.bankid = b.bankid and s.sourcebank = 1 ORDER BY s.bankid, b.bankBrnchNm";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){$branch = $t1['r'];}

	//Get all Callers
	$q1 = "SELECT userid as callerid, realname as callernm, active from ob_sa.tbmuser where active = 2 and recoveryagent = 1 order by active desc, realname asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$caller = $t1['r'];
	}
	//Get all SRAs1
	$q1 = "SELECT brkrid as sraid, brkrnm as sranm, active from ".$dbPrefix.".tbmbroker where active != 3 and brkrtyp = 2 order by active desc, brkrnm asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
			$sra = $t1['r'];
	}

/********************************Filters****************************************/
	$c = 0;
	$filters = array();

	$filters ['hpdt'] = array('title' => '','hover' => 'Hire Purchase Date of the vehicle','options' => NULL);
	$filters ['hpdt']['options'] = array(
		$c++ => array('value' => "All", 'query' => " " ),
		$c++ => array('value' => "Today & Yesterday", 'query' => " AND (date(d.hpdt) = date(curdate()) OR date(d.hpdt) = date(curdate()-1) ) " ),
		$c++ => array('value' => "Today", 'query' => " AND date(d.hpdt) = date(curdate()) "),
		$c++ => array('value' => "Yesterday", 'query' => " AND date(d.hpdt) = date(curdate()-1) "),
		$c++ => array('value' => "This Week", 'query' => " AND d.hpdt >= '".date('Y-m-d',strtotime('monday this week'))."' "),
		$c++ => array('value' => date('Y-M'), 'query' => " AND d.hpdt between '".date('Y-m-01')."' AND '".date('Y-m-t')."' "),
	);
	for($m = 1; $m <=6; $m++){
		$filters ['hpdt']['options'][$c++] = array('value' => date('Y-M',strtotime("-$m month", strtotime($fd))), 'query' => " AND  d.hpdt between '".date('Y-m-01', strtotime("-$m month", strtotime($fd)))."' AND '".date('Y-m-t', strtotime("-$m month", strtotime($fd)))."' ");
	}

	$filters ['hpdt']['options'][$c++] = array('value' => "Year: ".date('Y'), 'query' =>  " AND year(d.hpdt) = year(curdate()) ");
	$filters ['hpdt']['options'][$c++] = array('value' => "Year: ".(date('Y')-1), 'query' =>  " AND year(d.hpdt) = year(curdate())-1 ");
	for($i = date('Y'); $i >= 2008; $i--){
		$fy = substr($i,-2)."-".substr($i+1,-2);
		$filters ['hpdt']['options'][$c++] = array('value' =>"FY $fy", 'query' => " AND  d.fy ='$fy'");
	}
	$filters ['hpdt']['options'][$c++] = array('value'=>"Fresh Bouncing", 'query'=>" AND d.hpdt > '2014-03-31' ");
	$filters ['hpdt']['options'][$c++] = array('value'=>"Old", 'query'=>" AND d.hpdt < '2014-04-01'");

	$c =0;
	$filters ['fordt'] = array('title' => 'For','hover' => 'Recovery Month','options' => array(),);
	for($m = 1; $m <=4; $m++){
		$filters ['fordt']['options'][$c++] = array('value' => date('Y-M',strtotime("-$m month", strtotime($fd))), 'query' => " AND  ason between '".date('Y-m-01', strtotime("-$m month", strtotime($fd)))."' AND '".date('Y-m-t', strtotime("-$m month", strtotime($fd)))."' ");
	}

	$c =0;
	$filters ['centre'] = array('title' => '','hover' => 'Deal Centre','options' => array(
		$c++ => array('value' => "-Centre-", 'query' => " "),
		)
	);
	foreach($centres as $cen){
		$filters ['centre']['options'][$c++] = array('value' => $cen['centre'], 'query' => " AND d.centre = '".$cen['centre']."' ");
	}

	$c =0;
	$filters ['state'] = array('title' => '','hover' => 'State','options' => array(
		$c++ => array('value' => "-State-", 'query' => " "),
		$c++ => array('value' => "MP", 'query' => " AND d.state = 'MADHYA PRADESH' "),
		$c++ => array('value' => "MH", 'query' => " AND d.state = 'MAHARASHTRA' "),
		$c++ => array('value' => "CG", 'query' => " AND d.state = 'CHHATTISGARH' "),
		)
	);

	$c =0;
	$filters ['reccentre'] = array('title' => '','hover' => 'Recovery Centre','options' => array(
		$c++ => array('value' => "-Rec Centre-", 'query' => " "),
		)
	);
	foreach($centres as $cen){
		$filters ['reccentre']['options'][$c++] = array('value' => $cen['centre'], 'query' => " AND b.centre = '".$cen['centre']."' ");
	}

	$c =0;
	$filters ['salesman'] = array('title' => '','hover' => 'Salesman','options' => array(
		$c++ => array('value' => "-Salesman-", 'query' => " "),
		)
	);
	foreach($salesman as $s){
		$filters ['salesman']['options'][$c++] = array('value' => $s['salesmannm'], 'query' => " AND salesmannm = '".$s['salesmannm']."' ");
	}

	$c =0;
	$filters ['branch'] = array('title' => '','hover' => 'Bank Branches','options' => array(
		$c++ => array('value' => "-Bank & Branch-", 'query' => " "),
		)
	);

	foreach($banks as $s){
		$filters ['branch']['options'][$c++] = array('value' => $s['banknm']." (All)", 'query' => " AND bank.bankid = '".$s['bankid']."' ");
	}

	foreach($branch as $s){
		$filters ['branch']['options'][$c++] = array('value' => $s['branch'], 'query' => " AND bankBrnchid = '".$s['bankBrnchid']."' ");
	}

	$c =0;
	$filters ['caller'] = array('title' => '','hover' => 'Caller','options' => array(
		$c++ => array('value' => "-Caller-", 'query' => " "),
		$c++ => array('value' => "All Non Assigned", 'query' => " AND tbxfieldrcvry.callerid is NULL"),
		$c++ => array('value' => "All Assigned", 'query' => " AND tbxfieldrcvry.callerid is not NULL"),
		)
	);
	foreach($caller as $s){
		$filters ['caller']['options'][$c++] = array('value' => $s['callernm'], 'query' => " AND tbxfieldrcvry.callerid = '".$s['callerid']."' ");
	}

	$c =0;
	$filters ['sra'] = array('title' => '','hover' => 'SRA','options' => array(
		$c++ => array('value' => "-SRA-", 'query' => " "),
		$c++ => array('value' => "All Non Assigned", 'query' => " AND tbxfieldrcvry.sraid is NULL"),
		$c++ => array('value' => "All Assigned", 'query' => " AND tbxfieldrcvry.sraid is not NULL"),
		)
	);
	foreach($sra as $s){
		$filters ['sra']['options'][$c++] = array('value' => $s['sranm'], 'query' => " AND tbxfieldrcvry.sraid = '".$s['sraid']."' ");
	}

	$c =0;
	$filters ['dealer'] = array('title' => '','hover' => 'Dealer','options' => array(
		$c++ => array('value' => "-Dealer-", 'query' => " "),
		)
	);
	foreach($dealer as $d){
		$filters ['dealer']['options'][$c++] = array('value' => $d['dealer'], 'query' => " AND brkrnm = '".$d['dealer']."' ");
	}

	$c =0;
	$filters ['status'] = array('title' => '','hover' => 'Status','options' => array(
		$c++ => array('value' => "-Status-", 'query' => " "),
		$c++ => array('value' => "Active", 'query' => " AND dealsts = 1 "),
		$c++ => array('value' => "Closed", 'query' => " AND dealsts = 3 "),
		)
	);

	$c =0;
	$filters ['seizestatus'] = array('title' => '','hover' => 'Seized Status','options' => array(
		$c++ => array('value' => "Seized", 'query' => " AND status = 'Seized' "),
		$c++ => array('value' => "Released", 'query' => " AND status = 'Released' "),
		$c++ => array('value' => "Closed", 'query' => " AND status = 'Closed' "),
		$c++ => array('value' => "All", 'query' => " "),
		)
	);

	$c =0;
	$filters ['bucket'] = array('title' => '','hover' => 'Bucket','options' => array(
		$c++ => array('value' => "-Bucket-", 'query' => " "),
		$c++ => array('value' => "In Bckt", 'query' => " AND tbxfieldrcvry.rgid is not null "),
		$c++ => array('value' => "No Bckt", 'query' => " AND (tbxfieldrcvry.rgid is null Or tbxfieldrcvry.rgid = 0 ) "),
		$c++ => array('value' => "1", 'query' => " AND tbxfieldrcvry.rgid = 1 "),
		$c++ => array('value' => "2", 'query' => " AND tbxfieldrcvry.rgid = 2 "),
		$c++ => array('value' => "3", 'query' => " AND tbxfieldrcvry.rgid = 3 "),
		$c++ => array('value' => "4", 'query' => " AND tbxfieldrcvry.rgid = 4 "),
		$c++ => array('value' => "5", 'query' => " AND tbxfieldrcvry.rgid = 5 "),
		$c++ => array('value' => "6+", 'query' => " AND tbxfieldrcvry.rgid >= 5 "),
		)
	);

	$c =0;
	$filters ['bckt'] = array('title' => '', 'hover' => 'Bucket','options' => array());
	$filters ['bckt']['options'][$c++] = array('value' => "-Bucket-", 'query' => " ");
	for($i = 1; $i < 11; $i++){
		$filters ['bckt']['options'][$c++] = array('value' => "$i", 'query' => " AND d.rgid = $i ");
		$filters ['bckt']['options'][$c++] = array('value' => "LT $i", 'query' => " AND d.rgid <= $i ");
	}
	$filters ['bckt']['options'][$c++] = array('value' => "GT ".($i-1), 'query' => " AND d.rgid >= $i ");

	$c =0;
	$filters ['rec_emi'] = array('title' => 'EMIs Rec','hover' => 'Received EMIs','options' => array());
	for($i = 0; $i < 11; $i++){
		$filters ['rec_emi']['options'][$c++] = array('value' => "$i", 'query' => " AND rec_emi = $i ");
		$filters ['rec_emi']['options'][$c++] = array('value' => "LT $i", 'query' => " AND rec_emi <= $i ");
	}

	$filters ['rec_emi']['options'][$c++] = array('value' => "GT ".($i-1), 'query' => " AND rec_emi >= $i ");

	$c =0;
	$filters ['duedt'] = array('title' => '','hover' => 'Due Date for the deal','options' => array(
		$c++ => array('value' => "-Due Dt-", 'query' => " "),
		$c++ => array('value' => "3", 'query' => " AND day(d.startduedt) <=3 "),
		$c++ => array('value' => "8", 'query' => " AND day(d.startduedt) > 3  AND day(d.startduedt) <= 8"),
		$c++ => array('value' => "13", 'query' => " AND day(d.startduedt) > 8  AND day(d.startduedt) <= 13"),
		$c++ => array('value' => "18", 'query' => " AND day(d.startduedt) > 13  AND day(d.startduedt) <= 18"),
		$c++ => array('value' => "23", 'query' => " AND day(d.startduedt) > 18  AND day(d.startduedt) <= 23"),
		$c++ => array('value' => "28", 'query' => " AND day(d.startduedt) > 23  AND day(d.startduedt) <= 28"),
		$c++ => array('value' => ">28", 'query' => " AND day(d.startduedt) > 28 "),
		)
	);

	$c =0; $yy = date('Y'); $mm =date('n');
	$filters ['expired'] = array('title' => '','hover' => 'Expired or Active','options' => array(
		$c++ => array('value' => "-Both-", 'query' => " "),
		$c++ => array('value' => "Expired", 'query' => " AND d.hpexpdt < '$yy-$mm-01' "),
		$c++ => array('value' => "Active", 'query' => " AND d.hpexpdt >= '$yy-$mm-01' "),
		)
	);

	$c =0;
	$filters ['disbursed'] = array('title' => '','hover' => 'Disbursed Or Not','options' => array(
		$c++ => array('value' => "Not Disbursed", 'query' => " AND d.bankduedt is null"),
		$c++ => array('value' => "Disbursed", 'query' => " AND d.bankduedt is not null"),
		$c++ => array('value' => "All", 'query' => " "),
		)
	);

	$c =0;
	$filters ['department'] = array('title' => '','hover' => 'Department','options' => array(
		$c++ => array('value' => "-Department-", 'query' => " "),
		$c++ => array('value' => "Accounts", 'query' => " AND department = 'Accounts' "),
		$c++ => array('value' => "Back Office", 'query' => " AND department = 'Back Office' "),
		$c++ => array('value' => "Call Center", 'query' => " AND department = 'Call Center' "),
		$c++ => array('value' => "IT", 'query' => " AND department = 'IT' "),
		$c++ => array('value' => "Recovery", 'query' => " AND department = 'Recovery' "),
		$c++ => array('value' => "Sales", 'query' => " AND department = 'Sales' "),
		)
	);

	$c =0;
	$filters ['role'] = array('title' => '','hover' => 'Role','options' => array(
		$c++ => array('value' => "-Role-", 'query' => " "),
		$c++ => array('value' => "Employee", 'query' => " AND role = 'Employee' "),
		$c++ => array('value' => "Manager", 'query' => " AND role = 'Manager' "),
		$c++ => array('value' => "Regional Manager", 'query' => " AND role = 'Regional Manager' "),
		$c++ => array('value' => "Team Lead", 'query' => " AND role = 'Team Lead' "),
		)
	);
	$c =0; $yy = date('Y'); $mm =date('n');
	$filters ['mm'] = array('title' => '','hover' => 'Month','options' => array());
	for($i=0; $i<=5; $i++){
		$strm = strtotime("-$i month", strtotime($fd));
		$filters ['mm']['options'][$c++] = array('value' => date('Y-M',$strm), 'query' => ' AND mm = '. date('m',$strm).'' ,'query1' => " AND rcptdt between '".date('Y-m-d',$strm)."' and '".date('Y-m-t',$strm)."' ");
	}

	$c =0; $yy = date('Y'); $mm =date('n');
	$filters ['seizemm'] = array('title' => 'Seized On','hover' => 'Seized Month','options' => array());


	$filters ['seizemm']['options'][$c++] = array('value' => '-All-', 'query' => ' ');
	for($i=0; $i<6; $i++){
		$strm = strtotime("-$i month", strtotime($fd));
		$filters ['seizemm']['options'][$c++] = array('value' => date('Y-M',$strm), 'query' => " AND VhclSzDT between '".date('Y-m-d',$strm)."' and '".date('Y-m-t',$strm)."' ");
	}

	$c =0;
	$filters ['dd'] = array('title' => '','hover' => 'Operning or New','options' => array());
	$filters ['dd']['options'][$c++] = array('value' => '-Both-', 'query' => '');
	$filters ['dd']['options'][$c++] = array('value' => 'Opening', 'query' => ' AND dd = 1');
	$filters ['dd']['options'][$c++] = array('value' => 'New', 'query' => ' AND dd > 1 ');

	$c =0;
	$filters ['chduedt'] = array('title' => 'Due:','hover' => 'Due Day of the Month','options' => array(
		$c++ => array('value' => "-All-", 'query' => " "),
		$c++ => array('value' => "03", 'query' => " AND day(duedt) = 3 "),
		$c++ => array('value' => "08", 'query' => " AND day(duedt) = 8 "),
		$c++ => array('value' => "13", 'query' => " AND day(duedt) = 13 "),
		$c++ => array('value' => "18", 'query' => " AND day(duedt) = 18 "),
		$c++ => array('value' => "23", 'query' => " AND day(duedt) = 23 "),
		$c++ => array('value' => "28", 'query' => " AND day(duedt) = 28 "),
		)
	);

	/***********CHEQUES, NACH, ECS RELATED ************************************************************/
	$c =0;
	$filters ['pt_nac'] = array('title' => '','hover' => 'NACH Approved?','options' => array(
		$c++ => array('value' => "--NACH--", 'query' => " "),
		$c++ => array('value' => "Approved", 'query' => " AND n.ApprvFlg = 1 "),
		$c++ => array('value' => "Rejected", 'query' => " AND n.ApprvFlg = 2 "),
		$c++ => array('value' => "Pending", 'query' => " AND n.ApprvFlg = 0 "),
		$c++ => array('value' => "Not Applied", 'query' => " AND n.ApprvFlg is NULL ")
		)
	);
	$c =0;
	$filters ['pt_ecs'] = array('title' => '','hover' => 'ECS Approved?','options' => array(
		$c++ => array('value' => "--ECS--", 'query' => " "),
		$c++ => array('value' => "Approved", 'query' => " AND  e.ApprvFlg = 1 "),
		$c++ => array('value' => "Rejected", 'query' => " AND  e.ApprvFlg = 2 "),
		$c++ => array('value' => "Pending", 'query' => " AND  e.ApprvFlg = 0 "),
		$c++ => array('value' => "Not Applied", 'query' => " AND  e.ApprvFlg is NULL "),
		)
	);
	$c =0;
	$filters ['pt_pdc'] = array('title' => '','hover' => 'PDC Available?','options' => array(
		$c++ => array('value' => "-PDCs-", 'query' => " "),
		$c++ => array('value' => "0 PDCs", 'query' => " AND  PendingPDC = 0 "),
		$c++ => array('value' => "1 PDCs", 'query' => " AND  PendingPDC = 1 "),
		$c++ => array('value' => "2 PDCs", 'query' => " AND  PendingPDC = 2 "),
		$c++ => array('value' => "3 PDCs", 'query' => " AND  PendingPDC = 3 "),
		$c++ => array('value' => "4+ PDCs", 'query' => " AND PendingPDC > 3 "),
		)
	);
	$c =0;
	$filters ['nacind'] = array('title' => '','hover' => 'NACH Deposited this month?','options' => array(
		$c++ => array('value' => "-NAC ND-", 'query' => " "),
		$c++ => array('value' => "N", 'query' => " AND nind.NACDpstInd = 'N' "),
		$c++ => array('value' => "Y", 'query' => " AND nind.NACDpstInd = 'Y' "),
		)
	);

	$c =0;
	$filters ['ecsind'] = array('title' => '','hover' => 'ECS Deposited this month?','options' => array(
		$c++ => array('value' => "-ECS ND-", 'query' => " "),
		$c++ => array('value' => "Empty", 'query' => " AND eind.ECSDpstInd is null "),
		$c++ => array('value' => "N", 'query' => " AND eind.ECSDpstInd = 'N' "),
		$c++ => array('value' => "Y", 'query' => " AND eind.ECSDpstInd = 'Y' "),
		)
	);
	$c =0;
	$filters ['pdcind'] = array('title' => '','hover' => 'PDC Deposited this month?','options' => array(
		$c++ => array('value' => "-PDC ND-", 'query' => " "),
		$c++ => array('value' => "Empty", 'query' => " AND pdcind.PDCDpstInd is null "),
		$c++ => array('value' => "N", 'query' => " AND pdcind.PDCDpstInd = 'N' "),
		$c++ => array('value' => "Y", 'query' => " AND pdcind.PDCDpstInd = 'Y' "),
		)
	);

	$c =0;
	$filters ['bouncingstatus'] = array('title' => 'Status:','hover' => 'Status','options' => array(
		$c++ => array('value' => "-All-", 'query' => " "),
		$c++ => array('value' => "Not Presented", 'query' => " AND cbflg is null "),
		$c++ => array('value' => "Cleared", 'query' => " AND cbflg = 0 "),
		$c++ => array('value' => "Bounced", 'query' => " AND cbflg = -1 "),
		)
	);

	$c =0;
	$filters ['rcptpaytype'] = array('title' => '','hover' => 'Pay Mode - NAC, PDC, ECS','options' => array(
		$c++ => array('value' => "-All-", 'query' => " "),
		$c++ => array('value' => "PDC/NACH", 'query' => " AND rcptpaymode = 2 "),
		$c++ => array('value' => "ECS", 'query' => " AND rcptpaymode = 6 "),
		$c++ => array('value' => "Cash", 'query' => " AND rcptpaymode = 1 "),
		)
	);

	$c =0;
	$filters ['paytype'] = array('title' => '','hover' => 'Pay Mode - NAC, PDC, ECS','options' => array(
		$c++ => array('value' => "-All-", 'query' => " "),
		$c++ => array('value' => "PDC", 'query' => " AND pt.paytype = 1 "),
		$c++ => array('value' => "ECS", 'query' => " AND pt.paytype = 2 "),
		$c++ => array('value' => "NACH", 'query' => " AND pt.paytype = 3 "),
		$c++ => array('value' => "Nothing", 'query' => " AND pt.paytype is NULL "),
		)
	);

/********************************Filters****************************************/

/********************************Query****************************************/
	$query = array(); $qi=0;

	$c = 0;	$qi = 0;
	$query[$qi] = array(
		'title'=> 'Pay Instrument Report',
		'default_sort' => 'd.dealid',
		'default_sort_type' => $DEFAULT_SORT_TYPE,
		'filters' => array('hpdt', 'bucket', 'centre','duedt', 'pt_nac','nacind', 'pt_ecs', 'ecsind', 'pt_pdc', 'pdcind'),
		'q' => "SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) AS dealnm, DATE_FORMAT(d.startduedt, '%d-%b-%y') AS startduedt, tcase(d.centre) AS Centre,
			f.rgid,
			CASE n.ApprvFlg WHEN 0 THEN 'Pending' WHEN 1 THEN 'Approved' WHEN 2 THEN 'Rejected' ELSE NULL END AS NAC, TRIM(CONCAT(DATE_FORMAT(n.ApproveRejectDt, '%d-%b-%y'),' ', NACRemark)) AS NACApproved, nind.NACDpstInd,
			CASE e.ApprvFlg WHEN 0 THEN 'Pending' WHEN 1 THEN 'Approved' WHEN 2 THEN 'Rejected' ELSE NULL END AS ECS, TRIM(CONCAT(DATE_FORMAT(e.ApproveRejectDt, '%d-%b-%y'),' ', ECSRemark)) AS ECSApproved, eind.ECSDpstInd,
			pdc.PendingPDC, pdcind.PDCDpstInd
			FROM $dbPrefix.tbmdeal d
			LEFT JOIN $dbPrefix_curr.tbxfieldrcvry f on d.dealid = f.dealid and f.mm= ".date('n')."
			LEFT JOIN (SELECT d.dealid, SUM(CASE WHEN p.PDCDt < DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND p.PDCDpstInd = 'N' THEN 1
			WHEN p.PDCDt > DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) THEN 1 ELSE 0 END) AS PendingPDC FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmdealpdc p ON d.dealid = p.dealid AND d.dealsts = 1 GROUP BY p.dealid) AS pdc
				ON d.dealid = pdc.dealid
			LEFT JOIN $dbPrefix.tbmdealecs e ON e.dealid = d.dealid
			LEFT JOIN $dbPrefix.tbmdealnac n ON n.dealid = d.dealid
			LEFT JOIN (SELECT e.dealid, ECSDt, ECSDpstInd FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmdealecsdtl e ON e.dealid = d.dealid AND d.dealsts = 1 WHERE ECSDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') eind ON eind.dealid = d.dealid
			LEFT JOIN (SELECT n.dealid, NACDt, NACDpstInd FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmdealnacdtl n ON n.dealid = d.dealid AND d.dealsts = 1 WHERE NACDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') nind ON nind.dealid = d.dealid
			LEFT JOIN (SELECT p.dealid, PDCDt, PDCDpstInd FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmdealpdc    p ON p.dealid = d.dealid AND d.dealsts = 1 WHERE PDCDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') pdcind ON pdcind.dealid = d.dealid
			WHERE d.dealsts = 1 AND d.startduedt  <= ' ".date('Y-m-t')."' :hpdt :bucket :centre :duedt :pt_nac :nacind :pt_ecs :ecsind :pt_pdc :pdcind ",

		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Customer Name',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Start Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bucket',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'NACH',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'NACH Remarks',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'NAC Dep',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'ECS',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'ECS Remarks',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'ECS Dep',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'PDCs',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'PDC Dep',),
		),
	);

	//index == 1
	$c = 0;
	$q = "SELECT t1.mm,
	SUM(assigned_fd) AS assigned_fd, SUM(recovered_fd) AS recovered_fd, (SUM(recovered_fd)/SUM(assigned_fd)*100) as per_fd,

	SUM(assigned_dm) AS assigned_dm, SUM(recovered_dm) AS recovered_dm, (SUM(recovered_dm)/SUM(assigned_dm)*100) as per_dm,
	COUNT(dealid) AS assigned,  SUM(recovered) AS recovered, round(SUM(recovered)/COUNT(dealid)*100) as per";
	for($b = 1; $b <=$BUCKET_SIZE; $b++){
		$q .=", SUM(a$b) AS a$b, SUM(r$b) AS r$b ";
	}
	$q .= "
	FROM (
		SELECT d.mm, d.inserttimestamp, d.dealid, d.OdDueAmt, d.dd, t.dealid AS rdid, t.rcptamt, d.OdDueAmt - t.rcptamt AS balance,
		CASE WHEN d.dd = 1 THEN 1 ELSE 0 END AS assigned_fd, CASE WHEN d.dd = 1 AND t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered_fd,
		CASE WHEN d.dd != 1 THEN 1 ELSE 0 END AS assigned_dm, CASE WHEN d.dd != 1 AND t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered_dm, ";

		//Bucket 1 to previous last bucket
		for($b = 1; $b < $BUCKET_SIZE; $b++){
			$q .= " CASE WHEN d.rgid = $b THEN 1 ELSE 0 END AS a$b ,"; // Assigned
			$q .= " CASE WHEN t.dealid IS Not NULL AND d.rgid = $b THEN 1 ELSE 0 END AS r$b ,"; // Recovered
		}
		//Last bucket
		$q .= " CASE WHEN d.rgid >= $b THEN 1 ELSE 0 END AS a$b ,"; // Assigned
		$q .= " CASE WHEN t.dealid IS Not NULL AND d.rgid >= $b THEN 1 ELSE 0 END AS r$b ,"; // Recovered
		$q .= " CASE WHEN t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered, ";

		$q .= " d.sraid, tcase(d.centre) as centre
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d
		LEFT JOIN (
			SELECT Month(r.rcptdt) as mm, r.dealid, SUM(rd.rcptamt) AS rcptamt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid WHERE r.cclflg = 0 AND r.CBflg = 0 AND (rd.dctyp = 101 OR rd.dctyp = 111) and r.rcptpaymode = 1
			GROUP BY r.dealid, month(r.rcptdt)
		) AS t ON d.dealid = t.dealid and d.mm = t.mm where 1 :hpdt :centre :state
	) t1 GROUP BY t1.mm having 1 ";

	$columns = array(
		$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 0, 'name' => 'Month'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'OPENING','style'=>'background-color:#F5F5DC'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'O-REC','style'=>'background-color:#F5F5DC'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%','style'=>'background-color:#F5F5DC', 'suffix'=> '%'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'NEW','style'=>'background-color:#FFE4C4'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'N-REC','style'=>'background-color:#FFE4C4'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%','style'=>'background-color:#FFE4C4', 'suffix'=> '%'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'Total','style'=>'background-color:#F5F5DC'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'T-REC','style'=>'background-color:#F5F5DC'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%','style'=>'background-color:#F5F5DC', 'suffix'=> '%'),
	);
	for($b = 1; $b <=$BUCKET_SIZE; $b++){
		$style = ($b%2 == 1 ?  'background-color:#F6F6F6' : '');
		$columns[$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'B'.$b, 'style'=> $style);
		$columns[$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '', 'style'=> $style);
	}

	$qi = 1;
	$query[$qi] = array(
		'title'=> 'Recovery History',
		'default_sort' => 't1.mm',
		'default_sort_type' => 'ASC',
		'filters' => array('hpdt', 'centre', 'state'),
		'q' => $q,
		'columns' => $columns,
	);

	$qi = 2; $c =0;
	$query[$qi] = array(
		'title'=> 'Caller Tags',
		'default_sort' => 'rectagid_caller',
		'default_sort_type' => 'ASC',
		'alternate' => array(array(2, 'Caller Tags'), array(3, 'SRA Tags'),array(4, 'Caller Tag Entry'),array(5,'SRA Tag Entry')),
		'filters' => array('mm', 'hpdt', 'centre', 'state', 'bucket', 'dd'),
		'q' => "SELECT ct.description AS CALLER_TAG, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN $dbPrefix.tbmrecoverytags ct ON rectagid_caller = ct.tagid
		WHERE rec_flg = 0 :mm :hpdt :centre :state :bucket :dd GROUP BY rectagid_caller",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Caller Tag'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deals'),
		),
	);

	$qi = 3; $c =0;
	$query[$qi] = array(
		'title'=> 'SRA Tags',
		'default_sort' => 'rectagid_sra',
		'default_sort_type' => 'ASC',
		'alternate' => array(array(2, 'Caller Tags'), array(3, 'SRA Tags'),array(4, 'Caller Tag Entry'),array(5,'SRA Tag Entry')),
		'filters' => array('mm', 'hpdt', 'centre', 'state', 'bucket', 'dd'),
		'q' => "SELECT ct.description AS SRA_TAG, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN $dbPrefix.tbmrecoverytags ct ON rectagid_sra = ct.tagid
		WHERE rec_flg = 0 :mm :hpdt :centre :state :bucket :dd GROUP BY rectagid_sra",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'SRA Tag'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deals'),
		),
	);

	$qi = 4; $c =0;
	$query[$qi] = array(
		'title'=> 'Caller Tag Entry',
		'default_sort' => 'realname',
		'default_sort_type' => 'ASC',
		'alternate' => array(array(2, 'Caller Tags'), array(3, 'SRA Tags'),array(4, 'Caller Tag Entry'),array(5,'SRA Tag Entry')),
		'filters' => array('mm', 'hpdt', 'centre', 'state', 'bucket','dd'),
		'q' => "SELECT u.realname, sum(case when rectagid_caller is null then 0 else 1 end) as entered, sum(case when rectagid_caller is null then 1 else 0 end) as notentered, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN ob_sa.tbmuser u ON tbxfieldrcvry.callerid = u.userid
		WHERE rec_flg = 0 :mm :hpdt :centre :state :bucket :dd GROUP BY tbxfieldrcvry.callerid",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Caller Name'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Not Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total'),
		),
	);

	$qi = 5; $c =0;
	$query[$qi] = array(
		'title'=> 'SRA Tag Entry',
		'default_sort' => 'brkrnm',
		'default_sort_type' => 'ASC',
		'alternate' => array(array(2, 'Caller Tags'), array(3, 'SRA Tags'),array(4, 'Caller Tag Entry'),array(5,'SRA Tag Entry')),
		'row_limit' => 100,
		'filters' => array('mm', 'hpdt', 'centre', 'state', 'bucket','dd'),
		'q' => "SELECT sql_calc_found_rows b.brkrnm, sum(case when rectagid_sra is null then 0 else 1 end) as entered, sum(case when rectagid_sra is null then 1 else 0 end) as notentered, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN $dbPrefix.tbmbroker b ON tbxfieldrcvry.sraid = b.brkrid and b.brkrtyp = 2
		WHERE rec_flg = 0 :mm :hpdt :centre :state :bucket :dd GROUP BY tbxfieldrcvry.sraid",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'SRA Name'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Not Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total'),
		),
	);

	//index == 6
	$c =0;$qi = 6;
	$query[$qi] = array(
		'title'=> 'Disbursement Report',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre', 'state' , 'dealer', 'salesman', 'disbursed'),
		'q' => "SELECT sql_calc_found_rows d.dealno, d.hpdt, d.dealrefno AS account, tcase(d.dealnm) as dealnm, DATE_ADD(d.bankduedt, INTERVAL -1 MONTH) AS disbursementdt, tcase(br.brkrnm) as brkrnm, CONCAT(v.make, ' ',v.model) AS Vehicle, tcase(d.centre) as centre, d.financeamt, f.disbursementamt, tcase(s.salesmannm) as salesmannm, b.banknm, b.bankbrnchnm
		FROM $dbPrefix.tbmdeal d
		JOIN $dbPrefix.tbadealsalesman a JOIN $dbPrefix.tbmsalesman s ON d.dealid = a.dealid AND a.salesmanid = s.salesmanid AND d.dealsts = 1 :hpdt :centre :state :salesman :disbursed
		LEFT JOIN $dbPrefix.tbmbroker br ON br.brkrid = d.brkrid
		LEFT JOIN $dbPrefix.tbadealfnncdtls f ON d.dealid = f.dealid
		LEFT JOIN $dbPrefix.tbmdealvehicle v ON d.dealid = v.dealid
		LEFT JOIN (SELECT bank.banknm, branch.bankbrnchnm, branch.bankbrnchid FROM $dbPrefix.tbmsourcebank bank JOIN $dbPrefix.tbmsourcebankbrnch branch ON bank.bankid = branch.bankid) AS b ON d.bankbrnchid = b.bankbrnchid where 1 :dealer ",

		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'V Acc No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Customer Name',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Disbure Dt',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Dealer Name',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Vehicle',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Finance',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Disbursement',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Salesman',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bank',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Branch',),
		),
	);

	//index == 7
	$c =0;$qi = 7;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Daywise Recovery',
		'default_sort' => 'rcptdt',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'dd', 'hpdt', 'centre', 'state'),
		'row_limit' => 50,
		'q' => "SELECT r.rcptdt,
			SUM(CASE WHEN d.dd = 1 THEN 1 ELSE 0 END) AS fd,
			SUM(CASE WHEN d.dd != 1 THEN 1 ELSE 0 END) AS dm,
			COUNT(DISTINCT r.dealid) AS recovered,
			COUNT(DISTINCT r.dealid)/(SELECT COUNT(dealid) FROM ".$dbPrefix_curr.".tbxfieldrcvry d WHERE 1 :mm :hpdt :centre :state)*100 AS tot_per
			, SUM(r.totrcptamt) as totamt
			, SUM(CASE WHEN d.rgid = 1 THEN 1 ELSE 0 END) AS B1
			, SUM(CASE WHEN d.rgid = 2 THEN 1 ELSE 0 END) AS B2
			, SUM(CASE WHEN d.rgid = 3 THEN 1 ELSE 0 END) AS B3
			, SUM(CASE WHEN d.rgid = 4 THEN 1 ELSE 0 END) AS B4
			, SUM(CASE WHEN d.rgid = 5 THEN 1 ELSE 0 END) AS B5
			, SUM(CASE WHEN d.rgid > 5 THEN 1 ELSE 0 END) AS B6
			FROM ".$dbPrefix_curr.".tbxfieldrcvry d JOIN (select dealid, rcptdt, sum(totrcptamt) as totrcptamt from ".$dbPrefix_curr.".tbxdealrcpt rd
			where rd.cbflg =0 AND rd.cclflg =0 AND rd.rcptpaymode = 1 and rd.totrcptamt > 0 $rcpt_clause group by dealid, day(rcptdt)) as r ON r.dealid = d.dealid :mm :dd :hpdt :centre :state
			GROUP BY r.rcptdt ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 2, 'name' => 'Date',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Opening','style'=>'background-color:#F5F5DC','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'New','style'=>'background-color:#FFE4C4','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Total','style'=>'background-color:#F5F5DC', 'cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '%','style'=>'background-color:#F5F5DC', 'suffix'=> '%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Amount', 'cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'B1',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'B2','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'B3',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'B4','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'B5',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'B6','style'=>'background-color:#F6F6F6'),
		),
	);

	//index == 8
	$c =0;$qi = 8;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'SRA Commission',
		'default_sort' => 'centre asc, brkrnm',
		'default_sort_type' => 'asc',
		'alternate' => array(array(23, 'Caller Commission'), array(8, 'SRA Commission'),),
		'filters' => array('mm', 'dd', 'hpdt','reccentre', 'state'),
		'row_limit' => 100,
		'q' => "SELECT tcase(b.centre) as centre, tcase(ifnull(b.brkrnm,'Unassigned')) as brkrnm,
		sum(case when dd=1 then 1 else 0 end) as Assigned, sum(case when dd=1 and (rc.od is not null or d.rec_flg = 1) then 1 else 0 end) as Recovered,
		sum(case when dd=1 and (rc.od is not null or d.rec_flg = 1) then 1 else 0 end)/sum(case when dd=1 then 1 else 0 end)*100 as per,
		SUM(rc.od) AS total,
		SUM(CASE WHEN rgid=1 THEN LEAST(rc.od, d.initod) ELSE 0 END) AS B1, SUM(CASE WHEN rgid=2 THEN LEAST(rc.od, d.initod) ELSE 0 END) AS B2,
		SUM(CASE WHEN rgid=3 THEN LEAST(rc.od, d.initod) ELSE 0 END) AS B3, SUM(CASE WHEN rgid=4 THEN LEAST(rc.od, d.initod) ELSE 0 END) AS B4,
		SUM(CASE WHEN rgid=5 THEN LEAST(rc.od, d.initod) ELSE 0 END) AS B5, SUM(CASE WHEN rgid >5 THEN LEAST(rc.od, d.initod) ELSE 0 END) AS B6,
		SUM(CASE WHEN rgid=1 THEN LEAST(rc.od, d.initod) ELSE 0 END)*0.02+SUM(CASE WHEN rgid=2 THEN LEAST(rc.od, d.initod) ELSE 0 END)*0.04+SUM(CASE WHEN rgid=3 THEN LEAST(rc.od, d.initod) ELSE 0 END)*0.06+ SUM(CASE WHEN rgid=4 THEN LEAST(rc.od, d.initod) ELSE 0 END)*0.08+SUM(CASE WHEN rgid=5 THEN LEAST(rc.od, d.initod) ELSE 0 END)*0.08+SUM(CASE WHEN rgid >5 THEN LEAST(rc.od, d.initod) ELSE 0 END)*0.1 as Commission
		, SUM(rc.cb) as cb, SUM(rc.penalty) as penalty,	SUM(rc.cb)*0.15 + SUM(rc.penalty)*0.1 as commission2
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d LEFT JOIN ".$dbPrefix.".tbmbroker b ON d.sraid = b.brkrid AND b.brkrtyp = 2
		LEFT JOIN (
			SELECT dealid,
			IFNULL(SUM(CASE WHEN dctyp = 101 or dctyp = 102 or dctyp = 111 then ifnull(rcptamt,0) else 0 end),0) AS od,
			IFNULL(SUM(CASE WHEN dctyp = 103 then ifnull(rcptamt,0) else 0 end),0) AS cb,
			IFNULL(SUM(CASE WHEN dctyp = 104 then ifnull(rcptamt,0) else 0 end),0) AS penalty
			FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid
			AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1
			WHERE 1 $rcpt_clause GROUP BY r.dealid
		) rc ON d.dealid = rc.dealid WHERE 1 :mm :dd :reccentre :state :hpdt GROUP BY b.centre, sraid ",

		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'SRA Name'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Assigned'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Recovered'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%', 'style'=>'font-weight:bold;background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Collection', 'style'=>'font-weight:bold'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B1'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B2'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B3'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B4'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B5'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'OTC(70% Rec)','style'=>'font-weight:bold;'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Chq Bouncing'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Commission', 'style'=>'font-weight:bold;'),
		),
	);

	//index == 9
	$c =0;$qi = 9;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'EMI Recovery - Executive Wise',
		'default_sort' => 'centre asc, brkrnm',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'dd', 'hpdt', 'reccentre', 'state'),
		'alternate' => array(array(9, 'Executive Wise'), array(30, 'Centre Wise'), array(31, 'Month Wise')),
		'row_limit' => 100,
		'q' => "SELECT tcase(b.centre) as centre, tcase(ifnull(b.brkrnm,'Uniassigned')) as brkrnm, SUM(ifnull(OdDueAmt,0)/EMI) AS Assigned, SUM(rc.amt/EMI) AS Recovered, (SUM(rc.amt/EMI)/SUM(OdDueAmt/EMI)*100) AS Percentage
		, SUM(case when d.rgid = 1 then d.rgid else 0 end) as A1
		, SUM(case when d.rgid = 1 then rc.amt/EMI else 0 end) as B1
		, SUM(case when d.rgid = 2 then d.rgid else 0 end) as A2
		, SUM(case when d.rgid = 2 then rc.amt/EMI else 0 end) as B2
		, SUM(case when d.rgid = 3 then d.rgid else 0 end) as A3
		, SUM(case when d.rgid = 3 then rc.amt/EMI else 0 end) as B3
		, SUM(case when d.rgid = 4 then d.rgid else 0 end) as A4
		, SUM(case when d.rgid = 4 then rc.amt/EMI else 0 end) as B4
		, SUM(case when d.rgid = 5 then d.rgid else 0 end) as A5
		, SUM(case when d.rgid = 5 then rc.amt/EMI else 0 end) as B5
		, SUM(case when d.rgid > 5 then d.rgid else 0 end) as A6
		, SUM(case when d.rgid > 5 then rc.amt/EMI else 0 end) as B6
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d LEFT JOIN ".$dbPrefix.".tbmbroker b ON d.sraid = b.brkrid AND b.brkrtyp = 2 :reccentre
		LEFT JOIN (SELECT dealid, IFNULL(SUM(rcptamt),0) AS amt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1 AND (rd.dctyp = 101 OR rd.dctyp = 102 OR rd.dctyp = 111) WHERE 1 $rcpt_clause GROUP BY r.dealid) rc
		ON d.dealid = rc.dealid WHERE 1 :mm :dd :hpdt :state GROUP BY b.centre, sraid ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'SRA Name'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Assigned'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Recovered'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B1','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B2'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B3','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B4'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B5','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
		),
	);

	//index == 10
	$c =0;$qi = 10;
	$query[$qi] = array(
		'title'=> 'Vehicle - Details Not Updated',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre', 'state' , 'dealer', 'salesman', 'disbursed'),
		'q' => "SELECT sql_calc_found_rows d.dealno, d.hpdt, tcase(d.dealnm) as dealnm,  tcase(d.centre) as centre, DATE_ADD(d.bankduedt, INTERVAL -1 MONTH) AS disbursementdt, tcase(br.brkrnm) as brkrnm,tcase(s.salesmannm) as salesmannm, CONCAT(v.make, ' ',v.model) AS Vehicle, v.engineno, v.chasis, v.rtoregno
		FROM $dbPrefix.tbmdeal d
		JOIN $dbPrefix.tbadealsalesman a JOIN $dbPrefix.tbmsalesman s ON d.dealid = a.dealid AND a.salesmanid = s.salesmanid AND d.dealsts = 1 :hpdt :centre :state :salesman :disbursed
		LEFT JOIN $dbPrefix.tbmbroker br ON br.brkrid = d.brkrid
		LEFT JOIN $dbPrefix.tbmdealvehicle v ON d.dealid = v.dealid
		where 1 :dealer and (v.chasis ='NA' or v.engineno ='NA') ",

		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Customer Name',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Disbure Dt',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Dealer Name',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Vehicle',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Salesman',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Engine No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Chassis No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'RTO Reg No',),
		),
	);

	//index == 11
	$c =0;$qi = 11;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$duedt_clause = str_replace('rcptdt' , 'duedt', $filters ['mm']['options'][$mm]['query1']);
	$closedt_clause = str_replace('rcptdt' , 'd.closedt', $filters ['mm']['options'][$mm]['query1']);

	$query[$qi] = array(
		'title'=> 'Due List Report',
		'default_sort' => 'hpdt',
		'default_sort_type' => 'asc',
		'filters' => array('mm','hpdt', 'centre', 'state', 'bouncingstatus', 'chduedt', 'rcptpaytype'),
		'row_limit' => 100,
		'q' => "SELECT sql_calc_found_rows dealno, tcase(d.dealnm) as dealnm, d.hpdt, tcase(d.centre) as centre, d.period, dl.SrNo, dl.duedt, (dl.dueamt+dl.collectionchrgs) AS emi, concat(CASE r.rcptpaymode WHEN 2 THEN 'PDC' WHEN 6 THEN 'ECS' ELSE NULL END, ' (',CASE d.paytype WHEN 1 THEN 'PDC' WHEN 2 THEN 'ECS' WHEN 3 THEN 'NACH' ELSE NULL END,')') as mode, r.rcptdt, r.totrcptamt, CASE r.cbflg WHEN 0 THEN 'Cleared' WHEN -1 THEN 'Bounced' ELSE NULL END AS cbflg, ifnull( count(r.dealid),0)
		FROM ".$dbPrefix.".tbmDeal d JOIN ".$dbPrefix.".tbmDueList dl
		ON dl.DealId= d.DealID AND (d.DealSts = 1 OR (d.cancleflg = 0 $closedt_clause)) :hpdt :centre :state $duedt_clause :chduedt
		LEFT JOIN ".$dbPrefix_curr.".tbxdealrcpt r ON d.dealid = r.dealid AND (r.RcptPayMode = 2 OR r.RcptPayMode=6) $rcpt_clause where 1 :bouncingstatus :rcptpaytype group by d.dealid, r.dealid ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 2, 'name' => 'Customer Name'),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Tenure'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Inst No'),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Due Date'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'EMI'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Type'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Receipt Dt'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Receipt'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Status'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'cnt'),
		),
	);

	//index == 12
	$c =0;$qi = 12;
	$query[$qi] = array(
		'title'=> 'NOC Report',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre', 'state', 'branch', 'status'),
		'q' => "
			SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) as dealnm, d.hpdt, CASE d.dealsts WHEN 1 THEN 'Active' WHEN 3 THEN 'Closed' ELSE d.dealsts END as status, tcase(d.centre) as centre, np.acxndt AS BankPaymentDt, dn.NocDate AS BankNOCDt, dn.NocNo AS BankNOCNo, cn.NOCDate AS CustomerNOCDt, cn.RtnDate, tcase(br.brkrnm) AS SRA, cn.SendDate, bank.brnch
			FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbadealnocpmnt np on d.dealid = np.dealid AND d.cancleflg = 0 :status :hpdt :centre :state
			JOIN (SELECT b.bankBrnchid, concat(s.banknm, ' - ', tcase(b.bankBrnchNm)) as brnch FROM $dbPrefix.tbmsourcebank s JOIN $dbPrefix.tbmsourcebankbrnch b ON s.bankid = b.bankid :branch ) bank
			ON d.bankbrnchid = bank.bankbrnchid
			LEFT JOIN $dbPrefix.tbadealnoc dn ON np.dealid = dn.dealid
			LEFT JOIN $dbPrefix.tbadealcustnoc cn ON np.dealid = cn.dealid
			LEFT JOIN $dbPrefix.tbmbroker br ON cn.sraid = br.brkrid WHERE 1 ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Customer Name',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Status',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Paid to Bank',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Bank NOC Dt',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bank NOC #',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'To Customer On',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Returned On',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'SRA',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'To SRA On',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bank',),
		),
	);

	$q = "";
	for ($d =2008; $d <= date('Y'); $d++){
		$db = "$dbPrefix".$d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
		$q .= " SELECT $d, acid, COUNT(acid) AS vcnt, SUM(CASE WHEN acxntyp = 2 THEN acxnamt ELSE 0 END) - SUM(CASE WHEN acxntyp = 1 THEN acxnamt ELSE 0 END ) AS vcamt FROM $db.tbxacvoucher v WHERE (acvchtyp = 1401 OR acvchtyp = 1402) AND acxnsrno = 0 GROUP BY acid
		UNION";
	}
	$q = rtrim($q, "UNION");

	$q1 = "SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) as dealnm, d.hpdt, tcase(d.centre) as centre, d.startduedt, d.bankduedt, pm.mthlyamt AS bankemi, d.period, ROUND(SUM(z.vcamt)/pm.mthlyamt) AS EMIPosted,
		CASE WHEN (TIMESTAMPDIFF(MONTH, bankduedt, DATE_ADD(NOW(), INTERVAL 3 DAY))+1) > period THEN period ELSE (TIMESTAMPDIFF(MONTH, bankduedt, DATE_ADD(NOW(), INTERVAL 3 DAY))+1) END AS EMIPending,
		SUM(z.vcamt) AS posted,
		pm.mthlyamt * CASE WHEN (TIMESTAMPDIFF(MONTH, bankduedt, DATE_ADD(NOW(), INTERVAL 3 DAY))+1) > period THEN period ELSE (TIMESTAMPDIFF(MONTH, bankduedt, DATE_ADD(NOW(), INTERVAL 3 DAY))+1) END AS tobeposted, bank.brnch
		FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmpmntschd pm ON d.dealid = pm.dealid :hpdt :centre :state
		JOIN (SELECT b.bankBrnchid, concat(s.banknm, ' - ', tcase(b.bankBrnchNm)) as brnch FROM $dbPrefix.tbmsourcebank s JOIN $dbPrefix.tbmsourcebankbrnch b ON s.bankid = b.bankid :branch ) bank ON d.bankbrnchid = bank.bankbrnchid :branch
		JOIN $dbPrefix.tbadealaccount a ON d.dealid = a.dealid AND a.dealactyp = 504 AND d.dealsts = 1 AND d.bankduedt IS NOT NULL
		LEFT JOIN (".$q.") AS z ON a.acid = z.acid
		LEFT JOIN $dbPrefix.tbadealbnkforeclose fc on d.dealid = fc.dealid
		where fc.dealid is null
		GROUP BY d.dealid HAVING posted != tobeposted ";

	//index == 13
	$c =0;$qi = 13;
	$query[$qi] = array(
		'title'=> 'No Liability Posting',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => 'desc',
		'filters' => array('hpdt', 'centre', 'state', 'branch'),
		'q' => $q1,
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Customer Name',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Start Due',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Bank Due',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bank EMI',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Period',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Posted EMIs',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Due EMIs',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Posted Amt',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Due Amt',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bank',),
		),
	);

	//index == 14
	$c =0;$qi = 14;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Last Payment (Cases in OD with last payment month and HP Date)',
		'default_sort' => 'mm',
		'default_sort_type' => ' ',
		'filters' => array('hpdt', 'centre', 'rcptpaytype'),
		'row_limit' => 15,
		'q' => "SELECT MONTH(t.dt) as mm, COUNT(dealid) AS total
			,SUM(CASE WHEN t.fy = '08-09' THEN 1 ELSE 0 END) AS '08-09'
			,SUM(CASE WHEN t.fy = '09-10' THEN 1 ELSE 0 END) AS '09-10'
			,SUM(CASE WHEN t.fy = '10-11' THEN 1 ELSE 0 END) AS '10-11'
			,SUM(CASE WHEN t.fy = '11-12' THEN 1 ELSE 0 END) AS '11-12'
			,SUM(CASE WHEN t.fy = '12-13' THEN 1 ELSE 0 END) AS '12-13'
			,SUM(CASE WHEN t.fy = '13-14' THEN 1 ELSE 0 END) AS '13-14'
			,SUM(CASE WHEN t.fy = '14-15' THEN 1 ELSE 0 END) AS '14-15'
			,SUM(CASE WHEN t.fy = '15-16' THEN 1 ELSE 0 END) AS '15-16'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 4 THEN 1 ELSE 0 END) AS 'Apr-15'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 5 THEN 1 ELSE 0 END) AS 'May-15'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 6 THEN 1 ELSE 0 END) AS 'Jun-15'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 7 THEN 1 ELSE 0 END) AS 'Jul-15'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 8 THEN 1 ELSE 0 END) AS 'Aug-15'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 9 THEN 1 ELSE 0 END) AS 'Sep-15'
			,SUM(CASE WHEN t.fy = '15-16' AND MONTH(hpdt) = 10 THEN 1 ELSE 0 END) AS 'Oct-15' FROM
			(
			SELECT d.dealid, d.hpdt, d.fy, SUM(totrcptamt) AS amt, MAX(rcptdt) AS dt FROM $dbPrefix_curr.tbxfieldrcvry d LEFT JOIN $dbPrefix_curr.tbxdealrcpt r ON d.dealid = r.dealid AND r.cclflg = 0 AND r.cbflg = 0 :rcptpaytype WHERE d.mm = ".date('n')." :centre :hpdt GROUP BY d.dealid
			) t GROUP BY MONTH(t.dt) ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 2, 'name' => 'Payment Month',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Total/HP Date','style'=>'background-color:#FFE4C4'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '08-09'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '09-10'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '10-11'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '11-12'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '12-13'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '13-14'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '14-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '15-16','style'=>'background-color:#ccc'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Apr-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'May-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Jun-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Jul-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Aug-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Sep-15'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Oct-15'),
		),
	);

	//index == 15
	$c =0;$qi = 15;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$query[$qi] = array(
		'title'=> 'Regular Recovery',
		'default_sort' => 'dealno',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre' ,'state'),
		'row_limit' => 50,
		'q' => "SELECT sql_calc_found_rows d.dealno, d.dealnm, d.hpdt, d.centre, dl.duedt, (dl.dueamt+dl.collectionchrgs) AS emi, r.rcptdt, r.totrcptamt, b.brkrnm AS SRA
			FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmduelist dl ON d.dealid = dl.dealid :hpdt :centre :state
			JOIN $dbPrefix_curr.tbxdealrcpt r ON d.dealid = r.dealid AND r.rcptpaymode = 1 AND r.rcptdt = dl.duedt
			JOIN $dbPrefix.tbmbroker b ON r.sraid = b.brkrid AND b.brkrtyp = 2
			GROUP BY d.dealid, r.rcptdt ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Customer Name',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Due Date',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'EMI',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Receipt Dt',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Amount',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'SRA',),
		),
	);

	//index == 16
	$c =0;$qi = 16;
	$query[$qi] = array(
		'title'=> 'Seized Vehicle Report',
		'default_sort' => 'VhclSzDT',
		'default_sort_type' => 'desc',
		'filters' => array('seizemm','hpdt', 'centre', 'state', 'seizestatus'),
		'row_limit' => 50,
		'q' => "SELECT sql_calc_found_rows dealno, dealnm, hpdt, centre, model, status, VhclSzDT, VhclRlDt, tcase(GdwnNm) as godown, VhclSaleDt, SaleAmt,COUNT(dealid) as seizeCount
		FROM
		(SELECT d.DealID, d.DealNo , tcase(DealNm) as dealnm, d.hpdt, tcase(d.centre) as centre, trim(concat(dv.make,' ', dv.model, ' ', dv.modelyy)) as model, VhclSzDT, VhclRlDt, VhclSaleDt, g.GdwnNm , (CASE WHEN dv.siezeFlg=0 THEN (CASE WHEN d.DealSts = 1 THEN 'Released' WHEN  d.DealSts = 3 THEN 'Closed' END) WHEN dv.siezeFlg=-1 THEN 'Seized' END) AS status, SaleAmt
		FROM  $dbPrefix_curr.tbxvhclsz AS seize
		JOIN  $dbPrefix.tbmgdwn AS g ON g.GdwnId=seize.GdwnId :seizemm
		LEFT JOIN  $dbPrefix_curr.tbxvhclrl AS rel ON seize.VhclSzRlId= rel.VhclSzRlId
		LEFT JOIN  $dbPrefix_curr.tbxszvhclsale AS sale ON  seize.VhclSzRlId= sale.VhclSzRlId
		JOIN $dbPrefix.tbmDeal AS d  ON d.DealId=seize.DealID :hpdt :centre :state
		JOIN $dbPrefix.tbmdealvehicle AS dv ON d.DealId=dv.DealID WHERE CclFlg = 0 ORDER BY seize.DealId,VhclSzDT DESC ) AS seize GROUP BY DealNo having 1 :seizestatus ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 2, 'name' => 'Customer Name',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Model',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Status',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Seize Dt',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Release Dt',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Godown',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Sale Dt',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Sale Amt',),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Times Seized',),
		),
	);

	//index == 17
	$c =0;$qi = 17;
	$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);

	$q = "SELECT DAY(rcptdt) AS d, COUNT(rcptid) AS total, SUM(CASE WHEN cbflg = -1 THEN 1 ELSE 0 END) AS Bounced, (SUM(CASE WHEN cbflg = -1 THEN 1 ELSE 0 END)/COUNT(rcptid)*100) AS Per ";


	for($i = 1; $i <= $m_in_fy; $i++){
		$mn = ($i+3)%12; $startdt = date("Y-$mn-01"); $enddt = date("Y-$mn-t");
		$q .= "
		,SUM(CASE WHEN rcptdt BETWEEN '$startdt' AND '$enddt' THEN 1 ELSE 0 END) AS '$mn-t', SUM(CASE WHEN cbflg = -1 AND rcptdt BETWEEN '$startdt' AND '$enddt' THEN 1 ELSE 0 END) AS '$mn-b'";
	}
	$q .= " FROM $dbPrefix.tbmdeal d join $dbPrefix_curr.tbxdealrcpt r on d.dealid = r.dealid :hpdt :centre :state WHERE cclflg = 0 AND (rcptpaymode = 2 OR rcptpaymode = 6)
		GROUP BY DAY(rcptdt)";

	$query[$qi] = array(
		'title'=> 'Bouncing Report - Daywise',
		'default_sort' => 'd',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre', 'state'),
		'alternate' => array(array(17, 'Show Daywise'), array(18, 'Show Centrewise'),array(19, 'Show Executivewise')),
		'row_limit' => 50,
		'q' => $q,
		'columns' => array(
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Day',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deposit',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bounced',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%'),
		),
	);

	for($i=1; $i <= $m_in_fy; $i++){
		$mn = ($i+3)%12;
		$query[$qi]['columns'][$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => toMonthName($mn),);
		$query[$qi]['columns'][$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => toMonthName($mn).'-Bncd','style'=>'background-color:#ecf6fc');
	}

	//index == 18
	$c =0;$qi = 18;
	$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);

	$q = "SELECT tcase(centre) as centre, COUNT(rcptid) AS total, SUM(CASE WHEN cbflg = -1 THEN 1 ELSE 0 END) AS Bounced, (SUM(CASE WHEN cbflg = -1 THEN 1 ELSE 0 END)/COUNT(rcptid)*100) AS Per ";

	for($i = 1; $i <= $m_in_fy; $i++){
		$mn = ($i+3)%12; $startdt = date("Y-$mn-01"); $enddt = date("Y-$mn-t");
		$q .= "
		,SUM(CASE WHEN rcptdt BETWEEN '$startdt' AND '$enddt' THEN 1 ELSE 0 END) AS '$mn-t', SUM(CASE WHEN cbflg = -1 AND rcptdt BETWEEN '$startdt' AND '$enddt' THEN 1 ELSE 0 END) AS '$mn-b'";
	}
	$q .= " FROM $dbPrefix.tbmdeal d join $dbPrefix_curr.tbxdealrcpt r on d.dealid = r.dealid :hpdt :state WHERE cclflg = 0 AND (rcptpaymode = 2 OR rcptpaymode = 6)
		GROUP BY centre having 1 :centre ";

	$query[$qi] = array(
		'title'=> 'Bouncing Report - CentreWise',
		'default_sort' => 'per',
		'default_sort_type' => 'desc',
		'filters' => array('hpdt', 'centre', 'state'),
		'alternate' => array(array(17, 'Show Daywise'), array(18, 'Show Centrewise'),array(19, 'Show Executivewise')),
		'row_limit' => 50,
		'q' => $q,
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deposit',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bounced',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%'),
		),
	);

	for($i=1; $i <= $m_in_fy; $i++){
		$mn = ($i+3)%12;
		$query[$qi]['columns'][$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => toMonthName($mn),);
		$query[$qi]['columns'][$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => toMonthName($mn).'-Bncd','style'=>'background-color:#ecf6fc');
	}

	//index == 19
	$c =0;$qi = 19;
	$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);

	$q = "SELECT tcase(s.centre) as centre, tcase(s.salesmannm) as exec, COUNT(rcptid) AS total, SUM(CASE WHEN cbflg = -1 THEN 1 ELSE 0 END) AS Bounced, (SUM(CASE WHEN cbflg = -1 THEN 1 ELSE 0 END)/COUNT(rcptid)*100) AS Per ";

	for($i = 1; $i <= $m_in_fy; $i++){
		$mn = ($i+3)%12; $startdt = date("Y-$mn-01"); $enddt = date("Y-$mn-t");
		$q .= "
		,SUM(CASE WHEN rcptdt BETWEEN '$startdt' AND '$enddt' THEN 1 ELSE 0 END) AS '$mn-t', SUM(CASE WHEN cbflg = -1 AND rcptdt BETWEEN '$startdt' AND '$enddt' THEN 1 ELSE 0 END) AS '$mn-b'";
	}
	$q .= " FROM $dbPrefix.tbmdeal d join $dbPrefix.tbadealsalesman sa on d.dealid = sa.dealid :hpdt :centre :state
			JOIN $dbPrefix.tbmsalesman s ON s.salesmanid = sa.salesmanid
			JOIN $dbPrefix_curr.tbxdealrcpt r ON d.dealid = r.dealid  WHERE cclflg = 0 AND (rcptpaymode = 2 OR rcptpaymode = 6)
		GROUP BY s.centre, s.salesmanid ";

	$query[$qi] = array(
		'title'=> 'Bouncing Report - Sales Executive Wise',
		'default_sort' => 'per',
		'default_sort_type' => 'desc',
		'filters' => array('hpdt', 'centre', 'state'),
		'alternate' => array(array(17, 'Show Daywise'), array(18, 'Show Centrewise'),array(19, 'Show Executivewise')),
		'row_limit' => 200,
		'q' => $q,
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Centre',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Sales Exec',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deposit',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bounced',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%'),
		),
	);

	for($i=1; $i <= $m_in_fy; $i++){
		$mn = ($i+3)%12;
		$query[$qi]['columns'][$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => toMonthName($mn),);
		$query[$qi]['columns'][$c++] = array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => toMonthName($mn).'-Bncd','style'=>'background-color:#ecf6fc');
	}

	//index == 20
	$c =0;$qi = 20;
	$query[$qi] = array(
		'title'=> 'Non-Starter Cases',
		'default_sort' => 'startduedt asc, d.dealnm ',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre','state' , 'bckt', 'rec_emi'),
		'row_limit' => 200,
		'q' => "SELECT SQL_CALC_FOUND_ROWS d.dealno, tcase(d.dealnm) as name, tcase(d.centre) as centre, d.hpdt, tbmdeal.startduedt, d.hpexpdt, d.oddueamt, d.rgid, round((ifnull(p.rcptamt,0)/EMI)) as rec_emi, tbmdeal.financeamt, d.model, tcase(s.salesmannm) as sexc, tcase(b.brkrnm) as rexe, p.rcptamt
		FROM $dbPrefix.tbmdeal JOIN $dbPrefix_curr.tbxfieldrcvry d ON tbmdeal.dealid = d.dealid and tbmdeal.startduedt < DATE_ADD(NOW(), INTERVAL -1 MONTH) :hpdt :centre :state :bckt
		LEFT JOIN
			(SELECT rc.dealid, SUM(rc.rcptamt) AS rcptamt FROM (
				SELECT '200809', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa200809.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '200910', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa200910.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '201011', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa201011.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '201112', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa201112.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '201213', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa201213.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '201314', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa201314.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '201415', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa201415.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION
				SELECT '201516', r.dealid, SUM(r.totrcptamt) AS rcptamt FROM lksa201516.tbxdealrcpt r WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				) AS rc GROUP BY rc.dealid
			) p
			ON d.dealid = p.dealid
			JOIN $dbPrefix.tbmsalesman s on d.salesmanid = s.salesmanid
			LEFT JOIN $dbPrefix.tbmbroker b on d.sraid = b.brkrid
			WHERE mm= ".date('n')."  HAVING 1 :rec_emi ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 2, 'name' => 'Customer Name',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'StartDueDt',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Expriy',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'OD',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Bucket',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Received EMI',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Finance',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Model',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Sales Exe',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Assigned To',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Receipt Amt',),
		),
	);


	//index == 21
	$c =0;$qi = 21;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Employee List',
		'default_sort' => 'name',
		'default_sort_type' => 'asc',
		'filters' => array('centre', 'department', 'role'),
		'row_limit' => 200,
		'q' => "SELECT sql_calc_found_rows id, `name`, centre, mobile, department, role, designation, joiningdt, birthdt FROM $dbPrefix.tbmemployee d WHERE d.active = 2 :centre :department :role",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Employee Id'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Name'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Mobile'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Department'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Role'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Designation'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Joining'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Birth Dt'),
		),
	);

	//index == 22 // AND dl.duedt BETWEEN '".date('Y-m-d')."' AND '".date('Y-m').'-'.(date('d')+5)."'
	$c =0;$qi = 22;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Due Date List',
		'default_sort' => 'duedt asc, dealno',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt','centre', 'state', 'duedt', 'paytype', 'bucket'),
		'row_limit' => 100,
		'q' => "
			SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) AS dealnm, tcase(d.centre) AS centre, d.city, d.hpdt, dl.duedt, TRIM(CONCAT(d.mobile,' ' ,d.mobile2,' ', d.tel1,' ', d.tel2)) as phone, case d.paytype when 1 then 'PDC' when 2 then 'ECS' when 3 then 'Direct Debit' ELSE 'Nothing' End as rcptpaymode,
			pt.drownon, pt.place,
			dl.dueamt + dl.collectionchrgs AS emi, tbxfieldrcvry.rgid, tbxfieldrcvry.OdDueAmt, tbxfieldrcvry.DueAmt, (d.period - dl.srno + 1) AS PendingTenure
			FROM lksa.tbmdeal d JOIN lksa.tbmduelist dl ON d.dealid = dl.dealid AND d.dealsts = 1 AND dl.duedt between '".date('Y-m-01')."' and '".date('Y-m-t')."' :centre :state :duedt :hpdt
			LEFT JOIN lksa.tbmpaytype pt ON d.dealid = pt.dealid AND d.paytype = pt.paytype
			LEFT JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON tbxfieldrcvry.dealid = d.dealid AND tbxfieldrcvry.mm = ".date('m')." where  1 :paytype :bucket ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 1, 'stotal' => 2, 'name' => 'Customer Name',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'City',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'HP Dt',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'DueDt',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Phone',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Type', 'style' =>'font-weight:bold'),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bank',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Branch',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'EMI',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Bucket',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'OD',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'TotalDue',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Pending Tenure',),
		),
	);

	if(isset($_SESSION['is_admin_login']) && $_SESSION['is_admin_login']){
	//index == 23
	//LEFT JOIN lksa.tbmemployee e ON e.oldid = d.callerid
	//LEFT JOIN ".$dbPrefix_curr.".tbxempscheme sh ON e.id = sh.empid :mm
	//CASE WHEN SUM(rc.cb) + SUM(rc.penalty) > SUM(rc.collection)*0.07 then SUM(rc.cb) + SUM(rc.penalty) - SUM(rc.collection)*0.07 ELSE 0 END as Target,
	$c =0;$qi = 23;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Caller Commission',
		'default_sort' => 'name',
		'default_sort_type' => 'asc',
		'alternate' => array(array(23, 'Caller Commission'), array(8, 'SRA Commission'),),
		'filters' => array('mm', 'dd'),
		'row_limit' => 100,
		'q' => "SELECT tcase(IFNULL(realname,' Unassigned')) AS NAME, Assigned, Recovered, Recovered/Assigned *100 AS per, cb, penalty, cb+penalty AS total, collection, (cb + penalty)*0.06 AS commission,
		sra_hra,
		CASE WHEN sra_hra = 1 then I1 When sra_hra = 2 THEN I2 WHEN sra_hra = 3 THEN I3 WHEN sra_hra = 4 THEN I4 ELSE 0 END as incentive,
		CASE WHEN sra_hra = 1 then I1 When sra_hra = 2 THEN I2 WHEN sra_hra = 3 THEN I3 WHEN sra_hra = 4 THEN I4 ELSE 0 END + (cb + penalty)*0.06 as final
		FROM
		(SELECT b.realname, b.sra_hra,
		COUNT(d.dealid) AS Assigned, SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END) AS Recovered,
		SUM(rc.cb) AS cb, SUM(rc.penalty) AS penalty, SUM(rc.collection) AS collection,
		CASE WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 95 THEN 8000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 90 THEN 7000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 85 THEN 6000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 80 THEN 5000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 75 THEN 4000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 70 THEN 3000
			ELSE 0
		END AS I1,
		CASE WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 95 THEN 10000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 90 THEN 9000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 85 THEN 8000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 80 THEN 7000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 75 THEN 6000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 70 THEN 5000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 65 THEN 4000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 60 THEN 3500
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 55 THEN 3000
			ELSE 0
		END AS I2,
		CASE WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 75 THEN 11000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 70 THEN 10000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 65 THEN 9000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 60 THEN 8000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 55 THEN 7000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 50 THEN 6000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 45 THEN 5000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 40 THEN 4000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 35 THEN 3500
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 30 THEN 3000
			ELSE 0
		END AS I3,
		CASE WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 95 THEN 10000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 90 THEN 9000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 85 THEN 8000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 80 THEN 7000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 75 THEN 6000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 70 THEN 5000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 65 THEN 4500
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 60 THEN 4000
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 55 THEN 3500
			WHEN SUM(CASE WHEN rc.od IS NOT NULL OR d.rec_flg = 1 THEN 1 ELSE 0 END)/COUNT(d.dealid)*100 >= 50 THEN 3000
			ELSE 0
		END AS I4
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d
		LEFT JOIN ob_sa.tbmuser b ON d.callerid = b.userid
		LEFT JOIN (
		SELECT dealid,
		IFNULL(SUM(rcptamt),0) AS collection,
		IFNULL(SUM(CASE WHEN dctyp = 101 or dctyp = 102 or dctyp = 111 then ifnull(rcptamt,0) else 0 end),0) AS od,
		IFNULL(SUM(CASE WHEN dctyp = 103 then ifnull(rcptamt,0) else 0 end),0) AS cb,
		IFNULL(SUM(CASE WHEN dctyp = 104 then ifnull(rcptamt,0) else 0 end),0) AS penalty
		FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1
		WHERE 1 $rcpt_clause GROUP BY r.dealid) rc ON d.dealid = rc.dealid WHERE 1 :mm :dd GROUP BY b.userid ) ee ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'Caller Name'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Assigned'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Recovered'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%', 'style'=>'font-weight:bold;background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Chq Bouncing'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Collection', 'style'=>'font-weight:bold'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'PCB Commission', 'style'=>'font-weight:bold;'),
			$c++ => array('align'=>0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Scheme'),
/*			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'I1',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'I2',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'I3',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'I4',),
*/			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Incentive',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total',),
		),
	);
	}

	if(isset($_SESSION['is_admin_login']) && $_SESSION['is_admin_login']){
	//index == 24
	$c =0;$qi = 24;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$acxndt_clause = str_replace('rcptdt' , 'acxndt', $filters ['mm']['options'][$mm]['query1']);
	$query[$qi] = array(
		'title'=> 'Bank Repayment Report',
		'alternate' => array(array(24, 'Bank Repayments'), array(25, 'Customer Repayments')),
		'default_sort' => 'AcxnDt',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'hpdt','centre', 'state', 'branch'),
		'row_limit' => 100,
		'q' => "
		SELECT v.AcxnDt, SUM(CASE WHEN b.bankid = 1 THEN 1 ELSE 0 END) AS NNSBDeals, SUM(CASE WHEN b.bankid = 1 THEN v.AcxnAmt ELSE 0 END) AS NNSB,
		SUM(CASE WHEN b.bankid = 100001021 THEN 1 ELSE 0 END) AS BUCSDeals, SUM(CASE WHEN b.bankid = 100001021 THEN v.AcxnAmt ELSE 0 END) AS BUCS,
		count(d.dealid) as dealcount, SUM(v.AcxnAmt) as total
		FROM lksa.tbmDeal d JOIN lksa.tbaDealAccount da ON d.DealID = da.DealID AND da.DealAcTyp=504 :hpdt :centre :state
		JOIN (SELECT bank.bankid, bank.banknm, branch.bankbrnchnm, branch.bankbrnchid FROM lksa.tbmsourcebank bank JOIN lksa.tbmsourcebankbrnch branch ON bank.bankid = branch.bankid :branch) AS b
		ON d.bankbrnchid = b.bankbrnchid
		JOIN ".$dbPrefix_curr.".tbxAcVoucher v ON v.AcID = da.AcId AND AcVchTyp = 1401 $acxndt_clause
		GROUP BY  v.AcxnDt ",
		'columns' => array(
			$c++ => array('align'=>0, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 2, 'name' => 'Repayment Dt',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'NNSB-Deals', ),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Repayments','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'BUCS-Deals',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Repayments','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deals',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Amount','cummulative'=>1),
		),
	);
	}

	if($_SESSION['is_admin_login']){
	//index == 25
	$c =0;$qi = 25;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Customer Repayment Report',
		'alternate' => array(array(24, 'Bank Repayments'), array(25, 'Customer Repayments')),
		'default_sort' => 'rcptdt',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'hpdt','centre', 'state', 'branch'),
		'row_limit' => 100,
		'q' => "SELECT r.rcptdt, SUM(CASE WHEN r.rcptpaymode = 1 THEN rd.rcptamt ELSE 0 END) AS Cash,
		SUM(CASE WHEN r.rcptpaymode = 6 THEN rd.rcptamt ELSE 0 END) AS ECS,
		SUM(CASE WHEN r.rcptpaymode = 2 THEN rd.rcptamt ELSE 0 END) AS PDC,
		SUM(rd.rcptamt) as total
		FROM lksa.tbmdeal d JOIN ".$dbPrefix_curr.".tbxdealrcpt r ON d.dealid = r.dealid :hpdt :centre :state
		JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode IN (1,2,6) $rcpt_clause
		GROUP BY r.rcptdt",
		'columns' => array(
			$c++ => array('align'=>0, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 2, 'name' => 'Receipt Dt',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Cash','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'ECS','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'PDC','cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total','cummulative'=>1),
		),
	);
	}

	if($_SESSION['is_admin_login']){
	//index == 26
	$c =0;$qi = 26;
	$query[$qi] = array(
		'title'=> 'Monthly Total Cash Collection',
		'alternate' => array(array(26, 'Total Cash Collection'), array(27, 'Recovery Cash Collection')),
		'default_sort' => 'mnth',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt','centre', 'state'),
		'row_limit' => 100,
		'q' => "SELECT MONTH(rcptdt) as mnth, COUNT(DISTINCT r.dealid) AS deals , SUM(rd.rcptamt) AS Total,
			SUM(CASE WHEN rd.dctyp = 101 OR rd.dctyp = 102 OR rd.dctyp = 111 THEN rd.rcptamt ELSE 0 END) AS EMI,
			SUM(CASE WHEN rd.dctyp = 103 THEN rd.rcptamt ELSE 0 END) AS Bouncing,
			SUM(CASE WHEN rd.dctyp = 104 THEN rd.rcptamt ELSE 0 END) AS Penalty,
			SUM(CASE WHEN rd.dctyp = 105 THEN rd.rcptamt ELSE 0 END) AS Seizing,
			SUM(CASE WHEN rd.dctyp = 107 THEN rd.rcptamt ELSE 0 END) AS Other
			FROM lksa.tbmdeal d JOIN ".$dbPrefix_curr.".tbxdealrcpt r ON d.dealid = r.dealid :hpdt :centre :state
			JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON rd.rcptid = r.rcptid AND r.rcptpaymode = 1 AND r.cclflg = 0 AND r.cbflg = 0
			GROUP BY MONTH(rcptdt) ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 2, 'name' => 'Month'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deals'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total','style'=>'background-color:#F5F5DC'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'EMI',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bouncing',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Seizing'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Oter'),
		),
	);
	}

	if($_SESSION['is_admin_login']){
	//index == 27
	$c =0;$qi = 27;
	$query[$qi] = array(
		'title'=> 'Monthly Recovery Cash Collection',
		'alternate' => array(array(26, 'Total Cash Collection'), array(27, 'Recovery Cash Collection')),
		'default_sort' => 'mnth asc, rgid ',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt','centre', 'state', 'bckt'),
		'row_limit' => 100,
		'q' => "SELECT MONTH(rcptdt) AS Mnth, d.rgid, COUNT(DISTINCT r.dealid) AS deals, SUM(rd.rcptamt) AS Total,
			SUM(CASE WHEN rd.dctyp = 101 OR rd.dctyp = 102 OR rd.dctyp = 111 THEN rd.rcptamt ELSE 0 END) AS EMI,
			SUM(CASE WHEN rd.dctyp = 103 THEN rd.rcptamt ELSE 0 END) AS Bouncing,
			SUM(CASE WHEN rd.dctyp = 104 THEN rd.rcptamt ELSE 0 END) AS Penalty,
			SUM(CASE WHEN rd.dctyp = 105 THEN rd.rcptamt ELSE 0 END) AS Seizing,
			SUM(CASE WHEN rd.dctyp = 107 THEN rd.rcptamt ELSE 0 END) AS Other
			 FROM
			$dbPrefix_curr.tbxfieldrcvry d JOIN $dbPrefix_curr.tbxdealrcpt r ON d.dealid = r.dealid :hpdt :centre :bckt :state
			AND r.rcptpaymode = 1 AND r.cclflg = 0 AND r.cbflg = 0 AND d.mm = MONTH(r.rcptdt)
			JOIN $dbPrefix_curr.tbxdealrcptdtl rd ON rd.rcptid = r.rcptid GROUP BY
			MONTH(r.rcptdt), d.rgid ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 2, 'name' => 'Month'),
			$c++ => array('align'=>0, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'Bucket'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deals'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total','style'=>'background-color:#F5F5DC'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'EMI',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bouncing',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Seizing'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Other'),
		),
	);
	}


	//index == 28
	$c =0;$qi = 28;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Cash Receipts',
		'default_sort' => 'rcptdt DESC, rcptid ',
		'default_sort_type' => 'desc',
		'filters' => array('mm', 'hpdt','centre', 'state', 'bucket', 'caller', 'sra'),
		'row_limit' => 50,
		'q' => "SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) as customer, tcase(d.centre) as centre, tbxfieldrcvry.EMI, rt.rcptdt, b1.brkrnm, rt.EMI as OD, rt.Bouncing, rt.Penalty, rt.Seizing, rt.Other, tbxfieldrcvry.rgid, b2.brkrnm as assignedsra, u.realname FROM
			(SELECT r.rcptid, r.rcptdt, dealid, sraid,
			SUM(CASE WHEN rd.dctyp IN (101,102,111) THEN rd.rcptamt ELSE 0 END) AS EMI,
			SUM(CASE WHEN rd.dctyp = 103 THEN rd.rcptamt ELSE 0 END) AS Bouncing,
			SUM(CASE WHEN rd.dctyp = 104 THEN rd.rcptamt ELSE 0 END) AS Penalty,
			SUM(CASE WHEN rd.dctyp = 105 THEN rd.rcptamt ELSE 0 END) AS Seizing,
			SUM(CASE WHEN rd.dctyp = 107 THEN rd.rcptamt ELSE 0 END) AS Other
			FROM $dbPrefix_curr.tbxdealrcpt r JOIN $dbPrefix_curr.tbxdealrcptdtl rd
			ON rd.rcptid = r.rcptid AND r.rcptpaymode = 1  AND r.cclflg = 0 AND r.cbflg = 0
			$rcpt_clause
			GROUP BY r.rcptid
			) rt
			JOIN lksa.tbmdeal d ON d.dealid = rt.dealid :hpdt :centre :state
			JOIN lksa.tbmbroker b1 ON rt.sraid = b1.brkrid AND b1.brkrtyp = 2
			LEFT JOIN $dbPrefix_curr.tbxfieldrcvry ON rt.dealid = tbxfieldrcvry.dealid :mm
			LEFT JOIN lksa.tbmbroker b2 ON tbxfieldrcvry.sraid = b2.brkrid AND b2.brkrtyp = 2
			LEFT JOIN ob_sa.tbmuser u ON tbxfieldrcvry.callerid = u.userid where 1 :caller :sra :bucket",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 1, 'stotal' => 2, 'name' => 'Customer Name',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'EMI',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 0, 'name' => 'Receipt Dt',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Taken By',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'OD',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bouncing',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Seizing',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Other',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Bucket',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Assigned SRA',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Caller',),
		),
	);

	//index == 29
	$c =0;$qi = 29;
	$query[$qi] = array(
		'title'=> 'Mobile View',
		'default_sort' => 'name',
		'default_sort_type' => 'asc',
		'filters' => array('centre'),
		'row_limit' => 100,
		'q' => "SELECT sql_calc_found_rows tcase(d.name) as name, CONCAT('http://r.loksuvidha.com:81/app/?imei=',v.imei) AS url, tcase(d.centre) as centre, c.state, v.imei FROM $dbPrefix.tbmemployee d JOIN $dbPrefix.tbmdevices v ON d.id = v.empid AND d.department = 'Recovery'  AND v.active = 2 AND d.active = 2 JOIN $dbPrefix.tbmcentre c ON d.centre = c.centrenm :centre GROUP BY d.id",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Name',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'URL', 'type' => 'URL',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'State',),
			$c++ => array('align'=>0, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'IMEI',),
		),
	);

	//index == 30
	$c =0;$qi = 30;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'EMI Recovery - Centre Wise',
		'default_sort' => 'centre',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'dd', 'hpdt', 'reccentre', 'state'),
		'alternate' => array(array(9, 'Executive Wise'), array(30, 'Centre Wise'), array(31, 'Month Wise')),
		'row_limit' => 100,
		'q' => "SELECT tcase(ifnull(b.centre, ' Uniassigned')) as centre, SUM(ifnull(OdDueAmt,0)/EMI) AS Assigned, SUM(rc.amt/EMI) AS Recovered, (SUM(rc.amt/EMI)/SUM(OdDueAmt/EMI)*100) AS Percentage
		, SUM(case when d.rgid = 1 then d.rgid else 0 end) as A1
		, SUM(case when d.rgid = 1 then rc.amt/EMI else 0 end) as B1
		, SUM(case when d.rgid = 2 then d.rgid else 0 end) as A2
		, SUM(case when d.rgid = 2 then rc.amt/EMI else 0 end) as B2
		, SUM(case when d.rgid = 3 then d.rgid else 0 end) as A3
		, SUM(case when d.rgid = 3 then rc.amt/EMI else 0 end) as B3
		, SUM(case when d.rgid = 4 then d.rgid else 0 end) as A4
		, SUM(case when d.rgid = 4 then rc.amt/EMI else 0 end) as B4
		, SUM(case when d.rgid = 5 then d.rgid else 0 end) as A5
		, SUM(case when d.rgid = 5 then rc.amt/EMI else 0 end) as B5
		, SUM(case when d.rgid > 5 then d.rgid else 0 end) as A6
		, SUM(case when d.rgid > 5 then rc.amt/EMI else 0 end) as B6
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d LEFT JOIN ".$dbPrefix.".tbmbroker b ON d.sraid = b.brkrid AND b.brkrtyp = 2 :reccentre
		LEFT JOIN (SELECT dealid, IFNULL(SUM(rcptamt),0) AS amt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1 AND (rd.dctyp = 101 OR rd.dctyp = 102 OR rd.dctyp = 111) WHERE 1 $rcpt_clause GROUP BY r.dealid) rc
		ON d.dealid = rc.dealid WHERE 1 :mm :dd :hpdt :state GROUP BY b.centre",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Assigned'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Recovered'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B1','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B2'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B3','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B4'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B5','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
		),
	);


	//index == 31
	$c =0;$qi = 31;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'EMI Recovery - Monthwise',
		'default_sort' => 'mm ',
		'default_sort_type' => 'asc',
		'filters' => array('dd', 'hpdt', 'centre', 'state'),
		'alternate' => array(array(9, 'Executive Wise'), array(30, 'Centre Wise'), array(31, 'Month Wise')),
		'row_limit' => 100,
		'q' => "SELECT mm, SUM(ifnull(OdDueAmt,0)/EMI) AS total, SUM(case when sraid is not null then ifnull(OdDueAmt,0)/EMI ELSE 0 END) AS Assigned, SUM(ifnull(rc.amt,0)/EMI) AS Recovered, (SUM(ifnull(rc.amt,0)/EMI)/SUM(ifnull(OdDueAmt,0)/EMI)*100) AS t_per, (SUM(ifnull(rc.amt,0)/EMI)/SUM(case when sraid is not null then ifnull(OdDueAmt,0)/EMI ELSE 0 END)*100) as a_per
		, SUM(case when d.rgid = 1 then d.rgid else 0 end) as A1, SUM(case when d.rgid = 1 then rc.amt/EMI else 0 end) as B1
		, SUM(case when d.rgid = 2 then d.rgid else 0 end) as A2, SUM(case when d.rgid = 2 then rc.amt/EMI else 0 end) as B2
		, SUM(case when d.rgid = 3 then d.rgid else 0 end) as A3, SUM(case when d.rgid = 3 then rc.amt/EMI else 0 end) as B3
		, SUM(case when d.rgid = 4 then d.rgid else 0 end) as A4, SUM(case when d.rgid = 4 then rc.amt/EMI else 0 end) as B4
		, SUM(case when d.rgid = 5 then d.rgid else 0 end) as A5, SUM(case when d.rgid = 5 then rc.amt/EMI else 0 end) as B5
		, SUM(case when d.rgid > 5 then d.rgid else 0 end) as A6, SUM(case when d.rgid > 5 then rc.amt/EMI else 0 end) as B6
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d
		LEFT JOIN (SELECT dealid, Month(rcptdt) as rcm, IFNULL(SUM(rcptamt),0) AS amt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1 AND rd.dctyp in (101,102,111) GROUP BY r.dealid, month(r.rcptdt)) rc
		ON d.dealid = rc.dealid and d.mm = rc.rcm WHERE 1 :dd :hpdt :state group by mm ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 0, 'name' => 'Month'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Assigned'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Recovered'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'Total %', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'Assigned %', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B1','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B2'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B3','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B4'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B5','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => '','style'=>'background-color:#F6F6F6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B6'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => ''),
		),
	);

	if($_SESSION['is_admin_login']){
	//index == 32
	$c =0;$qi = 32;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Collections - Date Wise',
		'alternate' => array(array(32, 'Date Wise'), array(33, 'Month Wise')),
		'default_sort' => 'rcptdt',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'hpdt','centre', 'state', 'rcptpaytype'),
		'row_limit' => 100,
		'q' => "SELECT r.rcptdt,
		SUM(CASE WHEN rd.dctyp IN (101,102,111) THEN rd.rcptamt ELSE 0 END) as OD,
		SUM(CASE WHEN rd.dctyp = 103 THEN rd.rcptamt ELSE 0 END) as CB,
		SUM(CASE WHEN rd.dctyp = 104 THEN rd.rcptamt ELSE 0 END) as Penalty,
		SUM(CASE WHEN rd.dctyp IN(103,104) THEN rd.rcptamt ELSE 0 END) as CBP,
		SUM(CASE WHEN rd.dctyp IN(103,104) THEN rd.rcptamt ELSE 0 END)/sum(rd.rcptamt)*100 as per,
		SUM(CASE WHEN rd.dctyp = 105 THEN rd.rcptamt ELSE 0 END) as Seizing,
		SUM(CASE WHEN rd.dctyp = 107 THEN rd.rcptamt ELSE 0 END) as Other,
		sum(rd.rcptamt) as total
		FROM lksa.tbmdeal d JOIN ".$dbPrefix_curr.".tbxdealrcpt r ON d.dealid = r.dealid AND rcptpaymode in (1,2,6) AND r.cbflg = 0 AND r.cclflg = 0 :hpdt :centre :state :rcptpaytype $rcpt_clause
		JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd
		ON r.rcptid = rd.rcptid  GROUP BY r.rcptdt",
		'columns' => array(
			$c++ => array('align'=>0, 'sort'=>0, 'ops'=> 'df', 'link'=> 0, 'stotal' => 2, 'name' => 'Receipt Dt',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'OD'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bouncing'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'CBP', 'cummulative'=>1),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'CBP %', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Seizing'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Other'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total','cummulative'=>1),
		),
	);

	//index == 33
	$c =0;$qi = 33;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'Collections - Month Wise',
		'alternate' => array(array(32, 'Date Wise'), array(33, 'Month Wise')),
		'default_sort' => 'mm',
		'default_sort_type' => 'asc',
		'filters' => array('mm', 'hpdt','centre', 'state', 'rcptpaytype'),
		'row_limit' => 100,
		'q' => "SELECT month(r.rcptdt) as mm,
		SUM(CASE WHEN rd.dctyp IN (101,102,111) THEN rd.rcptamt ELSE 0 END) as OD,
		SUM(CASE WHEN rd.dctyp = 103 THEN rd.rcptamt ELSE 0 END) as CB,
		SUM(CASE WHEN rd.dctyp = 104 THEN rd.rcptamt ELSE 0 END) as Penalty,
		SUM(CASE WHEN rd.dctyp IN(103,104) THEN rd.rcptamt ELSE 0 END) as CBP,
		SUM(CASE WHEN rd.dctyp IN(103,104) THEN rd.rcptamt ELSE 0 END)/sum(rd.rcptamt)*100 as per,
		SUM(CASE WHEN rd.dctyp = 105 THEN rd.rcptamt ELSE 0 END) as Seizing,
		SUM(CASE WHEN rd.dctyp = 107 THEN rd.rcptamt ELSE 0 END) as Other,
		sum(rd.rcptamt) as total
		FROM lksa.tbmdeal d JOIN ".$dbPrefix_curr.".tbxdealrcpt r ON d.dealid = r.dealid AND rcptpaymode in (1,2,6) :hpdt :centre :state :rcptpaytype
		JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd
		ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 GROUP BY month(r.rcptdt)",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 2, 'name' => 'Month',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'OD'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Bouncing'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Penalty'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'CBP',),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'CBP %', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Seizing'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Other'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total','cummulative'=>1),
		),
	);

	}

	/**************************** Controller Starts Here **************************************/
	$c=0;
	if($index >= count($query)){
		echo "<center class='red'>Wrong selection! Please choose correct option from menu!</center>";
		return;
	}

	foreach($query[$index]['filters'] as $f){
		$var = $f."_in";
		$$var = isset($_REQUEST[$f]) ? $_REQUEST[$f] : 0;
	}

	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : (isset($query[$index]['row_limit']) ? $query[$index]['row_limit'] : $_SESSION['ROWS_IN_TABLE']);
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$sval = isset($_REQUEST['sval']) ? (empty($_REQUEST['sval']) ? $query[$index]['default_sort'] : $_REQUEST['sval'] ) : $query[$index]['default_sort'];
    $stype = isset($_REQUEST['stype']) ? (empty($_REQUEST['stype']) ? $query[$index]['default_sort_type'] : $_REQUEST['stype'] ) : $query[$index]['default_sort_type'];

	$from = ($limit * ($page - 1));
    $till = ($limit + $from);

	$q = $query[$index]['q'];

	foreach($query[$index]['filters'] as $f){
		$var = $f."_in";
		$q = str_replace (':'.$f ,$filters[$f]['options'][$$var]['query'], $q);
	}
	$q .=" order by $sval $stype limit $from, $limit;";

//	echo $index;
	print_a($q);
//	die();

	$name = array(); $align= array(); $sort = array(); $link = array(); $ops = array(); $stotal = array(); $style = array(); $suffix= array();$cummulative= array();$type= array();

	foreach($query[$index]['columns'] as $i => $attr){
		$name[$i] = $attr['name'];
		$align[$i] = $attr['align'];
		$sort[$i] = $attr['sort'];
		$link[$i] = $attr['link'];
		$ops[$i] = $attr['ops'];
		$stotal[$i] = $attr['stotal'];
		$style[$i] = isset($attr['style']) ? $attr['style'] : NULL;
		$suffix[$i] = isset($attr['suffix']) ? $attr['suffix'] : NULL;
		$cummulative[$i] = isset($attr['cummulative']) ? $attr['cummulative'] : NULL;
		$type[$i] = isset($attr['type']) ? $attr['type'] : NULL;
	}

	$totalRows =0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$data = $t1['r'];
		$totalRows = $t1['found_rows'];
	}
	/***************** Rendering Starts Here *********************/
    ?>
    <div id="element-box">
        <div class="m">
            <div id="blanket" style="display:none;"></div>
            <div id="popUpDiv" style="display:none;"></div>
            <form method="post" name="adminForm" onSubmit="return false;">
			<table><!---Showing Filters-->
				<tbody>
					<tr>
						<td align="left" width="100%" nowrap="nowrap">
							<h3><?=$query[$index]['title']?></h3>
						</td>
						<td nowrap="nowrap">
							<?if(isset($query[$index]['alternate'])){
								foreach($query[$index]['alternate'] as $f){?>
									<a class='alternate' href='?task=generic&index=<?=$f[0]?>'><?=$f[1]?></a> <span class='separator'>|</span>
								<?}
							}?>
							<?foreach($query[$index]['filters'] as $f){?>
							<b><?=$filters[$f]['title']?></b>
                            <select name="<?=$f?>" id="<?=$f?>" class="inputbox" size="1" onchange="refresh();">
                            <? $i=0;
                            	foreach ($filters[$f]['options'] as $key => $value){
                            		$var = $f."_in";
                            	?>
                            		<option value="<?=$key?>" <?if($$var==$key){?> selected="selected" <? }?>><?=$value['value']?></option>
								<? $i++;
								}?>
                            </select>
							<?}?>
          				</td>
                    </tr>
                </tbody>
            </table>
            <br>
			<?if($totalRows <= 0){?>
            	<table class="adminlist" cellspacing="1" width="100%" id="ls-content-box"><thead><tr><td>No results to show (<?=$page?>)</td></tr></thead></table>
            <?}else{?>
            	<style>
            		<?
					$coli= 2;
					foreach($align as $i=>$v){?>
						table.table-<?=$index?> > tbody > tr > td:nth-child(<?=$coli?>){
							text-align:<?=($align[$i] == -1 ? 'left;padding-left:3px !important;' : ($align[$i] == 1 ? 'right;padding-right:3px !important;' : 'center'))?>
						}
						<?
						$coli++;
						if(isset($cummulative[$i])){?>
						table.table-<?=$index?> > tbody > tr > td:nth-child(<?=$coli?>){
							text-align:<?=($align[$i] == -1 ? 'left;padding-left:3px !important;' : ($align[$i] == 1 ? 'right;padding-right:3px !important;' : 'center'))?>
						}
						<?
						$coli++;
						}
	            	}?>
            	</style>
				<?
				$total=array();
				foreach(array_keys($data[0]) as $i=> $key){
					$total[$key] =0;
					if(isset($cummulative[$i]) && $cummulative[$i] == 1){
						$total[$key.'_cum'] =0;
					}

				}
				$totalPages = ceil($totalRows/$limit);
    			$colspan = count($total) + 1;
				$slNo = ($from + 1);
				$itr = 1;
            	?>
				<!--Table Started --->
	            <table class="table-<?=$index?> adminlist" cellspacing="1" width="100%" id="ls-content-box">
	            <thead><!---Showing Column Heads-->
	                <tr>
	                	<th class="textright">#</th>
						<?foreach(array_keys($data[0]) as $i => $key){?>
							<?if(isset($sort[$i])){?>
								<th class="textleft"><a href="javascript:sort('<?=$key?>'); refresh();"><?=$name[$i]?></a></th>
							<?}else{?><?=$name[$i]?>
							<?}?>
							<?=(isset($cummulative[$i]) && $cummulative[$i] == 1 ? '<th class="textleft">Cummulative</th>' : '')?>
						<?}?>
					</tr>
				</thead>
				<tfoot><!---Showing Pagination-->
					<tr>
						<td colspan="<?=$colspan?>">
						<del class="container">
							<div class="pagination">
								<? $limitarray = array("5","10","15","20","25","30","50","100","200","500");?>
								<div class="limit">Display #
									<select name="limit" id="limit" class="inputbox" size="1" onchange="refresh();">
									<? for($i=0; $i<count($limitarray); $i++){?>
										<option value="<?=$limitarray[$i]?>" <? if($limit==$limitarray[$i]){?>selected="selected" <? }?>><?=$limitarray[$i]?></option>
									<?}?>
										<option value="1551615" <? if($limit==1551615){?>selected="selected" <? }?>>All</option>
									</select>
								</div>

								<? if($page<=1){ $classvalright="button2-right off"; }else{  $classvalright="button2-right"; }
								if($page>=$totalPages){ $class_left="button2-left off"; }else{  $class_left="button2-left"; } ?>
								<div class="<?=$classvalright?>"><div class="start"><? if($page<=1){?><span>Start</span><? }else{?><a href="#" title="First" onclick="javascript: ge('page').value=1; refresh(); return false;">Start</a><? }?></div></div>
								<div class="<?=$classvalright?>"><div class="prev"><? if($page<=1){?><span>Prev</span><? }else{?><a href="#" title="Previous" onclick="javascript: ge('page').value=<?=($page-1)?>; refresh();return false;">Prev</a><? }?></div></div>
								<div class="button2-left"><div class="page"><span><?=$page?></span></div></div>
								<div class="<?=$class_left?>"><div class="next"><? if($page>=$totalPages){?><span>Next</span><? }else{?><a href="#" title="Next" onclick="javascript: ge('page').value=<?=($page+1)?>; refresh();return false;">Next</a><? }?></div></div>
								<div class="<?=$class_left?>"><div class="end"><? if($page>=$totalPages){?><span>End</span><? }else{?><a href="#" title="Last" onclick="javascript: ge('page').value=<?=$totalPages?>; refresh(); return false;">End</a><? }?></div></div>
								<div class="limit">Page <?=$page?> of <?=$totalPages?> (Total:<?=$totalRows?>)</div>
							</div>
						</del>
						</td>
					</tr>
				</tfoot>
				<tbody><!---Showing Rows-->
				<?foreach ($data as $row){
					$i=0;
				?><tr><td class="textright"><?=$slNo++?></td><?
						foreach($row as $k => $v){?>
							<td <?=(isset($style[$i]) ? 'style="'.$style[$i].'"' : '')?>>
							<?if(is_null($type[$i])){?>
								<?=($link[$i] == 0 ? (is_null($ops[$i]) ? $v : $ops[$i]($v)) : "<a target='_blank' href='?task=deal&dealno=".$row['dealno']."'>".(is_null($ops[$i]) ? $v : $ops[$i]($v))."</a>")?><?=(is_null($suffix[$i]) ? '' : ' '.$suffix[$i])?>
							<?}else if($type[$i] =='URL'){?>
								<a target='_blank' href="<?=$row['url']?>"><?=$row['url']?></a>
							<?}?>
							</td>
							<?$total[$k] += $v;
							if(isset($cummulative[$i]) && $cummulative[$i] == 1){?><td><?=nf($total[$k])?></td><?}
							$i++;
						}?>
					</tr>
				<?}
					$i=0;
				?>
				</tbody>
				<tfoot><!---Showing total-->
				<tr>
					<th> </th>
					<?foreach($row as $k => $v){?>
						<th <?=($stotal[$i]==2 ? "class='textleft'" : ($stotal[$i] == 1 ? "class='textright'" : ''))?>><?=($stotal[$i] == 2 ? 'Total' : ($stotal[$i]==1 ? (is_null($ops[$i]) ? $total[$k] : $ops[$i]($total[$k])).''.(is_null($suffix[$i]) ? '' : ' '.$suffix[$i]) : ''))?></th>
						<?=(isset($cummulative[$i]) && $cummulative[$i] == 1 ? '<th></th>' : '')?>
						<?$i++;
					}?>
				</tr>
				</tfoot>
				</table>
			<?}?>
			<input name="page" id="page" value="<?=$page?>" type="hidden">
			<input name="sval" id="sval" value="<?=$sval?>" type="hidden">
			<input name="stype" id="stype" value="<?=$stype?>" type="hidden">
			<input name="index" id="index" value="<?=$index?>" type="hidden">
			</form>
		</div>
	</div>
	<?return ;
}?>