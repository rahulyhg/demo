<?
if(!isset($task) || $task!="se_report"){
    echo "Invalid Access";
    die();
}
else se_report();
function se_report(){
    $dbPrefix = $_SESSION['DB_PREFIX'];
	$reportMonth = 13;
	$columnMonths = 4;//Including current month
	$columnYears = 3;//Including current month

	$fd = date('Y-M-01');

    //$month = isset($_REQUEST['month']) && !empty($_REQUEST['month']) ? $_REQUEST['month'] : date('mY');
    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
//    $state = isset($_REQUEST['state']) ? $_REQUEST['state'] : 0;

    $salesmanid = isset($_REQUEST['salesmanid']) ? $_REQUEST['salesmanid'] : 0;
	$zero = isset($_REQUEST['zeroDeals']) ? $_REQUEST['zeroDeals'] : 0;
	$active = isset($_REQUEST['active']) ? $_REQUEST['active'] : 0;
	$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 0;
	$region = isset($_REQUEST['region']) ? $_REQUEST['region'] : "";

    $q1 = "SELECT salesmanid, tcase(salesmannm) as salesmannm, active from ".$dbPrefix.".tbmsalesman where active != 3 and Department = 'SALES' order by active desc, salesmannm";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$salesmans = $t1['r'];
	}

    $q1 = "SELECT distinct region as region from ".$dbPrefix.".tbmcentre order by region asc";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$regions = $t1['r'];
	}

    //Get all centers
    $q1 = "SELECT tcase(centrenm) as centre from ".$dbPrefix.".tbmcentre order by centre";
	$t1 = executeSelect($q1);
	if($t1['row_count'] > 0){
		$centres = $t1['r'];
	}

	$columns = array();
	for ($i=0; $i < $columnMonths; $i++){
		$columns[] = date('Y-M',strtotime('-'.($i).' month', strtotime($fd)));
	}
	for ($i=0; $i < $columnYears; $i++){
			if(date('n') > 3)
				$fy = date('y', strtotime('-'.($i).' years')).'-'.date('y', strtotime('-'.($i-1).' years'));
			else
				$fy = date('y', strtotime('-'.($i-1).' years')).'-'.date(y, strtotime('-'.($i).' years'));

			$columns[] = 'FY_'.$fy;
	}
	$columns[] = 'Total';

	$salesman_clause = "";
	switch($salesmanid){
		case 0:
			break;
		case  -1:
			$salesman_clause = " AND s.active = 2";
			break;
		case -2:
			$salesman_clause = " AND s.active = 1";
			break;
		default:
			$salesman_clause = " AND s.salesmanid =  '$salesmanid' ";
			break;
	}

	$c = 0;

	$q = " SELECT tcase(c.region) as region, tcase(s.centre) as centre, ".($type==1 ? " s.salesmanid, tcase(s.salesmannm) as salesmannm, s.Role, s.active,  " : "" )."";
		for($i=0; $i < $columnMonths; $i++){
			$q .= " SUM(CASE WHEN hpdt BETWEEN '".date('Y-m-01', strtotime('-'.($i).' month', strtotime($fd))) ."' AND '".date('Y-m-t',strtotime('-'.($i).' month', strtotime($fd)))."' THEN 1 ELSE 0 END) AS `".$columns[$c++]."`,
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
		FROM ".$dbPrefix.".tbmsalesman s  JOIN ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix.".tbadealsalesman a
		ON d.dealid = a.dealid AND d.cancleflg=0 AND a.salesmanid = s.salesmanid ".$salesman_clause."
		JOIN lksa.tbmcentre c on s.centre = c.centrenm
		WHERE 1 ".($centre != "" ? " AND s.centre = '$centre' " : ""). "
		GROUP BY c.region, s.centre ".($type==1 ? ", s.salesmanid " : "")."";

	if($region != "")
		$q .=" having region = '$region' ";

	$q .=" Order by c.region, s.centre";

//	print_a($q);
//	die();

	$totalRows = 0;
	$t1 = executeSelect($q, ($type==0 ? 'centre' : 'salesmanid'));
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
                            <b>Type:</b> <select name="type" id="type" class="inputbox" size="1" onchange="callSEReport();">
                            	<option value=0 <? if($type ==0){?> selected="selected" <? }?>>Centre Wise</option>
                            	<option value=1 <? if($type ==1){?> selected="selected" <? }?>>Executive Wise</option>
                            </select>
                        </td>
                        <td nowrap="nowrap">
							<select name="region" id="region" class="inputbox" size="1" onchange="callSEReport();">
                            <option value="" <? if($region ==""){?> selected="selected" <? }?>>- All Regions-</option>
								<? // Populate Dropdown with values of Region field
								foreach($regions as $c1){?>
									<option value="<?=$c1['region']?>" <?if($region==$c1['region']){?> selected="selected" <?}?>><?=$c1['region']?></option>
								<?}?>
                            </select>

                            <select name="centre" id="centre" class="inputbox" size="1" onchange="callSEReport();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres-</option>
								<? // Populate Dropdown with values of Centre field
								foreach($centres as $c1){?>
									<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
								<?}?>
                            </select>

							<select name="salesmanid" id="salesmanid" class="inputbox" size="1" onchange="callSEReport();">
                            <option value="0" <? if($salesmanid == 0){?> selected="selected" <? }?>>- All Executives-</option>
                            <option value="-1" <? if($salesmanid == -1){?> selected="selected" <? }?>><b>- Active Executive Only-</b></option>
                            <option value="-2" <? if($salesmanid == -2){?> selected="selected" <? }?>>- Inactive Executive Only-</option>
                         		<? // Populate Dropdown with values of Salesman field
                         		foreach($salesmans as $s1){?>
                         			<option value="<?=$s1['salesmanid']?>" <? if($salesmanid==$s1['salesmanid']){?> selected="selected" <? }?>><?=$s1['salesmannm']?><?=($s1['active']==1 ? ' (DC)' : '')?></option>
                         		<?}?>
                            </select>
							<!--a onclick="PrintElement('ls-content-box');">Print</a-->
                            <!---- From & To Date for HP Dates -->
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table class="adminlist" cellspacing="1" width="100%" id="ls-content-box">
            <thead>
                <tr>
                    <th class="textleft">#</th>
                    <th nowrap="nowrap" class="textleft">Region</th>
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
					$itr = 1; $oldCentre =""; $oldRegion ="";
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
								<?=($oldRegion != $row['region'] ? '<b>'.$row['region'].'</b>' : $row['region'] )?>
							</td>
							<td class="textleft">
								<a href="#" onclick="javascript:document.adminForm.type.value=1; document.adminForm.centre.value='<?=$row['centre']?>'; callSEReport(); return false;">
								<?=($oldCentre != $row['centre'] ? '<b>'.$row['centre'].'</b>' : $row['centre'] )?>
								</a>
							</td>
							<?if($type==1){?>
								<td class="textleft <?=($row['active'] != 2 ? 'red' : '')?>"><?=$row['salesmannm']?><?=($row['active']!=2 ? " [INACTIVE] ": "")?></td><?}?>
							<?
							foreach($columns as $c){?>
								<td class="textright"><?=($row[$c]==0 ? '-' : nf($row[$c]))?></td>
							<?	$totals[$c] += $row[$c];
							}?>
						</tr>
						<?$oldCentre = $row['centre']; $oldRegion = $row['region'];
					}?>
            	</tbody>
            	<tfoot>
				<tr class='b'>
					<th class="textright"></th>
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


























	if($type == 0){
		$currMonth = $mnth;
		$currYear = $year;
		for($m = 0; $m < $columnMonths; $m++){
			$query[$m] = "SELECT  s.centre, s.salesmanid, s.salesmannm, s.active, s.Role, COUNT(d.dealid) AS cases_$m
			FROM ".$dbPrefix.".tbmsalesman s ".($zero == 1 ? "LEFT" : "")." JOIN ".$dbPrefix.".tbmdeal d JOIN  ".$dbPrefix.".tbadealsalesman a
				ON d.dealid = a.dealid AND d.cancleflg=0 AND MONTH(d.hpdt) = ".$currMonth." AND YEAR(hpdt) =".$currYear."
				".($zero == 1 ? "ON" : "AND")." a.salesmanid = s.salesmanid AND s.department = 'SALES'
				WHERE 1 ". ($salesmanid !=0 ? " AND s.salesmanid =  '$salesmanid' " : "")."".($centre != "" ? " AND s.centre = '$centre' " : ""). "
				GROUP BY s.centre, s.Role, s.Salesmanid ORDER BY s.centre, s.role DESC, s.Salesmannm";
				$currMonth -= 1;
				if($currMonth == 0){
					$currMonth = 12;
					$currYear = $currYear - 1;
				}
		}
	} else if($type == 1){
			$fy = ""; $last_fy = "";

			if(date('n') < 4){ //lastyear-thisyear
				$fy = date('y',  strtotime('-1 year'))."-".date('y');
				$last_fy = date('y',  strtotime('-2 year'))."-".date('y',  strtotime('-1 year'));
			}
			else {//thisyear-nextyear
				$fy = date('y')."-".date('y',  strtotime('+1 year'));
				$last_fy = date('y',  strtotime('-1 year'))."-".date('y');
			}

			$q = "
			SELECT s.centre,
			SUM(CASE WHEN hpdt BETWEEN '2015-05-01' AND '2015-05-31' THEN 1 ELSE 0 END) AS `May-15`,
			SUM(CASE WHEN hpdt BETWEEN '2015-04-01' AND '2015-04-30' THEN 1 ELSE 0 END) AS `Apr-15`,
			SUM(CASE WHEN hpdt BETWEEN '2015-03-01' AND '2015-03-31' THEN 1 ELSE 0 END) AS `Mar-15`,
			SUM(CASE WHEN fy='15-16' THEN 1 ELSE 0 END) AS `FY_15-16`,
			SUM(CASE WHEN fy='14-15' THEN 1 ELSE 0 END) AS `FY_14-15`,
			SUM(CASE WHEN fy='13-14' THEN 1 ELSE 0 END) AS `FY_13-14`,
			SUM(CASE WHEN fy='12-13' THEN 1 ELSE 0 END) AS `FY_12-13`,
			SUM(CASE WHEN fy='11-12' THEN 1 ELSE 0 END) AS `FY_11-12`,
			SUM(CASE WHEN fy='10-11' THEN 1 ELSE 0 END) AS `FY_10-11`,
			SUM(CASE WHEN fy='09-10' THEN 1 ELSE 0 END) AS `FY_09-10`,
			SUM(CASE WHEN fy='08-09' THEN 1 ELSE 0 END) AS `FY_08-09`,
			COUNT(d.dealid) AS total
			FROM ".$dbPrefix.".tbmsalesman s  JOIN ".$dbPrefix.".tbmdeal d JOIN ".$dbPrefix.".tbadealsalesman a
			WHERE 1 ". ($salesmanid !=0 ? " AND s.salesmanid =  '$salesmanid' " : "")."".($centre != "" ? " AND s.centre = '$centre' " : ""). "
			ON d.dealid = a.dealid AND d.cancleflg=0 AND a.salesmanid = s.salesmanid GROUP BY s.centre ORDER BY total DESC";
			print_a($q);
	} else if($type == 2){
		$fy = ""; $last_fy = "";

		if(date('n') < 4){ //lastyear-thisyear
			$fy = date('y',  strtotime('-1 year'))."-".date('y');
			$last_fy = date('y',  strtotime('-2 year'))."-".date('y',  strtotime('-1 year'));
		}
		else {//thisyear-nextyear
			$fy = date('y')."-".date('y',  strtotime('+1 year'));
			$last_fy = date('y',  strtotime('-1 year'))."-".date('y');
		}

		for($m = 0; $m < $columnMonths; $m++){
			$query[$m] = "SELECT  s.centre, s.salesmanid, s.salesmannm, s.active, s.Role, COUNT(d.dealid) AS cases_".$m."
				FROM ".$dbPrefix.".tbmsalesman s ".($zero == 1 ? "LEFT" : "")." JOIN ".$dbPrefix.".tbmdeal d JOIN  ".$dbPrefix.".tbadealsalesman a
					ON d.dealid = a.dealid AND d.cancleflg=0 AND d.fy = '".$fy."'
					".($zero == 1 ? "ON" : "AND")." a.salesmanid = s.salesmanid AND s.department = 'SALES'
					WHERE 1 ". ($salesmanid !=0 ? " AND s.salesmanid =  '$salesmanid' " : "")."".($centre != "" ? " AND s.centre = '$centre' " : ""). "
					GROUP BY s.centre, s.Role, s.Salesmanid ORDER BY s.centre, s.role DESC, s.Salesmannm";

			$currMonth -= 1;
			if($currMonth == 0){
				$currMonth = 12;
				$currYear = $currYear - 1;
			}
		}
	}

//	print_a($query);

	for($m = 0; $m < count($query); $m++){
		$monthWise[$m] = array();
		$t1 = executeSelect($query[$m], "salesmanid");
		if($t1['row_count'] > 0)
			$monthWise[$m] = $t1['r'];
		if($m == 0)
			$list = $monthWise[$m];
		else
			$list = array_replace_recursive($list, $monthWise[$m]);
	}

	$totalRows = count($list);

    ?>
    <div id="element-box">
        <!--div class="t"><div class="t"><div class="t"></div></div></div-->
        <div class="m">
            <div id="blanket" style="display:none;"></div>
            <div id="popUpDiv" style="display:none;"></div>
            <form method="post" name="adminForm" onSubmit="return false;">
            <table>
                <tbody>
                    <tr>
                        <td align="left" width="100%">
                            <b>Type:</b> <select name="type" id="type" class="inputbox" size="1" onchange="callODReport();">
                            	<option value=0 <? if($type ==0){?> selected="selected" <? }?>>Sales Report - Centre Wise</option>
                            	<option value=1 <? if($type ==1){?> selected="selected" <? }?>>Sales Report - Executive Wise</option>
                            </select>
                        </td>
                        <td nowrap="nowrap">
                            <select name="zeroDeals" id="zeroDeals" class="inputbox" size="1" onchange="callSEReport();">
                           		<option value="0" <? if($zero == 0){?> selected="selected" <?}?>>Hide 0 Cases</option>
                           		<option value="1" <? if($zero == 1){?> selected="selected" <?}?>>Show All Cases</option>
                            </select>

                            <select name="month" id="month" class="inputbox" size="1" onchange="callSEReport();">
							<?	for($m=0; $m<= $reportMonth;$m++){
									$mStr = date('M Y', strtotime(-$m." month"));
									$mth = date('mY', strtotime(-$m." month"));
								?>
                            		<option value="<?=$mth?>" <? if($month==$mth){?> selected="selected" <?}?>><?=$mStr?></option>
                            	<?}?>
                            </select>

                            <select name="centre" id="centre" class="inputbox" size="1" onchange="callSEReport();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres-</option>
								<? // Populate Dropdown with values of Centre field
								foreach($centres as $c1){?>
									<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
								<?}?>
                            </select>

							<select name="salesmanid" id="salesmanid" class="inputbox" size="1" onchange="callSEReport();">
                            <option value="0" <? if($salesmanid == 0){?> selected="selected" <? }?>>- All Executives-</option>
                         		<? // Populate Dropdown with values of Salesman field
                         		foreach($salesmans as $s1){?>
                         			<option value="<?=$s1['salesmanid']?>" <? if($salesmanid==$s1['salesmanid']){?> selected="selected" <? }?>><?=$s1['salesmannm']?><?=($s1['active']==1 ? ' (DC)' : '')?></option>
                         		<?}?>
                            </select>
							<!--a onclick="PrintElement('ls-content-box');">Print</a-->
                            <!---- From & To Date for HP Dates -->
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
                    <th nowrap="nowrap" class="textleft">Sales Executive</th>
                    <?
                    for($m=0; $m<$columnMonths; $m++){?>
	                    <th class="textleft"><?=date('M-y',mktime(0,0,0,$mnth-$m,10,$year))?> Cases</th>
	              	<?}?>
		        </tr>
            </thead>
            <? if($totalRows>0){?>
                    <tbody>
                        <?
                            $itr = 1; $oldCentre ="";
                            $totals = array();
                            for($m=0; $m<count($query); $m++){
                            	$totals["cases_$m"] = 0;
                            }

                            foreach ($list as $row){
                            	for($m=0; $m<count($query); $m++){
                            		if(!isset($row["cases_$m"]))
                            			$row["cases_$m"] = 0;
                            	}
                            ?>
								<tr>
									<td class="textright"><?=$itr++?></td>
									<td class="textleft"><?=($oldCentre != $row['centre'] ? $row['centre'] : '' )?></td>
									<td class="textleft <?=($row['Role']!= '1-SALESEXEC' ? 'b bg_grey' : '')?> <?=($row['active'] != 2 ? 'red' : '')?>"><?=$row['salesmannm']?></td>
									<?
									for($m=0; $m<count($query); $m++){?>
										<td class="textright"><?=nf($row["cases_$m"])?></td>
									<?	$totals["cases_$m"] += $row["cases_$m"];
									}?>
								</tr>
                            <?
                            	$oldCentre = $row['centre'];
                            }

                        if($totalRows==0){?>
                            <tr>
                                <td colspan="<?=$colspan?>" align="center">
                                    No Records found!
                                </td>
                            </tr>
                     <? }?>
                        </tbody>
                    <tfoot>
						<tr class='b'>
							<th class="textright" colspan=3>Grand Total</th>
							<?
							for($m=0; $m<count($query); $m++){?>
								<th class="textright"><?=nf($totals["cases_$m"])?></th>
							<?}
							?>
						</tr>

                    </tfoot>
                    </table>
                    <input name="task" value="" type="hidden">
                    <input name="boxchecked" value="0" type="hidden">
                    <input name="filter_order" value="m.position" type="hidden">
                    <input name="filter_order_Dir" value="" type="hidden">
                    <input name="3b4e25d51768ab0929c5d0dfc65f8b1c" value="1" type="hidden">
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
<?
}
?>
