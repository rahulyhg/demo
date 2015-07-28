<?
if(!isset($task) || $task != "deallist"){
    echo "Invalid Access";
    die();
}
else deallist();


function deallist(){
    $dbPrefix = $_SESSION['DB_PREFIX'];
    $DEFAULT_SORT  = 'rid'; $DEFAULT_SORT_TYPE = 'desc';
//    print_a($_REQUEST);
	$fy = ""; $last_fy = "";
	if(date('n') < 4){ //lastyear-thisyear
		$fy = date('y',  strtotime('-1 year'))."-".date('y');
		$last_fy = date('y',  strtotime('-2 year'))."-".date('y',  strtotime('-1 year'));
	}
	else {//thisyear-nextyear
		$fy = date('y')."-".date('y',  strtotime('+1 year'));
		$last_fy = date('y',  strtotime('-1 year'))."-".date('y');
	}

    $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : "";

    $centre = isset($_REQUEST['centre']) ? $_REQUEST['centre'] : "";
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 0;
    $salesmanid = isset($_REQUEST['salesmanid']) ? $_REQUEST['salesmanid'] : 0;
    $period = isset($_REQUEST['period']) ? $_REQUEST['period'] : 0;

//    $fromdt = isset($_REQUEST['fromdt']) ? $_REQUEST['fromdt'] : "";
//    $todt = isset($_REQUEST['todt']) ? $_REQUEST['todt'] : "";

    $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $_SESSION['ROWS_IN_TABLE'];
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $sval = isset($_REQUEST['sval']) ? (empty($_REQUEST['sval']) ? $DEFAULT_SORT : $_REQUEST['sval'] ) : $DEFAULT_SORT;
    $stype = isset($_REQUEST['stype']) ? (empty($_REQUEST['stype']) ? $DEFAULT_SORT_TYPE : $_REQUEST['stype'] ) : $DEFAULT_SORT_TYPE;

	$from = ($limit * ($page - 1));
    $till = ($limit + $from);

    $q1 = "SELECT salesmanid, salesmannm, active from ".$dbPrefix.".tbmsalesman where active != 3 and Department ='SALES' order by active desc, salesmannm";
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

   	$period_options = array(
   		array("Today & Yesterday", " AND (date(hpdt) = date(curdate()) OR date(hpdt) = date(curdate()-1) ) "),
   		array("Today", " AND date(hpdt) = date(curdate()) "),
   		array("Yesterday", " AND date(hpdt) = date(curdate()-1) "),
	   	array("This Week", " AND hpdt >= '".date('Y-m-d',strtotime('monday this week'))."' "),
//		array("This Quarter", " AND hpdt >= MAKEDATE(YEAR(NOW()),1) + INTERVAL QUARTER(NOW())-1 QUARTER AND hpdt < MAKEDATE(YEAR(NOW()),1) + INTERVAL QUARTER(NOW())-0 QUARTER "),
   		array(date('Y-M'), " AND month(hpdt) = month(curdate()) and year(hpdt) = year(curdate()) "),
   		array(date('Y-M',strtotime('-1 month')), " AND hpdt >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01' ) AND hpdt < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01') "),
   		array(date('Y-M',strtotime('-2 months')), " AND hpdt >= DATE_FORMAT(CURRENT_DATE - INTERVAL 2 MONTH, '%Y/%m/01' ) AND hpdt < DATE_FORMAT( CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01') "),
   		array(date('Y-M',strtotime('-3 months')), " AND hpdt >= DATE_FORMAT(CURRENT_DATE - INTERVAL 3 MONTH, '%Y/%m/01' ) AND hpdt < DATE_FORMAT( CURRENT_DATE - INTERVAL 2 MONTH, '%Y/%m/01') "),
// 		array("Last 2 Months", " AND hpdt >= DATE_FORMAT(CURRENT_DATE - INTERVAL 2 MONTH, '%Y/%m/01' ) AND hpdt < DATE_FORMAT( CURRENT_DATE, '%Y/%m/01') "),


// 		array("Last Quarter", " AND hpdt >= MAKEDATE(YEAR(NOW()),1) + INTERVAL QUARTER(NOW())-2 QUARTER AND hpdt < MAKEDATE(YEAR(NOW()),1) + INTERVAL QUARTER(NOW())-1 QUARTER "),
   		array("Full Year: ".date('Y'), " AND year(hpdt) = year(curdate()) "),
   		array("Full Year: ".(date('Y')-1), " AND year(hpdt) = year(curdate())-1 "),
   		array("FY: $fy", " AND FY='$fy' "),
   		array("FY: $last_fy", " AND FY='$last_fy' "),
   	);

	$status_options = array(
		array("- Select Status -"," And cancleflg = 0 "),
		array("Active"," AND d.dealsts = '$status' and d.cancleflg = 0 "),
		array("Draft"," AND d.dealsts = '$status'  and d.cancleflg = 0 "),
		array("Closed"," AND d.dealsts = '$status' and d.cancleflg = 0 "),
		array("Cancelled"," AND d.cancleflg = -1 "),
		array("Insurance & Seized", "AND d.active = 4 and d.dealsts != 3 "),
	);

    //Get List of all valid deals for centre for selected date period


    $q = "SELECT sql_calc_found_rows d.pkid, d.pkid as rid, d.dealid, d.dealno, d.dealsts, d.dealnm, d.area, d.city as city, d.cancleflg, d.hpdt, d.financeamt, s.salesmanid as salesmanid, s.salesmannm as salesmannm, tcase(d.centre) as centre, s.active from ".$dbPrefix.".tbmdeal d join ".$dbPrefix.".tbadealsalesman a join ".$dbPrefix.".tbmsalesman s ";

    $q .= " on d.dealid = a.dealid and a.salesmanid = s.salesmanid ";

    $q .= " where 1 ";

	if($centre != "")
		$q .= " AND d.centre = '$centre' ";

	if($salesmanid != 0)
		$q .= " AND s.salesmanid = '$salesmanid' ";

	$q .= $status_options[$status][1];

    if($search!=""){
		if(is_numeric($search)){
			if(strlen($search) < 6)
				$search = str_pad($search, 6, "0", STR_PAD_LEFT);
			$q .= " AND (d.dealno = '$search' or d.dealid = $search)";
		}
		else
			$q .= " AND (d.dealnm like '%$search%')";
    }
    else {
		$q .= $period_options[$period][1];

  //  	if($fromdt!="" && $todt !="")
  //  		$q .= " AND hpdt >= '$fromdt' AND hpdt <= '$todt' ";
	}
    $q .= " order by $sval $stype, rid desc limit $from, $limit";

 	print_a($q);

	$totalRows =0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$deals = $t1['r'];
		$totalRows = $t1['found_rows'];
	}

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
                        <td align="left" width="100%">Search:
                            <input name="search" id="search" class="text_area" onchange="callListOfDeals();" type="text" value="<?=$search?>">
                            <!--button onclick="callListOfDeals();">Go</button-->
                            <button onclick="ge('search').value=''; callListOfDeals();">Reset</button>
                        </td>
                        <td nowrap="nowrap">
                            <select name="period" id="period" class="inputbox" size="1" onchange="callListOfDeals();">
                            <?
                            	$i=0;
                            	foreach ($period_options as $p){?>
                            		<option value="<?=$i?>" <?if($period==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
                            </select>

                            <select name="status" id="status" class="inputbox" size="1" onchange="callListOfDeals();">
                            <?
                            	$i=0;
                            	foreach ($status_options as $p){?>
                            		<option value="<?=$i?>" <?if($status==$i){?> selected="selected" <? }?>><?=$p[0]?></option>
								<? $i++;
								}?>
                            </select>

                            <select name="centre" id="centre" class="inputbox" size="1" onchange="callListOfDeals();">
                            <option value="" <? if($centre ==""){?> selected="selected" <? }?>>- All Centres-</option>
                         	<? // Populate Dropdown with values of Centre field
                         	foreach($centres as $c1){?>
                         		<option value="<?=$c1['centre']?>" <? if($centre==$c1['centre']){?> selected="selected" <? }?>><?=$c1['centre']?></option>
                         	<?}?>
                            </select>

                            <select name="salesmanid" id="salesmanid" class="inputbox" size="1" onchange="callListOfDeals();">
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
                    <th nowrap="nowrap" class="textleft"><a href="javascript:sort('dealsts'); callListOfDeals();">Status</a></th>
                    <th nowrap="nowrap" class="textleft"><a href="javascript:sort('dealno'); callListOfDeals();">Deal #</a></th>
                    <th nowrap="nowrap" class="textleft"><a href="javascript:sort('dealnm'); callListOfDeals();">Name</a></th>
                    <th nowrap="nowrap" class="textleft">City</th>
                    <th nowrap="nowrap" class="textleft">Area</th>
                    <th nowrap="nowrap" class="textleft"><a href="javascript:sort('centre'); callListOfDeals();">Centre</a></th>
                    <th nowrap="nowrap" class="textleft"><a href="javascript:sort('financeamt'); callListOfDeals();">Finance</a></th>
                    <th nowrap="nowrap" class="textleft"><a href="javascript:sort('salesmannm'); callListOfDeals();">Executive</a></th>
					<th nowrap="nowrap" class="textleft"><a href="javascript:sort('hpdt'); callListOfDeals();">HP Date</a></th>
		        </tr>
            </thead>
            <? if($totalRows>0){
                        $totalPages = ceil($totalRows/$limit);
                    ?>
                    <tfoot>
                        <tr>
                        <? $colspan=15;?>
                            <td colspan="<?=$colspan?>"><del class="container"><div class="pagination">
                                <? $limitarray = array("5","10","15","20","25","30","50","100","200","500");?>
                                <div class="limit">Display #<select name="limit" id="limit" class="inputbox" size="1" onchange="callListOfDeals();">
                                <? for($i=0; $i<count($limitarray); $i++){?>
                                <option value="<?=$limitarray[$i]?>" <? if($limit==$limitarray[$i]){?>selected="selected" <? }?>><?=$limitarray[$i]?></option><? }?>
                                <option value="18446744073709551615" <? if($limit==18446744073709551615){?>selected="selected" <? }?>>All</option></select></div>
                                <? if($page<=1){ $classvalright="button2-right off"; }else{  $classvalright="button2-right"; }
                                if($page>=$totalPages){ $class_left="button2-left off"; }else{  $class_left="button2-left"; } ?>
                                <div class="<?=$classvalright?>"><div class="start"><? if($page<=1){?><span>Start</span><? }else{?><a href="#" title="First" onclick="javascript: ge('page').value=1; callListOfDeals(); return false;">Start</a><? }?></div></div>
                                <div class="<?=$classvalright?>"><div class="prev"><? if($page<=1){?><span>Prev</span><? }else{?><a href="#" title="Previous" onclick="javascript: ge('page').value=<?=($page-1)?>; callListOfDeals();return false;">Prev</a><? }?></div></div>
                                <div class="button2-left"><div class="page"><span><?=$page?></span></div></div>
                                <div class="<?=$class_left?>"><div class="next"><? if($page>=$totalPages){?><span>Next</span><? }else{?><a href="#" title="Next" onclick="javascript: ge('page').value=<?=($page+1)?>; callListOfDeals();return false;">Next</a><? }?></div></div>
                                <div class="<?=$class_left?>"><div class="end"><? if($page>=$totalPages){?><span>End</span><? }else{?><a href="#" title="Last" onclick="javascript: ge('page').value=<?=$totalPages?>; callListOfDeals(); return false;">End</a><? }?></div></div>
                                <div class="limit">Page <?=$page?> of <?=$totalPages?> (Total:<?=$totalRows?>)</div>
                                </div></del>
                            </td>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?
                        	$slNo = ($from + 1); $total = 0;
                            foreach ($deals as $deal){?>
								<tr>
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
									<td class="textleft"><?=$deal['city']?></td>
									<td class="textleft"><?=$deal['area']?></td>
									<td class="textleft"><?=$deal['centre']?></td>
									<td class="textright"><?=nf($deal['financeamt'])?></td>
									<td class="textleft <?=($deal['active']==1 ? 'red' :'')?>"><?=$deal['salesmannm']?></td>
									<td class="textright"><? echo date('Y-m-d',strtotime($deal['hpdt']));?></td>
								</tr>
                            	<?
                            	$total +=  $deal['financeamt'];
                            }?>
						</tbody>
						<tfoot>
						<?
                        if($totalRows==0){?>
                            <tr>
                                <td colspan="<?=$colspan?>" align="center">
                                    No Records found!
                                </td>
                            </tr>
                     	<?}
                     	else{?>
                            <tr>
								<th></th>
								<th></th>
								<th></th>
								<th>Total (Shown rows only)</th>
								<th></th>
								<th></th>
								<th></td>
								<th class="textright"><?=nf($total, true)?></th>
								<th></th>
								<th></th>
                            </tr>
                     	<?}?>
						</tfoot>
                    </table>
          <?}else{
                   $colspan=10;?>
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
			<input name="page" id="page" value="<?=$page?>" type="hidden">
			<input name="sval" id="sval" value="<?=$sval?>" type="hidden">
			<input name="stype" id="stype" value="<?=$stype?>" type="hidden">
            </form>
            <!--div class="legend"><b>Lengends</b><br><b> NA / - :</b> Not Applicable / Not Attempted</div><div class="legend"><b>In Progress</b> Not Submitted</div><div class="legend"><b>Number:</b> Click to see results</div-->
            <div class="clr"></div>
        </div>
        <!--div class="b"><div class="b"><div class="b"></div></div></div-->

    </div>
<?}?>