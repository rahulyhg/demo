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
	$filters ['nac'] = array('title' => '','hover' => 'NACH Approved?','options' => array(
		$c++ => array('value' => "--NACH--", 'query' => " "),
		$c++ => array('value' => "Approved", 'query' => " AND n.ApprvFlg = 1 "),
		$c++ => array('value' => "Rejected", 'query' => " AND n.ApprvFlg = 2 "),
		$c++ => array('value' => "Pending", 'query' => " AND n.ApprvFlg = 0 "),
		$c++ => array('value' => "Not Applied", 'query' => " AND n.ApprvFlg is NULL ")
		)
	);
	$c =0;
	$filters ['ecs'] = array('title' => '','hover' => 'ECS Approved?','options' => array(
		$c++ => array('value' => "--ECS--", 'query' => " "),
		$c++ => array('value' => "Approved", 'query' => " AND  e.ApprvFlg = 1 "),
		$c++ => array('value' => "Rejected", 'query' => " AND  e.ApprvFlg = 2 "),
		$c++ => array('value' => "Pending", 'query' => " AND  e.ApprvFlg = 0 "),
		$c++ => array('value' => "Not Applied", 'query' => " AND  e.ApprvFlg is NULL "),
		)
	);
	$c =0;
	$filters ['pdc'] = array('title' => '','hover' => 'PDC Available?','options' => array(
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
	$filters ['bucket'] = array('title' => '','hover' => 'Bucket','options' => array(
		$c++ => array('value' => "-Bucket-", 'query' => " "),
		$c++ => array('value' => "0", 'query' => " AND (f.rgid is null Or f.rgid = 0 ) "),
		$c++ => array('value' => "1", 'query' => " AND f.rgid = 1 "),
		$c++ => array('value' => "2", 'query' => " AND f.rgid = 2 "),
		$c++ => array('value' => "3", 'query' => " AND f.rgid = 3 "),
		$c++ => array('value' => "4", 'query' => " AND f.rgid = 4 "),
		$c++ => array('value' => "5", 'query' => " AND f.rgid = 5 "),
		$c++ => array('value' => "5+", 'query' => " AND f.rgid >= 5 "),
		)
	);
	$c =0;
	$filters ['centre'] = array('title' => '','hover' => 'Centre','options' => array(
		$c++ => array('value' => "-Centre-", 'query' => " "),
		)
	);
	foreach($centres as $cen){
		$filters ['centre']['options'][$c++] = array('value' => $cen['centre'], 'query' => " AND d.centre = '".$cen['centre']."' ");
	}

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
/********************************Filters****************************************/

/********************************Query****************************************/
	$query = array(); $qi=0;

	$c = 0;
	$query[$qi++] = array(
		'title'=> 'Pay Instrument Report',
		'default_sort' => 'd.dealid',
		'default_sort_type' => $DEFAULT_SORT_TYPE,
		'filters' => array('hpdt', 'bucket', 'centre','duedt', 'nac','nacind', 'ecs', 'ecsind', 'pdc', 'pdcind'),

		'q' => "SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) AS dealnm, DATE_FORMAT(d.startduedt, '%d-%b-%y') AS startduedt, tcase(d.centre) AS Centre,
			f.rgid,
			CASE n.ApprvFlg WHEN 0 THEN 'Pending' WHEN 1 THEN 'Approved' WHEN 2 THEN 'Rejected' ELSE NULL END AS NAC, TRIM(CONCAT(DATE_FORMAT(n.ApproveRejectDt, '%d-%b-%y'),' ', NACRemark)) AS NACApproved, nind.NACDpstInd,
			CASE e.ApprvFlg WHEN 0 THEN 'Pending' WHEN 1 THEN 'Approved' WHEN 2 THEN 'Rejected' ELSE NULL END AS ECS, DATE_FORMAT(e.ApproveRejectDt, '%d-%b-%y') AS ECSApproved, eind.ECSDpstInd,
			pdc.PendingPDC, pdcind.PDCDpstInd

			FROM lksa.tbmdeal d
			LEFT JOIN lksa201516.tbxfieldrcvry f on d.dealid = f.dealid and f.mm=7
			LEFT JOIN (SELECT d.dealid, SUM(CASE WHEN p.PDCDt < DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND p.PDCDpstInd = 'N' THEN 1
				WHEN p.PDCDt > DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) THEN 1 ELSE 0 END) AS PendingPDC FROM lksa.tbmdeal d JOIN lksa.tbmdealpdc p ON d.dealid = p.dealid AND d.dealsts = 1 GROUP BY p.dealid) AS pdc
				ON d.dealid = pdc.dealid
			LEFT JOIN lksa.tbmdealecs e ON e.dealid = d.dealid
			LEFT JOIN lksa.tbmdealnac n ON n.dealid = d.dealid


			LEFT JOIN (SELECT e.dealid, ECSDt, ECSDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealecsdtl e ON e.dealid = d.dealid AND d.dealsts = 1 WHERE ECSDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') eind ON eind.dealid = d.dealid
			LEFT JOIN (SELECT n.dealid, NACDt, NACDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealnacdtl n ON n.dealid = d.dealid AND d.dealsts = 1 WHERE NACDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') nind ON nind.dealid = d.dealid
			LEFT JOIN (SELECT p.dealid, PDCDt, PDCDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealpdc    p ON p.dealid = d.dealid AND d.dealsts = 1 WHERE PDCDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') pdcind ON pdcind.dealid = d.dealid
			WHERE d.dealsts = 1 AND d.startduedt  <= ' ".date('Y-m-t')."'",

			/*
			WHERE ECSDt BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND CURRENT_DATE
			WHERE NACDt BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND CURRENT_DATE
			WHERE p.PDCDt BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND CURRENT_DATE

			" e.ApprvFlg = 1 AND eind.ECSDpstInd ='N'",
			" e.ApprvFlg = 1 AND eind.ECSDpstInd ='N' and (pdcind.PDCDpstInd is null or pdcind.PDCDpstInd != 'Y')",
			" n.ApprvFlg = 1 AND nind.NACDpstInd ='N'",
			" n.ApprvFlg = 1 AND nind.NACDpstInd ='N' and (pdcind.PDCDpstInd is null or pdcind.PDCDpstInd != 'Y')",
			" (e.ApprvFlg is null or e.ApprvFlg != 1) AND (n.ApprvFlg is null or n.ApprvFlg != 1) AND (PendingPDC = 0 or PendingPDC is null) ",
			" (e.ApprvFlg is null or e.ApprvFlg != 1) AND (n.ApprvFlg is null or n.ApprvFlg != 1) AND PendingPDC = 1 AND (pdcind.PDCDpstInd is null or pdcind.PDCDpstInd = 'N')",
			" (e.ApprvFlg is null or e.ApprvFlg != 1) AND (n.ApprvFlg is null or n.ApprvFlg != 1) AND PendingPDC > 1 AND (pdcind.PDCDpstInd is null or pdcind.PDCDpstInd = 'N')",
			*/
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


	$c = 0;
	$q = "SELECT tcase(t1.centre) as centre, COUNT(dealid) AS due,";
	for($b = 1; $b <=$BUCKET_SIZE; $b++){
		$q .=" SUM(a$b) AS a$b, SUM(r$b) AS r$b, SUM(b$b) AS b$b, ";
	}
	$q .= "
	SUM(assigned_fd) AS assigned_fd, SUM(recovered_fd) AS recovered_fd, SUM(assigned_dm) AS assigned_dm, SUM(recovered_dm) AS recovered_dm, SUM(recovered) AS recovered
	FROM (
		SELECT d.inserttimestamp, d.dealid, d.OdDueAmt, d.dd, t.dealid AS rdid, t.rcptamt, d.OdDueAmt - t.rcptamt AS balance,
		CASE WHEN d.dd = 1 THEN 1 ELSE 0 END AS assigned_fd, CASE WHEN d.dd = 1 AND t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered_fd,
		CASE WHEN d.dd != 1 THEN 1 ELSE 0 END AS assigned_dm, CASE WHEN d.dd != 1 AND t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered_dm, ";

		//Bucket 1 to previous last bucket
		for($b = 1; $b < $BUCKET_SIZE; $b++){
			$q .= " CASE WHEN d.rgid = $b THEN 1 ELSE 0 END AS a$b ,"; // Assigned
			$q .= " CASE WHEN t.dealid IS Not NULL AND d.rgid = $b THEN 1 ELSE 0 END AS r$b ,"; // Recovered
			$q .= " CASE WHEN t.dealid IS NULL AND d.rgid = $b THEN 1 ELSE 0 END AS b$b ,"; // Balance
		}
		//Last bucket
		$q .= " CASE WHEN d.rgid >= $b THEN 1 ELSE 0 END AS a$b ,"; // Assigned
		$q .= " CASE WHEN t.dealid IS Not NULL AND d.rgid >= $b THEN 1 ELSE 0 END AS r$b ,"; // Recovered
		$q .= "	CASE WHEN t.dealid IS NULL AND d.rgid >= $b THEN 1 ELSE 0 END AS b$b, "; // Balance
		$q .= " CASE WHEN t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered, ";

		$q .= " d.sraid, tcase(d.centre) as centre
		FROM ".$dbPrefix_curr.".tbxfieldrcvry d
		LEFT JOIN (
			SELECT Month(r.rcptdt) as mm, r.dealid, SUM(rd.rcptamt) AS rcptamt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid
				WHERE r.cclflg = 0 AND r.CBflg = 0 AND (rd.dctyp = 101 OR rd.dctyp = 111) and r.rcptpaymode = 1
				GROUP BY r.dealid, month(r.rcptdt)
		) AS t ON d.dealid = t.dealid and d.mm = t.mm
	) t1 GROUP BY t1.centre having 1 ";

	$query[$qi++] = array(
		'title'=> 'Recovery History',
		'default_sort' => 't1.mm',
		'default_sort_type' => 'ASC',
		'filters' => array('hpdt', 'centre'),
		'q' => $q,
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












	$c =0;
	$query[$qi++] = array(
		'title'=> 'Pay Instrument Summary',
		'default_sort' => 'd.fy',
		'default_sort_type' => $DEFAULT_SORT_TYPE,
		'filters' => array(),
		'q' => "SELECT sql_calc_found_rows d.dealno, tcase(d.dealnm) AS dealnm, DATE_FORMAT(d.startduedt, '%d-%b-%y') AS startduedt, tcase(d.centre) AS Centre,
			f.rgid,
			CASE n.ApprvFlg WHEN 0 THEN 'Pending' WHEN 1 THEN 'Approved' WHEN 2 THEN 'Rejected' ELSE NULL END AS NAC, TRIM(CONCAT(DATE_FORMAT(n.ApproveRejectDt, '%d-%b-%y'),' ', NACRemark)) AS NACApproved, nind.NACDpstInd,
			CASE e.ApprvFlg WHEN 0 THEN 'Pending' WHEN 1 THEN 'Approved' WHEN 2 THEN 'Rejected' ELSE NULL END AS ECS, DATE_FORMAT(e.ApproveRejectDt, '%d-%b-%y') AS ECSApproved, eind.ECSDpstInd,
			pdc.PendingPDC, pdcind.PDCDpstInd

			FROM lksa.tbmdeal d
			LEFT JOIN lksa201516.tbxfieldrcvry f on d.dealid = f.dealid and f.mm=7
			LEFT JOIN (SELECT d.dealid, SUM(CASE WHEN p.PDCDt < DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND p.PDCDpstInd = 'N' THEN 1
				WHEN p.PDCDt > DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) THEN 1 ELSE 0 END) AS PendingPDC FROM lksa.tbmdeal d JOIN lksa.tbmdealpdc p ON d.dealid = p.dealid AND d.dealsts = 1 GROUP BY p.dealid) AS pdc
				ON d.dealid = pdc.dealid
			LEFT JOIN lksa.tbmdealecs e ON e.dealid = d.dealid
			LEFT JOIN lksa.tbmdealnac n ON n.dealid = d.dealid


			LEFT JOIN (SELECT e.dealid, ECSDt, ECSDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealecsdtl e ON e.dealid = d.dealid AND d.dealsts = 1 WHERE ECSDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') eind ON eind.dealid = d.dealid
			LEFT JOIN (SELECT n.dealid, NACDt, NACDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealnacdtl n ON n.dealid = d.dealid AND d.dealsts = 1 WHERE NACDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') nind ON nind.dealid = d.dealid
			LEFT JOIN (SELECT p.dealid, PDCDt, PDCDpstInd FROM lksa.tbmdeal d JOIN lksa.tbmdealpdc    p ON p.dealid = d.dealid AND d.dealsts = 1 WHERE PDCDt BETWEEN '".date('Y-m-01')."' AND '".date('Y-m-t')."') pdcind ON pdcind.dealid = d.dealid
			WHERE d.dealsts = 1 AND d.startduedt  <= ' ".date('Y-m-t')."'",

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


	$c=0;
	$query[$qi++] = array(
		'id' => 1,
		'active' => 1,
		'title'=> 'Deals',
		'default_sort' => 'd.hpdt',
		'default_sort_type' => $DEFAULT_SORT_TYPE,
		'filters' => array('hpdt'),
		'link' => array(1, NULL, 1, NULL, NULL, NULL,NULL, NULL, NULL),
		'q' => "select sql_calc_found_rows dealno, case dealsts when 1 then 'Active' when 2 then 'Draft' when 3 then 'Closed' end as Status, tcase(dealnm) as Name, DATE_FORMAT(hpdt, '%d-%b-%y') as HPdt, tcase(d.city) as City, tcase(d.centre) as Centre, tcase(s.salesmannm) as salesmannm, round(FinanceAmt) as Finance, tcase(trim(CONCAT(v.make, ' ', v.model, ' ', v.modelyy))) as Model from ".$dbPrefix.".tbmdeal d join ".$dbPrefix.".tbmdealvehicle v on d.dealid = v.dealid JOIN ".$dbPrefix.".tbmsalesman s JOIN ".$dbPrefix.".tbadealsalesman a ON d.dealid = a.dealid AND a.salesmanid = s.salesmanid where d.dealsts != 2 ",
		'columns' => array(
			$c++ => array('align'=> 1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 0, 'name' => 'Deal No',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Status',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 1, 'stotal' => 2, 'name' => 'Customer Name',),
			$c++ => array('align'=> 0, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'HP Date',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'City',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Centre',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Salesman',),
			$c++ => array('align'=> 1, 'sort'=>1, 'ops'=> 'nf', 'link'=> 0, 'stotal' => 1, 'name' => 'Finace Amount',),
			$c++ => array('align'=>-1, 'sort'=>1, 'ops'=> NULL, 'link'=> 0, 'stotal' => 0, 'name' => 'Vehicle Model',),
		),
	);
	$c=0;
/*	$query[$qi++] = array(
		'id' => 1,
		'active' => 0,
	);
*/
/********************************Query****************************************/

	$index = isset($_REQUEST['index']) ? $_REQUEST['index'] : 1;

	if($index >= count($query))
		die('Wrong selection! Please choose correct option from menu!');

	foreach($query[$index]['filters'] as $f){
		$var = $f."_in";
		$$var = isset($_REQUEST[$f]) ? $_REQUEST[$f] : 0;
	}

	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $_SESSION['ROWS_IN_TABLE'];
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$sval = isset($_REQUEST['sval']) ? (empty($_REQUEST['sval']) ? $query[$index]['default_sort'] : $_REQUEST['sval'] ) : $query[$index]['default_sort'];
    $stype = isset($_REQUEST['stype']) ? (empty($_REQUEST['stype']) ? $query[$index]['default_sort_type'] : $_REQUEST['stype'] ) : $query[$index]['default_sort_type'];

	$from = ($limit * ($page - 1));
    $till = ($limit + $from);

	$q = $query[$index]['q'];

	foreach($query[$index]['filters'] as $f){
		$var = $f."_in";
		$q .= $filters[$f]['options'][$$var]['query'];
	}
	$q .=" order by $sval $stype limit $from, $limit;";

	print_a($q);

	die();

	$name = array(); $align= array(); $sort = array(); $link = array(); $ops = array(); $stotal = array();

	foreach($query[$index]['columns'] as $i => $attr){
		$name[$i] = $attr['name'];
		$align[$i] = $attr['align'];
		$sort[$i] = $attr['sort'];
		$link[$i] = $attr['link'];
		$ops[$i] = $attr['ops'];
		$stotal[$i] = $attr['stotal'];
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
										<option value="18446744073709551615" <? if($limit==18446744073709551615){?>selected="selected" <? }?>>All</option>
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
							<td><?=($link[$i] == 0 ? (is_null($ops[$i]) ? $v : $ops[$i]($v)) : "<a target='_blank' href='?task=deal&dealno=".$row['dealno']."'>".(is_null($ops[$i]) ? $v : $ops[$i]($v))."</a>")?></td>
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
						<th <?=($stotal[$i]==2 ? "class='textleft'" : ($stotal[$i] == 1 ? "class='textright'" : ''))?>><?=($stotal[$i] == 2 ? 'Total (Shown Rows Only)' : ($stotal[$i]==1 ? $ops[$i]($total[$k]) : ''))?></th>
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