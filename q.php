<?
include '../functions.php';

if(isset($_REQUEST['t'])){
	$switch = $_REQUEST['t'];
}
else {
	echo ("<html><body><div>
	usage t= <br/>

	case 1: //Update Recovery details - OD, Total amount, SRA and REC Flag in field recovery
	case 2: // Update Seize Flag into field recovery
	case 3: //Insert Higher bucket rows into tbxbucketwisedue table<br/>
	case 4: //Update last receipt details from receipts table into tbxbucketwisedues table<br/>
	case 5: //Update tbxbucketwisedue - set recovery information for last month - Recovery status, recovery OD amount, total Amount
	case 6: //Update tbxbucketwisedue - set recovery information for last month - recovery sra
	case 7: //Update tbxbucketwisedue - set recovery information for last month - assigned sra, assigned caller
	case 8: //Update tbxbucketwisedue - set recovery information for last month - sra comment and caller comment
	case 9: //Update tbxbucketwisedue - set recovery information for last month - SRA Tag
	case 10: //Update tbxbucketwisedue - set recovery information for last month - Caller Tag
	case 11: //See bucketwise number of deals for coming month
	case 12: //Get all deal for field recovery table
	case 13://Get all deal in bucket 1 group by their due day of the month
	case 14://Get the list of all deals for field recovery
	case 15://Get all SRAs with their ids



	case 200: //List of deals from all years which are in at least 1 bucket (OD >= 0.8  EMI) as of now. Please note <= with duedt is very important	<br>
	case 201: //Update OD_current into Field recovery<br/>
	case 202: //Query to Update OD in fieldrecovery table<br/>
	case 203: //Build Query to Generate tbxPHPFieldRecovery Table<br/>
	</div></body></html>");
	die();
}

$dtdt = date('dmy');
$mm = date('m');; $yy = date('Y');
$dbPrefix = "lksa";
$dbPrefix_user = "ob_sa";
$dbPrefix_curr = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));

switch($switch){

	case 1: //Update Recovery details - OD, Total amount, SRA and REC Flag in field recovery
		echo "#Update Recovery details - OD, Total amount, SRA and REC Flag in field recovery";
		$q = "UPDATE ".$dbPrefix_curr.".tbxfieldrcvry fr JOIN (SELECT dealid, sraid, SUM(odAmt) AS odAmt, SUM(tot) AS tot FROM (SELECT r.dealid, r.sraid, SUM(CASE WHEN rd.DcTyp IN(101,102,111) THEN rd.RcptAmt END) AS odAmt, SUM(rd.RcptAmt) AS tot
		FROM ".$dbPrefix_curr.".tbxdealrcpt AS r JOIN ".$dbPrefix_curr.".tbxdealrcptDtl AS rd
		ON r.RcptId = rd.RcptId WHERE MONTH(r.RcptDt) = MONTH(NOW()) AND r.RcptPayMode=1 AND r.cbFlg=0 AND r.cclFlg=0
		GROUP BY r.dealid, r.sraid ORDER BY r.dealid, odAmt DESC, r.sraid) t GROUP BY dealid having odAmt >= 450) AS rt
		ON fr.dealid = rt.dealid  AND fr.mm = MONTH(NOW())
		SET fr.rec_sraid = rt.sraid, fr.rec_od = IFNULL(rt.odamt,0), fr.rec_total = IFNULL(rt.tot,0), fr.rec_flg = 1;";
		print_a($q);
		break;

	case 2: // Update Seize Flag into field recovery
		echo "#Update Seize Flag into field recovery";
		$q = "UPDATE ".$dbPrefix_curr.".tbxfieldrcvry fr JOIN ".$dbPrefix.".tbadealcatagory c on fr.dealid = c.dealid and fr.mm = $mm and c.catgid = 25
		SET fr.catid = 25, fr.rec_flg = 2;";
		print_a($q);
		break;

	case 3: // Build Query to check receipt
		$dt = date('Y-m-d H:s:i');
		$d = date('d', strtotime($dt));
		echo "#As On: $dt - Build query to Check receipts";
		//(AsOn, DealId, DealNo, DealNm, Centre, Area, City, FY, HPDt, HPExpDt, FinanceAmt, Period, StartDueDt, SalesmanNm, EMI, EMI_TD, EMI_Rec, EMI_Due, Bucket, Othr_Due, Tot_Due, Clearing_Chrges, Chq_Boucing_Chrges, Penalty_Chrges, Seizing_Chrges, Other_Chrges)
		$q = "insert into ".$dbPrefix_curr.".tbxbucketwisedue
		(AsOn, DealId, DealNo, DealNm, Centre, Area, City, FY, HPDt, HPExpDt, FinanceAmt, Period, StartDueDt, SalesmanNm, EMI, EMI_TD, EMI_Rec, EMI_Due, Bucket, Othr_Due, Tot_Due, Clearing_Chrges, Chq_Boucing_Chrges, Penalty_Chrges, Seizing_Chrges, Other_Chrges, category, model)
		select '$dtdt', d.DealId, d.DealNo, d.dealnm, d.Centre,  d.Area, d.City,  d.FY, d.hpdt, d.hpexpdt, d.financeamt, d.Period, d.Startduedt, s.salesmannm,
		(sc.mthlyamt+sc.collectionchrgs) AS EMI, due.emi AS TOT_EMI, IFNULL(rt.REC_EMI,0) AS REC_EMI, due.emi - IFNULL(rt.REC_EMI,0) AS EMI_OD,
		case
		WHEN (due.emi - ifnull(rt.REC_EMI,0)) < 0.5 * (sc.mthlyamt+sc.collectionchrgs) AND DATE_ADD(hpexpdt, INTERVAL -2 MONTH) <= NOW() THEN 111
		WHEN (due.emi - ifnull(rt.REC_EMI,0)) < 0.5 * (sc.mthlyamt+sc.collectionchrgs) AND DATE_ADD(hpexpdt, INTERVAL -2 MONTH) > NOW() THEN 0
		ELSE round((due.emi - ifnull(rt.REC_EMI,0))/(sc.mthlyamt+sc.collectionchrgs)) end as Bucket,
		ifnull(chr.OTHER_DUE,0), (due.emi - IFNULL(rt.REC_EMI,0) + ifnull(chr.OTHER_DUE,0)) as TOT_DUE, rt.d102 as Clearing, rt.d103 as ChqBoucing, rt.d104 as Penalty, rt.d105 as Seizing, rt.d107 as Other, cat.catgid, concat(dv.make, ' ',dv.model, ' ' ,dv.modelyy)
		from
		(select dealid, dealno, dealnm, hpdt, fy, area, city, active, centre, financeamt, period, hpexpdt, startduedt from lksa.tbmdeal where dealsts = 1) as d
		join
		lksa.tbmpmntschd sc
		join
		(select dealid, SUM(u.dueamt + u.CollectionChrgs) AS emi from lksa.tbmduelist u where u.duedt < '$dt' group by dealid) as due
			on d.dealid = due.dealid and sc.dealid = d.dealid
		join
		lksa.tbadealsalesman sa
		join
		lksa.tbmsalesman s
			on d.dealid = sa.dealid and sa.salesmanid = s.salesmanid
		LEFT JOIN
		(SELECT dealid, SUM(chrgsapplied)-SUM(chrgsrcvd) AS OTHER_DUE FROM lksa.`tbmdealchrgs`  where dctyp NOT IN (101, 111) GROUP BY dealid) as chr
			on d.dealid = chr.dealid
		LEFT join
			lksa.tbmdealvehicle dv
			on d.dealid = dv.dealid
		LEFT JOIN
			lksa.tbadealcatagory cat on d.dealid = cat.dealid
		LEFT join
			(
			select sum(rc.rcptamt) as rcptamt, ";
			for($dc=101; $dc <= 111; $dc++){
				$q.= "IFNULL(SUM(d$dc),0) AS d$dc, ";
			}
			$q .= " rc.dealid, IFNULL(SUM(d101),0) + IFNULL(SUM(d111),0) AS REC_EMI from (";
			for ($d =2008; $d <= date('Y'); $d++){
				$yy = $d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
				$q.= "
				SELECT '$yy', r.dealid,";
				for($dc=101; $dc <= 111; $dc++){
					$q.= "SUM(CASE WHEN dctyp=$dc THEN rcptamt ELSE 0 END) AS d$dc, ";
				}
				$q .=" SUM(rd.rcptamt) AS rcptamt FROM lksa$yy.tbxdealrcpt r join lksa$yy.tbxdealrcptdtl rd on r.rcptid = rd.rcptid
				WHERE r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
				UNION";
			}
			$q = rtrim($q, "UNION");

			$q.= "
			) as rc group by rc.dealid
		) as rt
		ON rt.dealid = d.dealid
		#having bucket >=1  limit 0, 2000
		;";
		print_a($q);
		break;

	case 4: //Update last receipt details from receipts table into tbxbucketwisedue table
		//Taking only active SRA and Igonoring direct as well as null SRAs
		echo "#Update last receipt details from receipts table into tbxbucketwisedue table";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, (select l.dealid, l.DT, l.AMT, l.SRA from
		(	SELECT t.dealid, MAX(rcptdt) AS DT, AMT, SRA FROM (
			SELECT Y, dealid, rcptdt, amt, sra FROM (";
		for ($d =2008; $d <= date('Y'); $d++){
			$yy = $d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
			$q.= "
			SELECT '$yy' as y, r.dealid, r.rcptdt as rcptdt, sum(r.totrcptamt) as AMT, b.brkrnm as SRA, b.active FROM lksa$yy.tbxdealrcpt r LEFT JOIN lksa.tbmbroker b on r.sraid = b.brkrid and b.brkrtyp = 2 and b.active = 2 where r.cclflg = 0 AND r.CBflg = 0 and r.rcptpaymode = 1 and sraid is not null and sraid !=223 GROUP BY r.dealid, r.rcptdt HAVING AMT > 0
			UNION";
		}
		$q = rtrim($q, "UNION");
		$q.= "
			) t1 ORDER BY t1.dealid ASC, t1.rcptdt DESC
		) t GROUP BY t.dealid) l join lksa.tbmdeal d on l.dealid = d.dealid and d.dealsts = 1) g set h.LastPaymentDt = g.DT, h.LastPaymentAmt = g.AMT,  h.LastPaymentSRA = g.SRA where h.dealid = g.dealid and h.ason = '$dtdt' ;";
		print_a($q);
		break;

	case 5: //Update tbxbucketwisedue - set recovery information for last month - Recovery status, recovery OD amount, total Amount
		echo "#Update tbxbucketwisedue - set recovery information for last month - Recovery status, recovery OD amount, total Amount";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, ".$dbPrefix_curr.".tbxfieldrcvry fr
		set h.rec_flg = fr.rec_flg,
		h.rec_od = fr.rec_od,
		h.rec_total = fr.rec_total
		where h.ason = '$dtdt' and fr.mm = $mm  and h.dealid = fr.dealid;";
		/*********#######********$$$$$$$ Take Care for "$mm" Parameter here *****$$$$$$######*********/


		print_a($q);
		break;

	case 6: //Update tbxbucketwisedue - set recovery information for last month - recovery sra
		echo "#Update tbxbucketwisedue - set recovery information for last month - recovery sra";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, ".$dbPrefix_curr.".tbxfieldrcvry fr, ".$dbPrefix.".tbmbroker b
		set h.rec_sra = b.brkrnm
		where h.ason = '$dtdt' and mm = $mm and h.dealid = fr.dealid and fr.rec_sraid = b.brkrid and b.brkrtyp = 2;";
		/*********#######********$$$$$$$ Take Care for "$mm" Parameter here *****$$$$$$######*********/

		print_a($q);
		break;

	case 7: //Update tbxbucketwisedue - set recovery information for last month - assigned sra, assigned caller
		echo "#Update tbxbucketwisedue - set recovery information for last month - assigned sra, assigned caller";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, ".$dbPrefix_curr.".tbxfieldrcvry fr, ".$dbPrefix.".tbmbroker b, ".$dbPrefix_user.".tbmuser u
		set h.sranm = b.brkrnm,
		h.callernm = u.realname
		where h.ason = '$dtdt' and mm = $mm and h.dealid = fr.dealid and fr.sraid = b.brkrid and b.brkrtyp = 2 and fr.callerid = u.userid ;";
		/*********#######********$$$$$$$ Take Care for "$mm" Parameter here *****$$$$$$######*********/

		print_a($q);
		break;

	case 8: //Update tbxbucketwisedue - set recovery information for last month - sra comment and caller comment
		echo "#Update tbxbucketwisedue - set recovery information for last month - sra comment and caller comment";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, ".$dbPrefix_curr.".tbxfieldrcvry fr
		set
		h.reccomment_sra = fr.reccomment_sra,
		h.reccomment_caller = fr.reccomment_caller
		where h.ason = '$dtdt' and fr.mm = $mm  and h.dealid = fr.dealid;";
		/*********#######********$$$$$$$ Take Care for "$mm" Parameter here *****$$$$$$######*********/

		print_a($q);
		break;


	case 9: //Update tbxbucketwisedue - set recovery information for last month - SRA Tag
		echo "#Update tbxbucketwisedue - set recovery information for last month - SRA Tag";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, ".$dbPrefix_curr.".tbxfieldrcvry fr, ".$dbPrefix.".tbmrecoverytags t
		set h.rectag_sra = t.description
		where h.ason = '$dtdt' and fr.mm = $mm and h.dealid = fr.dealid and fr.rectagid_sra = t.tagid;";
		/*********#######********$$$$$$$ Take Care for "$mm" Parameter here *****$$$$$$######*********/

		print_a($q);
		break;


	case 10: //Update tbxbucketwisedue - set recovery information for last month - Caller Tag
		echo "#Update tbxbucketwisedue - set recovery information for last month - Caller Tag";

		$q = "UPDATE ".$dbPrefix_curr.".tbxbucketwisedue h, ".$dbPrefix_curr.".tbxfieldrcvry fr, ".$dbPrefix.".tbmrecoverytags t
		set h.rectag_caller = t.description
		where h.ason = '$dtdt' and fr.mm = $mm and h.dealid = fr.dealid and fr.rectagid_caller = t.tagid;";
		print_a($q);
		break;

	case 11: //See bucketwise number of deals for coming month
		echo "#See bucketwise number of deals for coming month";
		$q = "SELECT bucket, COUNT(*) FROM ".$dbPrefix_curr.".tbxbucketwisedue WHERE ason = '$dtdt' and category != 25 GROUP BY bucket";
		print_a($q);
		break;

	case 12: //Get all deal for field recovery table
		echo "#Get all deal for field recovery table";
		$q = "SELECT COUNT(*) FROM $dbPrefix_curr.tbxbucketwisedue WHERE ason = '$dtdt' AND category != 25 AND bucket > 0 AND bucket < 36";
		print_a($q);
		break;

	case 13://Get all deal in bucket 1 group by their due day of the month
		echo "#Get all deal in bucket 1 group by their due day of the month";
		$q = "SELECT DAY(startduedt), bucket, COUNT(*) FROM $dbPrefix_curr.tbxbucketwisedue WHERE ason = '$dtdt' AND bucket = 1 GROUP BY bucket, DAY(startduedt)";
		print_a($q);
		break;

	case 14://Get the final list for assignment
		echo "#Get the final list for assignment (Not taking seized vehicles";
		$q = "SELECT Dealid, dealno AS DealNo, dealnm AS Customer, Centre, `Area`, City, FY, HpDt, hpexpdt AS Expriy_Dt, FinanceAmt, Period, startduedt AS START_DUE_DT, SalesmanNm AS Salesman, EMI, EMI_DUE, Bucket, Tot_Due AS Total_Due, Category, LastPaymentDt AS Last_Payment_Dt, lastpaymentamt AS Last_Payment_Amt, lastpaymentsra AS Last_Payment_To, Model, sranm AS Assigned_SRA, callernm AS Assigned_Caller, rec_flg AS Recovered_Last_Month, rec_sra AS Recovery_BY, rec_od AS Recovered_OD, rec_total AS Recovered_Total, Rectag_Sra, Rectag_Caller  FROM $dbPrefix_curr.tbxbucketwisedue WHERE ason = '$dtdt' AND category != 25 AND bucket > 0 AND bucket <= 36";
		print_a($q);
		break;

	case 15://Get all SRAs with their ids
		echo "#Get all SRAs with their ids";
		$q = "SELECT brkrnm, brkrid, centre FROM lksa.tbmbroker WHERE active = 2 AND brkrtyp = 2";
		print_a($q);
		break;











	case 200: //##List of deals from all years which are in at least 1 bucket (OD >= 0.8 * EMI) as of now. Please note "<=" with duedt is very important
		$q = "
		update ".$dbPrefix_curr.".tbxfieldrcvry fr, (SELECT dealid, dealno, hpdt, fy, due_emi, IFNULL(received,0), due_emi - IFNULL(received,0) AS gap, emi , (due_emi-IFNULL(received,0))/emi AS bucket FROM
		(SELECT u.dealid, d.dealno, d.hpdt, d.period, CONCAT('Y-',d.fy) AS FY, SUM(u.DueAmt+u.CollectionChrgs) AS due_emi, sc.MthlyAmt + sc.CollectionChrgs AS emi
			FROM lksa.tbmduelist u JOIN lksa.tbmdeal d JOIN lksa.tbmpmntschd sc
			ON d.dealsts = 1 AND u.duedt <= DATE_FORMAT(NOW() ,'%Y-%m-%d')  AND sc.dealid = d.dealid
			AND u.dealid = d.dealid AND d.dealsts = 1 GROUP BY u.dealid
		) AS e
		LEFT JOIN
		(SELECT id, SUM(rec) AS received FROM (";

		for ($d =2008; $d <= date('Y'); $d++){
			$yy = $d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
			$q.= "
			SELECT '$yy' as y, SUM(d.rcptamt) AS rec, r.dealid AS id FROM lksa$yy.tbxdealrcpt r JOIN lksa$yy.tbxdealrcptdtl d ON d.rcptid = r.rcptid AND r.CBFlg = 0 AND r.cclflg = 0 WHERE d.DCTyp IN(101,111) GROUP BY r.dealid
			UNION";
		}

		$q = rtrim($q, "UNION");
		$q.= ") AS p GROUP BY p.id
		) AS s
		ON s.id = e.dealid) as due set fr.CurrOd = due.gap where fr.dealid = due.dealid;";

		print_a($q);
		break;

	case 201: //Update OD_current into Field recovery
		$q = "insert into tbxfieldrcvry
		(AsOn, DealId, DealNo, DealNm, Centre, Area, City, FY, HPDt, HPExpDt, FinanceAmt, Period, StartDueDt, SalesmanNm, EMI, EMI_TD, EMI_Rec, EMI_Due, Bucket, Othr_Due, Tot_Due, Clearing_Chrges, Chq_Boucing_Chrges, Penalty_Chrges, Seizing_Chrges, Other_Chrges)
		select '$dtdt', d.DealId, d.DealNo, d.dealnm, d.Centre,  d.Area, d.City,  d.FY, d.hpdt, d.hpexpdt, d.financeamt, d.Period, d.Startduedt, s.salesmannm, (sc.mthlyamt+sc.collectionchrgs) AS EMI, due.emi AS TOT_EMI, IFNULL(rt.REC_EMI,0) AS REC_EMI, due.emi - IFNULL(rt.REC_EMI,0) AS EMI_OD, case when (due.emi - ifnull(rt.REC_EMI,0)) < 0.8 * (sc.mthlyamt+sc.collectionchrgs) then 0 else round((due.emi - ifnull(rt.REC_EMI,0))/(sc.mthlyamt+sc.collectionchrgs)) end as Bucket, chr.OTHER_DUE, (due.emi - IFNULL(rt.REC_EMI,0) +chr.OTHER_DUE) as TOT_DUE, rt.d102 as Clearing, rt.d103 as ChqBoucing, rt.d104 as Penalty, rt.d105 as Seizing, rt.d107 as Other;";

		break;


	case 202://Query to update OD amount

		$q = "select pkid, dealid, InsertTimeStamp  from ".$dbPrefix_curr.".tbxfieldrcvry where odrecovered is null order by pkid desc";
		$totalRows =0;
		$t1 = executeSelect($q);
		if($t1['row_count'] > 0){
			$deals = $t1['r'];
			$totalRows = $t1['found_rows'];
		}
		else die('No data to work on!!');

		$i =0;
		foreach($deals as $deal){
			$pkid = $deal['pkid'];
			$dt = $deal['InsertTimeStamp'];
			$dealid = $deal['dealid'];
			$d = date('d', strtotime($dt)); $mm = date('m', strtotime($dt)); $yy = date('Y', strtotime($dt));
			$q = "update ".$dbPrefix_curr.".tbxfieldrcvry tr, (SELECT  due.dealid, due.emi, rt.REC_EMI, ifnull(due.emi,0) - ifnull(rt.REC_EMI,0) AS due
FROM
(SELECT dealid, SUM(u.dueamt + u.CollectionChrgs) AS emi FROM lksa.tbmduelist u WHERE u.duedt <= '$dt' AND dealid = ".$dealid." GROUP BY dealid) AS due
LEFT JOIN
(
	SELECT rc.dealid, IFNULL(SUM(d101),0) + IFNULL(SUM(d111),0) AS REC_EMI FROM
	(";
	for ($d =2008; $d <= date('Y'); $d++){
		$yy = $d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
		$q.= " SELECT '$yy', r.dealid, SUM(CASE WHEN dctyp=101 THEN rcptamt ELSE 0 END) AS d101, SUM(CASE WHEN dctyp=111 THEN rcptamt ELSE 0 END) AS d111 FROM lksa$yy.tbxdealrcpt r JOIN lksa$yy.tbxdealrcptdtl rd ON r.rcptid = rd.rcptid and r.dealid = $dealid and r.rcptdt <= '$dt' and r.cclflg = 0 AND r.CBflg = 0 GROUP BY r.dealid
		UNION";
	}
	$q = rtrim($q, "UNION");

	$q.= ") AS rc GROUP BY rc.dealid
) AS rt
ON rt.dealid = due.dealid) as d set tr.odrecovered = d.due where tr.pkid = $pkid;";
//		print_a($q);
//		die();
			executeUpdate($q);
			//echo "$pkid , ";
			$i++;
			if($i%20 == 0){
			//	echo "<br>";
			}

			if($i%2000 == 0)
				die();
		}

		echo "<br>=============================DONE=============================";
		break;

	case 203: // Build query to generate tbxPHPField recovery Table
		$dt_arr = array(
		//"2015-04-01","2015-04-05","2015-04-10","2015-04-15","2015-04-20", "2015-04-25", "2015-04-29",
		//"2015-05-01","2015-05-05","2015-05-10","2015-05-15","2015-05-20", "2015-05-25", "2015-05-29",
		"2015-06-01","2015-06-05"
		);

		foreach($dt_arr as $dt){
		$d = date('d', strtotime($dt));

		$q = "insert into lksa201516.tbxPHPfieldrcvry
		(yy, mm, ason, dealid, dealno, dealnm, centre, `area`, city, hpdt, fy, salesmanid, emi, DueEMI_FD, DueEMI_Latest, Recovered_FD, Recovered_Latest, OdEMI_FD, OdEMI_Latest, TotalDue_FD, TotalDue_Latest, bucket_FD, bucket_Latest, sraid)
		select ".date('Y', strtotime($dt)).", ".date('m', strtotime($dt)).", '$dt', d.dealid, d.dealno, d.dealnm, d.centre,  d.area, d.city, d.hpdt, d.fy, s.salesmanid,
		(sc.mthlyamt+sc.collectionchrgs) as EMI,
		". ($d==1 ? " due.emi" : 'null')." as DueEMI_FD, due.emi as DueEMI_Latest,
		". ($d==1 ? " ifnull(rt.rcptamt,0)" : 'null')." as Recovered_FD, ifnull(rt.rcptamt,0) as Recovered_Latest,
		". ($d==1 ? " due.emi - ifnull(rt.rcptamt,0)" : 'null')." as OdEMI_FD,  due.emi - ifnull(rt.rcptamt,0) as OdEMI_Latest,
		null as TotalDue_FD, null as TotalDue_Latest,";

		if($d==1) {
			$q .=" case when (due.emi - ifnull(rt.rcptamt,0)) < 0.8 * (sc.mthlyamt+sc.collectionchrgs) then 0 ";
			for($i=1; $i <= 10; $i++){
			 $q .= " when (due.emi - ifnull(rt.rcptamt,0)) < $i.5 * (sc.mthlyamt+sc.collectionchrgs) then $i ";
			}
			 $q .= " else $i end ";
		}
		else {
			$q .= 'null';
		}

			$q.="  as bucket_FD, case when (due.emi - ifnull(rt.rcptamt,0)) < 0.8 * (sc.mthlyamt+sc.collectionchrgs) then 0 ";
				for($i=1; $i <= 10; $i++){ $q .= " when (due.emi - ifnull(rt.rcptamt,0)) < $i.5 * (sc.mthlyamt+sc.collectionchrgs) then $i ";}
				$q .= " else $i
			end  as bucket_Latest, 0
		from
		(select dealid, dealno, dealnm, hpdt, fy, area, city, active, centre from lksa.tbmdeal where dealsts = 1 or (dealsts = 3 and closedt > '$dt')) as d join lksa.tbmpmntschd sc join
		(select dealid, SUM(u.dueamt + u.CollectionChrgs) AS emi from lksa.tbmduelist u where u.duedt < '$dt' group by dealid) as due
		on d.dealid = due.dealid and d.hpdt < '$dt' and sc.dealid = d.dealid
		join lksa.tbadealsalesman sa join lksa.tbmsalesman s on d.dealid = sa.dealid and sa.salesmanid = s.salesmanid

		left join
		(select rc.dealid, sum(rc.rcptamt) as rcptamt from (";
		for ($d =2008; $d <= date('Y'); $d++){
			$yy = $d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
			$q.= " SELECT '$yy', r.dealid, SUM(rd.rcptamt) AS rcptamt FROM lksa$yy.tbxdealrcpt r join lksa$yy.tbxdealrcptdtl rd on r.rcptid = rd.rcptid
				WHERE (r.cclflg = 0 || (r.cclflg = -1 && r.ccldt >= '$dt')) AND (r.CBflg = 0 ||	(r.cbflg = -1 && r.cbdt >= '$dt')) and rd.dctyp in (101,111) and r.rcptdt < '$dt' GROUP BY r.dealid
		UNION";
		}
		$q = rtrim($q, "UNION");

		$q.= "
		) as rc group by rc.dealid

		) as rt
		ON rt.dealid = d.dealid
		where (due.emi - IFNULL(rt.rcptamt,0)) > 200

		On DUPLICATE KEY UPDATE DueEMI_Latest=due.emi, Recovered_Latest=ifnull(rt.rcptamt,0), OdEMI_Latest =  due.emi - ifnull(rt.rcptamt,0), bucket_Latest = ";

		$q .=" case when (due.emi - ifnull(rt.rcptamt,0)) < 0.8 * (sc.mthlyamt+sc.collectionchrgs) then 0 ";
				for($i=1; $i <= 10; $i++){
				 $q .= "
				 when (due.emi - ifnull(rt.rcptamt,0)) < $i.5 * (sc.mthlyamt+sc.collectionchrgs) then $i ";
				}
			 $q .= " else $i end ;";

		echo "<br>### == $dt ======================================================================================<br>";
		print_a($q);
		//$t1 = executeUpdate($q);
		}

		break;
}//Switch Block Ends here

?>