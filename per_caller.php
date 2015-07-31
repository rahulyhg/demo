<?
if(!isset($task) || $task!="per_caller"){
    echo "Invalid Access in OD Report";
    die();
}
else per_caller();

function per_caller(){
    $dbPrefix = $_SESSION['DB_PREFIX'];
    $user_dbPrefix = $_SESSION['USER_DB_PREFIX'];
//	print_a($_REQUEST);
    $DEFAULT_SORT  = 'dd';$DEFAULT_SORT_TYPE = 'desc';
    $BUCKET_SIZE = 6;
	$MIN_RECEIPT_AMT = 450;

	$fy = ""; $last_fy = "";
	$default_sort_value  = ' dd desc, dealid asc ';

	if(date('n') < 4){ //lastyear-thisyear
		$fy = date('y',  strtotime('-1 year'))."-".date('y');
		$last_fy = date('y',  strtotime('-2 year'))."-".date('y',  strtotime('-1 year'));
	}
	else {//thisyear-nextyear
		$fy = date('y')."-".date('y',  strtotime('+1 year'));
		$last_fy = date('y',  strtotime('-1 year'))."-".date('y');
	}
	$currMonth = date('m');
	$currYear = date('Y');

	$lastMonth = $currMonth - 1;
	$lastYear = $currYear;
	if($lastMonth == 0){
		$lastMonth = 12;
		$lastYear = $currYear - 1;
	}
	$fd = date('Y-M-01');

	/**** Inputs **********/
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;
	$ason = isset($_REQUEST['ason']) ? $_REQUEST['ason'] : 0;
    $hpdt = isset($_REQUEST['hpdt']) ? $_REQUEST['hpdt'] : 0;
	$callerid = isset($_REQUEST['callerid']) ? $_REQUEST['callerid'] : "";
	$rc_sraid = isset($_REQUEST['rc_sraid']) ? $_REQUEST['rc_sraid'] : "";
    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
	$bucket = isset($_REQUEST['bucket']) ? $_REQUEST['bucket'] : -1;
	$dd = isset($_REQUEST['dd']) ? $_REQUEST['dd'] : -1;
	$compare = isset($_REQUEST['compare']) ? $_REQUEST['compare'] : 1;
	$expired = isset($_REQUEST['expired']) ? $_REQUEST['expired'] : 0;
	$callertag = isset($_REQUEST['callertag']) ? $_REQUEST['callertag'] : 0;
	$sratag = isset($_REQUEST['sratag']) ? $_REQUEST['sratag'] : 0;
	/**** Inputs **********/

	/****** Pagination & Sorting *************/
	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $_SESSION['ROWS_IN_TABLE'];
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$sval = isset($_REQUEST['sval']) ? (empty($_REQUEST['sval']) ? $DEFAULT_SORT : $_REQUEST['sval'] ) : $DEFAULT_SORT;
    $stype = isset($_REQUEST['stype']) ? (empty($_REQUEST['stype']) ? $DEFAULT_SORT_TYPE : $_REQUEST['stype'] ) : $DEFAULT_SORT_TYPE;

	$from = ($limit * ($page - 1));
    $till = ($limit + $from);
	/****** Pagination & Sorting *************/

    //Get all centers
    $q1 = "SELECT tcase(centrenm) as centre from ".$dbPrefix.".tbmcentre order by centre";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$centres = $t1['r'];
	}

	//Get all SRAs1
	$q1 = "SELECT brkrid as sraid, brkrnm as sranm, active from ".$dbPrefix.".tbmbroker where active != 3 and brkrtyp = 2 order by active desc, brkrnm asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$sra = $t1['r'];
	}

	//Get all Callers
	$q1 = "SELECT userid as callerid, realname as callernm, active from ob_sa.tbmuser where active != 3 and recoveryagent = 1 order by active desc, realname asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$caller = $t1['r'];
	}

	//Get all Caller tags
	$q1 = "SELECT tagid, description from ".$dbPrefix.".tbmrecoverytags where active = 2 and (allowtagto = 0 or allowtagto = 1) order by active asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$callertags = $t1['r'];
	}

	//Get all SRA tags
	$q1 = "SELECT tagid, description from ".$dbPrefix.".tbmrecoverytags where active = 2 and (allowtagto = 0 or allowtagto = 2) order by active asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$sratags = $t1['r'];
	}

   	$ason_options = array(
   		array(date('Y-M',strtotime('-0 month', strtotime($fd))), date('m',strtotime('-0 month', strtotime($fd))), date('Y', strtotime('-0 month', strtotime($fd)))),
   		array(date('Y-M',strtotime('-1 month', strtotime($fd))), date('m',strtotime('-1 month', strtotime($fd))), date('Y', strtotime('-1 month', strtotime($fd)))),
   		array(date('Y-M',strtotime('-2 month', strtotime($fd))), date('m',strtotime('-2 month', strtotime($fd))), date('Y', strtotime('-2 month', strtotime($fd)))),
		array(date('Y-M',strtotime('-3 month', strtotime($fd))), date('m',strtotime('-3 month', strtotime($fd))), date('Y', strtotime('-3 month', strtotime($fd)))),
/*
   		array(date('Y-M',strtotime('-4 month', strtotime($fd))), date('m',strtotime('-4 month', strtotime($fd))), date('Y', strtotime('-4 month', strtotime($fd)))),
   		array(date('Y-M',strtotime('-5 month', strtotime($fd))), date('m',strtotime('-5 month', strtotime($fd))), date('Y', strtotime('-5 month', strtotime($fd)))),
   		array(date('Y-M',strtotime('-6 month', strtotime($fd))), date('m',strtotime('-6 month', strtotime($fd))), date('Y', strtotime('-6 month', strtotime($fd))))
*/
   	);
	$mm = $ason_options[$ason][1]; $yy = $ason_options[$ason][2];
	$dbPrefix_curr = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));

	$hp_options = array(array("- HP Date -",""));
	$hp_options[] = array("Fresh Bouncing", " AND d.hpdt > '2014-03-31' ");
	$hp_options[] = array("Old", " AND d.hpdt < '2014-04-01'");

	for($z=1; $z<=6; $z++){
   		$hp_options []  = array(date('Y-M',strtotime("-$z month", strtotime($fd))), " AND d.hpdt between '".date('Y-m-01', strtotime("-$z month", strtotime($fd)))."' AND '".date('Y-m-t', strtotime("-$z month", strtotime($fd)))."' ");
   	}

  	$hp_options []  = array("Year: ".date('Y'), " AND year(d.hpdt)=".$currYear."");
	for($i = date('Y'); $i >= 2008; $i--){
		$fy = substr($i,-2)."-".substr($i+1,-2);
		$hp_options[] = array("FY $fy", " AND  d.fy = '$fy' ");
	}

    //Build Query depending on various filter criterions
	switch($type){
		case 0: //Center Wise
		case 1: //Executive Wise
			$q = "SELECT t1.callerid, t1.callernm, t1.calleractive, ".($type == 1 ? " tcase(t1.centre) as centre, " : '')."
			COUNT(dealid) AS due, ";

			for($b = 1; $b <=$BUCKET_SIZE; $b++){
				$q .="SUM(a$b) AS a$b, SUM(r$b) AS r$b, SUM(b$b) AS b$b, ";
			}
			$q .= "
			SUM(assigned_fd) AS assigned_fd,
			SUM(recovered_fd) AS recovered_fd,
			SUM(assigned_dm) AS assigned_dm,
			SUM(recovered_dm) AS recovered_dm,
			SUM(recovered) AS recovered
			FROM (
			SELECT d.inserttimestamp, d.dealid, tcase(d.centre) as centre, d.OdDueAmt, d.dd, t.dealid AS rdid, t.rcptamt, d.OdDueAmt - t.rcptamt AS balance,
			CASE WHEN d.dd = 1 THEN 1 ELSE 0 END AS assigned_fd,
			CASE WHEN d.dd = 1 AND t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered_fd,
			CASE WHEN d.dd != 1 THEN 1 ELSE 0 END AS assigned_dm,
			CASE WHEN d.dd != 1 AND t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered_dm,";

			for($b = 1; $b < $BUCKET_SIZE; $b++){
				$q .= " CASE WHEN d.rgid = $b THEN 1 ELSE 0 END AS a$b ,"; // Assigned
				$q .= " CASE WHEN t.dealid IS Not NULL AND d.rgid = $b THEN 1 ELSE 0 END AS r$b ,"; // Recovered
				$q .= " CASE WHEN t.dealid IS NULL AND d.rgid = $b THEN 1 ELSE 0 END AS b$b ,"; // Balance
			}
			$q .= " CASE WHEN d.rgid >= $b THEN 1 ELSE 0 END AS a$b ,"; // Assigned
			$q .= " CASE WHEN t.dealid IS Not NULL AND d.rgid >= $b THEN 1 ELSE 0 END AS r$b ,"; // Recovered
			$q .= "	CASE WHEN t.dealid IS NULL AND d.rgid >= $b THEN 1 ELSE 0 END AS b$b, "; // Balance

			$q .= " CASE WHEN t.dealid IS NOT NULL THEN 1 ELSE 0 END AS recovered ,d.callerid, b.realname as callernm, b.active as calleractive
			FROM ".$dbPrefix_curr.".tbxfieldrcvry d
			LEFT JOIN ob_sa.tbmuser b on d.callerid = b.userid and b.recoveryagent = 1
			LEFT JOIN (
				SELECT r.dealid, SUM(rd.rcptamt) AS rcptamt FROM ".$dbPrefix_curr.".tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid
					WHERE r.cclflg = 0 AND r.CBflg = 0 AND (rd.dctyp = 101 OR rd.dctyp = 111) and r.rcptpaymode = 1
					AND r.rcptdt between '$yy-$mm-01' and '".date('Y-m-t',strtotime(date("$yy-$mm-01")))."'
					GROUP BY r.dealid having rcptamt >= ".$MIN_RECEIPT_AMT."
			) AS t ON d.dealid = t.dealid WHERE d.mm = $mm ".$hp_options[$hpdt][1];

			switch($expired){
				case 0://both
					break;
				case 1://Expired
					$q .= " and d.hpexpdt <'".date("$yy-$mm-01")."' ";
					break;
				case 2://Active
					$q .= " and d.hpexpdt >='".date("$yy-$mm-01")."' ";
				break;
			}

			switch($callerid){
				case "": //Assinged & Unassigned Cases
					break;
				case 0: //Unassigned Cases
					$q .= " and d.callerid is null ";
					break;
				case  -1://All Assigned Cases
					$q .= " and d.callerid > 0 ";
					break;
				default://Exact Match
					$q .= " and d.callerid = '$callerid' ";
					break;
			}

			if($centre != "")
				$q .= " AND centre = '$centre'";
			$q .= ") t1 GROUP BY t1.callerid ".($type == 1 ? ", t1.centre  " : '')."";



			$q .=" order by t1.callernm ".($type == 1  ? ", t1.centre " : '')."";
			break;

		case 2: //Deal Wise
			$q = " SELECT  SQL_CALC_FOUND_ROWS d.dealid, d.dealid, d.dealno, d.dealnm, d.hpdt, tcase(d.centre) as centre, d.emi ,d.callerid, b.realname as callernm, b.brkrnm as sranm, d.rgid, d.oddueamt, d.catid, d.dd, t.rc_sraid, t.rc_sranm, t.rcptamt,
			d.recstatus_sra, d.rectagid_sra, d.rectagid_caller, st.description as tag_sra, ct.description as tag_caller
			FROM ".$dbPrefix_curr.".tbxfieldrcvry d
			LEFT JOIN ".$user_dbPrefix.".tbmuser b ON d.callerid = b.userid AND b.recoveryagent = 1
			LEFT JOIN ".$dbPrefix.".tbmbroker b ON d.sraid = b.brkrid AND b.brkrtyp = 2
			LEFT JOIN ".$dbPrefix.".tbmrecoverytags st ON d.rectagid_sra = st.tagid
			LEFT JOIN ".$dbPrefix.".tbmrecoverytags ct ON d.rectagid_caller = ct.tagid
			LEFT JOIN(
				SELECT t1.dealid,t1.sraid, t1.rcptamt, b.brkrid AS rc_sraid, b.brkrnm AS rc_sranm
				FROM
					(SELECT r.dealid, r.sraid, SUM(rd.rcptamt) AS rcptamt
						FROM lksa201516.tbxdealrcpt r JOIN ".$dbPrefix_curr.".tbxdealrcptdtl rd
						ON r.rcptid = rd.rcptid
						WHERE r.cclflg = 0 AND r.CBflg = 0 AND (rd.dctyp = 101 OR rd.dctyp = 111) AND r.rcptpaymode = 1
						AND r.rcptdt BETWEEN '$yy-$mm-01' AND '".date('Y-m-t',strtotime(date("$yy-$mm-01")))."'
						GROUP BY r.dealid HAVING rcptamt >= ".$MIN_RECEIPT_AMT."
					) AS t1 JOIN ".$dbPrefix.".tbmbroker b
					ON t1.sraid = b.brkrid AND b.brkrtyp = 2
			) AS t ON d.dealid = t.dealid WHERE d.mm = $mm ".$hp_options[$hpdt][1]." ";

			if($centre != "")
				$q .= " AND d.centre = '$centre' ";

			if($callertag != 0)
				$q .= " AND rectagid_caller = '$callertag' ";

			if($sratag != 0)
				$q .= " AND rectagid_sra = '$sratag' ";

			switch($expired){
				case 0://both
					break;
				case 1://Expired
					$q .= " and d.hpexpdt <'".date("$yy-$mm-01")."' ";
					break;
				case 2://Active
					$q .= " and d.hpexpdt >='".date("$yy-$mm-01")."' ";
				break;
			}

			if($bucket != -1){
				if($bucket == 6)
					$q .= " AND d.rgid >= '$bucket' ";
				else
					$q .= " AND d.rgid = '$bucket' ";
			}

			switch($rc_sraid){
				case "": //Recovered & Pending Cases
					break;
				case 0: //Pending Cases
					$q .= " and t.rc_sraid is null ";
					break;
				case  -1://All Recovered Cases
					$q .= " and t.rc_sraid > 0 ";
					break;
				default://Exact Match
					$q .= " and t.rc_sraid = '$rc_sraid' ";
					break;
			}

		    switch($callerid){
		    	case "": //Assinged & Unassigned Cases
		    		break;
		    	case 0: //Unassigned Cases
		    		$q .= " and d.callerid is null ";
		    		break;
		    	case  -1://All Assigned Cases
		    		$q .= " and d.callerid > 0 ";
		    		break;
		    	default://Exact Match
		    		$q .= " and d.callerid = '$callerid' ";
		    		break;
			}

			switch($dd){
				case 0: //Both
					$q .= "";
					break;
				case 1://first day
					$q .= " and d.dd = 1 ";
					break;
				case 2://during
					$q .= " and d.dd > 1 ";
					break;
			}

			$q .= " order by $sval $stype, d.rgid desc limit $from, $limit";

			break;
	}

//	print_a($q);
//	die();

	$totalRows =0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$deals = $t1['r'];
		$totalRows = $t1['found_rows'];
	}
	$cols1 = 2;

    ?>
    <div id="element-box">
        <div class="m">
            <div id="blanket" style="display:none;"></div>
            <div id="popUpDiv" style="display:none;"></div>
            <form method="post" name="adminForm" onSubmit="return false;">
            <table width="100%" id='filterbar'>
                <tbody>
                    <tr>
						<td align="left" width="20%" nowrap="nowrap">
                            <select name="type" id="type" class="inputbox" size="1" onchange="call_per_caller();">
                            	<option value=0 <? if($type ==0){?> selected="selected" <? }?>>Agent Wise</option>
                            	<option value=1 <? if($type ==1){?> selected="selected" <? }?>>Agent - Centre Wise</option>
                            	<option value=2 <? if($type ==2){?> selected="selected" <? }?>>Deal Wise</option>
                            </select>
						</td>
                        <td width="75%" style='text-align:right'>
                            <b>For:</b> <select name="ason" id="ason" class="inputbox" size="1" onchange="call_per_caller();">
                            <?
                            	$i=0;
                            	foreach ($ason_options as $p){?>
                            		<option value="<?=$i?>" <?if($ason==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
                            </select>

							<select name="hpdt" id="hpdt" class="inputbox" size="1" onchange="call_per_caller();">
							<?
								$i=0;
								foreach ($hp_options as $p){?>
									<option value="<?=$i?>" <?if($hpdt==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
							</select>

                            <select name="centre" id="centre" class="inputbox" size="1" onchange="call_per_caller();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres -</option>
                         	<? // Populate Dropdown with values of Centre field
                         	foreach($centres as $c1){?>
                         		<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
                         	<?}?>
                            </select>

                            <select name="dd" id="dd" class="inputbox" size="1" onchange="call_per_caller();" <?=($type != 2 ? 'style="display:none"': '')?>>
                            	<option value="0" <? if($dd == 0){?> selected="selected" <? }?>>-Assigned On-</option>
                           		<option value="1" <? if($dd == 1){?> selected="selected" <? }?>>First Day</option>
                            	<option value="2" <? if($dd == 2){?> selected="selected" <? }?>>During Month</option>
                            </select>

                            <select name="bucket" id="bucket" class="inputbox" size="1" onchange="call_per_caller();" <?=($type != 2 ? 'style="display:none"': '')?>>
                            	<option value="-1" <? if($bucket == -1){?> selected="selected" <? }?>>Bucket</option>
                            	<?for($b=1;$b < $BUCKET_SIZE;$b++){?>
                            		<option value="<?=$b?>" <? if($bucket == $b){?> selected="selected" <? }?>>BKT <?=$b?></option>
                            	<?}?>
                            	<option value="<?=$b?>" <? if($bucket == $b){?> selected="selected" <? }?>>BKT <?=$b?>++</option>
                            </select>

							<select name="compare" id="compare" class="inputbox" size="1" onchange="call_per_caller();" <?=($type == 2 ? 'style="display:none"': '')?>>
								<option value="0" <? if($compare == 0){?> selected="selected" <? }?>>Balance</option>
								<option value="1" <? if($compare == 1){?> selected="selected" <? }?>>Compare</option>
							</select>

							<select name="expired" id="expired" class="inputbox" size="1" onchange="call_per_caller();">
								<option value="0" <? if($expired == 0){?> selected="selected" <? }?>>Both</option>
								<option value="1" <? if($expired == 1){?> selected="selected" <? }?>>Expired</option>
								<option value="2" <? if($expired == 2){?> selected="selected" <? }?>>Active</option>
							</select>

                            <select name="callerid" id="callerid" class="inputbox" size="1" onchange="call_per_caller();">
								<optgroup label='- RECOVERY AGENT -'>
								<option value="" <? if($callerid ==""){?> selected="selected" <? }?>>All Assigned & Unassigned</option>
								<option value="0" <? if($callerid =="0"){?> selected="selected" <? }?>>All Unassigned Cases</option>
								<option value="-1" <? if($callerid =="-1"){?> selected="selected" <? }?>>All Assigned Cases</option>
								</optgroup>
								<optgroup label='ACTIVE AGENTS'>
								<? // Populate Dropdown with values of caller field
								$found =0;
								foreach($caller as $c1){
									if($c1['active']!=2 && $found == 0){?>
										</optgroup><optgroup label='INACTIVE AGENTS'>
										<?$found = 1;
									}?>
									<option value="<?=$c1['callerid']?>" <? if($callerid==$c1['callerid']){?> selected="selected" <? }?>><?=titleCase($c1['callernm'])?> <?=($c1['active']==2 ? '' : ' (DC)')?></option>
								<?}?>
								</optgroup>
                            </select>

							<select name="rc_sraid" id="rc_sraid" class="inputbox" size="1" onchange="call_per_caller();" <?=($type != 2 ? 'style="display:none"': '')?>>
								<option value="" <? if($rc_sraid ==""){?> selected="selected" <? }?>>All Recovered & Pending</option>
								<option value="0" <? if($rc_sraid =="0"){?> selected="selected" <? }?>>All Pending Cases</option>
								<option value="-1" <? if($rc_sraid =="-1"){?> selected="selected" <? }?>>All Recovered Cases</option>
							</select>
                            <select name="callertag" id="callertag" class="inputbox" size="1" onchange="call_per_caller();" <?=($type != 2 ? 'style="display:none"': '')?>>
								<option value="0" <? if($sratag =="0"){?> selected="selected" <?}?>>- Caller TAG -</option>
                         			<?foreach($callertags as $tag){?>
										<option value="<?=$tag['tagid']?>" <?=($callertag==$tag['tagid'] ? 'selected="selected"' : '')?>><?=$tag['description']?></option>
									<?}?>
	    	                     	<option value="-1" <?=($callertag==-1 ? 'selected="selected"' : '')?>>Other</option>
                            </select>

                            <select name="sratag" id="sratag" class="inputbox" size="1" onchange="call_per_caller();" <?=($type != 2 ? 'style="display:none"': '')?>>
								<option value="0" <? if($sratag =="0"){?> selected="selected" <?}?>>- SRA TAG -</option>
                         			<?foreach($sratags as $tag){?>
		                         		<option value="<?=$tag['tagid']?>" <?=($sratag==$tag['tagid'] ? 'selected="selected"' : '')?>><?=$tag['description']?></option>
	    	                     	<?}?>
	    	                     	<option value="-1" <?=($sratag==-1 ? 'selected="selected"' : '')?>>Other</option>
                            </select>

                        </td>
                    </tr>
                </tbody>
            </table>

<!--Table Started --->
            <table class="adminlist" cellspacing="1" width="100%" id="ls-content-box">
            <thead>
                <tr>
                   	<?switch($type){
                   		case 0:
                   		case 1:?>
						<tr>
							<th></th>
							<th></th>
							<?if($type == 1){?><th></th><?}?>
							<th colspan="<?=$cols1?>">OPENING</th>
							<th colspan="<?=$cols1?>">NEW</th>
							<th colspan="3">Total Cases</th>
							<th>Pending</th>
							<th colspan="<?=($compare == 1 ? 12 : 6)?>">Buckets</th>
						</tr>
						<tr>
							<th>SN</th>
							<th class='textleft'>Calling Agent</th>
							<?if($type == 1){?><th class='textleft'>Centre</th><?}?>
							<th class='fd'>Default</th><th class='fd'>Recovered</th>
							<th class='dm'>Default</th><th class='dm'>Recovered</th>
							<th class='total'>Default</th><th class='total'>Recovered</th><th class='total'>%</th>
							<th></th>
								<?for($b=1;$b < $BUCKET_SIZE;$b++){?>
									<th class='b<?=$b?>' colspan="<?=($compare == 1 ? 2 : 1)?>">B-<?=$b?></th>
								<?}?>
							<th class='b<?=$b?>' colspan="<?=($compare == 1 ? 2 : 1)?>">B-<?=$b?>++</th>
						</tr>
						<?break;
					case 2:?>
					<tr>
						<th class="textleft">SN</th>
						<th class="textleft"><a href="javascript:sort('dd'); call_per_caller();">As On</a></th>
						<th class="textleft"><a href="javascript:sort('dealno'); call_per_caller();">Deal #</a></th>
						<th class="textleft"><a href="javascript:sort('dealnm'); call_per_caller();">Name</a></th>
						<th class="textleft date_column"><a href="javascript:sort('hpdt'); call_per_caller();">HP Date</a></th>
						<th class="textleft"><a href="javascript:sort('centre'); call_per_caller();">Centre</a></th>
						<th class="textleft"><a href="javascript:sort('callernm'); call_per_caller();">Caller</a></th>
						<th class="textleft"><a href="javascript:sort('sranm'); call_per_caller();">SRA</a></th>
						<th class="textleft"><a href="javascript:sort('rc_sranm'); call_per_caller();">Recovered By</a></th>
						<th class="textleft">EMI</th>
						<th class="textleft"><a href="javascript:sort('rgid'); call_per_caller();">Bucket</a></th>
						<th class="textleft"><a href="javascript:sort('oddueamt'); call_per_caller();">Due EMI</a></th>
						<th class="textleft"><a href="javascript:sort('rcptamt'); call_per_caller();">Received</a></th>
						<th class="textleft"><a href="javascript:sort('rectagid_caller'); call_per_caller();">Caller tag</a></th>
						<th class="textleft"><a href="javascript:sort('rectagid_sra'); call_per_caller();">SRA tag</a></th>
						<th class="textleft"></th>
					<?
					break;
					}//type = 2 ?>
            </thead>
			<? $colspan=18;?>
            <? if($totalRows>0){
                        $totalPages = ceil($totalRows/$limit);
                        $total=array("assigned_fd"=>0, "assigned_dm"=>0, "recovered_fd"=>0,"recovered_dm"=>0, "due"=>0, "recovered"=>0,"balance"=>0);
                        for($b=1; $b <=$BUCKET_SIZE; $b++){
							$total['a'.$b] = 0;$total['r'.$b] = 0;$total['b'.$b] = 0;
						}

                    if($type == 2){?>
                    <tfoot>
                        <tr>
                            <td colspan="<?=$colspan?>">
                            	<del class="container">
                            		<div class="pagination">
										<? $limitarray = array("5","10","15","20","25","30","50","100","200","500");?>
										<div class="limit">Display #<select name="limit" id="limit" class="inputbox" size="1" onchange="call_per_caller();">
										<? for($i=0; $i<count($limitarray); $i++){?>
										<option value="<?=$limitarray[$i]?>" <? if($limit==$limitarray[$i]){?>selected="selected" <? }?>><?=$limitarray[$i]?></option><? }?>
										<option value="18446744073709551615" <? if($limit==18446744073709551615){?>selected="selected" <? }?>>All</option></select></div>
										<? if($page<=1){ $classvalright="button2-right off"; }else{  $classvalright="button2-right"; }
										if($page>=$totalPages){ $class_left="button2-left off"; }else{  $class_left="button2-left"; } ?>
										<div class="<?=$classvalright?>"><div class="start"><? if($page<=1){?><span>Start</span><? }else{?><a href="#" title="First" onclick="javascript: ge('page').value=1; call_per_caller(); return false;">Start</a><? }?></div></div>
										<div class="<?=$classvalright?>"><div class="prev"><? if($page<=1){?><span>Prev</span><? }else{?><a href="#" title="Previous" onclick="javascript: ge('page').value=<?=($page-1)?>; call_per_caller();return false;">Prev</a><? }?></div></div>
										<div class="button2-left"><div class="page"><span><?=$page?></span></div></div>
										<div class="<?=$class_left?>"><div class="next"><? if($page>=$totalPages){?><span>Next</span><? }else{?><a href="#" title="Next" onclick="javascript: ge('page').value=<?=($page+1)?>; call_per_caller();return false;">Next</a><? }?></div></div>
										<div class="<?=$class_left?>"><div class="end"><? if($page>=$totalPages){?><span>End</span><? }else{?><a href="#" title="Last" onclick="javascript: ge('page').value=<?=$totalPages?>; call_per_caller(); return false;">End</a><? }?></div></div>
										<div class="limit">Page <?=$page?> of <?=$totalPages?> (Total:<?=$totalRows?>)</div>
                                	</div>
                                </del>
                            </td>
                        </tr>
                    </tfoot>
                    <?}//if type is 2 ?>
                    <tbody>
                        <?
                        	$slNo = ($from + 1);
							$itr = 1; $oldAgent =-1;
                            foreach ($deals as $deal){
								switch($type){
									case 0://Centre Wise
									case 1://Executive Wise
									?>
									<tr>
										<td class="textright"><?=$slNo++?></td>
										<!--Agent-->
										<td class='textleft'>
											<? if($oldAgent != $deal['callerid']){?>
												<a href="#" onclick="javascript:ge('type').value=1; ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>'; ge('centre').value=''; call_per_caller();  return false;">
												<?=(is_null($deal['callerid']) ? '<span class="red">Unassigned</span>' : '' )?>
												<?=($deal['calleractive'] != 2 ? '<span class="red">'.titleCase($deal['callernm']).'</span>' : titleCase($deal['callernm']))?>
											<?}
											$oldAgent = $deal['callerid'];
											?>
											</a>
										</td>
										<!--Centre-->
										<?if($type == 1){?>
										<td>
											<a href="#" onclick="javascript:ge('type').value=<?=($totalRows==1 ? 2 : 1)?>; ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>'; <?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?> call_per_caller(); return false;"><?=titleCase($deal['centre'])?></a>
										</td>
										<?}?>
										<!--First Day Default Deals-->
										<td class="textright fd">
											<a href="#" onclick="javascript:ge('type').value=2;
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('dd').value ='1';  call_per_caller(); return false;">
											<?=nf($deal['assigned_fd'],0)?>
											</a>
										</td>
										<!--First Day Recovered Deals-->
										<td class="textright fd">
											<a href="#" onclick="javascript:ge('type').value=2;
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('dd').value ='1';
											ge('rc_sraid').value = -1;
											call_per_caller(); return false;">
											<?=nf($deal['recovered_fd'],0)?></td>
											</a>
										<!--During the month default Deals-->
										<td class="textright dm">
											<a href="#" onclick="javascript:ge('type').value=2;
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('dd').value ='2';  call_per_caller(); return false;">
											<?=nf($deal['assigned_dm'],0)?>
											</a>
										</td>
										<!--During the month recovered Deals-->
										<td class="textright dm">
											<a href="#" onclick="javascript:ge('type').value=2;
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('dd').value ='2';
											ge('rc_sraid').value = -1;
											call_per_caller(); return false;">
											<?=nf($deal['recovered_dm'],0)?>
											</a>
										</td>

										<!--Total Default deals-->
										<td class="textright total">
											<a href="#" onclick="javascript:ge('type').value=2;
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											ge('dd').value ='0';  call_per_caller(); return false;">
											<?=nf($deal['due'],0)?>
											</a>
										</td>

										<!--Total recovered deals-->
										<td class="textright total">
											<a href="#" onclick="javascript:ge('type').value=2;
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('dd').value ='0';
											ge('rc_sraid').value = -1;
											call_per_caller(); return false;">
											<?=nf($deal['recovered'],0)?>
											</a>
										</td>
										<td class="textright total">
											<?=($deal['due'] == 0 ? '-' : nf($deal['recovered']*100/$deal['due'],0).' %')?>
										</td>


										<!--Pending Deals-->
										<th class="textright">
											<a href="#" onclick="javascript:ge('type').value=2;
											ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>';
											ge('dd').value ='0';
											<?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?>
											ge('rc_sraid').value = 0;
											call_per_caller(); return false;">
											<?=nf($deal['due']-$deal['recovered'],0)?>
											</a>
										</th>
										<?
										for($b=1;$b<=$BUCKET_SIZE;$b++){?>
											<!--Pending Deals with Bucket = <?=$b?>-->
											<? if($compare == 1){?>
											<td class="textright b<?=$b?>">
												<a href="#" onclick="javascript:ge('type').value=2; ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>'; <?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?> ge('dd').value ='0'; ge('rc_sraid').value = ''; ge('bucket').value = <?=$b?>; call_per_caller(); return false;">
													<?=nf($deal["a$b"],0)?>
												</a>
											</td>
											<td class="textright b<?=$b?>">
												<a href="#" onclick="javascript:ge('type').value=2; ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>'; <?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?> ge('dd').value ='0'; ge('rc_sraid').value = -1; ge('bucket').value = <?=$b?>; call_per_caller(); return false;">
													<?=nf($deal["r$b"],0)?>
												</a>
											</td>
											<?}else{?>
											<td class="textright b<?=$b?>">
												<a href="#" onclick="javascript:ge('type').value=2; ge('callerid').value='<?=($deal['callerid']==null ? 0 : $deal['callerid'])?>'; <?if($type == 1){?>ge('centre').value='<?=$deal['centre']?>';<?}?> ge('dd').value ='0'; ge('rc_sraid').value = 0; ge('bucket').value = <?=$b?>; call_per_caller(); return false;">
													<?=nf($deal["b$b"],0)?>
												</a>
											</td>
											<?}?>
										<?}?>
									</tr>
									<?
									$total['assigned_fd']+= $deal['assigned_fd'];
									$total['assigned_dm']+= $deal['assigned_dm'];
									$total['recovered_fd']+= $deal['recovered_fd'];
									$total['recovered_dm']+= $deal['recovered_dm'];
									$total['due']+= $deal['due'];
									$total['recovered']+= $deal['recovered'];
									for($b=1; $b <=$BUCKET_SIZE; $b++){
										$total['a'.$b] += $deal['a'.$b];
										$total['r'.$b] += $deal['r'.$b];
										$total['b'.$b] += $deal['b'.$b];
									}
									break;

									case 2://Dealwise
										$color = '';
										if($deal['catid'] == 12) // Insurance Case
											$color = 'insurance';
										else if ($deal['catid'] == 13) // Police Station
											$color = 'police-station';
										else if ($deal['catid'] == 24) // Write Off Cases
											$color = 'write-off';
										else if($deal['dd'] == 1) //First Day of the month
											$color = 'fd';
										else // Other dates
											$color = 'dm';
									?>
									<tr class='<?=$color?>'>
										<td class="textright"><?=$slNo++?></td>
										<td class="textright"><?=$deal['dd']?>-<?=date('M',strtotime(date("d-$mm-Y")))?></td>
										<td class="textright"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=$deal['dealno']?></a></td>
										<td class="textleft"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=titleCase($deal['dealnm'])?></a></td>
										<td class="textright"><?=date('d-M-Y',strtotime($deal['hpdt']))?></td>
										<td class="textleft"><?=titleCase($deal['centre'])?></td>
										<td class="textleft"><?=titleCase($deal['callernm'])?></td>
										<td class="textleft"><?=titleCase($deal['sranm'])?></td>
										<td class="textleft"><?=titleCase($deal['rc_sranm'])?></td>
										<td class="textright"><?=nf($deal['emi'],0)?></td>
										<td class="textright"><?=$deal['rgid']?></td>
										<td class="textright"><?=nf($deal['oddueamt'],0)?></td>
										<td class="textright <?=($deal['oddueamt'] - $deal['rcptamt'] < 5 ? 'green' : 'red')?>"><?=nf($deal['rcptamt'],0)?></td>
										<td class="textleft"><?=$deal['tag_caller']?></td>
										<td class="textleft"><?=$deal['tag_sra']?></td>
										<td class="textleft">
										<?if($_SESSION['user_id'] == $deal['callerid']){?>
											<a href="javascript:callAddComment(<?=$deal['dealid']?>);"><img src="/images/comments.png" width="14"/></a>
										<?}?>
									</tr>
	                            	<?
									$total['due']+=$deal['oddueamt']; $total['recovered']+=$deal['rcptamt'];
									break;
								}//Switch
                            } //for loop
                        if($totalRows==0){?>
                            <tr>
                                <td colspan="<?=$colspan?>" align="center">
                                    No Records found!
                                </td>
                            </tr>
						<?}
                     	else{ // Footer Row - Show Sum of each column
                     		switch($type){
								case 0:
								case 1:?>
								<tr>
									<th class='textright'>&nbsp;</th>
									<th class='textleft'>Total (Shown Rows Only)</th>
									<?if($type==1){?><th class='textright'>&nbsp;</th><?}?>
									<th class="textright">
										<a href="#" onclick="javascript:ge('type').value=2;
										ge('dd').value ='1';
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										call_per_caller(); return false;">
										<?=nf($total['assigned_fd'])?>
										</a>
									</th>
									<th class='textright'>
										<a href="#" onclick="javascript:ge('type').value=2;
										ge('dd').value ='1'; ge('rc_sraid').value = -1;
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										call_per_caller(); return false;">
										<?=nf($total['recovered_fd'])?>
										</a>
									</th>
									<th class='textright'>
										<a href="#" onclick="javascript:ge('type').value=2;
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										ge('dd').value ='2';
										call_per_caller(); return false;">
										<?=nf($total['assigned_dm'])?>
										</a>
									</th>
									<th class='textright'>
										<a href="#" onclick="javascript:ge('type').value=2; ge('dd').value ='2'; ge('rc_sraid').value = -1;
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										call_per_caller(); return false;">
										<?=nf($total['recovered_dm'])?>
										</a>
									</th>
									<th class='textright'>
										<a href="#" onclick="javascript:ge('type').value=2; ge('dd').value ='0';
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										call_per_caller(); return false;">
										<?=nf($total['due'])?>
										</a>
									</th>
									<th class='textright'>
										<a href="#" onclick="javascript:ge('type').value=2;ge('dd').value ='0'; ge('rc_sraid').value = -1;
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										call_per_caller(); return false;">
										<?=nf($total['recovered'])?>
										</a>
									</th>
									<th class="textright">
										<?=($total['due'] == 0 ? '-' : nf($total['recovered']*100/$total['due'],0).' %')?>
									</th>

									<th class="textright">
										<a href="#" onclick="javascript:ge('type').value=2; ge('dd').value ='0'; ge('rc_sraid').value = 0;
										ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
										call_per_caller(); return false;">
										<?=nf($total['due']-$total['recovered'],0)?>
										</a>
									</th>
									<?
									for($b=1; $b <=$BUCKET_SIZE; $b++){?>
										<?if($compare == 1){?>
											<th class="textright">
												<a href="#" onclick="javascript:ge('type').value=2; ge('dd').value ='0';
												ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
												ge('rc_sraid').value = ''; ge('bucket').value = <?=$b?>; call_per_caller(); return false;">
												<?=nf($total['a'.$b],0)?>
												</a>
											</th>
											<th class="textright">
												<a href="#" onclick="javascript:ge('type').value=2; ge('dd').value ='0';
												ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
												ge('rc_sraid').value = -1; ge('bucket').value = <?=$b?>; call_per_caller(); return false;">
												<?=nf($total['r'.$b],0)?>
												</a>
											</th>
										<?}else{?>
											<th class="textright">
												<a href="#" onclick="javascript:ge('type').value=2; ge('dd').value ='0';
												ge('callerid').value='<?=($type == 1 ? $deal['callerid'] : '')?>';
												ge('rc_sraid').value = 0; ge('bucket').value = <?=$b?>; call_per_caller(); return false;">
												<?=nf($total['b'.$b],0)?>
												</a>
											</th>
										<?}?>
									<?}?>
								</tr>
								<tr>
									<td class='textright'>&nbsp;</td>
									<td class='textright'>&nbsp;</td>
									<?if($type == 1){?><td class='textleft'>&nbsp;</td><?}?>
									<td class='textright fd'>&nbsp;</td>
									<td class='textright fd'><?=($total['assigned_fd'] == 0 ? '-' : nf($total['recovered_fd']*100/$total['assigned_fd']))?> %</td>
									<td class='textright dm'>&nbsp;</th>
									<td class='textright dm'><?=($total['assigned_dm'] == 0 ? '-' : nf($total['recovered_dm']*100/$total['assigned_dm']))?> %</td>
									<td class='textright total'>&nbsp;</td>
									<td class='textright total'><?=($total['due'] == 0 ? '-' : nf($total['recovered']*100/$total['due']))?> %</td>
									<td class='textright total'></td>
									<th class='textright'></th>
									<?for($b=1; $b <=$BUCKET_SIZE; $b++){?>
										<?if($compare == 1){?>
											<td colspan ="2" class='textright b<?=$b?>'><?=($total['a'.$b] == 0 ? '-' : nf($total['r'.$b]*100/$total['a'.$b]))?> %</td>
										<?}else{?>
											<td class='textright  b<?=$b?>'><?=($total['due']-$total['recovered'] == 0 ? '-' : nf($total['b'.$b]*100/($total['due']-$total['recovered'])))?> %</td>
										<?}?>
									<?}?>
								</tr>
									<?break;
								case 2:?>
								<tr>
									<th class='textright'>&nbsp;</th>
									<th class='textright'>&nbsp;</th>
									<th class='textleft'>&nbsp;</th>
									<th class='textright'>&nbsp;</th>
									<th class='textleft'>&nbsp;</th>
									<th class='textleft'>&nbsp;</th>
									<th colspan='2' class='textleft'>Total (Shown Rows Only)</th>
									<th></th>
									<th class='textright'>&nbsp;</th>
									<th class='textright'>&nbsp;</th>
									<th class='textright'><?=nf($total['due'])?></th>
									<th class="textright"><?=nf($total['recovered'])?></th>
									<th></th>
									<th></th>
									<th></th>
								</tr>

									<?break;
							}?>
                     	<?}?>
                        </tbody>
                    </table>
          <?} else{?>
                   <table class="adminlist" cellspacing="1" width="100%">
                       <tfoot>
                           <tr>
                               <td colspan="<?=$colspan?>">
                                   No Records found!
                               </td>
                           </tr>
                       </tfoot>
                   </table>
         <?}
//        print_a($q);
         ?>
			<input name="sval" id="sval" value="<?=$sval?>" type="hidden">
			<input name="page" id="page" value="<?=$page?>" type="hidden">
			<input name="stype" id="stype" value="<?=$stype?>" type="hidden">
            </form>

         <?if($type==2){?>
         	<table class="adminlist legend-box" cellspacing = "1" style="width:80% !important;margin-left:10%">
				<tr><th class="">Colour Coding</th>
         			<td class="insurance center">Insurance Case</td>
         			<td class="write-off center">Write Off Case</td>
         			<td class="police-station center">Vehicle in Police Station</td>
         			<td class="fd center">Assigned On First Day</td>
         			<td class="dm center">Assigned During the Month</td></tr>
         	</table>
         <?}?>
            <!--div class="legend"><b>Lengends</b><br><b> NA / - :</b> Not Applicable / Not Attempted</div><div class="legend"><b>In Progress</b> Not Submitted</div><div class="legend"><b>Number:</b> Click to see results</div-->
            <div class="clr"></div>
        </div>
    </div>
<?}?>