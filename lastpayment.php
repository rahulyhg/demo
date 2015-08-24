<?
if(!isset($task) || $task!="lastpayment"){
    echo "Invalid Access in Last Payment Report";
    die();
}
else lastpayment();

function lastpayment(){
    $dbPrefix = $_SESSION['DB_PREFIX'];
    $user_dbPrefix = $_SESSION['USER_DB_PREFIX'];
//	print_a($_REQUEST);
    $DEFAULT_SORT  = 'dealnm ';$DEFAULT_SORT_TYPE = 'asc';
    $BUCKET_SIZE = 6;

	/**** Inputs **********/
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 2;
	$mm = isset($_REQUEST['mm']) ? ($_REQUEST['mm'] == -1 ? NULL : $_REQUEST['mm']) : 0;
    $hpdt = isset($_REQUEST['hpdt']) ? $_REQUEST['hpdt'] : 0;
    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
	$expired = isset($_REQUEST['expired']) ? $_REQUEST['expired'] : 0;
	$bucket = isset($_REQUEST['bucket']) ? $_REQUEST['bucket'] : -1;
	$paytype = isset($_REQUEST['paytype']) ? $_REQUEST['paytype'] : 0;
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

	$dbPrefix_curr = "lksa".(date('n') < 4 ? (date('Y') - 1)."".substr(date('Y'),-2) : date('Y')."".(substr(date('Y'),-2)+1));

	$montharr = array(array(0, '-Month-'), array(-1, 'No Month'), array(4,'Apr'),array(5,'May'),array(6,'Jun'),array(7,'Jul'),array(8,'Aug'),array(9,'Sep'),array(10,'Oct'),array(11,'Nov'),array(12,'Dec'),array(1,'Jan'),array(2,'Feb'),array(3,'Mar'));

	$hp_options = array(array("- HP Date -",""));

	for($i = 2008; $i <= date('Y'); $i++){
		$fy = substr($i,-2)."-".substr($i+1,-2);
		$hp_options[] = array("$fy", " AND d.fy = '$fy' ");
	}
	$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
	for($i = 1; $i <= $m_in_fy; $i++){
		$mn = $i+3;
		$hp_options[] = array(toMonthName($mn), " AND d.fy = '$fy' AND month(hpdt) = $mn ");
	}
	$hp_options[] = array("Fresh Bouncing", " AND d.hpdt > '2014-03-31' ");
	$hp_options[] = array("Old", " AND d.hpdt < '2014-04-01'");
  	$hp_options []  = array("Year: ".date('Y'), " AND year(d.hpdt)=".date('Y')."");
  	$hp_options []  = array("Year: ".(date('Y')-1), " AND year(d.hpdt)=".(date('Y')-1)."");

	$colspan = 1;

    //Build Query depending on various filter criterions
	switch($type){
		case 0: //Month Wise
			$colspan += 2;
			$q = "SELECT MONTH(t.dt) as mm, COUNT(dealid) AS total ";

			for($i = 2008; $i <= date('Y'); $i++){
				$fy = substr($i,-2)."-".substr($i+1,-2);
				$q .= ",SUM(CASE WHEN t.fy = '$fy' THEN 1 ELSE 0 END) AS '$fy' ";
				$colspan++;
			}

			$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
			for($i = 1; $i <= $m_in_fy; $i++){
				$mn = $i+3;
				$q .= ",SUM(CASE WHEN t.fy = '$fy' AND MONTH(hpdt) = $mn THEN 1 ELSE 0 END) AS '".toMonthName($mn)."'";
				$colspan++;
			}

			$q .= "FROM (
				SELECT d.dealid, d.hpdt, d.fy, SUM(totrcptamt) AS amt, MAX(rcptdt) AS dt FROM $dbPrefix_curr.tbxfieldrcvry d LEFT JOIN $dbPrefix_curr.tbxdealrcpt r ON d.dealid = r.dealid AND r.cclflg = 0 AND r.cbflg = 0 and r.totrcptamt != 0 and r.rcptpaymode != 3 ";

				switch($paytype){
					case 0://Both
						$q .= " ";
						break;
					case 1://Cash
						$q .= " AND rcptpaymode = 1 ";
						break;
					case 2://Non-Cash
						$q .= " AND rcptpaymode != 1 ";
						break;
				}

				$q .= "WHERE d.mm = ".date('n');

				if($centre != "")
					$q .= " AND centre = '$centre' ";

				if($hpdt != 0)
					$q .= $hp_options[$hpdt][1];

				switch($expired){
					case 0://both
						break;
					case 1://Expired
						$q .= " and d.hpexpdt <'".date("Y-m-01")."' ";
						break;
					case 2://Active
						$q .= " and d.hpexpdt >='".date("Y-m-01")."' ";
					break;
				}

				switch($bucket){
					case -1:
						break;
					case 6: //
						$q .= " and d.rgid > 5 ";
						break;
					default://Exact Match
						$q .= " and d.rgid = $bucket ";
						break;
				}

				$q .= " GROUP BY d.dealid
			) t GROUP BY MONTH(t.dt) ";

			if(is_null($mm))
				$q .= " having mm is null ";
			else if($mm!=0){
				$q .= " having mm = $mm ";
			}

			break;

		case 1: //Deal Wise
			$q = "SELECT sql_calc_found_rows d.dealid, d.dealno, tcase(d.dealnm) as dealnm, d.fy, d.hpdt, d.hpexpdt, tcase(d.centre) as centre, d.rgid, d.catid, d.dueamt, d.oddueamt, SUM(totrcptamt) AS amt, MAX(rcptdt) AS rcptdt, month(MAX(rcptdt)) as mm
				FROM lksa201516.tbxfieldrcvry d LEFT JOIN lksa201516.tbxdealrcpt r ON d.dealid = r.dealid AND r.cclflg = 0 AND r.cbflg = 0 and r.totrcptamt != 0  and r.rcptpaymode != 3 ";
			switch($paytype){
				case 0://Both
					$q .= " ";
					break;
				case 1://Cash
					$q .= " AND rcptpaymode = 1 ";
					break;
				case 2://Non-Cash
					$q .= " AND rcptpaymode != 1 ";
					break;
			}

			$q .= "WHERE d.mm = ".date('n');
			if($centre != "")
				$q .= " AND centre = '$centre' ";
			if($hpdt != 0)
				$q .= $hp_options[$hpdt][1];
			switch($expired){
				case 0://both
					break;
				case 1://Expired
					$q .= " and d.hpexpdt <'".date("Y-m-01")."' ";
					break;
				case 2://Active
					$q .= " and d.hpexpdt >='".date("Y-m-01")."' ";
				break;
			}
			switch($bucket){
				case -1:
					break;
				case 6: //
					$q .= " and d.rgid > 5 ";
					break;
				default://Exact Match
					$q .= " and d.rgid = $bucket ";
					break;
			}
			$q .= " GROUP BY d.dealid ";

			if(is_null($mm))
				$q .= " having mm is null ";
			else if($mm!=0){
				$q .= " having mm = $mm ";
			}


			$q .= "order by $sval $stype limit $from, $limit";
			break;

		case 2: //Center Wise
			$colspan += 2;
			$q = "SELECT tcase(t.centre) as centre, COUNT(dealid) AS total ";

			$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
			for($i = $m_in_fy; $i > 0; $i--){
				$mn = ($i+3)%12;
				$q .= ",SUM(CASE WHEN month(t.dt) = $mn THEN 1 ELSE 0 END) AS '".toMonthName($mn)."'";
				$colspan++;
			}
			$q .= ",SUM(CASE WHEN t.dt IS NULL THEN 1 ELSE 0 END) AS 'NoMonth' ";

			$q .= "FROM (
				SELECT d.dealid, d.hpdt, d.fy, d.centre, SUM(totrcptamt) AS amt, MAX(rcptdt) AS dt FROM $dbPrefix_curr.tbxfieldrcvry d LEFT JOIN $dbPrefix_curr.tbxdealrcpt r ON d.dealid = r.dealid AND r.cclflg = 0 AND r.cbflg = 0 and r.totrcptamt != 0 and r.rcptpaymode != 3 ";

				switch($paytype){
					case 0://Both
						$q .= " ";
						break;
					case 1://Cash
						$q .= " AND rcptpaymode = 1 ";
						break;
					case 2://Non-Cash
						$q .= " AND rcptpaymode != 1 ";
						break;
				}

				$q .= "WHERE d.mm = ".date('n');

				if($centre != "")
					$q .= " AND centre = '$centre' ";

				if($hpdt != 0)
					$q .= $hp_options[$hpdt][1];

				switch($expired){
					case 0://both
						break;
					case 1://Expired
						$q .= " and d.hpexpdt <'".date("Y-m-01")."' ";
						break;
					case 2://Active
						$q .= " and d.hpexpdt >='".date("Y-m-01")."' ";
					break;
				}

				switch($bucket){
					case -1:
						break;
					case 6: //
						$q .= " and d.rgid > 5 ";
						break;
					default://Exact Match
						$q .= " and d.rgid = $bucket ";
						break;
				}

				$q .= " GROUP BY d.dealid
			) t GROUP BY t.centre ";

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
                            <select name="type" id="type" class="inputbox" size="1" onchange="call_lastpayment();">
                            	<option value=0 <? if($type ==0){?> selected="selected" <? }?>>HP Dt Wise</option>
                            	<option value=1 <? if($type ==1){?> selected="selected" <? }?>>Deal Wise</option>
                            	<option value=2 <? if($type ==2){?> selected="selected" <? }?>>Centre Wise</option>
                            </select>
						</td>
                        <td width="75%" style='text-align:right'>
							<select name="mm" id="mm" class="inputbox" size="1" onchange="call_lastpayment();" <?=($type == 0 ? 'style="display:none"': '')?>>
							<?foreach($montharr as $a){?>
								<option value="<?=$a[0]?>" <?if($mm==$a[0]){?> selected="selected" <? }?>><?=$a[1]?></option>
							<?}?>
							</select>
							<select name="hpdt" id="hpdt" class="inputbox" size="1" onchange="call_lastpayment();">
							<?$i=0;
								foreach ($hp_options as $p){?>
									<option value="<?=$i?>" <?if($hpdt==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
							</select>

                            <select name="centre" id="centre" class="inputbox" size="1" onchange="call_lastpayment();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres -</option>
                         	<? // Populate Dropdown with values of Centre field
                         	foreach($centres as $c1){?>
                         		<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
                         	<?}?>
                            </select>

                            <select name="bucket" id="bucket" class="inputbox" size="1" onchange="call_lastpayment();">
                            	<option value="-1" <? if($bucket == -1){?> selected="selected" <? }?>>Bucket</option>
                            	<?for($b=1;$b < $BUCKET_SIZE;$b++){?>
                            		<option value="<?=$b?>" <? if($bucket == $b){?> selected="selected" <? }?>>BKT <?=$b?></option>
                            	<?}?>
                            	<option value="<?=$b?>" <? if($bucket == $b){?> selected="selected" <? }?>>BKT <?=$b?>++</option>
                            </select>

							<select name="expired" id="expired" class="inputbox" size="1" onchange="call_lastpayment();">
								<option value="0" <? if($expired == 0){?> selected="selected" <? }?>>Both</option>
								<option value="1" <? if($expired == 1){?> selected="selected" <? }?>>Expired</option>
								<option value="2" <? if($expired == 2){?> selected="selected" <? }?>>Active</option>
							</select>
							<select name="paytype" id="paytype" class="inputbox" size="1" onchange="call_lastpayment();">
								<option value="0" <? if($paytype== 0){?> selected="selected" <? }?>>-Paytype-</option>
								<option value="1" <? if($paytype== 1){?> selected="selected" <? }?>>Cash</option>
								<option value="2" <? if($paytype== 2){?> selected="selected" <? }?>>Others</option>
							</select>
                        </td>
                    </tr>
                </tbody>
            </table>

			<!--Table Started --->
            <table class="adminlist" cellspacing="1" width="100%" id="ls-content-box">
            <thead>
                <tr>
                <?
                switch($type){
                	case 0:?>
						<tr>
							<th>SN</th>
							<th>Month</th>
							<th>Total</th>
							<th colspan ="<?=($colspan-3-$m_in_fy)?>">HP Date</th>
							<th colspan ="<?=($m_in_fy)?>">Current FY</th>
						</tr>
						<tr>
							<th></th>
							<th></th>
							<th></th>
							<?for($i = 2008; $i <= date('Y'); $i++){
								$fy = substr($i,-2)."-".substr($i+1,-2);?>
								<th class='textleft'><?=$fy?></th>
							<?}
							$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
							for($i = 1; $i <= $m_in_fy; $i++){
								$mn = $i+3;?>
								<th class='textleft'><?=toMonthName($mn)?></th>
							<?}?>
						</tr>
						<?break;
					case 1:?>
						<tr>
							<th class="textleft">SN</th>
							<th class="textleft"><a href="javascript:sort('mm'); call_lastpayment();">Last Paid</a></th>
							<th class="textleft"><a href="javascript:sort('dealno'); call_lastpayment();">Deal #</a></th>
							<th class="textleft"><a href="javascript:sort('dealnm'); call_lastpayment();">Name</a></th>
							<th class="textleft"><a href="javascript:sort('fy'); call_lastpayment();">FY</a></th>
							<th class="textleft date_column"><a href="javascript:sort('hpdt'); call_lastpayment();">HP Date</a></th>
							<th class="textleft date_column"><a href="javascript:sort('hpexpdt'); call_lastpayment();">Expiry</a></th>
							<th class="textleft"><a href="javascript:sort('centre'); call_lastpayment();">Centre</a></th>
							<th class="textleft"><a href="javascript:sort('rgid'); call_lastpayment();">Bucket</a></th>
							<th class="textleft date_column"><a href="javascript:sort('rcptdt'); call_lastpayment();">Receipt Dt</a></th>
							<th class="textleft"><a href="javascript:sort('dueamt'); call_lastpayment();">Due</a></th>
							<th class="textleft"><a href="javascript:sort('oddueamt'); call_lastpayment();">OD</a></th>
						</tr>
					<?
					break;
					case 2:?>
					<tr>
						<th>SN</th>
						<th>Centre</th>
						<th>Total</th>
						<?for($i = $m_in_fy; $i > 0; $i--){
							$mn = ($i+3)%12;?>
							<th class='textleft'><?=toMonthName($mn)?></th>
						<?}?>
						<th class='textleft'>No Month</th>
					</tr>
					<?
					break;
				}//type =  ?>
            </thead>
            <? if($totalRows>0){
					$totalPages = ceil($totalRows/$limit);
                    if($type == 1){
	                    $colspan = 12;
                    ?>
                    <tfoot>
                        <tr>
                            <td colspan="<?=$colspan?>">
                            	<del class="container">
                            		<div class="pagination">
										<? $limitarray = array("5","10","15","20","25","30","50","100","200","500");?>
										<div class="limit">Display #<select name="limit" id="limit" class="inputbox" size="1" onchange="call_lastpayment();">
										<? for($i=0; $i<count($limitarray); $i++){?>
										<option value="<?=$limitarray[$i]?>" <? if($limit==$limitarray[$i]){?>selected="selected" <? }?>><?=$limitarray[$i]?></option><? }?>
										<option value="18446744073709551615" <? if($limit==18446744073709551615){?>selected="selected" <? }?>>All</option></select></div>
										<? if($page<=1){ $classvalright="button2-right off"; }else{  $classvalright="button2-right"; }
										if($page>=$totalPages){ $class_left="button2-left off"; }else{  $class_left="button2-left"; } ?>
										<div class="<?=$classvalright?>"><div class="start"><? if($page<=1){?><span>Start</span><? }else{?><a href="#" title="First" onclick="javascript: ge('page').value=1; call_lastpayment(); return false;">Start</a><? }?></div></div>
										<div class="<?=$classvalright?>"><div class="prev"><? if($page<=1){?><span>Prev</span><? }else{?><a href="#" title="Previous" onclick="javascript: ge('page').value=<?=($page-1)?>; call_lastpayment();return false;">Prev</a><? }?></div></div>
										<div class="button2-left"><div class="page"><span><?=$page?></span></div></div>
										<div class="<?=$class_left?>"><div class="next"><? if($page>=$totalPages){?><span>Next</span><? }else{?><a href="#" title="Next" onclick="javascript: ge('page').value=<?=($page+1)?>; call_lastpayment();return false;">Next</a><? }?></div></div>
										<div class="<?=$class_left?>"><div class="end"><? if($page>=$totalPages){?><span>End</span><? }else{?><a href="#" title="Last" onclick="javascript: ge('page').value=<?=$totalPages?>; call_lastpayment(); return false;">End</a><? }?></div></div>
										<div class="limit">Page <?=$page?> of <?=$totalPages?> (Total:<?=$totalRows?>)</div>
                                	</div>
                                </del>
                            </td>
                        </tr>
                    </tfoot>
                    <?}//if type is 1 ?>
                    <tbody>
                        <?
                        	$slNo = ($from + 1);
							$itr = 1; $oldAgent =-1;
								switch($type){
									case 0://Month Wise
										$total=array('dueamt'=>0, 'oddueamt' =>0); $total['total'] = 0;
										for($i = 2008; $i <= date('Y'); $i++){
											$fy = substr($i,-2)."-".substr($i+1,-2);
											$total[$fy] = 0;
										}
										$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
										for($i = 1; $i <= $m_in_fy; $i++){
											$mn = $i+3;
											$total[toMonthName($mn)] = 0;
										}

									foreach ($deals as $deal){?>
									<tr>
										<td class="textright"><?=$slNo++?></td>
										<td class='textleft'><?=toMonthName($deal['mm'])?></td>
										<th class='textright'>
											<a href="#" onclick="javascript:ge('type').value=1; ge('hpdt').value=0;ge('mm').value=<?=(is_null($deal['mm']) ? -1 : $deal['mm'])?>; call_lastpayment(); return false;"><?=nf($deal['total'],0)?></a>
										</th>
										<!--FY-->
										<?
										$total['total'] += $deal['total'];
										$col = 0;
										for($i = 2008; $i <= date('Y'); $i++){
											$col++;
											$fy = substr($i,-2)."-".substr($i+1,-2);?>
											<td class="textright">
												<a href="#" onclick="javascript:ge('type').value=1;ge('hpdt').value=<?=$col?>;ge('mm').value=<?=(is_null($deal['mm']) ? -1 : $deal['mm'])?>; call_lastpayment(); return false;"><?=nf($deal[$fy],0)?></a>
											</td>
											<? $total[$fy] += $deal[$fy];
										}?>
										<!--FY Month-->
										<?
										$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
										for($i = 1; $i <= $m_in_fy; $i++){
											$col++;
											$mn = $i+3; $v = toMonthName($mn);?>
											<td class="textright">
												<a href="#" onclick="javascript:ge('type').value=1;ge('hpdt').value=<?=$col?>;ge('mm').value=<?=(is_null($deal['mm']) ? -1 : $deal['mm'])?>;call_lastpayment(); return false;"><?=nf($deal[$v],0)?></a>
											</td>
											<? $total[$v] += $deal[$v];
										}?>
									</tr>
									<?}//For
									break;

									case 1://Dealwise
										$total=array('dueamt'=>0, 'oddueamt' =>0);
									foreach ($deals as $deal){
										$color = '';
										if($deal['catid'] == 25) // Seized Vehicle
											$color = 'seized';
										else if($deal['catid'] == 12) // Insurance Case
											$color = 'insurance';
										else if ($deal['catid'] == 13) // Police Station
											$color = 'police-station';
										else if ($deal['catid'] == 24) // Write Off Cases
											$color = 'write-off';
									?>
									<tr class='<?=$color?>'>
										<td class="textright"><?=$slNo++?></td>
										<td class="textleft"><?=toMonthName($deal['mm'])?></td>
										<td class="textright"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=$deal['dealno']?></a></td>
										<td class="textleft"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=titleCase($deal['dealnm'])?></a></td>
										<td class="textleft"><?=$deal['fy']?></td>
										<td class="textright"><?=date('d-M-Y',strtotime($deal['hpdt']))?></td>
										<td class="textright"><?=date('d-M-Y',strtotime($deal['hpexpdt']))?></td>
										<td class="textleft"><?=titleCase($deal['centre'])?></td>
										<td class="textright"><?=$deal['rgid']?></td>
										<td class="textright"><?=date('d-M-Y',strtotime($deal['rcptdt']))?></td>
										<td class="textright"><?=nf($deal['dueamt'],0)?></td>
										<td class="textright"><?=nf($deal['oddueamt'],0)?></td>
									</tr>
	                            	<?
									$total['dueamt']+=$deal['dueamt']; $total['oddueamt']+=$deal['oddueamt'];
									}//for
									break;

									case 2://Centre Wise
										$total=array(); $total['total'] = 0;
										for($i = $m_in_fy; $i > 0; $i--){
											$mn = ($i+3)%12;
											$total[toMonthName($mn)] = 0;
										}
										$total['NoMonth'] =0;
									foreach ($deals as $deal){?>
										<tr>
											<td class="textright"><?=$slNo++?></td>
											<td class='textleft'><?=$deal['centre']?></td>
											<th class="textright">
											<a href="#" onclick="javascript:ge('type').value=1; ge('mm').value=0; ge('centre').value='<?=$deal['centre']?>'; call_lastpayment(); return false;"><?=nf($deal['total'],0)?></a>
											</th>
											<?for($i = $m_in_fy; $i > 0; $i--){
												$mn = ($i+3)%12;?>
												<td class='textright'><a href="#" onclick="javascript:ge('type').value=1; ge('mm').value=<?=$mn?>; ge('centre').value='<?=$deal['centre']?>'; call_lastpayment(); return false;"><?=nf($deal[toMonthName($mn)],0)?></a></td>
											<?$total[toMonthName($mn)] += $deal[toMonthName($mn)];
											}?>
											<td class='textright'><a href="#" onclick="javascript:ge('type').value=1; ge('mm').value=-1; ge('centre').value='<?=$deal['centre']?>'; call_lastpayment(); return false;"><?=nf($deal['NoMonth'],0)?></a></td>
											<?$total['NoMonth'] += $deal['NoMonth'];
											$total['total'] += $deal['total'];?>
										</tr>
									<?}
									break;
								}//Switch
                        if($totalRows==0){?>
                            <tr>
                                <td colspan="<?=$colspan?>" align="center">
                                    No Records found!
                                </td>
                            </tr>
						<?}
                     	else{ // Footer Row - Show Sum of each column
                     		switch($type){
								case 0:?>
								<tr>
									<th>&nbsp;</th>
									<th class='textleft'>Total in OD</th>
									<th class='textright'>
										<a href="#" onclick="javascript:ge('type').value=1; ge('hpdt').value=0; ge('mm').value=0;call_lastpayment(); return false;"><?=nf($total['total'],0)?>
										</a>
									</th>
									<?$col = 0;
									for($i = 2008; $i <= date('Y'); $i++){
										$col++;
										$fy = substr($i,-2)."-".substr($i+1,-2);?>
										<th class="textright">
											<a href="#" onclick="javascript:ge('type').value=1;ge('hpdt').value=<?=$col?>;ge('mm').value=0; call_lastpayment(); return false;"><?=nf($total[$fy],0)?></a>
										</th>
									<?}?>
									<!--FY Month-->
									<?
									$m_in_fy = (date('n') <= 3 ? 9+date('n') : date('n')-3);
									for($i = 1; $i <= $m_in_fy; $i++){
										$col++;
										$mn = $i+3; $v = toMonthName($mn);?>
										<th class="textright">
											<a href="#" onclick="javascript:ge('type').value=1;ge('hpdt').value=<?=$col?>;ge('mm').value=0; call_lastpayment(); return false;"><?=nf($total[$v],0)?></a>
										</th>
									<?}?>
								</tr>
									<?break;
								case 1:?>
								<tr>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th>Total</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
									<th class='textright'><?=nf($total['dueamt'],0)?></th>
									<th class='textright'><?=nf($total['oddueamt'],0)?></th>
								</tr>
								<?break;
								case 2:?>
								<tr>
									<th>&nbsp;</th>
									<th>Total in OD</th>
									<th class="textright"><a href="#" onclick="javascript:ge('type').value=1; ge('mm').value=0; ge('centre').value=''; call_lastpayment(); return false;"><?=nf($total['total'],0)?></a></th>
									<?for($i = $m_in_fy; $i > 0; $i--){
										$mn = ($i+3)%12;?>
										<th class='textright'><a href="#" onclick="javascript:ge('type').value=1; ge('mm').value=<?=$mn?>; ge('centre').value=''; call_lastpayment(); return false;"><?=nf($total[toMonthName($mn)],0)?></a></th>
									<?}?>
									<th class="textright"><a href="#" onclick="javascript:ge('type').value=1; ge('mm').value=-1; ge('centre').value=''; call_lastpayment(); return false;"><?=nf($total['NoMonth'],0)?></a></th>
								</tr>
								<?
								break;
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

         <?if($type==1){?>
         	<table class="adminlist legend-box" cellspacing = "1" style="width:80% !important;margin-left:10%">
				<tr><th class="">Colour Coding</th>
					<td class="seized center">Seized</td>
         			<td class="insurance center">Insurance Case</td>
         			<td class="write-off center">Write Off Case</td>
         			<td class="police-station center">Vehicle in Police Station</td>
         	</table>
         <?}?>
            <div class="clr"></div>
        </div>
    </div>
<?}?>