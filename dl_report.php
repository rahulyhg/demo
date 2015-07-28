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

    //$month = isset($_REQUEST['month']) && !empty($_REQUEST['month']) ? $_REQUEST['month'] : date('mY');
    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
    $brkrid = isset($_REQUEST['brkrid']) ? $_REQUEST['brkrid'] : 0;
	$zero = isset($_REQUEST['zeroDeals']) ? $_REQUEST['zeroDeals'] : 0;
	$active = isset($_REQUEST['active']) ? $_REQUEST['active'] : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;

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

	$columns = array();
	for ($i=0; $i < $columnMonths; $i++){
		$columns[] = date('M-y',strtotime('-'.($i).' months'));
	}
	for ($i=0; $i < $columnYears; $i++){
			if(date('n') > 3)
				$fy = date('y', strtotime('-'.($i).' years')).'-'.date('y', strtotime('-'.($i-1).' years'));
			else
				$fy = date('y', strtotime('-'.($i-1).' years')).'-'.date(y, strtotime('-'.($i).' years'));

			$columns[] = 'FY_'.$fy;
	}
	$columns[] = 'Total';

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

	$q = " SELECT tcase(s.centre) as centre, ".($type==1 ? " s.brkrid, s.brkrnm, s.active,  " : "" )."";
		for($i=0; $i < $columnMonths; $i++){
			$q .= " SUM(CASE WHEN hpdt BETWEEN '".date('Y-m-01', strtotime('-'.($i).' months'))."' AND '".date('Y-m-t',strtotime('-'.($i).' months'))."' THEN 1 ELSE 0 END) AS `".$columns[$c++]."`,
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

//	print_a($q);
//	die();

	$totalRows = 0;
	$t1 = executeSelect($q, ($type==0 ? 'centre' : 'brkrid'));
	if($t1['row_count'] > 0){
		$list = $t1['r'];
		$totalRows = count($list);
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
                <tr>
                    <th class="textleft">#</th>
                    <th nowrap="nowrap" class="textleft">Centre</th>
                    <?if($type==1){ ?><th nowrap="nowrap" class="textleft">Sales Executive</th><?}?>
                    <?
                    foreach($columns as $c){?>
	                    <th class="textleft"><?=$c?></th>
	              	<?}?>
		        </tr>
            </thead>
            <?if($totalRows > 0){?>
				<tbody>
				<?
					$itr = 1; $oldCentre ="";
					$totals = array();
					foreach($columns as $c){
						$totals[$c] = 0;
					}
					foreach ($list as $row){
						foreach($columns as $c){
							if(!isset($row[$c]))
								$row[$c] = 0;
						}?>
						<tr>
							<td class="textright"><?=$itr++?></td>
							<td class="textleft">
								<a href="#" onclick="javascript:ge('type').value=1; ge('centre').value='<?=$row['centre']?>'; callDLReport(); return false;">
								<?=($oldCentre != $row['centre'] ? $row['centre'] : '' )?>
								</a>
							</td>
							<?if($type==1){?>
								<td class="textleft <?=($row['active'] != 2 ? 'red' : '')?>"><?=$row['brkrnm']?><?=($row['active']!=2 ? " [INACTIVE] ": "")?></td><?}?>
							<?
							foreach($columns as $c){?>
								<td class="textright"><?=($row[$c]==0 ? '-' : nf($row[$c]))?></td>
							<?	$totals[$c] += $row[$c];
							}?>
						</tr>
						<?$oldCentre = $row['centre'];
					}?>
            	</tbody>
            	<tfoot>
				<tr class='b'>
					<th class="textright"></th>
					<th class="textright">Total</th>
					<?if($type==1){?><th></th><?}?>
						<?foreach($columns as $c){?>
								<th class="textright"><?=nf($totals[$c])?></th>
						<?}?>
				</tr>
				</tfoot>
	            </table>
  				<?}else{
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
         <? }?>
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
