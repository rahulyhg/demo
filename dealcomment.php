<?
if(!isset($task) || $task!="dealcomment"){
    echo "Invalid Access";
    die();
}
else dealcomment();

function dealcomment(){
	$dbPrefix = $_SESSION['DB_PREFIX'];
	$dealid = isset($_REQUEST['dealid'])? $_REQUEST['dealid'] : 0;
	if(isset($_REQUEST['dealno'])){
		$dealno = str_pad($_REQUEST['dealno'],6,"0", STR_PAD_LEFT);
		$q = "select dealid from ".$dbPrefix.".tbmdeal where dealno = '$dealno'";
		$dealid = executeSingleSelect($q);
	}
	if($dealid == 0){?>
			<div class="dealpage" id="content"><center class='empty'>No deal selected!</center></div>
		<?return;
	}
	$submit = isset($_REQUEST['submit'])? $_REQUEST['submit'] : NULL;
	$tagid = isset($_REQUEST['tagid'])? $_REQUEST['tagid'] : NULL;
	$comment = isset($_REQUEST['comment'])? addslashes($_REQUEST['comment']) : '';
	$updatedt = NULL;

	$mm = date('m');; $yy = date('Y');
	$dbPrefix_curr = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));
	$dbPrefix_last = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy-1,-2) : ($yy-1)."".(substr($yy-1,-2)+1));

	$q1 = "SELECT d.dealid, d.dealno,dealnm,d.city, d.state, d.centre, concat(d.add1, ' ', d.add2, ' ', d.area, ' ', d.tahasil) as address, d.area,  d.AnnualIncome, d.ProposalNo, d.refdealid, d.mobile, d.mobile2, DATE_FORMAT(d.hpexpdt, '%d-%m-%Y') as hpexpdt, round(d.financeamt) as finance, round(d.roi,2) as roi, round(d.bankroi,2) as bankroi, round(d.totdueamt) as due, d.extracharges, round(d.collectionchrgs) as cc,  d.period, d.dealsts, DATE_FORMAT(d.bankduedt, '%d-%m-%Y') as bankduedt, DATE_FORMAT(date_add(d.bankduedt, INTERVAL period-1 Month), '%d-%m-%Y') as bankexpdt, DATE_FORMAT(d.HPDt, '%d-%m-%Y') as hpdate, DATE_FORMAT(d.StartDueDt,'%d-%m-%Y') as startdt, d.profession, d.annualincome, round(d.CostOfVhcl) as cost, round(d.Marginmoney) as margin, round(d.Marginmoney/d.CostofVhcl*100,1) as permargin, d.active, d.closedealflg, d.closedealfinalflg, d.cancleflg, b.banknm as bank, br.bankbrnchnm as branch, brk.brkrnm as dealer
	FROM ".$dbPrefix.".tbmdeal d join lksa.tbmsourcebankbrnch br join lksa.tbmsourcebank b join lksa.tbmbroker brk
	on d.bankbrnchid = br.bankbrnchid and br.bankid = b.bankid and d.brkrid = brk.brkrid
	WHERE dealid = '$dealid'";

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
		select mm, yy, fr.dealnm, fr.dealno, fr.city, fr.centre, fr.rgid as bucket, fr.DueAmt, fr.dd, fr.model, fr.Mobile, fr.GuarantorMobile, fr.sraid, fr.callerid, fr.recstatus_sra, fr.rectagid_caller, fr.reccomment_caller, DATE_FORMAT(fr.callerupdatedt,'%d-%b-%y') as callerupdatedt, b.brkrnm as sra, u.realname as caller from $dbPrefix_curr.tbxfieldrcvry fr left join lksa.tbmbroker b on b.brkrid = fr.sraid left join ob_sa.tbmuser u on u.userid = fr.callerid where dealid = $dealid and fr.mm = $mm;";

	$q15 = "select tagid, description from ".$dbPrefix.".tbmRecoveryTags where active = 2 and (allowtagto = 0 or allowtagto = 1)";//Allowed to all and allowed to SRA

	$t1 = executeSelect($q1);
	if($t1['row_count'] <= 0){
		echo "No deal. Please click on the deal in the 'Deal Search' menu option.";
		return;
	}
	$deal = $t1['r'][0];

	$t1 = executeSelect($q11); if($t1['row_count'] > 0){$dealcharges = $t1['r'][0];}
	$t1 = executeSelect($q12); if($t1['row_count'] > 0){$dueListSum = $t1['r'][0];}
	$t1 = executeSelect($q13); if($t1['row_count'] > 0){$assignment = $t1['r'];}
	$t1 = executeSelect($q15); if($t1['row_count'] > 0){$tags = $t1['r'];}

	$isAssigned = 0;
	if($assignment[0]['callerid'] == $_SESSION['user_id']){
		$isAssigned = 1;
	}

	$returnFlag = -1; $returnStr = NULL;
	if($isAssigned == 0)
		$returnStr = "This deal is not assiged to you!";

	if(!is_null($submit) && $isAssigned == 1){
		if(is_null($tagid) || $tagid == 0){
			$returnFlag = 0;
			$returnStr = "Please select problem!";
		}
		else if($tagid == -1 && (empty($comment) || $comment == 'Comment')){
			$returnFlag = 0;
			$returnStr = "Please write comments if you have choosen Other";
		}
		else{
			//Everything looks ok, Lets update the comments in field recovery
			//Comment string is escapted string with javascript function. First un-escape it and then add to database. this is pending
			$q = "update $dbPrefix_curr.tbxfieldrcvry set rectagid_caller = '$tagid', reccomment_caller = '$comment', callerupdatedt = CURRENT_TIMESTAMP where dealid = $dealid and mm=".date('n').";";
			executeUpdate($q);
			$returnFlag = 1;
			$returnStr = "Saved Successfully!";
		}
	}
	if($returnFlag != 1){
		$tagid = is_null($assignment[0]['rectagid_caller']) ? 0 : $assignment[0]['rectagid_caller'];
		$comment = is_null($assignment[0]['reccomment_caller']) ? 'Comments' : $assignment[0]['reccomment_caller'];
		$updatedt = is_null($assignment[0]['callerupdatedt']) ? NULL : $assignment[0]['callerupdatedt'];
	}

	if($deal['dealsts']==1){
		$scolor =  "green"; $status = "Active";
	}else if($deal['dealsts']==3){
		$scolor =  "red"; $status = "Closed";
	}else if($deal['dealsts']==2){
		$scolor =  "gold"; $status = "Draft";
	}
	if($deal['cancleflg'] == -1){
		$scolor = "red"; $status = "Cancelled";
	}
	$status ="<span style='color:$scolor'>$status</span>";

	?>
	<div  id="content">
	<div class="deal" id ='dealcomment'>
		<div class="cname"><a><?=titleCase($assignment[0]['dealnm'])?></a></div>
		<div class="odamt"><?=nf($assignment[0]['DueAmt'])?></div>
		<div class="clear"></div>
		<div class="area"><?=$assignment[0]['dealno']?> &#8226; <?=titleCase($assignment[0]['city'])?>  &#8226; <?=titleCase($assignment[0]['model'])?> &#8226; <?=$status?></div>
		<div class="dt"></div>
		<div class="clear"></div>
	</div>

	<br>

	<div class="dealpage">
	<?if(isset($dealcharges['rEMI'])){?>
		<fieldset><legend>Deal Charges </legend>
			<table class="admintable" width="100%" cellspacing="1">
				<tbody>
					<?$totalDue =0;?>
					<tr>
						<td class="keys" valign="top"><label class="textsts">Assigned On</label></td>
						<td><?=$assignment[0]['dd']?>-<?=date('M')?></td>
					</tr>
					<tr>
						<td class="keys" valign="top"><label class="textsts">Current OD EMI</label></td>
						<td><?=nf($dueListSum['due'] - $dealcharges['rEMI'], true)?></td>
						<?$totalDue+=$dueListSum['due'] - $dealcharges['rEMI'];?>
					</tr>
					<?if($dealcharges['Clearing'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Clearing Charges</label></td>
							<td><?=nf($dealcharges['Clearing'], true)?></td>
							<?$totalDue+=$dealcharges['Clearing']?>
						</tr>
					<?}?>
					<?if($dealcharges['Bouncing'] != 0){?>
						<tr>
							<td class="keys" valign="top"><label class="textsts">Ch Bouncing Charges</label></td>
							<td><?=nf($dealcharges['Bouncing'], true)?></td>
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
					<tr>
						<td class="keys" valign="top"><label class="textsts">Assigned SRA</label></td>
						<td class="b"><?=titleCase($assignment[0]['sra'])?></td>
					</tr>
					<tr>
						<td class="keys" valign="top"><label class="textsts">Assigned Caller</label></td>
						<td class="b"><?=titleCase($assignment[0]['caller'])?></td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?}//If isset (rEMI) ?>
		<fieldset><legend>Final Status</legend>
			<?
				switch($returnFlag){
					case -1:
					case 0:?>
						<div class="noresults"><span class="red"><?=$returnStr?></span></div>
						<?break;
					case 1:?>
						<div class="noresults"><span class="green"><?=$returnStr?></span></div>
						<?break;
				}

			if($isAssigned == 1){
			?>
			<table class="admintable" width="100%" cellspacing="1">
				<tbody>
				<?if(!is_null($updatedt)){?>
					<tr>
						<td class="shortkeys" valign="top"><label class="textsts red">Last Updated On</label></td>
						<td class="red"><?=$updatedt?></td>
					</tr>
				<?}?>
					<tr>
						<td class="shortkeys" valign="top"><label class="textsts">Problem</label></td>
						<td>
							<select name="tagid" id="tagid" class="inputbox" size="1">
								<option value="0">Select Reason</option>
								<?foreach($tags as $t){?>
									<option value="<?=$t['tagid']?>" <? if($tagid  == $t['tagid']){?> selected="selected" <? }?>><?=$t['description']?></option>
								<?}?>
								<!--option value="-1"  <?if($tagid == -1){?> selected="selected" <?}?>>Other</option-->
							</select>
						</td>
					</tr>
					<tr>
						<td colspan = 2>
							<textarea rows="4" cols="40" name="comment" id="comment" onfocus="if(this.value=='Comments') this.value = '';"><?=$comment?></textarea>
						</td>
					</tr>
					<tr>
						<td></td>
						<td class="textleft"><button onclick="saveStatus('<?=$dealid?>')">Save</button></td>
					</tr>
			</table>
			<?}?>
		</fieldset>
	</div>
</div><!--Content -->
<?}?>