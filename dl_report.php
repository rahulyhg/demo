<?
if(!isset($task) || $task!="dl_report"){
    echo "Invalid Access";
    die();
}
else dl_report();

function dl_report(){
    $dbPrefix = $_SESSION['DB_PREFIX'];
	$reportMonth = 13;
	$columnMonths = 4;//Including current month
	$columnYears = 3;//Including current month
	$DEFAULT_SORT = 'dealno';
	$DEFAULT_SORT_TYPE = 'desc';

	$fd = date('Y-M-01');

    //$month = isset($_REQUEST['month']) && !empty($_REQUEST['month']) ? $_REQUEST['month'] : date('mY');
    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
    $brkrid = isset($_REQUEST['brkrid']) ? $_REQUEST['brkrid'] : 0;
	$zero = isset($_REQUEST['zeroDeals']) ? $_REQUEST['zeroDeals'] : 0;
	$active = isset($_REQUEST['active']) ? $_REQUEST['active'] : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;
    $hpdt = isset($_REQUEST['hpdt']) ? $_REQUEST['hpdt'] : 0;
	$expired = isset($_REQUEST['expired']) ? $_REQUEST['expired'] : 0;

	/****** Pagination & Sorting *************/
	$limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $_SESSION['ROWS_IN_TABLE'];
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
	$sval = isset($_REQUEST['sval']) ? (empty($_REQUEST['sval']) ? $DEFAULT_SORT : $_REQUEST['sval'] ) : $DEFAULT_SORT;
    $stype = isset($_REQUEST['stype']) ? (empty($_REQUEST['stype']) ? $DEFAULT_SORT_TYPE : $_REQUEST['stype'] ) : $DEFAULT_SORT_TYPE;

	$from = ($limit * ($page - 1));
    $till = ($limit + $from);
	/****** Pagination & Sorting *************/
	$currMonth = date('m');
	$currYear = date('Y');

	$lastMonth = $currMonth - 1;
	$lastYear = $currYear;
	if($lastMonth == 0){
		$lastMonth = 12;
		$lastYear = $currYear - 1;
	}
	$fd = date('Y-M-01');

	$q1 = "SELECT brkrid, brkrnm, active from ".$dbPrefix.".tbmbroker where active != 3 and brkrtyp = 1 order by active desc, brkrnm";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$dealer = $t1['r'];
	}

    //Get all centers
    $q1 = "SELECT tcase(centrenm) as centre from ".$dbPrefix.".tbmcentre order by centre";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$centres = $t1['r'];
	}

	$hp_options = array(array("- HP Date -",""));
	$hpi = 1;

	$columns = array();
	for ($i=0; $i < $columnMonths; $i++){
		$strtm = strtotime('-'.($i).' month', strtotime($fd));
		$col = date('M-y',$strtm);
		$columns[] = $col;
		$hp_options[$hpi++] = array($col, " AND d.hpdt between '".date('Y-m-01',$strtm)."' and '".date('Y-m-t',$strtm)."' ");

	}

	for ($i=0; $i < $columnYears; $i++){
		if(date('n') > 3)
			$fy = date('y', strtotime('-'.($i).' years')).'-'.date('y', strtotime('-'.($i-1).' years'));
		else
			$fy = date('y', strtotime('-'.($i-1).' years')).'-'.date(y, strtotime('-'.($i).' years'));

		$columns[] = 'FY_'.$fy;

		$hp_options[$hpi++] = array('FY_'.$fy, " AND d.fy = '$fy'");
	}

	$columns[] = 'Total';
	$hp_options[$hpi++] = array('Total', " ");

	$dealer_clause = "";
	switch($brkrid){
		case 0:
			break;
		case  -1:
			$dealer_clause = " AND s.active = 2";
			break;
		case -2:
			$dealer_clause = " AND s.active = 1";
			break;
		default:
			$dealer_clause = " AND s.brkrid =  '$brkrid' ";
			break;
	}

	$c = 0;
    //Build Query depending on various filter criterions
	switch($type){
		case 0: //Center Wise
		case 1: //Dealer Wise
			$q = " SELECT tcase(s.centre) as centre, ".($type==1 ? " s.brkrid, s.brkrnm, s.active,  " : "" )."";
				for($i=0; $i < $columnMonths; $i++){
					$q .= " SUM(CASE WHEN hpdt BETWEEN '".date('Y-m-01', strtotime('-'.($i).' month', strtotime($fd)))."' AND '".date('Y-m-t',strtotime('-'.($i).' month', strtotime($fd)))."' THEN 1 ELSE 0 END) AS `".$columns[$c++]."`,
					";
				}

				for($i=0; $i < $columnYears; $i++){
					if(date('n') > 3)
						$fy = date('y', strtotime('-'.($i).' years')).'-'.date('y', strtotime('-'.($i-1).' years'));
					else
						$fy = date('y', strtotime('-'.($i-1).' years')).'-'.date(y, strtotime('-'.($i).' years'));

					$q .= " SUM(CASE WHEN fy='$fy' THEN 1 ELSE 0 END) AS `".$columns[$c++]."`,
					";
				}

			$q .="
				COUNT(d.dealid) AS Total
				FROM ".$dbPrefix.".tbmbroker s  JOIN ".$dbPrefix.".tbmdeal d
				ON d.brkrid = s.brkrid AND s.brkrtyp =1 and d.cancleflg = 0 ".$dealer_clause."
				WHERE 1 ".($centre != "" ? " AND s.centre = '$centre' " : ""). "
				GROUP BY s.centre ".($type==1 ? ", s.brkrid " : "")."
				order by s.centre";

				break;
		case 2:
			$q = " SELECT  SQL_CALC_FOUND_ROWS d.dealid, d.dealno, tcase(d.dealnm) as dealnm, d.hpdt, tcase(d.centre) as centre, s.brkrid, s.brkrnm as dealer, sl.salesmanid, sl.salesmannm
			FROM $dbPrefix.tbmdeal d JOIN $dbPrefix.tbmbroker s ON d.brkrid = s.brkrid AND s.brkrtyp =1 and d.cancleflg = 0 $dealer_clause ".$hp_options[$hpdt][1];

			if($centre != "")
				$q .= " AND s.centre = '$centre' ";

			$q .=" JOIN ".$dbPrefix.".tbmsalesman sl JOIN ".$dbPrefix.".tbadealsalesman sa ON d.dealid = sa.dealid AND sa.salesmanid = sl.salesmanid
			order by $sval $stype limit $from, $limit";
			break;
		}
//	print_a($q);
//	die();

	$totalRows = 0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$list = $t1['r'];
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
                            <b>Type:</b> <select name="type" id="type" class="inputbox" size="1" onchange="callDLReport();">
                            	<option value=0 <? if($type ==0){?> selected="selected" <? }?>>Centre Wise</option>
                            	<option value=1 <? if($type ==1){?> selected="selected" <? }?>>Dealer Wise</option>
                            	<option value=2 <? if($type ==2){?> selected="selected" <? }?>>Deal Wise</option>
                            </select>
                        </td>
                        <td nowrap="nowrap">
                            <select name="centre" id="centre" class="inputbox" size="1" onchange="callDLReport();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres-</option>
								<? // Populate Dropdown with values of Centre field
								foreach($centres as $c1){?>
									<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
								<?}?>
                            </select>

							<select name="hpdt" id="hpdt" class="inputbox" size="1" onchange="callDLReport();" <?=($type != 2 ? 'style="display:none"': '')?>>
							<?
								$i=0;
								foreach ($hp_options as $p){?>
									<option value="<?=$i?>" <?if($hpdt==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
							</select>

							<select name="brkrid" id="brkrid" class="inputbox" size="1" onchange="callDLReport();">
                            <option value="0" <? if($brkrid == 0){?> selected="selected" <? }?>>- All Dealers-</option>
                            <option value="-1" <? if($brkrid == -1){?> selected="selected" <? }?>><b>- Active Dealers Only-</b></option>
                            <option value="-2" <? if($brkrid == -2){?> selected="selected" <? }?>>- Inactive Dealers Only-</option>
                         		<? // Populate Dropdown with values of Dealers field
                         		foreach($dealer as $s1){?>
                         			<option value="<?=$s1['brkrid']?>" <? if($brkrid==$s1['brkrid']){?> selected="selected" <? }?>><?=$s1['brkrnm']?><?=($s1['active']==1 ? ' (DC)' : '')?></option>
                         		<?}?>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table class="adminlist" cellspacing="1" width="100%" id="ls-content-box">
            <thead>
            	<?switch($type){
            		case 0:
            		case 1:?>
						<tr>
							<th class="textleft">#</th>
							<th nowrap="nowrap" class="textleft">Centre</th>
							<?if($type==1){ ?><th nowrap="nowrap" class="textleft">Sales Executive</th><?}?>
							<?
							foreach($columns as $c){?>
								<th class="textleft"><?=$c?></th>
							<?}?>
						</tr>
					<?break;
					case 2:?>
						<tr>
							<th class="textleft">SN</th>
							<th class="textleft"><a href="javascript:sort('dealno'); callDLReport();">Deal #</a></th>
							<th class="textleft"><a href="javascript:sort('dealnm'); callDLReport();">Customer Name</a></th>
							<th class="textleft date_column"><a href="javascript:sort('hpdt'); callDLReport();">HP Date</a></th>
							<th class="textleft"><a href="javascript:sort('centre'); callDLReport();">Centre</a></th>
							<th class="textleft"><a href="javascript:sort('dealer'); callDLReport();">Dealer</a></th>
							<th class="textleft"><a href="javascript:sort('salesmannm'); callDLReport();">Salesman</a></th>
						</tr>
					<?break;
				}?>
            </thead>
            <?if($totalRows > 0){
				$totalPages = ceil($totalRows/$limit);
				if($type == 2){?>
					<tfoot>
						<tr>
						<td colspan="7">
							<del class="container">
								<div class="pagination">
									<? $limitarray = array("5","10","15","20","25","30","50","100","200","500");?>
									<div class="limit">Display #<select name="limit" id="limit" class="inputbox" size="1" onchange="callDLReport();">
									<? for($i=0; $i<count($limitarray); $i++){?>
									<option value="<?=$limitarray[$i]?>" <? if($limit==$limitarray[$i]){?>selected="selected" <? }?>><?=$limitarray[$i]?></option><? }?>
									<option value="18446744073709551615" <? if($limit==18446744073709551615){?>selected="selected" <? }?>>All</option></select></div>
									<? if($page<=1){ $classvalright="button2-right off"; }else{  $classvalright="button2-right"; }
									if($page>=$totalPages){ $class_left="button2-left off"; }else{  $class_left="button2-left"; } ?>
									<div class="<?=$classvalright?>"><div class="start"><? if($page<=1){?><span>Start</span><? }else{?><a href="#" title="First" onclick="javascript: ge('page').value=1; callDLReport(); return false;">Start</a><? }?></div></div>
									<div class="<?=$classvalright?>"><div class="prev"><? if($page<=1){?><span>Prev</span><? }else{?><a href="#" title="Previous" onclick="javascript: ge('page').value=<?=($page-1)?>; callDLReport();return false;">Prev</a><? }?></div></div>
									<div class="button2-left"><div class="page"><span><?=$page?></span></div></div>
									<div class="<?=$class_left?>"><div class="next"><? if($page>=$totalPages){?><span>Next</span><? }else{?><a href="#" title="Next" onclick="javascript: ge('page').value=<?=($page+1)?>; callDLReport();return false;">Next</a><? }?></div></div>
									<div class="<?=$class_left?>"><div class="end"><? if($page>=$totalPages){?><span>End</span><? }else{?><a href="#" title="Last" onclick="javascript: ge('page').value=<?=$totalPages?>; callDLReport(); return false;">End</a><? }?></div></div>
									<div class="limit">Page <?=$page?> of <?=$totalPages?> (Total:<?=$totalRows?>)</div>
								</div>
							</del>
						</td>
						</tr>
					</tfoot>
				<?}//if type is 2?>
				<tbody>
				<?switch($type){
					case 0:
						$itr = 1;
						$totals = array();
						foreach($columns as $c){
							$totals[$c] = 0;
						}
						foreach ($list as $deal){
							foreach($columns as $c){
								if(!isset($deal[$c]))
									$deal[$c] = 0;
							}?>
							<tr>
								<td class="textright"><?=$itr++?></td>
								<td class="textleft">
									<a href="#" onclick="javascript:ge('type').value=1; ge('centre').value='<?=$deal['centre']?>'; callDLReport(); return false;">
									<?=$deal['centre']?>
									</a>
								</td>
								<?
									$x = 1;
									foreach($columns as $c){?>
									<td class="textright">
										<a href="#" onclick="javascript:ge('type').value=2; ge('centre').value='<?=$deal['centre']?>'; ge('hpdt').value = <?=$x?>; callDLReport(); return false;">
										<?=($deal[$c]==0 ? '-' : nf($deal[$c]))?></a>
									</td>

									<?$totals[$c] += $deal[$c];
									$x++;
									}?>
							</tr>
						<?}
						break;

					case 1:
						$itr = 1; $oldCentre ="";
						$totals = array();
						foreach($columns as $c){
							$totals[$c] = 0;
						}

						foreach ($list as $deal){
							foreach($columns as $c){
								if(!isset($row[$c]))
									$row[$c] = 0;
							}?>
							<tr>
								<td class="textright"><?=$itr++?></td>
								<td class="textleft">
									<?=($oldCentre != $deal['centre'] ? $deal['centre'] : '' )?>
								</td>
								<td class="textleft <?=($deal['active'] != 2 ? 'red' : '')?>"><?=$deal['brkrnm']?><?=($deal['active']!=2 ? " [INACTIVE] ": "")?></td>
								<? $x =1;
								foreach($columns as $c){?>
									<td class="textright"><a href="#" onclick="javascript:ge('type').value=2; ge('centre').value='<?=$deal['centre']?>'; ge('hpdt').value = <?=$x?>; ge('brkrid').value = <?=$deal['brkrid']?>; callDLReport(); return false;">
									<?=($deal[$c]==0 ? '-' : nf($deal[$c]))?>
									</a></td>
								<?	$totals[$c] += $deal[$c];
									$x++;
								}?>
							</tr>
							<?$oldCentre = $deal['centre'];
						}
					break;
					case 2:
						$slNo = 1;
						$totals = array();
						foreach ($list as $deal){?>
							<tr>
								<td class="textright"><?=$slNo++?></td>
								<td class="textright"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=$deal['dealno']?></a></td>
								<td class="textleft"><a target="_blank" href='?task=deal&dealid=<?=$deal['dealid']?>'><?=$deal['dealnm']?></a></td>
								<td class="textright"><?=df($deal['hpdt'])?></td>
								<td class="textleft"><?=$deal['centre']?></td>
								<td class="textleft"><?=$deal['dealer']?></td>
								<td class="textleft"><?=$deal['salesmannm']?></td>
							</tr>
						<?}
					break;
				}?>
            	</tbody>

				<?switch($type){
					case 0:?>
						<tfoot>
						<tr class='b'>
							<th class="textright"></th>
							<th class="textright">Total</th>
							<? $x = 1;
								foreach($columns as $c){?>
									<th class="textright">
									<a href="#" onclick="javascript:ge('type').value=2;
									ge('hpdt').value = <?=$x?>;
									callDLReport(); return false;">
									<?=nf($totals[$c])?>
									</a>
									</th>
							<? $x++;
							}?>
						</tr>
						</tfoot>
						<?break;
					case 1:?>
						<tfoot>
						<tr class='b'>
							<th class="textright"></th>
							<th class="textright">Total</th>
							<?if($type==1){?><th></th><?}?>
							<? $x = 1;
								foreach($columns as $c){?>
									<th class="textright">
									<a href="#" onclick="javascript:ge('type').value=2;
									ge('hpdt').value = <?=$x?>;
									callDLReport(); return false;">
									<?=nf($totals[$c])?>
									</a>
									</th>
							<? $x++;
							}?>
						</tr>
						</tfoot>
						<?break;
					case 2:
						break;
					}?>
	            </table>
			<?}
			else{
			   $colspan=9;?>
			   <table class="adminlist" cellspacing="1" width="100%">
				   <tfoot>
					   <tr>
						   <td colspan="<?=$colspan?>">
							   No Records found!
						   </td>
					   </tr>
				   </tfoot>
			   </table>
			<?}?>
            </form>
            <div class="legend"><b>Lengends</b>
            <div class="red"><b>Employee Left Oraganization</b></div>
            <div class="bg_grey"><b>Team Leader</b></div>
            <div class="clr"></div>
            </div>
        </div>
        <!--div class="b"><div class="b"><div class="b"></div></div></div-->
    </div>
<? return;
}?>
