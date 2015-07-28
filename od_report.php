<?
if(!isset($task) || $task!="od_report"){
    echo "Invalid Access in OD Report";
    die();
}
else od_report();

function od_report(){
    $dbPrefix = $_SESSION['DB_PREFIX'];
    $DEFAULT_SORT  = 'centre';$DEFAULT_SORT_TYPE = 'asc';
    $BUCKET_SIZE = 6;

    $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : "";
    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
    $salesmanid = isset($_REQUEST['salesmanid']) ? $_REQUEST['salesmanid'] : 0;
    $period = isset($_REQUEST['period']) ? $_REQUEST['period'] : 2;
	$od_from = isset($_REQUEST['od_from']) ? $_REQUEST['od_from'] : 0;
	$od_amt = isset($_REQUEST['od_amt']) ? $_REQUEST['od_amt'] : 500;
	$disburse = isset($_REQUEST['disburse']) ? $_REQUEST['disburse'] : "";
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;
	$bucket = isset($_REQUEST['bucket']) ? $_REQUEST['bucket'] : -1;

	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $_SESSION['ROWS_IN_TABLE'];
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$sval = isset($_REQUEST['sval']) ? (empty($_REQUEST['sval']) ? $DEFAULT_SORT : $_REQUEST['sval'] ) : $DEFAULT_SORT;
    $stype = isset($_REQUEST['stype']) ? (empty($_REQUEST['stype']) ? $DEFAULT_SORT_TYPE : $_REQUEST['stype'] ) : $DEFAULT_SORT_TYPE;

	$from = ($limit * ($page - 1));
    $till = ($limit + $from);
/*
	$update_query = array(
	//active = 1 (Inactive - Delete or not working - almost 0 deals)
	//active = 2 (Live deals)
	//active = 3 (Draft deals)
	//active = 4 (Dont consider for recovery)
	//for closed and cancelled deals
	//	"UPDATE tbmdeal SET active = 4 WHERE dealsts = 3 OR cancleflg = -1",
	//for insurance
		"UPDATE tbmdeal SET active = 4 WHERE dealid IN (SELECT a.dealid FROM `tbadealcatagory` AS a JOIN `tbmrcvrycatagory` AS b ON a.CatgId=b.pkid AND b.PkId=12)",
	//for seizing
		"UPDATE tbmdeal SET active = 4 WHERE dealid IN (SELECT dealid FROM tbmdealvehicle WHERE siezeflg = -1)",
	);
	$i=0;
	foreach ($update_query as $uq){
		$return = executeUpdate($uq);
	}
*/

	$q1 = "SELECT salesmanid, salesmannm, active from ".$dbPrefix.".tbmsalesman where active != 3 and Department = 'SALES' order by active desc, salesmannm asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$salesmans = $t1['r'];
	}

    //Get all centers
    $q1 = "SELECT tcase(centrenm) as centre from ".$dbPrefix.".tbmcentre order by centre";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$centres = $t1['r'];
	}

	$fd = date('Y-M-01');

	$c =0 ;
	$od_from_options = array();
	$od_from_options[$c++] = array("Right Now",date('Y-m-d'));//Now
	$od_from_options[$c++] = array(date('d-M-Y'), date('Y-m-d',strtotime('-1 day')));//Today
   	$od_from_options[$c++] = array(date('d-M-Y',strtotime('-1 day')), date('Y-m-d',strtotime('-2 day')));//Yesterday
	for($d=0; $d<=6; $d++){
		$od_from_options[$c++] = array(date('01-M-Y', strtotime("-$d month",strtotime($fd))), date('Y-m-t', strtotime('-'.($d+1).' months')) ); //First of last month and likewise
	}

	$c=0;
   	$period_options = array();
	for($d = 0; $d <=6; $d++){
		$strtime = strtotime("- $d month", strtotime($fd));
	   	$period_options[$c++] = array(date('Y-M', $strtime), " d.hpdt between '".date('Y-m-01', $strtime)."' AND '".date('Y-m-t', $strtime)."' ", date('Y-m-01', $strtime), date('Y-m-t', $strtime));
	}
	for($d = date('Y'); $d >= 2013; $d--){
	   	$period_options[$c++] = array("Year: $d"," d.hpdt between '$d-01-01' and '$d-12-31'", "$d-01-01", "$d-03-31");
	}
	for($d = date('Y'); $d >= 2008; $d--){
		$fy = substr($d,-2)."-".substr($d+1,-2);
		$period_options[$c++] = array("FY:$fy"," d.fy='$fy' ", "$d-04-01", ($d+1)."-03-31");

	}

	$hp_start_dt = $period_options[$period][2];
	$hp_end_dt = $period_options[$period][3];
   	$due_start_dt = $hp_start_dt;
   	$due_end_dt = $od_from_options[$od_from][1];
	//Receipt Start Date is same as Due Start Date And Receipt End Date is same as Due End Date
   	$rcpt_start_dt = $due_start_dt;
   	$rcpt_end_dt = $due_end_dt;

    //Get List of all valid deals for centre for selected date period
	$q = "";
	switch($type){
		case 0: //Center Wise
		case 1: //Executive Wise
			// If od_filter is set to "Show All" - Then consider all deals with OD amount != 0
			// If Od_filter is set to "OD >0" - Then consider all deals with OD > 0 and likewise
			//While calculating OD Amount, Always consider cases with OD > 0
			$q .= "SELECT tcase(p.centre) as centre, COUNT(p.dealid) AS cases, COUNT(p.bankduedt) AS disbursed, ";
			if($type == 1)
				$q .= " p.salesmanid, p.salesmannm, p.active, ";
	   		if($od_amt == -1 )
	   			$q .= " SUM(od < 0) AS od, sum(case when od < 0 then od else 0 end) as od_amt ";
	   		else
	   			$q .= " SUM(od > $od_amt) AS od, sum(case when od > $od_amt then od else 0 end) as od_amt, ";
	   		$q .="
	   		sum(case when bucket > 0 and bucket < 0.8 and od > $od_amt then 1 else 0 end) as B_0,
	   		sum(case when bucket >= 0.8 and bucket < 1.5 then 1 else 0 end) as B_1,
	   		sum(case when bucket >= 1.5 and bucket < 2.5 then 1 else 0 end) as B_2,
	   		sum(case when bucket >= 2.5 and bucket < 3.5 then 1 else 0 end) as B_3,
	   		sum(case when bucket >= 3.5 and bucket < 4.5 then 1 else 0 end) as B_4,
	   		sum(case when bucket >= 4.5 and bucket < 5.5  then 1 else 0 end) as B_5,
	   		sum(case when bucket >= 5.5 and bucket < 6.5 then 1 else 0 end) as B_6,
	   		sum(case when bucket >= 6.5 then 1 else 0 end) as B_7
			FROM
			( SELECT ";
			break;
		case 2: //Deal Wise
			$q .= "SELECT SQL_CALC_FOUND_ROWS *, ";
			break;
	}

	$q .= " l.dealid, l.dealno, l.dealnm, l.hpdt, l.cancleflg, l.dealsts, l.emi, l.ScheduledEMI, l.bankduedt, l.salesmanid, l.salesmannm, l.active, tcase(l.centre) as centre, rgt.received, l.emi - rgt.received AS od, ((l.emi - rgt.received)/l.ScheduledEMI) as bucket,
	(case when (l.emi - rgt.received)/l.ScheduledEMI < 0.8 then 0 else round((l.emi - rgt.received)/l.ScheduledEMI) end) as od_bucket
    FROM
    	(SELECT d.dealid, d.dealno, d.dealnm, DATE_FORMAT(d.hpdt,'%d %b %Y') AS hpdt, d.dealsts, d.cancleflg, d.bankduedt, s.salesmanid, s.salesmannm, s.active, tcase(s.centre) as centre, IFNULL(SUM(u.dueamt + u.CollectionChrgs),0) AS emi, sc.MthlyAmt + sc.CollectionChrgs as ScheduledEMI
    	FROM lksa.tbmdeal d JOIN lksa.tbadealsalesman sa JOIN lksa.tbmsalesman s JOIN lksa.tbmpmntschd sc
    	ON ".$period_options[$period][1]." AND d.dealsts = 1 and d.active = 2 AND sa.dealid = d.dealid AND sa.salesmanid = s.salesmanid AND sc.dealid = d.dealid ";

	   	if($centre != "")
	   		$q .= " AND s.centre = '$centre' ";

	   	if($disburse != "")
	   		if($disburse == 0)
	   			$q .= " AND  d.bankduedt is not null  ";
	   		else $q .= " AND d.bankduedt is null ";

	    if($salesmanid != 0)
	    	$q .= " AND s.salesmanid = '$salesmanid' ";

		$q .= "
		LEFT JOIN lksa.tbmduelist u
		ON d.dealid = u.dealid AND u.duedt between '$due_start_dt' and '$due_end_dt'
		GROUP BY d.dealid
		) as l JOIN
		(SELECT d.dealid, d.hpdt, d.dealsts, d.cancleflg, IFNULL(SUM(rcpt.rcptamt),0) AS received
		FROM lksa.tbmdeal d JOIN lksa.tbadealsalesman sa JOIN lksa.tbmsalesman s
		ON ".$period_options[$period][1]." AND d.dealsts = 1 and d.active = 2 AND sa.dealid = d.dealid AND sa.salesmanid = s.salesmanid ";

	   	if($centre != "")
	   		$q .= " AND s.centre = '$centre' ";

	   	if($disburse != "")
	   		if($disburse == 0)
	   			$q .= " AND  d.bankduedt is not null  "; // Disbursed
	   		else $q .= " AND d.bankduedt is null "; // Undisbursed

	    if($salesmanid != 0)
	    	$q .= " AND s.salesmanid = '$salesmanid' ";


		$q .= "
		LEFT JOIN (";

		for ($d =2008; $d <= date('Y'); $d++){
			$db = "lksa".$d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
			$q .="
			SELECT '".$d."', r.dealid, SUM(rd.rcptamt) AS rcptamt FROM ".$db.".tbxdealrcpt r JOIN ".$db.".tbxdealrcptdtl rd ON r.rcptid = rd.rcptid AND rd.dctyp IN (101,111) AND r.cclflg = 0 AND r.CBflg = 0 AND r.rcptdt between '$rcpt_start_dt' and '$rcpt_end_dt' GROUP BY r.dealid
			UNION";
		}
		$q = rtrim($q, "UNION");

		$q .= ") AS rcpt
			ON d.dealid = rcpt.dealid
			GROUP BY d.dealid";
	$q .= ") AS rgt
	ON l.dealid = rgt.dealid ";

	switch($type){
		case 0://Centrewise
			$q .=" ) AS p GROUP BY p.centre ORDER BY p.$sval $stype";
			break;
		case 1://Salesmanwise
			$q .=" ) AS p GROUP BY p.salesmanid ORDER BY p.centre asc, p.salesmannm";
			break;
		case 2://Dealwise
			$q .= " having ".($od_amt == -1 ? "od < 0" : "od > $od_amt");
			if($bucket !=-1){
				if($bucket == 0)
					$q .= " AND  od_bucket = 0 AND bucket < 0.8 ";
				else if($bucket == 7)
					$q .= " AND  od_bucket >= $bucket ";
				else
					$q .= " AND  od_bucket = $bucket ";
			}
			$q .=" order by $sval $stype limit $from, $limit";
			break;
	}
	print_a($q);

	//die();

	$totalRows =0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$deals = $t1['r'];
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
						<td align="left" width="100%">
                            <b>Type:</b> <select name="type" id="type" class="inputbox" size="1" onchange="callODReport();">
                            	<option value=0 <? if($type ==0){?> selected="selected" <? }?>>Centre Wise</option>
                            	<option value=1 <? if($type ==1){?> selected="selected" <? }?>>Executive Wise</option>
                            	<option value=2 <? if($type ==2){?> selected="selected" <? }?>>Deal Wise</option>
                            </select>
						</td>
                        <td nowrap="nowrap">
                            <b>HP Date:</b> <select name="period" id="period" class="inputbox" size="1" onchange="callODReport();">
                            <?
                            	$i=0;
                            	foreach ($period_options as $p){?>
                            		<option value="<?=$i?>" <?if($period==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
                            </select>

                            <b>AS ON:</b> <select name="od_from" id="od_from" class="inputbox" size="1" onchange="callODReport();">
                            <?
                            	$i=0;
                            	foreach ($od_from_options as $p){?>
                            		<option value="<?=$i?>" <?if($od_from==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
                            </select>


                            <select name="centre" id="centre" class="inputbox" size="1" onchange="callODReport();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres-</option>
                         	<? // Populate Dropdown with values of Centre field
                         	foreach($centres as $c1){?>
                         		<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
                         	<?}?>
                            </select>

                            <select name="salesmanid" id="salesmanid" class="inputbox" size="1" onchange="callODReport();">
                            <option value="0" <? if($salesmanid == 0){?> selected="selected" <? }?>>- All Executives-</option>
                         	<? // Populate Dropdown with values of Salesman field
                         	foreach($salesmans as $s1){?>
                         		<option value="<?=$s1['salesmanid']?>" <? if($salesmanid==$s1['salesmanid']){?> selected="selected" <? }?>><?=$s1['salesmannm']?><?=($s1['active']==1 ? ' (DC)': '')?></option>
                         	<?}?>
                            </select>

                            <select name="od_amt" id="od_amt" class="inputbox" size="1" onchange="callODReport();">
                            	<option value="-1" <? if($od_amt =="-1"){?> selected="selected" <? }?>>OD < 0</option>
                            	<option value="0" <? if($od_amt =="0"){?> selected="selected" <? }?>>OD > 0</option>
                            	<option value="500" <? if($od_amt =="500"){?> selected="selected" <? }?>>OD > 500</option>
                            	<option value="100" <? if($od_amt =="100"){?> selected="selected" <? }?>>OD > 100</option>
                            	<option value="1000" <? if($od_amt =="1000"){?> selected="selected" <? }?>>OD > 1000</option>
                            </select>

                            <select name="disburse" id="disburse" class="inputbox" size="1" onchange="callODReport();">
                            	<option value="" <? if($disburse ==""){?> selected="selected" <? }?>>Show All</option>
                            	<option value="0" <? if($disburse =="0"){?> selected="selected" <? }?>>Disbursed</option>
                            	<option value="1" <? if($disburse =="1"){?> selected="selected" <? }?>>Undisbursed</option>
                            </select>


                            <select name="bucket" id="bucket" class="inputbox" size="1" onchange="callODReport();">
                            	<option value="-1" <? if($bucket == -1){?> selected="selected" <? }?>>Bucket</option>
                            	<?for($b=0;$b < $BUCKET_SIZE;$b++){?>
                            		<option value="<?=$b?>" <? if($bucket == $b){?> selected="selected" <? }?>>BKT <?=$b?></option>
                            	<?}?>
                            	<option value="<?=$b?>" <? if($bucket == $b){?> selected="selected" <? }?>>BKT <?=$b?>++</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table class="adminlist" cellspacing="1" width="100%" id="ls-content-box">
            <thead>
                   	<?switch($type){
                   		case 0: // Centre Wise
                   		case 1: // Executive Wise ?>
                	<tr>
                    	<th class="textleft">#</th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('centre'); callODReport();">Centre</a></th>
						<?if($type == 1){?>
							<th nowrap="nowrap" class="textleft"><a href="javascript:sort('salesmannm'); callODReport();">Executive</a></th>
						<?}?>
						<th nowrap="nowrap" class="textleft">Cases</th>
						<th nowrap="nowrap" class="textleft">Disbursed</th>
						<th nowrap="nowrap" class="textleft">EMI OD</th>
						<th class="textleft">%</th>
						<? for($b=0; $b < $BUCKET_SIZE; $b++){?>
							<th nowrap="nowrap" class="textleft">B <?=$b?></th>
						<?}?>
						<th nowrap="nowrap" class="textleft">B <?=$b?>++</th>
						<th nowrap="nowrap" class="textleft">OD Amt</th>
					</tr>
					<?break;
					case 2://Dealwise?>
                	<tr>
                    	<th class="textleft">#</th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('dealsts)'; callODReport();">Status</a></th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('dealno'); callODReport();">Deal #</a></th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('dealnm'); callODReport();">Name</a></th>
						<th nowrap="nowrap" class="textleft">Dis</th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('salesmannm'); callODReport();">Executive</a></th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('centre'); callODReport();">Centre</a></th>
						<th nowrap="nowrap" class="textleft date_column"><a href="javascript:sort('hpdt'); callODReport();">HP Date</a></th>
						<th nowrap="nowrap" class="textleft">EMI</th>
						<th nowrap="nowrap" class="textleft">Due EMI</th>
						<th nowrap="nowrap" class="textleft">Received</th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('od'); callODReport();">EMI OD</a></th>
						<th nowrap="nowrap" class="textleft"><a href="javascript:sort('od_bucket'); callODReport();">Bucket</a></th>
			        </tr>
					<?}//switch ?>
            </thead>
			<? $colspan=18;?>
            <? if($totalRows>0){
                        $totalPages = ceil($totalRows/$limit);
                        $total=array("emi"=>0,"received"=>0, "od"=>0, "cases"=>0, "disbursed"=>0, "od_amt"=>0);
                        for($b=0; $b < 9; $b++)
							$total['B_'.$b] = 0;
                    if($type == 2){?>
                    <tfoot>
                        <tr>
                            <td colspan="<?=$colspan?>"><del class="container"><div class="pagination">
                                <? $limitarray = array("5","10","15","20","25","30","50","100","200","500");?>
                                <div class="limit">Display #<select name="limit" id="limit" class="inputbox" size="1" onchange="callODReport();">
                                <? for($i=0; $i<count($limitarray); $i++){?>
                                <option value="<?=$limitarray[$i]?>" <? if($limit==$limitarray[$i]){?>selected="selected" <? }?>><?=$limitarray[$i]?></option><? }?>
                                <option value="18446744073709551615" <? if($limit==18446744073709551615){?>selected="selected" <? }?>>All</option></select></div>
                                <? if($page<=1){ $classvalright="button2-right off"; }else{  $classvalright="button2-right"; }
                                if($page>=$totalPages){ $class_left="button2-left off"; }else{  $class_left="button2-left"; } ?>
                                <div class="<?=$classvalright?>"><div class="start"><? if($page<=1){?><span>Start</span><? }else{?><a href="#" title="First" onclick="javascript: ge('page').value=1; callODReport(); return false;">Start</a><? }?></div></div>
                                <div class="<?=$classvalright?>"><div class="prev"><? if($page<=1){?><span>Prev</span><? }else{?><a href="#" title="Previous" onclick="javascript: ge('page').value=<?=($page-1)?>; callODReport();return false;">Prev</a><? }?></div></div>
                                <div class="button2-left"><div class="page"><span><?=$page?></span></div></div>
                                <div class="<?=$class_left?>"><div class="next"><? if($page>=$totalPages){?><span>Next</span><? }else{?><a href="#" title="Next" onclick="javascript: ge('page').value=<?=($page+1)?>; callODReport();return false;">Next</a><? }?></div></div>
                                <div class="<?=$class_left?>"><div class="end"><? if($page>=$totalPages){?><span>End</span><? }else{?><a href="#" title="Last" onclick="javascript: ge('page').value=<?=$totalPages?>; callODReport(); return false;">End</a><? }?></div></div>
                                <div class="limit">Page <?=$page?> of <?=$totalPages?> (Total:<?=$totalRows?>)</div>
                                </div></del>
                            </td>
                        </tr>
                    </tfoot>
                    <?}//if type is 2 ?>
                    <tbody>
                        <?
                        	$slNo = ($from + 1);
							$itr = 1; $oldCentre ="";
                            foreach ($deals as $deal){
								switch($type){
									case 0://Centrewise
									case 1://Executive wise?>

									<tr>
										<td class="textright"><?=$slNo++?></td>
										<td class="textleft">
											<a href="#" onclick="javascript:ge('bucket').value=-1; ge('type').value=1; ge('centre').value='<?=$deal['centre']?>'; ge('salesmanid').value=0; callODReport(); return false;"><?=($oldCentre != $deal['centre'] ? $deal['centre'] : '' )?></a>
										</td>
										<?if($type == 1){?>
											<td class="textleft <?=($deal['active']==1 ? 'red' :'')?>"><?=$deal['salesmannm']?></td>

										<?}?>
										<td class="textright"><?=nf($deal['cases'],0)?></td>
										<td class="textright"><?=nf($deal['disbursed'],0)?></td>
										<td class="textright">
											<?if($deal['od'] != 0){?>
												<a href="#" onclick="javascript:ge('bucket').value=-1; ge('type').value=2; ge('centre').value='<?=$deal['centre']?>';
												<?if($type == 1){?> ge('salesmanid').value='<?=$deal['salesmanid']?>';<?}?>
												callODReport(); return false;"><?=nf($deal['od'],0)?></a>
											<?}
											else{?>-<?}?>
										</td>
										<td class="textright"><?=($deal['cases']!=0 ? nf($deal['od']/$deal['cases']*100) : '-')?> %</td>
										<? for($b=0; $b <= $BUCKET_SIZE; $b++){?>
											<td class="textright">
											<?if($deal['B_'.$b] != 0){?>
												<a href="#" onclick="javascript:ge('bucket').value=<?=$b?>; ge('type').value=2; ge('centre').value='<?=$deal['centre']?>';
												<?if($type == 1){?> ge('salesmanid').value='<?=$deal['salesmanid']?>';<?}?>
												callODReport(); return false;"><?=$deal['B_'.$b]?></a>
											<?}
											else{?>-<?}?>
											</td>
										<? $total['B_'.$b] += $deal['B_'.$b];
										}?>
										<td class="textright"><?=nf($deal['od_amt'],0)?></td>
									</tr>
									<?
									$total['cases']+=$deal['cases']; $total['disbursed']+=$deal['disbursed']; $total['od']+=$deal['od'];$total['od_amt']+=$deal['od_amt'];
									break;

									case 2://Dealwise?>
									<tr <?if($deal['bankduedt'] == null){?>class='bg_aliceblue'<?}?>>
										<td class="textright"><?=$slNo++?></td>
										<td class='textleft
										<?
										if($deal['cancleflg'] == -1)
											echo " blue'> Cancelled";
										else {
											if($deal['dealsts']== 1)
												echo " green'> Active";
											else if($deal['dealsts']== 2)
												echo "Draft";
											else if($deal['dealsts']== 3)
												echo " red'> Closed";
										}?></td>
										<td class="textright"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=$deal['dealno']?></a></td>
										<td class="textleft"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=$deal['dealnm']?></a></td>
										<td class="textleft"><?=($deal['bankduedt']== NULL ? 'N' : 'Y')?></td>
										<td class="textleft <?=($deal['active']==1 ? 'red' :'')?>"><?=$deal['salesmannm']?></td>
										<td class="textleft"><?=$deal['centre']?></td>
										<td class="textright"><?=$deal['hpdt']?></td>
										<td class="textright"><?=nf($deal['ScheduledEMI'],0)?></td>
										<td class="textright"><?=nf($deal['emi'],0)?></td>
										<td class="textright"><?=nf($deal['received'],0)?></td>
										<td class="textright"><?=nf($deal['od'],0)?></td>
										<td class="textright"><?=$deal['od_bucket']?></td>
									</tr>
	                            	<?
									$total['emi']+=$deal['emi']; $total['received']+=$deal['received']; $total['od']+=$deal['od'];
									break;
								}//Switch
								$oldCentre = $deal['centre'];
                            } //for loop
                        if($totalRows==0){?>
                            <tr>
                                <td colspan="<?=$colspan?>" align="center">
                                    No Records found!
                                </td>
                            </tr>
						<?}
                     	else{?>
							<?
								switch($type){
									case 0://Center Wise
									case 1://Executive Wise?>
									<tr>
										<th class='textright'>&nbsp;</th>
										<?if($type == 1){?><th class='textleft'>&nbsp;</th><?}?>
										<th class='textleft'>Total (Shown Rows Only)</th>
										<th class="textright"><?=nf($total['cases'])?></th>
										<th class='textright'><?=nf($total['disbursed'])?></th>
										<th class='textright'><a href='#' onclick="javascript:ge('type').value=2; callODReport(); return false;"><?=nf($total['od'])?></a></th>
										<th class="textright"><?=($total['cases']!=0 ? nf($total['od']/$total['cases']*100) : '-')?> %</th>
										<? for($b=0; $b <= $BUCKET_SIZE; $b++){?>
											<th class="textright">
											<?if($total['B_'.$b] != 0){?>
												<a href="#" onclick="javascript:ge('bucket').value=<?=$b?>; ge('type').value=2; callODReport(); return false;"><?=$total['B_'.$b]?></a>
											<?}?>
											 (<?=($total['od']!=0 ? nf(($total['B_'.$b]/$total['od'])*100) : '-')?> %)
											 </td>
										<?}?>
										<th class='textright'><?=nf($total['od_amt'])?></th>
									</tr>
										<?break;
									case 2:?>
									<tr>
										<th class='textright'>&nbsp;</th>
										<th class='textleft'>&nbsp;</th>
										<th class='textright'>&nbsp;</th>
										<th class='textright'>&nbsp;</th>
										<th class='textleft'>&nbsp;</th>
										<th class='textleft'>&nbsp;</th>
										<th class='textright'>&nbsp;</th>
										<th class='textleft'>Total (Shown Rows Only)</th>
										<th class='textright'>&nbsp;</th>
										<th class='textright'><?=nf($total['emi'])?></th>
										<th class="textright"><?=nf($total['received'])?></th>
										<th class="textright"><?=nf($total['od'])?></th>
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
        //print_a($q);
         ?>
			<input name="page" id="page" value="<?=$page?>" type="hidden">
			<input name="sval" id="sval" value="<?=$sval?>" type="hidden">
			<input name="stype" id="stype" value="<?=$stype?>" type="hidden">

            </form>
            <!--div class="legend"><b>Lengends</b><br><b> NA / - :</b> Not Applicable / Not Attempted</div><div class="legend"><b>In Progress</b> Not Submitted</div><div class="legend"><b>Number:</b> Click to see results</div-->
            <div class="clr"></div>
        </div>
    </div>
<?}?>