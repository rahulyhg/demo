<?php
require './mail/PHPMailerAutoload.php';
require 'functions.php';

/***Run update queries
1. Update all seize marked vehicles are recovered for this month
2. Update all deals where receipts are taken as receovered
***/

$mm = date('m');; $yy = date('Y');
$dbPrefix = 'lksa';
$dbPrefix_curr = "$dbPrefix".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));
$dbPrefix_last = "$dbPrefix".($mm < 4 ? ($yy - 1)."".substr($yy-1,-2) : ($yy-1)."".(substr($yy-1,-2)+1));
$today = date('Y-m-d');

//#### Update catid for seized vehicles in a given month in field recovery table
$q = "UPDATE $dbPrefix_curr.tbxfieldrcvry fr SET rec_flg = 1, catid = 25 WHERE fr.mm = MONTH(NOW()) AND fr.dealid IN (SELECT dv.dealid FROM $dbPrefix.tbmdealvehicle dv JOIN $dbPrefix_curr.tbxvhclsz s ON dv.dealid = s.dealid AND dv.siezeflg = -1 WHERE MONTH(vhclszdt) = MONTH(NOW()) AND YEAR(vhclszdt) = YEAR(NOW()) AND cclflg = 0)";

$rows1 = executeUpdate($q);
//#### Update field recovery current month set rec_flg = 1 where at least one cash receipt is received and total received OD amount > 450/-
$q = "UPDATE $dbPrefix_curr.tbxfieldrcvry fr JOIN (SELECT dealid, sraid, SUM(odAmt) AS odAmt, SUM(tot) AS tot FROM (
SELECT r.dealid, r.sraid, SUM(CASE WHEN rd.DcTyp IN(101,102,111) THEN rd.RcptAmt END) AS odAmt, SUM(rd.RcptAmt) AS tot
		FROM $dbPrefix_curr.tbxdealrcpt AS r JOIN $dbPrefix_curr.tbxdealrcptDtl AS rd
		ON r.RcptId = rd.RcptId WHERE MONTH(r.RcptDt) = MONTH(NOW()) AND r.RcptPayMode=1 AND r.cbFlg=0 AND r.cclFlg=0
		GROUP BY r.dealid, r.sraid ORDER BY r.dealid, odAmt DESC, r.sraid) t GROUP BY dealid HAVING odAmt >= 450) AS rt
		ON fr.dealid = rt.dealid  AND fr.mm = MONTH(NOW())
		SET fr.rec_sraid = rt.sraid, fr.rec_od = IFNULL(rt.odamt,0), fr.rec_total = IFNULL(rt.tot,0), fr.rec_flg = 1";
$rows2 = executeUpdate($q);

$q = "SELECT count(dv.dealid) as cnt FROM $dbPrefix.tbmdealvehicle dv JOIN $dbPrefix_curr.tbxvhclsz s ON dv.dealid = s.dealid AND dv.siezeflg = -1
WHERE vhclszdt = '$today' AND cclflg = 0";
$seized = executeSingleSelect($q);

//SUM(CASE WHEN rd.DcTyp IN(101,102,111) THEN rd.RcptAmt END) AS odAmt,
$q = "SELECT SUM(rd.RcptAmt) AS tot FROM $dbPrefix_curr.tbxdealrcpt AS r JOIN $dbPrefix_curr.tbxdealrcptDtl AS rd ON r.RcptId = rd.RcptId WHERE r.RcptDt = '$today' AND r.RcptPayMode=1 AND r.cbFlg=0 AND r.cclFlg=0";
$recovery_amt = nf(executeSingleSelect($q),true);

$q = "SELECT
SUM(CASE WHEN dd=1 THEN 1 ELSE 0 END) as opa, SUM(CASE WHEN dd=1 AND rec_flg = 1 THEN 1 ELSE 0 END) as opr, SUM(CASE WHEN dd=1 AND rec_flg = 1 THEN 1 ELSE 0 END)/SUM(CASE WHEN dd=1 THEN 1 ELSE 0 END)*100 as OPer,
SUM(CASE WHEN dd>1 THEN 1 ELSE 0 END) as nwa, SUM(CASE WHEN dd>1 AND rec_flg = 1 THEN 1 ELSE 0 END) as nwr, SUM(CASE WHEN dd>1 AND rec_flg = 1 THEN 1 ELSE 0 END)/SUM(CASE WHEN dd>1 THEN 1 ELSE 0 END)*100 as NPer,
count(dealid) as total, SUM(CASE WHEN rec_flg = 1 THEN 1 ELSE 0 END) as recovered, SUM(CASE WHEN rec_flg = 1 THEN 1 ELSE 0 END)/count(dealid)*100 as TPer
from $dbPrefix_curr.tbxfieldrcvry where mm = MONTH(NOW())";

$t1 = executeSelect($q);
if($t1['row_count'] <= 0){
	echo "No deal. Please click on the deal in the 'Deal Search' menu option.";
	die();
}
$recovery = $t1['r'][0];

$q = "SELECT count(dealid) from $dbPrefix.tbmdeal where cancleflg = 0 and hpdt = '$today'";
$logged_cases = executeSingleSelect($q);

$q = "SELECT COUNT(proposalno) FROM $dbPrefix.tbmproposal WHERE dono != 0 AND DATE(dotime) = DATE(CURDATE())";
$do_sent= executeSingleSelect($q);


$body  = "
<b>Todays Business Status</b><br><br>
<table border='1' cellpadding='2' style='borborder: 1px solid black; text-align:right; border-collapse: collapse;'>
<tr><th>New Cases</th><td>$logged_cases</td></tr>
<tr><th>DO Sent</th><td>$do_sent</td></tr>
<tr><th>Cash Recovery</th><td>$recovery_amt</td></tr>
<tr><th>Vehicles Seized</th><td>$seized</td></tr>
</table>
<br>
<b>Recovery Status</b><br><br>";

$body .="
<table border='1' cellpadding='2' style='borborder: 1px solid black; text-align:right; border-collapse: collapse;'>
<tr><th></th><th>Cases</th><th>Recovered</th><th>%</th></tr>
<tr><th>Opening</th><td>".nf($recovery['opa'],true)."</td><td><b>".nf($recovery['opr'],true)."</b></td><td>".nf($recovery['OPer'], true)."%</td></tr>
<tr><th>New</th><td>".nf($recovery['nwa'],true)."</td><td><b>".nf($recovery['nwr'],true)."</b></td><td>".nf($recovery['NPer'])."%</td></tr>
<tr><th>Total</th><td>".nf($recovery['total'],true)."</td><td><b>".nf($recovery['recovered'],true)."</b></td><td>".nf($recovery['TPer'], true)."%</td></tr>
</table>
";


$body .="
<br>
Thanks, <br>
Team Loksuvidha.<br>
<br>
Note: This is automated mail. Please do not reply!<br>";

$mail = new PHPMailer;
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;
$mail->Username = "lksadailytask@gmail.com";
$mail->Password = "lksatask";
$mail->setFrom('lksadailytask@gmail.com', 'LokSuvidha');
$mail->addReplyTo('lksadailytask@gmail.com', 'LokSuvidha');


$mail->addAddress('manoj.pagdhune@loksuvidha.com', 'Manoj Pagdhune');
$mail->addAddress('kamlesh.laddhad@gmail.com', 'Kamlesh Laddhad');
$mail->addAddress('nimish.laddhad@gmail.com', 'Nimish Laddhad');
$mail->addAddress('geeta.ghate@loksuvidha.com', 'Geeta Ghate');

$mail->Subject = "LokSuvidha Status:".date('d-M-Y');
$mail->Body    = $body;
$mail->AltBody = $body;

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';

//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
