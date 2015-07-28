<? include_once 'functions.php';
/*************************************
Charts to be added
1. Rural vs Urban
2. Proffession
3. Buckets Graph
5. Calling per day
6. Age Group
7. Sex - M/F
**************************************/

//$charts = executeSelect("select * from charts where active = 1 order by id");

$mm = date('m');; $yy = date('Y');
$dbPrefix_curr = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy,-2) : $yy."".(substr($yy,-2)+1));
$dbPrefix_last = "lksa".($mm < 4 ? ($yy - 1)."".substr($yy-1,-2) : ($yy-1)."".(substr($yy-1,-2)+1));

$activeClause = " d.cancleflg =0 and d.dealsts !=2 ";
$qOpen = ""; $qClosed = "";
for ($d =2008; $d <= date('Y'); $d++){
	$db = "lksa".$d."".str_pad($d+1-2000, 2, '0', STR_PAD_LEFT);
	$qOpen .=" select '$db' as yr, r.Dealid, -sum(r.CBFlg) as Bounces from $db.tbxdealrcpt r join lksa.tbmdeal d on r.dealid = d.dealid and d.dealsts = 1 group by r.dealid
	UNION";
	$qClosed .=" select '$db' as yr, r.Dealid, -sum(r.CBFlg) as Bounces from $db.tbxdealrcpt r join lksa.tbmdeal d on r.dealid = d.dealid and d.dealsts = 3 group by r.dealid
	UNION";
}
$qOpen = rtrim($qOpen, "UNION");$qClosed = rtrim($qClosed, "UNION");

//print_a("select t1.Bounces, Count(dealid) as Deals from (select t.Dealid, sum(t.Bounces) as Bounces from (".$qOpen.") as t group by t.Dealid) t1 group by Bounces");

$ctr = 0;
$charts = Array (
   $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "yearlyfinanceamount",
	    'heading' => "Yearly Financed Amount",
	    'type' => "ColumnChart",
	    'query' => "SELECT FY, round(SUM(FinanceAmt)/10000000,2) as FinanceAmount FROM lksa.tbmdeal d where $activeClause GROUP BY FY",
	    'options' => "{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#7093B1'],legend: {position: 'none' },title:'Total financed till date %total% Cr' ,vAxis: {viewWindow: {min: 0},gridlines:{count:10}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "monthlyfinanceamount",
	    'heading' => "Last 12 Months Financed Amount",
	    'type' => "ColumnChart",
	    'query' => "select Month, FinanceAmount from (SELECT Year(hpdt) as Y, month(hpdt) as M, DATE_FORMAT(hpdt, '%b-%y') as Month, round(SUM(FinanceAmt)/10000000,2) as FinanceAmount FROM lksa.tbmdeal d where $activeClause GROUP BY Y desc, M desc limit 13) t order by t.Y, t.M",
	    'options' => "{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#DE4D4E'],legend: {position: 'none' },title:'Financed in Last 13* Months: %total% Cr',vAxis: {viewWindow: {min: 0},gridlines:{count:10}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "yearlyavgloan",
	    'heading' => "Annual Average Loan Amount",
	    'type' => "LineChart",
	    'query' => "SELECT FY, round(SUM(FinanceAmt)/count(dealid)) as Avg_Loan FROM lksa.tbmdeal d where $activeClause GROUP BY FY",
	    'options' => "{width: 490, height: 350, chartArea:{left:50,top:20,width:'90%',height:'80%'},colors:['#0E3D59'],legend: {position: 'none' },vAxis: {viewWindow: {min:25000},gridlines:{count:10}}, lineWidth: 5,}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "monthlyavgloan",
	    'heading' => "Last 12 Month Average Loan",
	    'type' => "LineChart",
	    'query' => "select month, Avg_Loan from (SELECT Year(hpdt) as Y, Month(hpdt) as M, DATE_FORMAT(hpdt, '%b-%y') as month, round(SUM(FinanceAmt)/count(dealid)) as Avg_Loan FROM lksa.tbmdeal d where $activeClause GROUP BY Y desc, M desc limit 13) t order by t.Y, t.M",
	    'options' => "{width: 490, height: 350, chartArea:{left:50,top:20,width:'90%',height:'80%'},colors:['#0E3D59'],legend: {position: 'none' },vAxis: {viewWindow: {min:25000},gridlines:{count:10}},lineWidth: 5,}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "yearlyvehicles",
	    'heading' => "Yearly Vehicles",
	    'type' => "ColumnChart",
	    'query' => "SELECT FY, count(dealid) as Vehicles FROM lksa.tbmdeal d where $activeClause GROUP BY FY",
	    'options' => "{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#FFD041'],legend: {position: 'none' },title:'Across All Centers till date: %total%', vAxis: {viewWindow: {min: 0},gridlines:{count:10}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "monthlyvehicles",
	    'heading' => "Last 12 Months Vehicles",
	    'type' => "ColumnChart",
	    'query' => "select month, Vehicles from (SELECT Year(hpdt) as Y, Month(hpdt) as M, DATE_FORMAT(hpdt, '%b-%y') as month, count(dealid) as Vehicles FROM lksa.tbmdeal d where $activeClause GROUP BY Y desc, M desc limit 13) t order by t.Y, t.M",
	    'options' => "{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#6E9ECF'],legend: {position: 'none' },title:'Across all centers in last 12 months: %total%', vAxis: {viewWindow: {min: 0},gridlines:{count:10}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "income",
	    'heading' => "Annual Income",
	    'type' => "PieChart",
	    'query' => "SELECT concat(round(floor(AnnualIncome/50000)*0.5,1), ' to ', round(floor(AnnualIncome/50000)*0.5,1) +0.5 ,'L') as AI, count(*) as Cnt FROM lksa.tbmdeal d where $activeClause group by AI order by cnt desc",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Customers by Annual Income (in Lacs)',pieHole: 0.6}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),

    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "vehiclecost",
	    'heading' => "Vehicle Cost Trend",
	    'type' => "LineChart",
	//    'query' => "SELECT FY, COUNT(dealid) AS Cnt FROM lksa.tbmdeal d WHERE $activeClause GROUP BY FY ORDER BY FY ASC",
/*	    'query' => "SELECT d.FY, SUM(CASE WHEN CostOfVhcl < 50000 THEN 1 ELSE 0 END) AS 'LT 50K',
SUM(CASE WHEN CostOfVhcl >= 50000 AND CostOfVhcl < 60000 THEN 1 ELSE 0 END) AS '50K-60K',
SUM(CASE WHEN CostOfVhcl >= 60000 AND CostOfVhcl < 70000 THEN 1 ELSE 0 END) AS '60K-70K',
SUM(CASE WHEN CostOfVhcl >= 70000 AND CostOfVhcl < 80000 THEN 1 ELSE 0 END) AS '70K-80K',
SUM(CASE WHEN CostOfVhcl >= 80000 AND CostOfVhcl < 90000 THEN 1 ELSE 0 END) AS '80K-90K',
SUM(CASE WHEN CostOfVhcl >= 90000 AND CostOfVhcl < 100000 THEN 1 ELSE 0 END) AS '90K-1L',
SUM(CASE WHEN CostOfVhcl >= 100000 THEN 1 ELSE 0 END) AS 'GT 1L'
FROM tbmdeal d WHERE d.cancleflg = 0 AND d.dealsts != 2 GROUP BY d.FY ASC;",
*/
		'query' => "SELECT FY, ROUND(50K/Total*100) AS LT50K,
ROUND(60K/Total*100) AS LT60K,
ROUND(70K/Total*100) AS LT70K,
ROUND(80K/Total*100) AS LT80K,
ROUND(80KP/Total*100) AS GT1L FROM (
SELECT d.FY, SUM(CASE WHEN CostOfVhcl < 50000 THEN 1 ELSE 0 END) AS 50K,
SUM(CASE WHEN CostOfVhcl >= 50000 AND CostOfVhcl < 60000 THEN 1 ELSE 0 END) AS 60K,
SUM(CASE WHEN CostOfVhcl >= 60000 AND CostOfVhcl < 70000 THEN 1 ELSE 0 END) AS 70K,
SUM(CASE WHEN CostOfVhcl >= 70000 AND CostOfVhcl < 80000 THEN 1 ELSE 0 END) AS 80K,
SUM(CASE WHEN CostOfVhcl >= 80000 THEN 1 ELSE 0 END) AS 80KP,
COUNT(dealid) AS Total
FROM tbmdeal d WHERE d.cancleflg = 0 AND d.dealsts != 2 GROUP BY d.FY) t;",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Percentage vehiles with cost range',pieHole: 0.6}",
	    'active' => "1",
	    'columns' => "2",
	    'objectcolumn' => "0",
	),

    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "installments",
	    'heading' => "# Installments",
	    'type' => "PieChart",
	    'query' => "SELECT period, count(dealid) as cnt FROM lksa.tbmdeal d where $activeClause group by period order by cnt desc",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Distribution of Deals by number of Installments'}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "states",
	    'heading' => "Deals by State",
	    'type' => "PieChart",
	    'query' => "SELECT tcase(state), COUNT(dealid) AS Deals FROM tbmdeal WHERE dealsts != 2 AND cancleflg = 0 GROUP BY state ORDER BY Deals DESC",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Customers by States',pieHole: 0.6}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => "9",
	    'subgroup' => "1",
	    'title' => "cities08",
	    'heading' => "Yearly Centrewise Deals",
	    'type' => "BarChart",
	    'query' => "SELECT tcase(c.centrenm) AS `name`, t.deals as `08-09` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='08-09' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width:200, height: 500, chartArea:{left:90,top:20,width:'80%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "2",
	    'title' => "cities09",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `09-10` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='09-10' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#dc3912'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "3",
	    'title' => "cities10",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `10-11` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='10-11' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#ff9900'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "4",
	    'title' => "cities11",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `11-12` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='11-12' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#109618'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "5",
	    'title' => "cities12",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `12-13` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='12-13' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#990099'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "6",
	    'title' => "cities13",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `13-14` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='13-14' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#0099c6'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "7",
	    'title' => "cities14",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `14-15` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='14-15' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#dd4477'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "7",
	    'title' => "cities15",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "SELECT c.centrenm AS `name`, t.deals as `15-16` FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) AS deals FROM lksa.tbmdeal d WHERE $activeClause AND FY ='15-16' GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#0099c6'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => "8",
	    'title' => "citiestotal",
	    'heading' => NULL,
	    'type' => "BarChart",
	    'query' => "select c.centrenm as `name`, t.deals as Total FROM lksa.tbmcentre c LEFT JOIN (SELECT centre, COUNT(dealid) As deals FROM lksa.tbmdeal d WHERE $activeClause GROUP BY centre) t ON c.Centrenm = t.centre ORDER BY c.CentreId ASC",
	    'options' => "{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#dc3912'] }",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "brands",
	    'heading' => "Distribution by Brand",
	    'type' => "PieChart",
	    'query' => "SELECT v.make, COUNT(d.dealid) AS Deals FROM lksa.tbmdeal d LEFT JOIN lksa.tbmdealvehicle v ON d.dealid = v.dealid WHERE $activeClause GROUP BY v.make ORDER BY Deals DESC",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',pieHole: 0.3,slices: {0:{offset: 0.1}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "status",
	    'heading' => "Bucketwise Performance for ".date('M-Y'),
	    'type' => "PieChart",
	    'query' => "SELECT IFNULL(r.rgid,0) AS Bucket, COUNT(d.dealid) AS Deals FROM lksa.tbmdeal d LEFT JOIN ".$dbPrefix_curr.".tbxfieldrcvry r ON d.dealid = r.dealid AND r.mm = ".date('n')." WHERE d.dealsts = 1 GROUP BY r.rgid;",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total Active Cases: %total%',pieHole: 0.3,slices: {0:{offset: 0.1}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),

	/*******************Recovery Related******************
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "cbopen",
	    'heading' => "Cheque Bouncing (Closed Deals)",
	    'type' => "PieChart",
	    'query' => "SELECT Bounces, SUM(Deals) AS Deals FROM (SELECT CASE WHEN Bounces < 6 THEN Bounces ELSE '6+' END AS Bounces, COUNT(dealid) AS Deals FROM (SELECT t.Dealid, SUM(t.Bounces) AS Bounces FROM (".$qClosed.") AS t GROUP BY t.Dealid) t1 GROUP BY Bounces) t2 GROUP BY t2.Bounces",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',pieHole: 0.3,slices: {0:{offset: 0.1}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "cbclosed",
	    'heading' => "Cheque Bouncing (Open Deals)",
	    'type' => "PieChart",
	    'query' => "SELECT Bounces, SUM(Deals) AS Deals FROM (SELECT CASE WHEN Bounces < 6 THEN Bounces ELSE '6+' END AS Bounces, COUNT(dealid) AS Deals FROM (SELECT t.Dealid, SUM(t.Bounces) AS Bounces FROM (".$qOpen.") AS t GROUP BY t.Dealid) t1 GROUP BY Bounces) t2 GROUP BY t2.Bounces",
		    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',pieHole: 0.3,slices: {0:{offset: 0.1}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
	*************************************************************/
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "cashcollection",
	    'heading' => "#Visits for Cash Collection",
	    'type' => "ColumnChart",
	    'active' => "1",
	    'columns' => "1",
	    'query' => "SELECT `Month`, Deals FROM
(SELECT DATE_FORMAT(rcptdt, '%Y-%m') AS dt, DATE_FORMAT(rcptdt,'%b-%y') AS `Month`, COUNT(DISTINCT dealid) AS Deals FROM ".$dbPrefix_curr.".tbxdealrcpt WHERE rcptpaymode = 1 AND cclflg = 0 GROUP BY MONTH(rcptdt)
UNION
SELECT DATE_FORMAT(rcptdt,'%Y-%m') AS dt, DATE_FORMAT(rcptdt,'%b-%y') AS `Month`, COUNT(DISTINCT dealid) AS Deals FROM ".$dbPrefix_last.".tbxdealrcpt WHERE rcptpaymode = 1 AND cclflg = 0 GROUP BY MONTH(rcptdt)
)t ORDER BY t.dt DESC LIMIT 0,7",
	    'options' => "{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#7093B1'],legend: {position: 'none' },title:'By field executives' ,vAxis: {viewWindow: {min: 0},gridlines:{count:10}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),



	/*******************Risk Related******************/
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "marginchart",
	    'heading' => "Margin Amount trend",
	    'type' => "AnnotatedTimeLine",
	    'query' => "SELECT concat('new Date(',year(hpdt), ',',month(hpdt),',1)') as dt, round(avg(costofvhcl/1000),2) as vehiclecost, round(avg(marginmoney/costofvhcl*100),2) as margin FROM lksa.tbmdeal d where $activeClause AND costofvhcl != 0 group by year(hpdt), month(hpdt)",
	    'options' => "{width:470, height:350, displayAnnotations: false}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "1",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "highriskdeals",
	    'heading' => "Risky Deals (Deals < 30% Margin money)",
	    'type' => "LineChart",
	    'query' => "select t1.Year, round(t2.Deals/t1.Deals*100) as RiskyPercentage from (SELECT FY as Year, count(dealid) as Deals FROM lksa.tbmdeal d where $activeClause group by FY) t1 join ( SELECT FY as Year, count(dealid) as Deals FROM lksa.tbmdeal d where $activeClause AND (marginmoney/costofvhcl*100) <= 30 and costofvhcl != 0 group by FY) t2 on t1.Year = t2.Year",
	    'options' => "{width: 490, height: 350, chartArea:{left:50,top:20,width:'90%',height:'80%'},colors:['#0E3D59', '#916A4B', '#EA837C'],legend: {position: 'bottom' },title:'#Risky Deals & Percentage of Risky Deal per year',vAxis: {viewWindow: {min:0}},lineWidth: 2,}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),
    $ctr => Array(
	    'id' => $ctr++,
	    'group' => NULL,
	    'subgroup' => NULL,
	    'title' => "religion",
	    'heading' => "Religion",
	    'type' => "PieChart",
	    'query' => "SELECT  c.SystemDscrptn as Religion, COUNT(d.dealid) as Deals FROM tbmdeal d JOIN tbmcodedscrptnmasterdata c ON d.dealrlgn = c.CodeDscrptnMasterDataId  AND c.MasterId = 118 AND $activeClause GROUP BY d.dealrlgn",
	    'options' => "{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total: %total%',pieHole: 0.3,slices: {0:{offset: 0.1}}}",
	    'active' => "1",
	    'columns' => "1",
	    'objectcolumn' => "0",
	),

/*	$ctr => Array(
		'id' => $ctr++,
		'group' => NULL,
		'subgroup' => NULL,
		'title' => "centres",
		'heading' => "Centre Wise #vehicles",
		'type' => "BarChart",
		'query' => "SELECT centre, count(dealid) as deals FROM lksa.tbmdeal d WHERE $activeClause GROUP BY centre ORDER BY deals DESC",
		'options' => "{width: 490, height: 550, chartArea:{left:125,top:20,width:'80%',height:'80%'},legend: {position: 'none' },title:'Total: %total%',hAxis: {viewWindow: {min: 0},textPosition: 'none'}}",
		'active' => "1",
	    'columns' => "1",
		'objectcolumn' => "0",
	),
*/
);

$totals = array();
$columns = array();
$dataString = "var dashboardData  = {";
foreach($charts as $i =>$chart){
	$q = $chart['query'];
	echo "$i ==".$chart['title']."<br>";

	$t1 = executeSelect ($q);
	if($t1['row_count'] > 0){
		$res = $t1['r'];
	}
	else{
		echo "No data. $q";
		die();
	}

	$dataString .= "
	".$chart['title']."  :{data:[[";
	foreach(array_keys($res[0]) as $key)
		$dataString .= "'$key',";


	$dataString .= "],";
	$totals[$chart['title']] =0;$columns[$chart['title']] = count($res[0]);
	foreach($res as $index=>$row){
		$dataString .= "[";
		$i=0;
		foreach($row as $key => $value){
			//$columns[$chart['title']]++;
			if($i==0 && $chart['objectcolumn']==0)
				$dataString .= "'$value',";
			else{
			 	if(is_null($value))
			 		$value  = 0;
			 	$dataString .= $value.",";
			 	$totals[$chart['title']] += $value;
			 }
			$i++;
		}
		$dataString .= "],";
	}
	$dataString .= "]},";
}
$dataString = rtrim($dataString, ",");
$dataString .= "};";

$optionsString = "var options = {";
foreach($charts as $i =>$chart){
	$chart['options'] = str_replace("%total%", $totals[$chart['title']],  $chart['options']);
	$optionsString .= $chart['title'].":".$chart['options'].",
	";
}
$optionsString .= "};";

$dispalyString = "";

foreach($charts as $i =>$chart){
	$dispalyString .="
	data = new google.visualization.arrayToDataTable(dashboardData.".$chart['title'].".data);
	view = new google.visualization.DataView(data);
	view.setColumns([0";
	for($i=1;$i<$columns[$chart['title']];$i++){
		$dispalyString .=",$i,{calc:'stringify', sourceColumn: $i, type:'string', role:'annotation'}
		";
	}
	$dispalyString .="]);
	chart = new google.visualization.".$chart['type']."(document.getElementById('".$chart['title']."'));
	chart.draw(view, options.".$chart['title'].");";
}
ob_start();
?>
<!DOCTYPE html>
<!--[if IE 6]><html class="lt-ie9 lt-ie8 ie6"> <![endif]-->
<!--[if IE 7]><html class="lt-ie9 lt-ie8 ie7"> <![endif]-->
<!--[if IE 8]><html class="lt-ie9 ie8"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="">
<!--<![endif]-->

<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Loksuvidha Dashboard</title>
	<!-- Include CSS files -->
	<!--link rel="stylesheet" type="text/css" href="css/reset.css" /-->
	<!-- Include layout file for dashboard -->
	<link rel="stylesheet" type="text/css" href="css/layout.css" />
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript">
	<?=$dataString?>

	<?=$optionsString?>

	google.load('visualization', '1', {'packages':['corechart']});
	google.load('visualization', '1', {'packages':['table']});
	google.load('visualization', '1', {'packages':['annotatedtimeline']});

	google.setOnLoadCallback(drawChart);
	function drawChart() {var data, view, chart; <?=$dispalyString?>}
	</script>
</head>

<body>
    <!-- Modal Window -->
    <div id="modalWrapper">
        <a id="close-modal" class="close-modal icon" href="javascript:void(0)"></a>
        <h3 id="modal-title"></h3>
        <div class="modal" id='modal'>
        </div>
    </div>
    <!-- Modal Window ends -->

    <!-- The top level wrapper for markup begins -->
    <div id="wrapper">
        <!-- Container for the dashboard -->
        <div id="container">
            <!--div class="logo-container"><div id="fflogo"></div></div-->
            <div id="dashboard-content">
                <!-- Sidebar -->
                <!--div id="sidebar" class="pull-left">
                    <div id="main-menu" class="clearboth">
                        <div id="main-menu-icon" class="icon pull-left"></div>
                        <h4 class="pull-left">Dashboard</h4>
                    </div>
                </div-->
                <!-- Sidebar ends -->

                <!-- Summary Tab -->
                <div id="summary"><!-- class="hidden"-->
                    <div class="chart-content pull-right">
<?			$grpStart  = 0; $item = 1;
			foreach($charts as $i =>$chart){
				//echo "GRP: ".$chart['group']." SUB: ".$chart['subgroup'] ." GRPSTART: $grpStart i=$i<br>";
				if($chart['group'] > 0 or $chart['subgroup'] > 0){
					if($chart['group'] > 0) {
						$grpStart = $chart['group'] + $i -1;?>
						<h1 class="chart-group-heading pull-left"><?=$chart['heading']?></h1><div class="clearboth"></div>
						<div class="chart-category full-width">
						<div class="divider <?if($i%2==0){ echo 'clearboth';}?>"></div>
					<?}?>
					<div id="<?=$chart['title']?>" class="peryear"></div>
					<?if($grpStart == $i){ $grpStart=0;?></div><?}?>
				<?} else{?>
				<div class="chart-category pull-left <?if($item%2==0){ echo 'no-border';}?>">
					<h2 class="chart-category-heading"><?=$chart['heading']?></h2>
					<div class="divider <?if($i%2==1){ echo 'clearboth';}?>"></div>
					<div id="<?=$chart['title']?>"></div>
				</div>
				<?$item++;
				}?>
			<?}?>
                    </div>
                </div>
            </div>
        </div>
        <div class="clr"></div>
        <!-- Container for the dashboard ends -->
    </div>
    <!-- The top level wrapper for markup ends -->
	<div class="refresh-ico"><a href="#" onclick="javascript:refreshDashboard(); return false;"><img src ="/images/icon_refresh.png" width="16"/></a></div>

</body>
</html>
<?
$htmlStr = ob_get_contents();
// Clean (erase) the output buffer and turn off output buffering
ob_end_clean();
// Write final string to file
file_put_contents('dashboard.php', $htmlStr);
?>