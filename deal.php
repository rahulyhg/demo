<?
if(!isset($task) || $task!="deal"){
    echo "Invalid Access";
    die();
}
else deal();

function printScheduleFirstHalf($row, $dt, $rowCount, $emiCount){?>
	<tr <?=(($rowCount)%2==0 ? "class='bg_aliceblue'" : '')?>>
		<td class='textright'><?=($row['source'] == 1 ? $emiCount : '')?></td>
		<td class='dt textright'><?=$row['dDate']?><?=( $dt != $row['Date'] ? $row['rDate'] : '')?></td>
		<td class='textright'><?=nf($row['DueAmt'])?></td>
<?}

function printScheduleSecondHalf($row, $balance, $dueValue){?>
		<td class='textright <?=(($row['CBFlg']== -1 || $row['CCLflg']== -1) ? 'red' : '' )?> <?=($row['mode'] == 1 ? 'cash' : '')?>'><?=nf($row['rEMI'])?></td>
		<td><?if($row['rEMI'] > 0){?> <?=(($row['CBFlg']== -1 || $row['CCLflg']== -1) ? '&#10005' : '&#10003')?> <?}?></td>
		<td class='textright'><b><?=nf($balance, true)?></b></td>
		<td class='left'><?=(isset($_SESSION['PAY_MODE'][$row['mode']]) ? $_SESSION['PAY_MODE'][$row['mode']] : '')?> <?=(isset($row['reconind']) ? ($row['reconind'] == 1 ? '<span class="red">Pending</span>' : ($row['CBFlg']== -1 || $row['CCLflg']== -1 ? '<span class="red">Bounced</span>' : '<span class="green">Cleared</span>')) : '')?></td>
		<td class='textright <?=($row['Penalty'] ? 'red' : '')?>'><?=nf($row['Penalty'])?></td>
		<td class='textright'><?=nf($row['Others'])?></td>
		<td class='textright <?=(($row['CBFlg']== -1 || $row['CCLflg']== -1) ? 'red' : '' )?>'><?=nf($row['Received'])?></td>
		<td class='textleft <?=($row['CBFlg'] == -1 || $row['CCLflg'] == -1 ? 'red' : ($row['mode'] == 1 ? 'green' : ''))?>'>
			<?=($row['mode']==1 ? "By: ".titleCase($row['sranm']) : (($row['CBFlg']== -1 ? 'Bounced On:'.$row['cbdt'].' &#8226; '.titleCase($row['cbrsn']) : '').''.($row['CCLflg']== -1 ? 'Cancelled On:'.$row['ccldt'].' &#8226; Entry Cancelled/Reversed' : '')))?>
			<?=($row['mode']==3 ||$row['mode']==6 ? $row['Remarks'] : '')?></td>
	</tr>
<?}

function printScheduleToday($total, $balance, $dueValue, $rowCount, $tenure){?>
	<tr>
		<th class='textright'><?=$rowCount?></th>
		<th class='dt textright'>As of Today</th>
		<th class='textright'><?=nf($total['DueAmt'])?></th>
		<th class='textright'><?=nf($total['rEMI'])?></th>
		<th></th>
		<th class='textright red'><b><?=nf($balance,  true)?></b></th>
		<th></th>
		<th class='textright'><?=nf($total['Penalty'])?></th>
		<th class='textright'><?=nf($total['Others'])?></th>
		<th class='textright'><?=nf($total['Received'])?></th>
		<?
			$bucket = 0;
			if($dueValue != 0 && $balance > 0){
				$bucket = round($balance/$dueValue);
				if($bucket == 0 && $tenure == $rowCount)
					$bucket = 1;
			}
		?>
		<th class='red'>Bucket=<?=$bucket?></th>
	</tr>
<?}

function deal(){
	$removeWords = array("BEING INSTALLMENT AMOUNT RECEIVED BY","BEING CASH DEPOSITED", "BEING CHQ. CLEARED IN", "BEING AMT RCVD BY ECS", "BEING AMT RCVD", "BY ECS", "BEING AMT RECD BY");
	$dbPrefix = $_SESSION['DB_PREFIX'];
	$dealid = isset($_REQUEST['dealid'])? $_REQUEST['dealid'] : 0;
	if(isset($_REQUEST['dealno'])){
		$dealno = str_pad($_REQUEST['dealno'],6,"0", STR_PAD_LEFT);
		$q = "select dealid from ".$dbPrefix.".tbmdeal where dealno = '$dealno'";
		$dealid = executeSingleSelect($q);
	}
	$mm = date('m');; $yy = date('Y');
	$dbPrefix_curr = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));
	$dbPrefix_last = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy-1,-2) : ($yy-1)."".(substr($yy-1,-2)+1));

	$q1 = "SELECT d.dealid, d.dealno,dealnm,d.city, d.state, d.centre, d.AnnualIncome, d.ProposalNo, d.refdealid, d.mobile, d.mobile2, concat(d.add1, ' ', d.add2, ' ', d.area, ' ', d.tahasil) as address, DATE_FORMAT(d.hpexpdt, '%d-%b-%y') as hpexpdt, round(d.financeamt) as finance, round(d.roi,2) as roi, round(d.bankroi,2) as bankroi, round(d.totdueamt) as due, d.extracharges, round(d.collectionchrgs) as cc,  d.period, d.dealsts, DATE_FORMAT(d.bankduedt, '%d-%b-%y') as bankduedt, DATE_FORMAT(date_add(d.bankduedt, INTERVAL period-1 Month), '%d-%b-%y') as bankexpdt, DATE_FORMAT(d.HPDt, '%d-%b-%y') as hpdate, DATE_FORMAT(d.StartDueDt,'%d-%b-%y') as startdt, d.profession, d.annualincome, round(d.CostOfVhcl) as cost, round(d.Marginmoney) as margin, round(d.Marginmoney/d.CostofVhcl*100,1) as permargin, d.active, d.closedealflg, d.closedealfinalflg, d.cancleflg, b.banknm as bank, br.bankbrnchnm as branch, brk.brkrnm as dealer
	FROM ".$dbPrefix.".tbmdeal d join lksa.tbmsourcebankbrnch br join lksa.tbmsourcebank b join lksa.tbmbroker brk
	on d.bankbrnchid = br.bankbrnchid and br.bankid = b.bankid and d.brkrid = brk.brkrid
	WHERE dealid = '$dealid'";

	$q2 = "SELECT DueDt as Date, round(DueAmt) as Due, round(CollectionChrgs) as CC, round(DueAmt+CollectionChrgs) as Total, (case WHEN Duedt <= curdate() THEN 1 ELSE 0 END) as eligible  FROM ".$dbPrefix.".tbmduelist where dealid = $dealid order by Year(DueDt), Month(DueDt)";

	$q3 = "";

	for ($d =2008; $d <= date('Y'); $d++){
		$db = "lksa".$d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
		$q3 .="
		SELECT t1.sraid, b.brkrnm as sranm, t1.rcptdt as Date, round(sum(t2.rcptamt)) as Received, t1.rcptid, t1.rcptpaymode as mode, t1.CBFlg, t1.CBCCLFlg, t1.CCLflg, DATE_FORMAT(t1.cbdt, '%d-%b-%y') as cbdt, DATE_FORMAT(t1.ccldt, '%d-%b-%y') as  ccldt, t1.rmrk as Remarks, t1.cbrsn,
	sum(case when dctyp = 101 then round(t2.rcptamt) ELSE 0 END) as EMI,
	sum(case when dctyp = 102 then round(t2.rcptamt) ELSE 0 END) as Clearing,
	sum(case when dctyp = 103 then round(t2.rcptamt) ELSE 0 END) as CB,
	sum(case when dctyp = 104 then round(t2.rcptamt) ELSE 0 END) as Penalty,
	sum(case when dctyp = 105 then round(t2.rcptamt) ELSE 0 END) as Seizing,
	sum(case when dctyp = 107 then round(t2.rcptamt) ELSE 0 END) as Other,
	sum(case when dctyp = 111 then round(t2.rcptamt) ELSE 0 END) as CC
	, v.reconind
	FROM ".$db.".tbxdealrcpt t1 join ".$db.".tbxdealrcptdtl t2 on t1.rcptid = t2.rcptid and t1.dealid = $dealid
	LEFT JOIN ".$db.".tbxacvoucher v on v.xrefid = t1.rcptid and v.rcptno = t1.rcptno and xreftyp = 1100 and acvchtyp = 4 and acxnsrno = 0
	left join lksa.tbmbroker b on t1.sraid = b.brkrid group by t1.rcptid
	UNION";
	}

	$q3 = rtrim($q3, "UNION");

	$q4 = "select * from (
		SELECT 1 AS source, Date, DATE_FORMAT(Date, '%d-%b-%Y') as dDate, Due, Total as DueAmt, eligible, NULL as rDate, NULL AS Received, NULL AS rcptid, NULL as mode, NULL AS CBFlg, NULL AS CBCCLFlg, NULL AS CCLflg, NULL AS cbdt, NULL AS ccldt,  NULL as cbrsn, NULL as sranm, NULL AS Remarks, NULL AS rEMI, NULL AS Penalty, NULL AS Others, NULL as reconind FROM ($q2) as t1
	UNION
		SELECT 2 AS source, DATE, NULL as dDate, NULL AS Due, NULL AS DueAmt, NULL AS eligible, DATE_FORMAT(Date, '%d-%b-%Y') as rDate, Received, rcptid, mode, CBFlg, CBCCLFlg, CCLflg, cbdt, ccldt, cbrsn, sranm, Remarks, (EMI+CC) as rEMI, Penalty, (Clearing + CB + Seizing + Other) as Others, reconind FROM ($q3) as t2
	) t order by Date, source
	";

	$q5 ="Select salesmannm, centre, mobile from lksa.tbmsalesman s join lksa.tbadealsalesman sa on s.salesmanid = sa.salesmanid and sa.dealid = $dealid";

	$q6 ="Select model, modelyy, chasis, engineno, make, rtoregno, insuexpdt, vhclcolour, siezeflg from lksa.tbmdealvehicle where dealid =  $dealid";

	$q7 ="Select grtrnm as name, area, city, mobile, concat(add1, ' ', add2, ' ', area, ' ', tahasil) as address from lksa.tbmdealguarantors where dealid =  $dealid";

	$q8 ="Select 'ECS' as type,  ecsamt as amt, apprvflg as approved, approverejectdt as dt, pdcrcvd from lksa.tbmdealecs where dealid =  $dealid
	UNION
		Select 'NACH' as type, nacamt as amt, apprvflg as approved, approverejectdt as dt, pdcrcvd from lksa.tbmdealnac where dealid =  $dealid";

	$q9 ="SELECT n1.acxndt AS paidtobank, n2.nocdate as nocrcptdt, n2.nocno, n3.nocdate AS senttocustomerdt, n3.rtndate AS returndt, n3.rtnremark, n3.sraid, n3.senddate as senttosradt FROM tbadealnocpmnt n1 LEFT JOIN tbadealnoc n2  ON n1.dealid = n2.dealid LEFT JOIN tbadealcustnoc AS n3 ON n1.dealid = n3.dealid WHERE n1.dealid =$dealid";

	$q10 = "SELECT a.dealid FROM `tbadealcatagory` AS a JOIN `tbmrcvrycatagory` AS b ON a.CatgId=b.pkid AND b.PkId=12 and a.dealid = $dealid";

	$q11 = "SELECT
	SUM(CASE WHEN dctyp IN (101,111) THEN chrgsrcvd ELSE 0 END) AS rEMI,
	SUM(CASE WHEN dctyp = 102 THEN Chrgsapplied - chrgsrcvd ELSE 0 END) AS Clearing,
	SUM(CASE WHEN dctyp = 103 THEN Chrgsapplied - chrgsrcvd ELSE 0 END) AS Bouncing,
	SUM(CASE WHEN dctyp = 104 THEN Chrgsapplied - chrgsrcvd ELSE 0 END) AS Penalty,
	SUM(CASE WHEN dctyp = 105 THEN Chrgsapplied - chrgsrcvd ELSE 0 END) AS Seizing,
	SUM(CASE WHEN dctyp = 106 THEN Chrgsapplied - chrgsrcvd ELSE 0 END) AS Legal,
	SUM(CASE WHEN dctyp = 107 THEN Chrgsapplied - chrgsrcvd ELSE 0 END) AS Other
	FROM tbmdealchrgs WHERE dealid = $dealid";

	$q12 = "select sum(round(DueAmt+CollectionChrgs)) as due from lksa.tbmduelist where dealid = $dealid and Duedt <= curdate();";

	$q13 = "
		select * from
		(select mm, yy, fr.sraid, fr.callerid, b.brkrnm as sra, u.realname as caller, fr.rectagid_sra, st.description as tag_sra, fr.rectagid_caller, ct.description as tag_caller, fr.reccomment_sra, fr.reccomment_caller from $dbPrefix_curr.tbxfieldrcvry fr left join lksa.tbmbroker b on b.brkrid = fr.sraid left join ob_sa.tbmuser u on u.userid = fr.callerid left join lksa.tbmrecoverytags st on fr.rectagid_sra = st.tagid left join lksa.tbmrecoverytags ct on fr.rectagid_caller = ct.tagid where fr. dealid = $dealid
		union
		select mm, yy, fr.sraid, fr.callerid, b.brkrnm as sra, u.realname as caller, fr.rectagid_sra, st.description as tag_sra, fr.rectagid_caller, ct.description as tag_caller, fr.reccomment_sra, fr.reccomment_caller from $dbPrefix_last.tbxfieldrcvry fr left join lksa.tbmbroker b on b.brkrid = fr.sraid left join ob_sa.tbmuser u on u.userid = fr.callerid left join lksa.tbmrecoverytags st on fr.rectagid_sra = st.tagid left join lksa.tbmrecoverytags ct on fr.rectagid_caller = ct.tagid where fr. dealid = $dealid
		) t order by yy desc, mm desc limit 0, 4";

	$q14 = "
		SELECT t.dealid, t.dt, day(t.dt) as dd, date_format(t.dt,'%b') as mm, year(t.dt) as yy, t.type, t.callerid, u.realname AS caller, t.sraid, b.brkrnm AS sranm, date_format(t.followupdt,'%d-%b') as followupdt, t.remark FROM
		(
			SELECT dealid, followupdate AS dt, 'FIRSTCALL' AS `type`,  NULL AS callerid, Remark AS remark, NULL AS followupdt, NULL AS sraid FROM $dbPrefix_curr.tbxdealduedatefollowuplog WHERE dealid = $dealid
			UNION
			SELECT dealid, followupdate AS dt, 'CALLER' AS `type`,  webuserid AS callerid, FollowupRemark AS remark, NxtFollowupDate AS followupdt, NULL AS sraid FROM $dbPrefix_curr.tbxdealfollowuplog WHERE dealid = $dealid
			UNION
			SELECT dealid, followupdate AS dt, 'INTERNAL' AS `type`,  webuserid AS callerid, FollowupRemark AS remark, NULL AS followupdt, sraid FROM $dbPrefix_curr.tbxsrafollowuplog WHERE dealid = $dealid
		) t
		LEFT JOIN ob_sa.tbmuser u ON t.callerid = u.userid
		LEFT JOIN lksa.tbmbroker b ON t.sraid = b.brkrid AND b.brkrtyp = 2
	ORDER BY dt DESC";

//	print_a($q4);

	$sql = $q1;
	$t1 = executeSelect($sql);
	if($t1['row_count'] <= 0){
		echo "No deal. Please click on the deal in the 'Deal Search' menu option.";
		return;
	}
	$deal = $t1['r'][0];

	$t1 = executeSelect($q5);
	if($t1['row_count'] <= 0){
		echo "No deal. Please click on the deal in the 'Deal Search' menu option.";
		return;
	}
	$salesman = $t1['r'][0];

	$t1 = executeSelect($q6);
	if($t1['row_count'] <= 0){
		echo "No deal. Please click on the deal in the 'Deal Search' menu option.";
		return;
	}
	$vehicle = $t1['r'][0];

	$t1 = executeSelect($q7);
	if($t1['row_count'] <= 0){
		echo "No deal. Please click on the deal in the 'Deal Search' menu option.";
		return;
	}
	$guarantor = $t1['r'][0];

	$t1 = executeSelect($q8);
	if($t1['row_count'] <= 0){
		$payment = array("type"=>"PDC", "amt"=>0, "approved"=>0, "dt" => "", "pdcrcvd" => "");
	} else
		$payment = $t1['r'][0];

	$t1 = executeSelect($q9); if($t1['row_count'] > 0){$noc = $t1['r'][0];}

	$t1 = executeSelect($q10); if($t1['row_count'] > 0){$insurance = $t1['r'][0];}

	$t1 = executeSelect($q11); if($t1['row_count'] > 0){$dealcharges = $t1['r'][0];}
	$t1 = executeSelect($q12); if($t1['row_count'] > 0){$dueListSum = $t1['r'][0];}
	$t1 = executeSelect($q13); if($t1['row_count'] > 0){$assignment = $t1['r'];}
	$t1 = executeSelect($q14); if($t1['row_count'] > 0){$logs = $t1['r'];}

	$status =" <span style='color:";

	if($deal['dealsts']==1)
		$status .=  "green'> Active";
	else if($deal['dealsts']==3)
		$status .=  "red'> Closed";
	else if($deal['dealsts']==2)
		$status .=  "gold'> Draft";
	if($deal['cancleflg']== -1)
		$status .= "red'> Cancelled";
	$status .= "</span>";
	?>
	<div class="PageHeader"><a target="_blank" href="http://in.loksuvidha.com/Loans/Deal/Details/<?=$deal['dealid']?>"><?=$deal['dealnm']?></a></div>
	<div class="PageHeader" style="float:right;font-size:12px;width:inherit">
		Disbursement: <span style='color:red'><?=($deal['bankduedt']==NULL ? " Pending" : "Done")?></span> |
		<?=($vehicle['siezeflg']==0 ? '' : " <span style='color:red'>Vehicle Seized</span> | ")?>
		<?=(isset($insurance['dealid']) ? " <span style='color:red'>Insurance Claim: Active</span> | " : '' )?>
		Deal is <?=$status?>
		</div>
	<div class="clr"></div>
	<hr/>
	<?
	$lamount = $deal['finance']; // Loan Amount
	$mi = $deal['bankroi']; // Monthly interest %ge
	$ny = $deal['period']; // No of months
	$mic = ($mi/100) /12; // Monthly interest
	$top = pow((1+$mic),$ny);
	$bottom = $top - 1;
	$sp = ($bottom == 0 ? 0 : $top / $bottom);
	$emi = (($lamount * $mic) * $sp);
	$tenure = $deal['period'];
	?>
	<div class="dealdetails">

	<fieldset><legend>Deal Headers</legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody><tr>
					<td class="keys" valign="top"><label class="textsts">Deal No</label></td>
					<td><b><?=$deal['dealno']?></b></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Proposal No</label></td>
					<td><?=$deal['ProposalNo']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Centre</label></td>
					<td><?=$deal['centre']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Dealer</label></td>
					<td><?=$deal['dealer']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Salesman</label></td>
					<td><?=$salesman['salesmannm']?> (<?=$salesman['centre']?>)</td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Bank</label></td>
					<td><?=$deal['bank']?> (<?=$deal['branch']?>)</td>
				</tr>
			</tbody></table>
	</fieldset>

	<fieldset><legend>Customer Details</legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody><tr>
					<td class="keys" valign="top"><label class="textsts">Profession</label></td>
					<td><b><?=$deal['profession']?></b></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Annual Income</label></td>
					<td><?=nf($deal['AnnualIncome'])?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Address</label></td>
					<td><?=$deal['address']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">City</label></td>
					<td><?=$deal['city']?>, <?=$deal['state']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Mobile No</label></td>
					<td><?=$deal['mobile']?>, <?=$deal['mobile2']?> </td>
				</tr>
			</tbody></table>
	</fieldset>

	<fieldset><legend>Vehicle Details</legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Make</label></td>
					<td><?=$vehicle['make']?> <?=$vehicle['model']?> <?=$vehicle['modelyy']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Color</label></td>
					<td><?=$vehicle['vhclcolour']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Chessis Number</label></td>
					<td><?=$vehicle['chasis']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Engine Number</label></td>
					<td><?=$vehicle['engineno']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">RTO Reg Number</label></td>
					<td><?=$vehicle['rtoregno']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Insurance Expiry</label></td>
					<td><?=date('d-M-y',strtotime($vehicle['insuexpdt']))?></td>
				</tr>
			</tbody></table>
	</fieldset>

	<div class="clr"></div>

	<?if(isset($dealcharges['rEMI'])){?>
	<!--td-->
		<fieldset><legend>Deal Charges </legend>
			<table class="admintable" width="100%" cellspacing="1">
				<tbody>
					<?$totalDue =0;?>
					<tr>
						<td class="keys" valign="top"><label class="textsts">Overdue Installment</label></td>
						<td><?=nf($dueListSum['due'] - $dealcharges['rEMI'],true)?></td>
						<?$totalDue+=$dueListSum['due'] - $dealcharges['rEMI'];?>
					</tr>
					<?if($dealcharges['Clearing'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Clearing Charges</label></td>
							<td><?=nf($dealcharges['Clearing'],true)?></td>
							<?$totalDue+=$dealcharges['Clearing']?>
						</tr>
					<?}?>
					<?if($dealcharges['Bouncing'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Ch Bouncing Charges</label></td>
							<td><?=nf($dealcharges['Bouncing'],true)?></td>
							<?$totalDue+=$dealcharges['Bouncing']?>
						</tr>
					<?}?>
					<?if($dealcharges['Penalty'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Penalty Charges</label></td>
							<td><?=nf($dealcharges['Penalty'], true)?></td>
							<?$totalDue+=$dealcharges['Penalty']?>
						</tr>
					<?}?>
					<?if($dealcharges['Seizing'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Seizing Charges</label></td>
							<td><?=nf($dealcharges['Seizing'], true)?></td>
							<?$totalDue+=$dealcharges['Seizing']?>
						</tr>
					<?}?>
					<?if($dealcharges['Legal'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Legal Charges</label></td>
							<td><?=nf($dealcharges['Legal'], true)?></td>
							<?$totalDue+=$dealcharges['Legal']?>
						</tr>
					<?}?>
					<?if($dealcharges['Other'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Other Charges</label></td>
							<td><?=nf($dealcharges['Other'], true)?></td>
							<?$totalDue+=$dealcharges['Other']?>
						</tr>
					<?}?>
					<tr>
						<td class="keys" valign="top"><label class="textsts">Total Due</label></td>
						<td class="b"><?=nf($totalDue, true)?></td>
					</tr>

				</tbody>
			</table>
		</fieldset>
	<!--/td-->
	<?}//If isset (rEMI) ?>

	<fieldset><legend>Finance Details</legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody><tr>
					<td class="keys" valign="top"><label class="textsts">Finance</label></td>
					<td><?=nf($deal['finance'])?> for <b><?=$deal['period']?></b> Months</td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Vehicle Cost</label></td>
					<td><?=nf($deal['cost'])?> <span  class='<?=($deal['permargin']<= 30 ? 'red' : '')?>'>[Margin: <?=nf($deal['margin'])?> &#8226; <?=$deal['permargin']?>%]</span></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Total Due Amount</label></td>
					<td><?=nf($deal['due'])?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">HP & Expiry Date</label></td>
					<td><b><?=$deal['hpdate']?></b> to <b><?=$deal['hpexpdt']?></b></td>
				</tr>
			</tbody></table>
	</fieldset>

	<fieldset><legend>Guarantor Details</legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Name</label></td>
					<td><?=$guarantor['name']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Address</label></td>
					<td><?=$guarantor['address']?></td>
				</tr>

				<tr>
					<td class="keys" valign="top"><label class="textsts">City</label></td>
					<td><?=$guarantor['area']?>, <?=$guarantor['city']?></td>
				</tr>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Mobile</label></td>
					<td><?=$guarantor['mobile']?></td>
				</tr>
			</tbody>
		</table>
	</fieldset>

	<div class="clr"></div>

	<fieldset><legend>Payment Instrument</legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Type & Status</label></td>
					<td class='red'><?=$payment['type']?>
						<?if($payment['type'] != 'PDC'){?>(<?=($payment['approved']==2 ? 'Rejected' : ( $payment['approved']==1 ? 'Approved' : 'Pending') )?>)<?}?></td>
				</tr>
				<?if($payment['type'] != 'PDC'){?>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Action Date</label></td>
					<td><?=(is_null($payment['dt']) ? 'Pending' : date('d-M-y',strtotime($payment['dt'])))?></td>
				</tr>
				<?}
				if($payment['approved']==2){?>
				<tr>
					<td class="keys" valign="top"><label class="textsts">PDC Received</label></td>
					<td><?=($payment['pdcrcvd'] == 1 ? 'Yes' : 'No')?></td>
				</tr>
				<?}?>
			</tbody>
		</table>
	</fieldset>

	<fieldset><legend>NOC Status </legend>
		<table class="admintable" width="100%" cellspacing="1">
			<tbody>
				<?if(isset($noc)){?>
				<tr>
					<td class="keys" valign="top"><label class="textsts">Payment to Bank</label></td>
					<td><?=date('d-M-Y',strtotime($noc['paidtobank']))?></td>
				</tr>
					<?if(!is_null($noc['nocrcptdt'])){?>
					<tr>
						<td class="keys" valign="top"><label class="textsts">NOC Received</label></td>
						<td><?=date('d-M-Y',strtotime($noc['nocrcptdt']))?></td>
					</tr>
					<tr>
						<td class="keys" valign="top"><label class="textsts">NOC No</label></td>
						<td><?=$noc['nocno']?></td>
					</tr>

						<?if(!is_null($noc['senttocustomerdt'])){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Sent to Customer:</label></td>
							<td><?=date('d-M-Y',strtotime($noc['senttocustomerdt']))?></td>
						</tr>
							<?if(!is_null($noc['returndt'])){?>
							<tr>
								<td class="keys" valign="top"><label class="textsts">Returned from Customer:</label></td>
								<td><?=date('d-M-Y',strtotime($noc['returndt']))?> - <?=$noc['rtnremark']?></td>
							</tr>
							<?}?>
							<?if($noc['sraid']!=0){?>
							<tr>
								<td class="keys" valign="top"><label class="textsts">Send to SRA:</label></td>
								<td><?=date('d-M-Y',strtotime($noc['returndt']))?> - <?=$noc['rtnremark']?></td>
							</tr>
							<?}?>
						<?}?>
					<?}?>
				<?}else{?>
					<tr>
						<td class="keys" valign="top"><label class="textsts">NOC Status</label></td>
						<td>Not Applied</td>
					</tr>
				<?}?>
			</tbody>
		</table>
	</fieldset>

	<div class="clr"></div>

	<div class="assignment">
		<div class='header'>Deal Assignment</div>
		<?if(isset($assignment)){?>
			<table class="adminlist" width="100%" cellspacing="1">
				<thead>
					<tr><th>Month</th><th>SRA</th><th>Caller</th><th>SRA Tag</th><th>SRA Comment</th><th>Caller Tag</th><th>Caller Comment</th></tr>
				</thead>
				<tbody>
					<?foreach($assignment as $row){?>
					<tr>
						<td><?=date('M', mktime(0, 0, 0, $row['mm'], 10))?>-<?=substr($row['yy'],-2)?></td>
						<td><?=titleCase($row['sra'])?></td>
						<td><?=titleCase($row['caller'])?></td>
						<td><?=titleCase($row['tag_sra'])?></td>
						<td><?=titleCase($row['reccomment_sra'])?></td>
						<td><?=titleCase($row['tag_caller'])?></td>
						<td><?=titleCase($row['reccomment_caller'])?></td>
					</tr>
					<?}?>
				</tbody>
			</table>
			<?}//if(isset assignment)
			else{?>
				No Assigments since last financial year for this deal
			<?}?>
	</div>

	<div class="dealstatus">
		<div class="header">EMI Schedule & Clearing Status</div>
		<?
			$sql = $q4; $p=0;
			$t1 = executeSelect($sql);
			if($t1['row_count'] > 0){
				$res = $t1['r'];
			}
			else $res = array();
			?>
			<table class="adminlist" id="dealstatus">
			<thead>
				<tr><th>#</th><th class="dt">Date</th><th>Due</th><th>Received</th><th></th><th>Balance</th><th>Mode</th><th>Penalty</th><th>Others</th><th>Receipt</th><th></th>
				</tr>
			</thead>
			<tbody>

			<?
//			print_a($sql);
			$total = array("DueAmt" =>0, "rEMI"=>0, "Penalty"=>0, "Received"=>0, "Others"=>0);
			$cc=-1; $prev_dt = '';$i=0;
			$balance = 0; $shown = 0; $dueValue = 0; $dt = "";
			$rowStarted = 0;
			$lastRow_dDate = '';
			$emptyrow = array('CBFlg' => 0, 'CCLflg' => 0, 'Others' => 0, 'Penalty' => 0, 'Received' => 0, 'Remarks' => '', 'cbdt' => '', 'ccldt' => '', 'rEMI' => 0, 'sranm' =>'', 'mode'=>NULL);
			foreach ($res as $row){
				if(!is_null($row['DueAmt']) && $dueValue == 0) //Get EMI amount from first row
					$dueValue = $row['DueAmt'];

				foreach($removeWords as $w){ //Trim the remarks line and remove irrelevant text.
					if(startsWith($row['Remarks'], $w)) {
						 $row['Remarks'] = trim(substr($row['Remarks'], strlen($w)));
					}
				}

				if(!is_null($row['dDate'])){ // This is a row for DUE EMI from Duelist so start a new row for this
					if($rowStarted  == 1){// Check if earlier row was terminated and complete it if required.
						printScheduleSecondHalf($row, $balance, $dueValue);
						$rowStarted = 0;
					}
					if($row['Date'] > date('Y-m-d H:i:s') && $shown == 0){
						printScheduleToday($total, $balance, $dueValue, $i, $tenure);
						$shown = 1;
					}

					$balance += $row['DueAmt'];
					$total['DueAmt'] += $row['DueAmt'];

					$rowStarted = 1;
					printScheduleFirstHalf($row,$dt, ++$p, ++$i);
					$dt = $row['Date'];
					$lastRow_dDate = $row['dDate'];
					continue;
				}
				else { // This is a row for Receipt side
					if($row['rDate'] != $lastRow_dDate && $rowStarted == 1){ // We need a new row, so close earlier row
						printScheduleSecondHalf($emptyrow, $balance, $dueValue);
						$rowStarted =0;
					}
					if($rowStarted == 1){
						if($row['CBFlg']== 0 && $row['CCLflg']==0){
							$balance -= $row['rEMI'];
							$total['rEMI'] += $row['rEMI'];
							$total['Penalty'] += $row['Penalty'];
							$total['Others'] += $row['Others'];
							$total['Received'] += $row['Received'];
						}
						printScheduleSecondHalf($row, $balance, $dueValue);
						$rowStarted = 0;
					}
					else{
						if($row['Date'] > date('Y-m-d H:i:s') && $shown == 0){
							printScheduleToday($total, $balance, $dueValue, $i, $tenure);
							$shown = 1;
						}
						$rowStarted = 1;
						printScheduleFirstHalf($row,$dt,++$p, 0);
						if($row['CBFlg']== 0 && $row['CCLflg']==0){
							$balance -= $row['rEMI'];
							$total['rEMI'] += $row['rEMI'];
							$total['Penalty'] += $row['Penalty'];
							$total['Others'] += $row['Others'];
							$total['Received'] += $row['Received'];
						}
						printScheduleSecondHalf($row, $balance ,$dueValue);
						$rowStarted = 0;
					}
				}
				$dt = $row['Date'];
			}

			if($rowStarted == 1){
				printScheduleSecondHalf($row, $balance ,$dueValue);
				$rowStarted = 0;
			}
			if($shown == 0){
				printScheduleToday($total, $balance, $dueValue, $i, $tenure);
				$shown = 1;
			}?>
			</tbody>
			<tfoot>
			<tr>
				<th></th>
				<th class='textright'>Total</th>
				<th class='textright'><?=nf($total['DueAmt'])?></th>
				<th class='textright'><?=nf($total['rEMI'])?></th>
				<th></th>
				<th class='textright'><?=nf($balance, true)?></th>
				<th></th>
				<th class='textright'><?=nf($total['Penalty'])?></th>
				<th class='textright'><?=nf($total['Others'])?></th>
				<th class='textright'><?=nf($total['Received'])?></th>
				<th class='textleft'><a target="_blank" href="?task=show_receipts&dealid=<?=$dealid?>">Receipt List</a></th>
			</tr>
			</tfoot>
			</table>
			<div><span class='cash'>Cash Payment</span> | <span class='red'>Bounced or Cancelled</span></div>
	</div><!-- Deal Status -->

	<?if(isset($logs)){
		$logCnt = count($logs);
		$i =0; $breakAt = round($logCnt/3,0);
	?>
	<div id="deallogs" class="deallogs">
	<div class="header">Communication Logs</div>
		<div class="logcolumn" style="width:30%">
	<?foreach($logs as $lg){?>
			<div class='logitem'>
				<div class='logdt'>
					<div class='dd'><?=$lg['yy']?></div>
					<div class='mm'><b><?=$lg['dd']?></b> <?=$lg['mm']?></div>
				</div>
				<div class='logby'><?=($lg['type'] != 'FIRSTCALL' ? titleCase($lg['caller']) : '')?><?=($lg['type'] == 'INTERNAL' ? ' &#8594; '.titleCase($lg['sranm']) : '')?></div>
				<div class='lognfd'><?=($lg['type'] != 'INTERNAL' ? 'NFD: '.$lg['followupdt'] : '&nbsp;')?></div>
				<div style="clear:right"></div>
				<div class='logcomments'><?=$lg['remark']?></div>
				<div class="clear"></div>
			</div>
		<?	$i++;
			if($i == $breakAt){
				$i=0;
			?>
			</div>
		<div class="logcolumn" style="width:30%">
			<?}
		}
	}else{?>
		<div class="empty">No Logs yet!!</div>
	<?}?>
	</div>
	<div class="clear"></div>
	</div>
	</div>

	<div>Dealid = <?=$dealid?></div>
<?}?>