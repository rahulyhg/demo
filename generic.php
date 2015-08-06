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
	$dbPrefix_curr = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));
	$dbPrefix_last = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy-1,-2) : ($yy-1)."".(substr($yy-1,-2)+1));

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

/********************************Filters****************************************/
	$c = 0;
	$filters = array();

	$filters ['hpdt'] = array('title' => '','hover' => 'Hire Purchase Date of the vehicle','options' => NULL);
	$filters ['hpdt']['options'] = array(
		$c++ => array('value' => "FY: $fy", 'query' => " AND d.FY ='$fy' " ),
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
	for($i = date('Y')-1; $i >= 2008; $i--){
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
		$c++ => array('value' => "-Deal Centre-", 'query' => " "),
		)
	);
	foreach($centres as $cen){
		$filters ['centre']['options'][$c++] = array('value' => $cen['centre'], 'query' => " AND d.centre = '".$cen['centre']."' ");
	}

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
	$filters ['dealer'] = array('title' => '','hover' => 'Dealer','options' => array(
		$c++ => array('value' => "-Dealer-", 'query' => " "),
		)
	);
	foreach($dealer as $d){
		$filters ['dealer']['options'][$c++] = array('value' => $d['dealer'], 'query' => " AND brkrnm = '".$d['dealer']."' ");
	}

	$c =0;
	$filters ['bucket'] = array('title' => '','hover' => 'Bucket','options' => array(
		$c++ => array('value' => "-Bucket-", 'query' => " "),
		$c++ => array('value' => "0", 'query' => " AND (tbxfieldrcvry.rgid is null Or tbxfieldrcvry.rgid = 0 ) "),
		$c++ => array('value' => "1", 'query' => " AND tbxfieldrcvry.rgid = 1 "),
		$c++ => array('value' => "2", 'query' => " AND tbxfieldrcvry.rgid = 2 "),
		$c++ => array('value' => "3", 'query' => " AND tbxfieldrcvry.rgid = 3 "),
		$c++ => array('value' => "4", 'query' => " AND tbxfieldrcvry.rgid = 4 "),
		$c++ => array('value' => "5", 'query' => " AND tbxfieldrcvry.rgid = 5 "),
		$c++ => array('value' => "5+", 'query' => " AND tbxfieldrcvry.rgid >= 5 "),
		)
	);

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
		$c++ => array('value' => "-Disbused-", 'query' => " "),
		$c++ => array('value' => "Yes", 'query' => " AND d.bankduedt is not null"),
		$c++ => array('value' => "No", 'query' => " AND d.bankduedt is null"),
		)
	);

	$c =0; $yy = date('Y'); $mm =date('n');
	$filters ['mm'] = array('title' => '','hover' => 'Month','options' => array());
	for($i=0; $i<=4; $i++){
		$strm = strtotime("-$i month", strtotime($fd));
		$filters ['mm']['options'][$c++] = array('value' => date('Y-M',$strm), 'query' => ' AND mm = '. date('m',$strm).'' ,'query1' => " AND rcptdt between '".date('Y-m-d',$strm)."' and '".date('Y-m-t',$strm)."' ");
	}

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
			FROM lksa.tbmdeal d
			LEFT JOIN lksa201516.tbxfieldrcvry f on d.dealid = f.dealid and f.mm= ".date('n')."
			LEFT JOIN (SELECT d.dealid, SUM(CASE WHEN p.PDCDt < DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND p.PDCDpstInd = 'N' THEN 1
				WHEN p.PDCDt > DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) THEN 1 ELSE 0 END) AS PendingPDC FROM lksa.tbmdeal d JOIN lksa.tbmdealpdc p ON d.dealid = p.dealid AND d.dealsts = 1 GROUP BY p.dealid) AS pdc
				ON d.dealid = pdc.dealid
			LEFT JOIN lksa.tbmdealecs e ON e.dealid = d.dealid
			LEFT JOIN lksa.tbmdealnac n ON n.dealid = d.dealid
			LEFT JOIN (SELECT e.dealid, ECSDt, ECSDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealecsdtl e ON e.dealid = d.dealid AND d.dealsts = 1 WHERE ECSDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') eind ON eind.dealid = d.dealid
			LEFT JOIN (SELECT n.dealid, NACDt, NACDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealnacdtl n ON n.dealid = d.dealid AND d.dealsts = 1 WHERE NACDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') nind ON nind.dealid = d.dealid
			LEFT JOIN (SELECT p.dealid, PDCDt, PDCDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealpdc    p ON p.dealid = d.dealid AND d.dealsts = 1 WHERE PDCDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') pdcind ON pdcind.dealid = d.dealid
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
	$q = "SELECT t1.mm, SUM(assigned_fd) AS assigned_fd, SUM(recovered_fd) AS recovered_fd, SUM(assigned_dm) AS assigned_dm, SUM(recovered_dm) AS recovered_dm, COUNT(dealid) AS assigned,  SUM(recovered) AS recovered, round(SUM(recovered)/COUNT(dealid)*100) as per";
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
		) AS t ON d.dealid = t.dealid and d.mm = t.mm where 1 :hpdt :centre
	) t1 GROUP BY t1.mm having 1 ";

	$columns = array(
		$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> 'toMonthName', 'link'=> 0, 'stotal' => 0, 'name' => 'Month'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'OPENING','style'=>'background-color:#F5F5DC'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'O-REC','style'=>'background-color:#F5F5DC'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'NEW','style'=>'background-color:#FFE4C4'),
		$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => 'N-REC','style'=>'background-color:#FFE4C4'),
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
		'filters' => array('hpdt', 'centre'),
		'q' => $q,
		'columns' => $columns,
	);

	$qi = 2; $c =0;
	$query[$qi] = array(
		'title'=> 'Caller Tags',
		'default_sort' => 'rectagid_caller',
		'default_sort_type' => 'ASC',
		'filters' => array('mm', 'hpdt', 'centre', 'bucket'),
		'q' => "SELECT ct.description AS CALLER_TAG, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN lksa.tbmrecoverytags ct ON rectagid_caller = ct.tagid
		WHERE rec_flg = 0 :mm :hpdt :centre :bucket GROUP BY rectagid_caller",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Caller Tag'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Deals'),
		),
	);

	$qi = 3; $c =0;
	$query[$qi] = array(
		'title'=> 'Caller Tags',
		'default_sort' => 'rectagid_sra',
		'default_sort_type' => 'ASC',
		'filters' => array('mm', 'hpdt', 'centre', 'bucket'),
		'q' => "SELECT ct.description AS SRA_TAG, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN lksa.tbmrecoverytags ct ON rectagid_sra = ct.tagid
		WHERE rec_flg = 0 :mm :hpdt :centre :bucket GROUP BY rectagid_sra",
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
		'filters' => array('mm', 'hpdt', 'centre', 'bucket'),
		'q' => "SELECT u.realname, sum(case when rectagid_caller is null then 0 else 1 end) as entered, sum(case when rectagid_caller is null then 1 else 0 end) as notentered, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN ob_sa.tbmuser u ON tbxfieldrcvry.callerid = u.userid
		WHERE rec_flg = 0 :mm :hpdt :centre :bucket GROUP BY tbxfieldrcvry.callerid",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Caller Name'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Not Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total Pending'),
		),
	);

	$qi = 5; $c =0;
	$query[$qi] = array(
		'title'=> 'SRA Tag Entry',
		'default_sort' => 'brkrnm',
		'default_sort_type' => 'ASC',
		'filters' => array('mm', 'hpdt', 'centre', 'bucket'),
		'q' => "SELECT b.brkrnm, sum(case when rectagid_sra is null then 0 else 1 end) as entered, sum(case when rectagid_sra is null then 1 else 0 end) as notentered, COUNT(tbxfieldrcvry.dealid) AS deals FROM ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix_curr.".tbxfieldrcvry ON d.dealid = tbxfieldrcvry.dealid LEFT JOIN lksa.tbmbroker b ON tbxfieldrcvry.sraid = b.brkrid and b.brkrtyp = 2
		WHERE rec_flg = 0 :mm :hpdt :centre :bucket GROUP BY tbxfieldrcvry.sraid",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>0, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'SRA Name'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Not Entered'),
			$c++ => array('align'=>1, 'sort'=>0, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Total Pending'),
		),
	);

	//index == 6
	$c =0;$qi = 6;
	$query[$qi] = array(
		'title'=> 'Disbursement Report',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => 'desc',
		'filters' => array('hpdt', 'centre', 'dealer', 'salesman', 'disbursed'),
		'q' => "SELECT sql_calc_found_rows d.dealno, d.hpdt, d.dealrefno AS account, tcase(d.dealnm) as dealnm, DATE_ADD(d.bankduedt, INTERVAL -1 MONTH) AS disbursementdt, tcase(br.brkrnm) as brkrnm, CONCAT(v.make, ' ',v.model) AS Vehicle, tcase(d.centre) as centre, d.financeamt, f.disbursementamt, tcase(s.salesmannm) as salesmannm, b.banknm, b.bankbrnchnm
		FROM lksa.tbmdeal d
		JOIN lksa.tbadealsalesman a JOIN lksa.tbmsalesman s ON d.dealid = a.dealid AND a.salesmanid = s.salesmanid AND d.dealsts = 1 :hpdt :centre :salesman :disbursed
		LEFT JOIN lksa.tbmbroker br ON br.brkrid = d.brkrid
		LEFT JOIN lksa.tbadealfnncdtls f ON d.dealid = f.dealid
		LEFT JOIN lksa.tbmdealvehicle v ON d.dealid = v.dealid
		LEFT JOIN (SELECT bank.banknm, branch.bankbrnchnm, branch.bankbrnchid FROM lksa.tbmsourcebank bank JOIN lksa.tbmsourcebankbrnch branch ON bank.bankid = branch.bankid) AS b ON d.bankbrnchid = b.bankbrnchid where 1 :dealer ",

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
		'filters' => array('mm','hpdt', 'centre'),
		'row_limit' => 50,
		'q' => "SELECT r.rcptdt,
			SUM(CASE WHEN d.dd = 1 THEN 1 ELSE 0 END) AS fd,
			SUM(CASE WHEN d.dd = 1 THEN 1 ELSE 0 END)/(SELECT COUNT(dealid) FROM ".$dbPrefix_curr.".tbxfieldrcvry d WHERE dd = 1 :mm :hpdt :centre)*100 AS fd_per,
			SUM(CASE WHEN d.dd != 1 THEN 1 ELSE 0 END) AS dm,
			SUM(CASE WHEN d.dd != 1 THEN 1 ELSE 0 END)/(SELECT COUNT(dealid) FROM ".$dbPrefix_curr.".tbxfieldrcvry d WHERE dd != 1  :mm :hpdt :centre)*100 AS dm_per,
			COUNT(DISTINCT r.dealid) AS recovered,
			COUNT(DISTINCT r.dealid)/(SELECT COUNT(dealid) FROM ".$dbPrefix_curr.".tbxfieldrcvry WHERE 1 :mm :hpdt :centre)*100 AS tot_per
			, SUM(CASE WHEN d.rgid = 1 THEN 1 ELSE 0 END) AS B1
			, SUM(CASE WHEN d.rgid = 2 THEN 1 ELSE 0 END) AS B2
			, SUM(CASE WHEN d.rgid = 3 THEN 1 ELSE 0 END) AS B3
			, SUM(CASE WHEN d.rgid = 4 THEN 1 ELSE 0 END) AS B4
			, SUM(CASE WHEN d.rgid = 5 THEN 1 ELSE 0 END) AS B5
			, SUM(CASE WHEN d.rgid > 5 THEN 1 ELSE 0 END) AS B6
			FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxfieldrcvry d ON r.dealid = d.dealid :mm :hpdt :centre
			WHERE r.cbflg =0 AND r.cclflg =0 AND r.rcptpaymode = 1 $rcpt_clause GROUP BY r.rcptdt ",
		'columns' => array(
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'df', 'link'=> 0, 'stotal' => 2, 'name' => 'Date',),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Opening','style'=>'background-color:#F5F5DC'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '%','style'=>'background-color:#F5F5DC', 'suffix'=> '%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'New','style'=>'background-color:#FFE4C4'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '%','style'=>'background-color:#FFE4C4', 'suffix'=> '%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => 'Total','style'=>'background-color:#F5F5DC'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 1, 'name' => '%','style'=>'background-color:#F5F5DC', 'suffix'=> '%'),
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
		'title'=> 'Cash Collection Report',
		'default_sort' => 'centre asc, brkrnm',
		'default_sort_type' => 'asc',
		'filters' => array('mm','hpdt', 'reccentre'),
		'row_limit' => 100,
		'q' => "SELECT tcase(b.centre) as centre, tcase(ifnull(b.brkrnm,'Unassigned')) as brkrnm,
		sum(case when dd=1 then 1 else 0 end) as Assigned, sum(case when dd=1 and (rc.od is not null or d.rec_flg = 1) then 1 else 0 end) as Recovered, sum(case when dd=1		and (rc.od is not null or d.rec_flg = 1) then 1 else 0 end)/sum(case when dd=1 then 1 else 0 end)*100 as per,
		SUM(rc.od) AS total,
		SUM(CASE WHEN rgid=1 THEN rc.od ELSE 0 END) AS B1, SUM(CASE WHEN rgid=2 THEN rc.od ELSE 0 END) AS B2,
		SUM(CASE WHEN rgid=3 THEN rc.od ELSE 0 END) AS B3, SUM(CASE WHEN rgid=4 THEN rc.od ELSE 0 END) AS B4,
		SUM(CASE WHEN rgid=5 THEN rc.od ELSE 0 END) AS B5, SUM(CASE WHEN rgid >5 THEN rc.od ELSE 0 END) AS B6,

		SUM(CASE WHEN rgid=1 THEN rc.od ELSE 0 END)*0.02+SUM(CASE WHEN rgid=2 THEN rc.od ELSE 0 END)*0.04+SUM(CASE WHEN rgid=3 THEN rc.od ELSE 0 END)*0.06+
		SUM(CASE WHEN rgid=4 THEN rc.od ELSE 0 END)*0.08+SUM(CASE WHEN rgid=5 THEN rc.od ELSE 0 END)*0.08+SUM(CASE WHEN rgid >5 THEN rc.od ELSE 0 END)*0.1 as Comission
		, SUM(rc.cb) as cb, SUM(rc.penalty) as penalty,	SUM(rc.cb)*0.15 + SUM(rc.penalty)*0.1 as comission2

		FROM ".$dbPrefix_curr.".tbxfieldrcvry d LEFT JOIN ".$dbPrefix.".tbmbroker b ON d.sraid = b.brkrid AND b.brkrtyp = 2
		LEFT JOIN (
		SELECT dealid,
		IFNULL(SUM(CASE WHEN dctyp = 101 or dctyp = 102 or dctyp = 111 then ifnull(rcptamt,0) else 0 end),0) AS od,
		IFNULL(SUM(CASE WHEN dctyp = 103 then ifnull(rcptamt,0) else 0 end),0) AS cb,
		IFNULL(SUM(CASE WHEN dctyp = 104 then ifnull(rcptamt,0) else 0 end),0) AS penalty
		FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1
		WHERE 1 $rcpt_clause GROUP BY r.dealid) rc ON d.dealid = rc.dealid WHERE 1 :mm :hpdt :reccentre GROUP BY b.centre, sraid ",
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
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Comission', 'style'=>'font-weight:bold;'),
		),
	);

	//index == 9
	$c =0;$qi = 9;
	$mm = isset($_REQUEST['mm']) ? $_REQUEST['mm'] : 0;
	$rcpt_clause = $filters ['mm']['options'][$mm]['query1'];
	$query[$qi] = array(
		'title'=> 'EMI Recovery Report',
		'default_sort' => 'centre asc, brkrnm',
		'default_sort_type' => 'asc',
		'filters' => array('mm','hpdt', 'reccentre'),
		'row_limit' => 100,
		'q' => "SELECT tcase(b.centre) as centre, tcase(ifnull(b.brkrnm,'Uniassigned')) as brkrnm, SUM(ifnull(OdDueAmt,0)/EMI) AS Assigned, SUM(rc.amt/EMI) AS Recovered, (SUM(rc.amt/EMI)/SUM(OdDueAmt/EMI)*100) AS Percentage
		, case when d.rgid = 1 then SUM(rc.amt/EMI) else 0 end as B1
		, case when d.rgid = 2 then SUM(rc.amt/EMI) else 0 end as B2
		, case when d.rgid = 3 then SUM(rc.amt/EMI) else 0 end as B3
		, case when d.rgid = 4 then SUM(rc.amt/EMI) else 0 end as B4
		, case when d.rgid = 5 then SUM(rc.amt/EMI) else 0 end as B5
		, case when d.rgid > 5 then SUM(rc.amt/EMI) else 0 end as B6
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d LEFT JOIN ".$dbPrefix.".tbmbroker b ON d.sraid = b.brkrid AND b.brkrtyp = 2 :reccentre
		LEFT JOIN (SELECT dealid, IFNULL(SUM(rcptamt),0) AS amt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND r.cbflg = 0 AND r.cclflg = 0 AND r.rcptpaymode = 1 AND (rd.dctyp = 101 OR rd.dctyp = 102 OR rd.dctyp = 111) WHERE 1 $rcpt_clause GROUP BY r.dealid) rc
		ON d.dealid = rc.dealid WHERE 1 :mm :hpdt GROUP BY b.centre, sraid ",
		'columns' => array(
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre'),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 2, 'name' => 'SRA Name'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Assigned'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Recovered'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 0, 'name' => '%', 'suffix'=>'%'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B1'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B2'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B3'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B4'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B5'),
			$c++ => array('align'=>1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'B6'),
		),
	);

	//index == 10
	$c =0;$qi = 10;
	$query[$qi] = array(
		'title'=> 'Vehicle - Details Not Updated',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => 'asc',
		'filters' => array('hpdt', 'centre', 'dealer', 'salesman', 'disbursed'),
		'q' => "SELECT sql_calc_found_rows d.dealno, d.hpdt, tcase(d.dealnm) as dealnm,  tcase(d.centre) as centre, DATE_ADD(d.bankduedt, INTERVAL -1 MONTH) AS disbursementdt, tcase(br.brkrnm) as brkrnm,tcase(s.salesmannm) as salesmannm, CONCAT(v.make, ' ',v.model) AS Vehicle, v.engineno, v.chasis, v.rtoregno
		FROM lksa.tbmdeal d
		JOIN lksa.tbadealsalesman a JOIN lksa.tbmsalesman s ON d.dealid = a.dealid AND a.salesmanid = s.salesmanid AND d.dealsts = 1 :hpdt :centre :salesman :disbursed
		LEFT JOIN lksa.tbmbroker br ON br.brkrid = d.brkrid
		LEFT JOIN lksa.tbmdealvehicle v ON d.dealid = v.dealid
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
//	print_a($q);
//	die();

	$name = array(); $align= array(); $sort = array(); $link = array(); $ops = array(); $stotal = array(); $style = array(); $suffix= array();

	foreach($query[$index]['columns'] as $i => $attr){
		$name[$i] = $attr['name'];
		$align[$i] = $attr['align'];
		$sort[$i] = $attr['sort'];
		$link[$i] = $attr['link'];
		$ops[$i] = $attr['ops'];
		$stotal[$i] = $attr['stotal'];
		$style[$i] = isset($attr['style']) ? $attr['style'] : NULL;
		$suffix[$i] = isset($attr['suffix']) ? $attr['suffix'] : NULL;
	}

	$totalRows =0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$data = $t1['r'];
		$totalRows = $t1['found_rows'];
	}
    ?>
    <div id="element-box">
        <div class="m">
            <div id="blanket" style="display:none;"></div>
            <div id="popUpDiv" style="display:none;"></div>
            <form method="post" name="adminForm" onSubmit="return false;">
			<table>
				<tbody>
					<tr>
						<td align="left" width="100%" nowrap="nowrap">
							<h3><?=$query[$index]['title']?></h3>
						</td>
						<td nowrap="nowrap">
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
            		<?foreach($align as $i=>$v){?>table.table-<?=$index?> > tbody > tr > td:nth-child(<?=($i+2)?>){text-align:<?=($align[$i] == -1 ? 'left;padding-left:3px !important;' : ($align[$i] == 1 ? 'right;padding-right:3px !important;' : 'center'))?>}
	            	<?}?>
            	</style>
				<?
				$total=array();
				foreach(array_keys($data[0]) as $key){
					$total[$key] =0;
				}
				$totalPages = ceil($totalRows/$limit);
    			$colspan = count($total) + 1;
				$slNo = ($from + 1);
				$itr = 1;
            	?>
				<!--Table Started --->
	            <table class="table-<?=$index?> adminlist" cellspacing="1" width="100%" id="ls-content-box">
	            <thead>
	                <tr>
	                	<th class="textright">#</th>
						<?foreach(array_keys($data[0]) as $i => $key){?>
							<th class="textleft">
							<a href="javascript:sort('<?=$key?>'); refresh();"><?=$name[$i]?></a></th>
						<?}?>
					</tr>
				</thead>
				<tfoot>
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
				<tbody>
				<?foreach ($data as $row){
					$i=0;
				?><tr><td class="textright"><?=$slNo++?></td><?
						foreach($row as $k => $v){?>
							<td <?=(isset($style[$i]) ? 'style="'.$style[$i].'"' : '')?>><?=($link[$i] == 0 ? (is_null($ops[$i]) ? $v : $ops[$i]($v)) : "<a target='_blank' href='?task=deal&dealno=".$row['dealno']."'>".(is_null($ops[$i]) ? $v : $ops[$i]($v))."</a>")?><?=(is_null($suffix[$i]) ? '' : ' '.$suffix[$i])?></td>
							<?$i++;$total[$k] += $v;
						}?>
					</tr>
				<?}
					$i=0;
				?>
				</tbody>
				<tfoot>
				<tr>
					<th> </th>
					<?foreach($row as $k => $v){?>
						<th <?=($stotal[$i]==2 ? "class='textleft'" : ($stotal[$i] == 1 ? "class='textright'" : ''))?>><?=($stotal[$i] == 2 ? 'Total' : ($stotal[$i]==1 ? (is_null($ops[$i]) ? $total[$k] : $ops[$i]($total[$k])).''.(is_null($suffix[$i]) ? '' : ' '.$suffix[$i]) : ''))?></th>
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