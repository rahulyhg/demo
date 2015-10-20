<?
if(!isset($task) || $task != "show_receipts"){
    echo "Invalid Access";
    die();
}
else show_receipts();


function show_receipts(){
	$dbPrefix = $_SESSION['DB_PREFIX'];
	$dealid = isset($_REQUEST['dealid'])? $_REQUEST['dealid'] : 0;
	if(isset($_REQUEST['dealno'])){
		$dealno = str_pad($_REQUEST['dealno'],6,"0", STR_PAD_LEFT);
		$q = "select dealid from ".$dbPrefix.".tbmdeal where dealno = '$dealno'";
		$dealid = executeSingleSelect($q);
	}

	$q = "";

	for ($d =2008; $d <= date('Y'); $d++){
		$q .="
		SELECT DATE_FORMAT(t1.rcptdt, '%d-%m-%Y') as Date, round(sum(t2.rcptamt)) as Received, t1.rcptid, t1.CBFlg, t1.CBCCLFlg, t1.CCLflg, t1.rcptpaymode, DATE_FORMAT(t1.cbdt, '%d-%m-%Y') as cbdt, DATE_FORMAT(t1.ccldt, '%d-%m-%Y') as  ccldt, DATE_FORMAT(t1.cbccldt, '%d-%m-%Y') as cbccldt, t1.rmrk as Remarks,
	sum(case when dctyp = 101 then round(t2.rcptamt) ELSE 0 END) as EMI,
	sum(case when dctyp = 102 then round(t2.rcptamt) ELSE 0 END) as Clearing,
	sum(case when dctyp = 103 then round(t2.rcptamt) ELSE 0 END) as CB,
	sum(case when dctyp = 104 then round(t2.rcptamt) ELSE 0 END) as Penalty,
	sum(case when dctyp = 105 then round(t2.rcptamt) ELSE 0 END) as Seizing,
	sum(case when dctyp = 107 then round(t2.rcptamt) ELSE 0 END) as Other,
	sum(case when dctyp = 111 then round(t2.rcptamt) ELSE 0 END) as CC
	FROM lksa".$d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT).".tbxdealrcpt t1 join lksa".$d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT).".tbxdealrcptdtl t2 on t1.rcptid = t2.rcptid and t1.dealid = $dealid  group by t1.rcptid
	UNION";
	}
	$q = rtrim($q, "UNION");

	$i=1;

	$totalRows =0;
	$t1 = executeSelect($q);
	if($t1['row_count'] > 0){
		$deals = $t1['r'];
		$totalRows = $t1['found_rows'];
	}
	?>

	<div id="element-box">
	    <div class="m">
            <table class="adminlist" cellspacing="1" width="100%" id="ls-content-box">
            <thead>
                <tr>
                    <th class="textleft">#</th>
                    <th class="textright">Id</th>
                    <th class="textright">Date</th>
                    <th class="textright">CB</th>
                    <th class="textright">CB Date</th>

                    <th class="textright">CCL</th>
                    <th class="textright">CCL Date</th>

                    <th class="textright">CBCCL</th>
                    <th class="textright">CBCCL Date</th>

                    <th class="textright">Total</th>
                    <th class="textright">Bank EMI</th>
                    <th class="textright">CC</th>
                    <th class="textright">Clearing</th>
                    <th class="textright">Bouncing</th>
                    <th class="textright">Penalty</th>
                    <th class="textright">Seizing</th>
                    <th class="textright">Other</th>
                    <th class="textright">Pay Mode</th>
                    <th class="textright">Remarks</th>
				</tr>
			</thead>
			<?if($totalRows > 0){?>
			<tbody>
				<?
				$total = array("Received"=>0,"EMI"=>0,"CC"=>0,"Clearing"=>0,"CB"=>0,"Penalty"=>0,"Seizing"=>0,"Other"=>0);
				foreach($deals as $deal){?>
				<tr <?=($deal['CBFlg'] == -1 || $deal['CCLflg']==-1 ? "class='red'": '')?>>
                    <td class="textleft"><?=$i++?></td>
                    <td class="textright"><?=$deal['rcptid']?></td>
                    <td class="textright"><?=$deal['Date']?></td>
                    <td class="textright"><?=$deal['CBFlg']?></td>
                    <td class="textright"><?=$deal['cbdt']?></td>

                    <td class="textright"><?=$deal['CCLflg']?></td>
                    <td class="textright"><?=$deal['ccldt']?></td>

                    <td class="textright"><?=$deal['CBCCLFlg']?></td>
                    <td class="textright"><?=$deal['cbccldt']?></td>

                    <td class="textright"><?=nf($deal['Received'])?></td>
                    <td class="textright"><?=nf($deal['EMI'])?></td>
                    <td class="textright"><?=nf($deal['CC'])?></td>
                    <td class="textright"><?=nf($deal['Clearing'])?></td>
                    <td class="textright"><?=nf($deal['CB'])?></td>
                    <td class="textright"><?=nf($deal['Penalty'])?></td>
                    <td class="textright"><?=nf($deal['Seizing'])?></td>
                    <td class="textright"><?=nf($deal['Other'])?></td>
                    <td class="textleft"><?=$_SESSION['PAY_MODE'][$deal['rcptpaymode']]?></td>
                    <td class="textleft"><?=$deal['Remarks']?></td>
				</tr>
				<?
					if($deal['CBFlg'] == 0 && $deal['CCLflg']== 0){
						foreach($total as $t=>$v){
							$total[$t] += $deal[$t];
						}
					}
				}?>
			</tbody>
			<tfoot>
				<tr>
                    <th class="textleft"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"></th>
                    <th class="textright"><?=nf($total['Received'])?></th>
                    <th class="textright"><?=nf($total['EMI'])?></th>
                    <th class="textright"><?=nf($total['CC'])?></th>
                    <th class="textright"><?=nf($total['Clearing'])?></th>
                    <th class="textright"><?=nf($total['CB'])?></th>
                    <th class="textright"><?=nf($total['Penalty'])?></th>
                    <th class="textright"><?=nf($total['Seizing'])?></th>
                    <th class="textright"><?=nf($total['Other'])?></th>
                    <th class="textleft"></th>
                    <th class="textleft"></th>
				</tr>
			</tfoot>
		<?}else{?>
			<tfoot><tr><td colspan=20>No receipts found for this deal</td></tr></tfoot>
		<?}?>
		</table>
		</div>
	</div>

	<?
}
?>
