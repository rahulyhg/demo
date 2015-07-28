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
	var dashboardData  = {
	yearlyfinanceamount  :{data:[['FY','FinanceAmount',],['08-09',1.66,],['09-10',5.62,],['10-11',10.11,],['11-12',14.18,],['12-13',12.74,],['13-14',14.64,],['14-15',26.81,],['15-16',9.22,],]},
	monthlyfinanceamount  :{data:[['Month','FinanceAmount',],['Jul-14',1.85,],['Aug-14',1.72,],['Sep-14',1.62,],['Oct-14',2.65,],['Nov-14',3.79,],['Dec-14',2.43,],['Jan-15',3.07,],['Feb-15',2.19,],['Mar-15',2.93,],['Apr-15',2.46,],['May-15',2.76,],['Jun-15',2.76,],['Jul-15',1.24,],]},
	yearlyavgloan  :{data:[['FY','Avg_Loan',],['08-09',28266,],['09-10',28363,],['10-11',30018,],['11-12',31263,],['12-13',32836,],['13-14',35719,],['14-15',38816,],['15-16',42865,],]},
	monthlyavgloan  :{data:[['month','Avg_Loan',],['Jul-14',37198,],['Aug-14',37980,],['Sep-14',38010,],['Oct-14',38936,],['Nov-14',38278,],['Dec-14',39379,],['Jan-15',40369,],['Feb-15',41440,],['Mar-15',41839,],['Apr-15',42346,],['May-15',42645,],['Jun-15',42685,],['Jul-15',44892,],]},
	yearlyvehicles  :{data:[['FY','Vehicles',],['08-09',586,],['09-10',1982,],['10-11',3367,],['11-12',4536,],['12-13',3881,],['13-14',4100,],['14-15',6906,],['15-16',2150,],]},
	monthlyvehicles  :{data:[['month','Vehicles',],['Jul-14',497,],['Aug-14',452,],['Sep-14',426,],['Oct-14',681,],['Nov-14',990,],['Dec-14',618,],['Jan-15',760,],['Feb-15',529,],['Mar-15',700,],['Apr-15',581,],['May-15',647,],['Jun-15',646,],['Jul-15',276,],]},
	income  :{data:[['AI','Cnt',],['1.0 to 1.5L',11581,],['0.5 to 1.0L',5181,],['2.0 to 2.5L',4768,],['1.5 to 2.0L',3226,],['3.0 to 3.5L',673,],['2.5 to 3.0L',580,],['3.5 to 4.0L',492,],['0.0 to 0.5L',324,],['4.0 to 4.5L',177,],['4.5 to 5.0L',176,],['5.0 to 5.5L',103,],['6.0 to 6.5L',103,],['7.0 to 7.5L',33,],['8.0 to 8.5L',17,],['10.0 to 10.5L',16,],['5.5 to 6.0L',14,],['12.0 to 12.5L',9,],['9.5 to 10.0L',6,],['6.5 to 7.0L',4,],['7.5 to 8.0L',3,],['18.0 to 18.5L',2,],['20.0 to 20.5L',2,],['10.5 to 11.0L',2,],['9.0 to 9.5L',2,],['15.0 to 15.5L',2,],['150.0 to 150.5L',1,],['13.0 to 13.5L',1,],['15.5 to 16.0L',1,],['8.5 to 9.0L',1,],['80.0 to 80.5L',1,],['30.0 to 30.5L',1,],['40.0 to 40.5L',1,],['13.5 to 14.0L',1,],['24.0 to 24.5L',1,],['60.0 to 60.5L',1,],['36.0 to 36.5L',1,],['37.0 to 37.5L',1,],]},
	vehiclecost  :{data:[['FY','LT50K','LT60K','LT70K','LT80K','GT1L',],['08-09',78,13,8,1,0,],['09-10',77,13,9,1,0,],['10-11',61,29,7,2,1,],['11-12',46,44,3,5,1,],['12-13',30,57,5,4,3,],['13-14',14,65,12,6,4,],['14-15',6,65,17,6,6,],['15-16',2,54,28,5,11,],]},
	installments  :{data:[['period','cnt',],['24',11167,],['18',5938,],['12',3713,],['30',1662,],['36',1011,],['22',891,],['20',660,],['10',489,],['16',446,],['23',338,],['35',306,],['17',200,],['11',166,],['15',159,],['21',63,],['33',42,],['28',37,],['25',30,],['27',30,],['19',30,],['29',28,],['8',28,],['34',16,],['9',15,],['13',13,],['14',12,],['26',8,],['32',5,],['6',2,],['31',2,],['5',1,],]},
	states  :{data:[['tcase(state)','Deals',],['Madhya Pradesh',17853,],['Maharashtra',7279,],['Chhattisgarh',2372,],['Tamil Nadu',1,],['Punjab',1,],['New Delhi',1,],['Gujrat',1,],]},
	cities08  :{data:[['name','08-09',],['Nagpur',0,],['Amarwara',0,],['Aurangabad',0,],['Betul',0,],['Bhusawal',0,],['Chhindwara',333,],['Dewas',0,],['Ujjain',0,],['Dhule',0,],['Indore',0,],['Jalgaon',0,],['Mandla',0,],['Mhow',0,],['Nashik',0,],['Pandhurna',79,],['Raipur',0,],['Seoni',174,],['Shahada',0,],]},
	cities09  :{data:[['name','09-10',],['NAGPUR',0,],['AMARWARA',0,],['AURANGABAD',0,],['BETUL',0,],['BHUSAWAL',0,],['CHHINDWARA',547,],['DEWAS',0,],['UJJAIN',0,],['DHULE',0,],['INDORE',766,],['JALGAON',0,],['MANDLA',0,],['MHOW',25,],['NASHIK',0,],['PANDHURNA',109,],['RAIPUR',91,],['SEONI',444,],['SHAHADA',0,],]},
	cities10  :{data:[['name','10-11',],['NAGPUR',70,],['AMARWARA',0,],['AURANGABAD',0,],['BETUL',110,],['BHUSAWAL',0,],['CHHINDWARA',493,],['DEWAS',129,],['UJJAIN',289,],['DHULE',0,],['INDORE',1015,],['JALGAON',0,],['MANDLA',0,],['MHOW',278,],['NASHIK',153,],['PANDHURNA',14,],['RAIPUR',453,],['SEONI',363,],['SHAHADA',0,],]},
	cities11  :{data:[['name','11-12',],['NAGPUR',28,],['AMARWARA',0,],['AURANGABAD',157,],['BETUL',184,],['BHUSAWAL',0,],['CHHINDWARA',508,],['DEWAS',398,],['UJJAIN',522,],['DHULE',0,],['INDORE',897,],['JALGAON',0,],['MANDLA',0,],['MHOW',295,],['NASHIK',665,],['PANDHURNA',64,],['RAIPUR',565,],['SEONI',253,],['SHAHADA',0,],]},
	cities12  :{data:[['name','12-13',],['NAGPUR',1,],['AMARWARA',0,],['AURANGABAD',343,],['BETUL',220,],['BHUSAWAL',0,],['CHHINDWARA',494,],['DEWAS',384,],['UJJAIN',309,],['DHULE',1,],['INDORE',745,],['JALGAON',0,],['MANDLA',0,],['MHOW',307,],['NASHIK',534,],['PANDHURNA',63,],['RAIPUR',332,],['SEONI',148,],['SHAHADA',0,],]},
	cities13  :{data:[['name','13-14',],['NAGPUR',3,],['AMARWARA',0,],['AURANGABAD',64,],['BETUL',266,],['BHUSAWAL',0,],['CHHINDWARA',558,],['DEWAS',422,],['UJJAIN',516,],['DHULE',349,],['INDORE',546,],['JALGAON',0,],['MANDLA',0,],['MHOW',187,],['NASHIK',580,],['PANDHURNA',43,],['RAIPUR',510,],['SEONI',56,],['SHAHADA',0,],]},
	cities14  :{data:[['name','14-15',],['NAGPUR',365,],['AMARWARA',118,],['AURANGABAD',225,],['BETUL',296,],['BHUSAWAL',91,],['CHHINDWARA',594,],['DEWAS',577,],['UJJAIN',328,],['DHULE',1505,],['INDORE',915,],['JALGAON',262,],['MANDLA',139,],['MHOW',312,],['NASHIK',461,],['PANDHURNA',116,],['RAIPUR',348,],['SEONI',70,],['SHAHADA',184,],]},
	cities15  :{data:[['name','15-16',],['NAGPUR',146,],['AMARWARA',47,],['AURANGABAD',94,],['BETUL',73,],['BHUSAWAL',49,],['CHHINDWARA',132,],['DEWAS',140,],['UJJAIN',18,],['DHULE',497,],['INDORE',177,],['JALGAON',191,],['MANDLA',82,],['MHOW',53,],['NASHIK',170,],['PANDHURNA',37,],['RAIPUR',119,],['SEONI',37,],['SHAHADA',88,],]},
	citiestotal  :{data:[['name','Total',],['NAGPUR',613,],['AMARWARA',165,],['AURANGABAD',883,],['BETUL',1149,],['BHUSAWAL',140,],['CHHINDWARA',3659,],['DEWAS',2050,],['UJJAIN',1982,],['DHULE',2352,],['INDORE',5061,],['JALGAON',453,],['MANDLA',221,],['MHOW',1457,],['NASHIK',2563,],['PANDHURNA',525,],['RAIPUR',2418,],['SEONI',1545,],['SHAHADA',272,],]},
	brands  :{data:[['make','Deals',],['HERO HONDA',7999,],['HERO',6678,],['HONDA',4865,],['TVS',2669,],['BAJAJ',2550,],['SUZUKI',1646,],['YAMAHA',664,],['MAHINDRA',321,],['ROYAL ENFIELD',88,],['PIAGGIO',11,],['KINETIC',6,],['VESPA',6,],['ROYAL ENFILED',4,],['HINDA',1,],]},
	status  :{data:[['Bucket','Deals',],['0',10170,],['1',2271,],['2',745,],['3',399,],['4',245,],['5',178,],['6',136,],['7',115,],['8',105,],['9',88,],['10',64,],['11',560,],]},
	cashcollection  :{data:[['Month','Deals',],['Jul-15',1248,],['Jun-15',2498,],['May-15',2410,],['Apr-15',2125,],['Mar-15',2494,],['Feb-15',2062,],['Jan-15',1852,],]},
	marginchart  :{data:[['dt','vehiclecost','margin',],[new Date(2008,9,1),45.16,41.22,],[new Date(2008,10,1),44.94,38.45,],[new Date(2008,11,1),45.35,37.16,],[new Date(2008,12,1),46.37,39.78,],[new Date(2009,1,1),46.28,38.68,],[new Date(2009,2,1),46.02,39.19,],[new Date(2009,3,1),47.60,39.29,],[new Date(2009,4,1),46.64,40.13,],[new Date(2009,5,1),46.10,40.11,],[new Date(2009,6,1),45.38,39.26,],[new Date(2009,7,1),46.96,39.89,],[new Date(2009,8,1),47.02,39.02,],[new Date(2009,9,1),46.77,38.54,],[new Date(2009,10,1),46.23,37.84,],[new Date(2009,11,1),45.05,38.14,],[new Date(2009,12,1),45.63,39.35,],[new Date(2010,1,1),45.72,38.11,],[new Date(2010,2,1),49.73,37.86,],[new Date(2010,3,1),48.32,37.88,],[new Date(2010,4,1),47.54,38.23,],[new Date(2010,5,1),48.13,37.77,],[new Date(2010,6,1),50.75,37.96,],[new Date(2010,7,1),49.12,38.25,],[new Date(2010,8,1),48.28,38.10,],[new Date(2010,9,1),48.29,37.53,],[new Date(2010,10,1),48.81,38.00,],[new Date(2010,11,1),48.21,38.64,],[new Date(2010,12,1),49.79,39.40,],[new Date(2011,1,1),50.53,39.99,],[new Date(2011,2,1),51.13,40.21,],[new Date(2011,3,1),51.42,40.59,],[new Date(2011,4,1),51.59,39.87,],[new Date(2011,5,1),51.60,39.78,],[new Date(2011,6,1),51.41,40.26,],[new Date(2011,7,1),51.15,38.42,],[new Date(2011,8,1),51.52,39.10,],[new Date(2011,9,1),51.77,39.11,],[new Date(2011,10,1),51.78,38.15,],[new Date(2011,11,1),52.45,38.82,],[new Date(2011,12,1),51.72,39.02,],[new Date(2012,1,1),52.81,40.06,],[new Date(2012,2,1),52.39,40.22,],[new Date(2012,3,1),53.44,40.11,],[new Date(2012,4,1),54.18,40.58,],[new Date(2012,5,1),53.87,39.85,],[new Date(2012,6,1),53.54,39.46,],[new Date(2012,7,1),53.93,39.65,],[new Date(2012,8,1),54.82,40.53,],[new Date(2012,9,1),57.39,40.61,],[new Date(2012,10,1),56.06,39.87,],[new Date(2012,11,1),56.40,39.12,],[new Date(2012,12,1),55.40,39.01,],[new Date(2013,1,1),58.74,39.16,],[new Date(2013,2,1),55.89,38.93,],[new Date(2013,3,1),56.08,39.11,],[new Date(2013,4,1),56.87,38.53,],[new Date(2013,5,1),55.62,38.82,],[new Date(2013,6,1),56.30,38.32,],[new Date(2013,7,1),55.45,37.62,],[new Date(2013,8,1),56.80,37.08,],[new Date(2013,9,1),57.66,37.19,],[new Date(2013,10,1),57.48,36.43,],[new Date(2013,11,1),59.49,37.19,],[new Date(2013,12,1),57.30,36.88,],[new Date(2014,1,1),59.71,37.49,],[new Date(2014,2,1),58.08,37.30,],[new Date(2014,3,1),59.11,37.10,],[new Date(2014,4,1),57.68,36.89,],[new Date(2014,5,1),57.49,36.73,],[new Date(2014,6,1),57.99,36.36,],[new Date(2014,7,1),58.66,36.35,],[new Date(2014,8,1),59.26,35.69,],[new Date(2014,9,1),58.56,34.67,],[new Date(2014,10,1),59.22,33.85,],[new Date(2014,11,1),58.99,34.82,],[new Date(2014,12,1),60.85,34.73,],[new Date(2015,1,1),60.82,33.34,],[new Date(2015,2,1),62.77,33.58,],[new Date(2015,3,1),62.93,33.35,],[new Date(2015,4,1),65.12,33.22,],[new Date(2015,5,1),63.53,32.72,],[new Date(2015,6,1),63.56,32.43,],[new Date(2015,7,1),65.50,31.36,],]},
	highriskdeals  :{data:[['Year','RiskyPercentage',],['08-09',7,],['09-10',5,],['10-11',5,],['11-12',4,],['12-13',3,],['13-14',8,],['14-15',22,],['15-16',38,],]},
	religion  :{data:[['Religion','Deals',],['Hindu',26688,],['Muslim',806,],['Sikh',9,],['Isai',5,],]}};
	var options = {yearlyfinanceamount:{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#7093B1'],legend: {position: 'none' },title:'Total financed till date 94.98 Cr' ,vAxis: {viewWindow: {min: 0},gridlines:{count:10}}},
	monthlyfinanceamount:{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#DE4D4E'],legend: {position: 'none' },title:'Financed in Last 13* Months: 31.47 Cr',vAxis: {viewWindow: {min: 0},gridlines:{count:10}}},
	yearlyavgloan:{width: 490, height: 350, chartArea:{left:50,top:20,width:'90%',height:'80%'},colors:['#0E3D59'],legend: {position: 'none' },vAxis: {viewWindow: {min:25000},gridlines:{count:10}}, lineWidth: 5,},
	monthlyavgloan:{width: 490, height: 350, chartArea:{left:50,top:20,width:'90%',height:'80%'},colors:['#0E3D59'],legend: {position: 'none' },vAxis: {viewWindow: {min:25000},gridlines:{count:10}},lineWidth: 5,},
	yearlyvehicles:{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#FFD041'],legend: {position: 'none' },title:'Across All Centers till date: 27508', vAxis: {viewWindow: {min: 0},gridlines:{count:10}}},
	monthlyvehicles:{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#6E9ECF'],legend: {position: 'none' },title:'Across all centers in last 12 months: 7803', vAxis: {viewWindow: {min: 0},gridlines:{count:10}}},
	income:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Customers by Annual Income (in Lacs)',pieHole: 0.6},
	vehiclecost:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Percentage vehiles with cost range',pieHole: 0.6},
	installments:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Distribution of Deals by number of Installments'},
	states:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Customers by States',pieHole: 0.6},
	cities08:{width:200, height: 500, chartArea:{left:90,top:20,width:'80%',height:'80%'},legend: {position: 'bottom' },title:'Total: 586',hAxis: {viewWindow: {min: 0},textPosition: 'none'}},
	cities09:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 1982',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#dc3912'] },
	cities10:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 3367',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#ff9900'] },
	cities11:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 4536',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#109618'] },
	cities12:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 3881',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#990099'] },
	cities13:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 4100',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#0099c6'] },
	cities14:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 6906',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#dd4477'] },
	cities15:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 2150',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#0099c6'] },
	citiestotal:{width: 100, height: 500, chartArea:{left:0,top:20, width:'95%',height:'80%'},legend: {position: 'bottom' },title:'Total: 27508',hAxis: {viewWindow: {min: 0},textPosition: 'none'},colors:['#dc3912'] },
	brands:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total: 27508',pieHole: 0.3,slices: {0:{offset: 0.1}}},
	status:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total Active Cases: 15076',pieHole: 0.3,slices: {0:{offset: 0.1}}},
	cashcollection:{width: 490, height: 350, chartArea:{left:40,top:20,width:'90%',height:'80%'},colors:['#7093B1'],legend: {position: 'none' },title:'By field executives' ,vAxis: {viewWindow: {min: 0},gridlines:{count:10}}},
	marginchart:{width:470, height:350, displayAnnotations: false},
	highriskdeals:{width: 490, height: 350, chartArea:{left:50,top:20,width:'90%',height:'80%'},colors:['#0E3D59', '#916A4B', '#EA837C'],legend: {position: 'bottom' },title:'#Risky Deals & Percentage of Risky Deal per year',vAxis: {viewWindow: {min:0}},lineWidth: 2,},
	religion:{width: 490, height: 350, chartArea:{left:20,top:20,width:'90%',height:'80%'},legend: {position: 'bottom' },title:'Total: 27508',pieHole: 0.3,slices: {0:{offset: 0.1}}},
	};
	google.load('visualization', '1', {'packages':['corechart']});
	google.load('visualization', '1', {'packages':['table']});
	google.load('visualization', '1', {'packages':['annotatedtimeline']});

	google.setOnLoadCallback(drawChart);
	function drawChart() {var data, view, chart; 
	data = new google.visualization.arrayToDataTable(dashboardData.yearlyfinanceamount.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.ColumnChart(document.getElementById('yearlyfinanceamount'));
	chart.draw(view, options.yearlyfinanceamount);
	data = new google.visualization.arrayToDataTable(dashboardData.monthlyfinanceamount.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.ColumnChart(document.getElementById('monthlyfinanceamount'));
	chart.draw(view, options.monthlyfinanceamount);
	data = new google.visualization.arrayToDataTable(dashboardData.yearlyavgloan.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.LineChart(document.getElementById('yearlyavgloan'));
	chart.draw(view, options.yearlyavgloan);
	data = new google.visualization.arrayToDataTable(dashboardData.monthlyavgloan.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.LineChart(document.getElementById('monthlyavgloan'));
	chart.draw(view, options.monthlyavgloan);
	data = new google.visualization.arrayToDataTable(dashboardData.yearlyvehicles.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.ColumnChart(document.getElementById('yearlyvehicles'));
	chart.draw(view, options.yearlyvehicles);
	data = new google.visualization.arrayToDataTable(dashboardData.monthlyvehicles.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.ColumnChart(document.getElementById('monthlyvehicles'));
	chart.draw(view, options.monthlyvehicles);
	data = new google.visualization.arrayToDataTable(dashboardData.income.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.PieChart(document.getElementById('income'));
	chart.draw(view, options.income);
	data = new google.visualization.arrayToDataTable(dashboardData.vehiclecost.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		,2,{calc:'stringify', sourceColumn: 2, type:'string', role:'annotation'}
		,3,{calc:'stringify', sourceColumn: 3, type:'string', role:'annotation'}
		,4,{calc:'stringify', sourceColumn: 4, type:'string', role:'annotation'}
		,5,{calc:'stringify', sourceColumn: 5, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.LineChart(document.getElementById('vehiclecost'));
	chart.draw(view, options.vehiclecost);
	data = new google.visualization.arrayToDataTable(dashboardData.installments.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.PieChart(document.getElementById('installments'));
	chart.draw(view, options.installments);
	data = new google.visualization.arrayToDataTable(dashboardData.states.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.PieChart(document.getElementById('states'));
	chart.draw(view, options.states);
	data = new google.visualization.arrayToDataTable(dashboardData.cities08.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities08'));
	chart.draw(view, options.cities08);
	data = new google.visualization.arrayToDataTable(dashboardData.cities09.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities09'));
	chart.draw(view, options.cities09);
	data = new google.visualization.arrayToDataTable(dashboardData.cities10.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities10'));
	chart.draw(view, options.cities10);
	data = new google.visualization.arrayToDataTable(dashboardData.cities11.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities11'));
	chart.draw(view, options.cities11);
	data = new google.visualization.arrayToDataTable(dashboardData.cities12.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities12'));
	chart.draw(view, options.cities12);
	data = new google.visualization.arrayToDataTable(dashboardData.cities13.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities13'));
	chart.draw(view, options.cities13);
	data = new google.visualization.arrayToDataTable(dashboardData.cities14.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities14'));
	chart.draw(view, options.cities14);
	data = new google.visualization.arrayToDataTable(dashboardData.cities15.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('cities15'));
	chart.draw(view, options.cities15);
	data = new google.visualization.arrayToDataTable(dashboardData.citiestotal.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.BarChart(document.getElementById('citiestotal'));
	chart.draw(view, options.citiestotal);
	data = new google.visualization.arrayToDataTable(dashboardData.brands.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.PieChart(document.getElementById('brands'));
	chart.draw(view, options.brands);
	data = new google.visualization.arrayToDataTable(dashboardData.status.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.PieChart(document.getElementById('status'));
	chart.draw(view, options.status);
	data = new google.visualization.arrayToDataTable(dashboardData.cashcollection.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.ColumnChart(document.getElementById('cashcollection'));
	chart.draw(view, options.cashcollection);
	data = new google.visualization.arrayToDataTable(dashboardData.marginchart.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		,2,{calc:'stringify', sourceColumn: 2, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.AnnotatedTimeLine(document.getElementById('marginchart'));
	chart.draw(view, options.marginchart);
	data = new google.visualization.arrayToDataTable(dashboardData.highriskdeals.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.LineChart(document.getElementById('highriskdeals'));
	chart.draw(view, options.highriskdeals);
	data = new google.visualization.arrayToDataTable(dashboardData.religion.data);
	view = new google.visualization.DataView(data);
	view.setColumns([0,1,{calc:'stringify', sourceColumn: 1, type:'string', role:'annotation'}
		]);
	chart = new google.visualization.PieChart(document.getElementById('religion'));
	chart.draw(view, options.religion);}
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
				<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">Yearly Financed Amount</h2>
					<div class="divider clearboth"></div>
					<div id="yearlyfinanceamount"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Last 12 Months Financed Amount</h2>
					<div class="divider "></div>
					<div id="monthlyfinanceamount"></div>
				</div>
											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">Annual Average Loan Amount</h2>
					<div class="divider clearboth"></div>
					<div id="yearlyavgloan"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Last 12 Month Average Loan</h2>
					<div class="divider "></div>
					<div id="monthlyavgloan"></div>
				</div>
											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">Yearly Vehicles</h2>
					<div class="divider clearboth"></div>
					<div id="yearlyvehicles"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Last 12 Months Vehicles</h2>
					<div class="divider "></div>
					<div id="monthlyvehicles"></div>
				</div>
											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">Annual Income</h2>
					<div class="divider clearboth"></div>
					<div id="income"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Vehicle Cost Trend</h2>
					<div class="divider "></div>
					<div id="vehiclecost"></div>
				</div>
											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading"># Installments</h2>
					<div class="divider clearboth"></div>
					<div id="installments"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Deals by State</h2>
					<div class="divider "></div>
					<div id="states"></div>
				</div>
													<h1 class="chart-group-heading pull-left">Yearly Centrewise Deals</h1><div class="clearboth"></div>
						<div class="chart-category full-width">
						<div class="divider "></div>
										<div id="cities08" class="peryear"></div>
																	<div id="cities09" class="peryear"></div>
																	<div id="cities10" class="peryear"></div>
																	<div id="cities11" class="peryear"></div>
																	<div id="cities12" class="peryear"></div>
																	<div id="cities13" class="peryear"></div>
																	<div id="cities14" class="peryear"></div>
																	<div id="cities15" class="peryear"></div>
																	<div id="citiestotal" class="peryear"></div>
					</div>											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">Distribution by Brand</h2>
					<div class="divider "></div>
					<div id="brands"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Bucketwise Performance for Jul-2015</h2>
					<div class="divider clearboth"></div>
					<div id="status"></div>
				</div>
											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">#Visits for Cash Collection</h2>
					<div class="divider "></div>
					<div id="cashcollection"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Margin Amount trend</h2>
					<div class="divider clearboth"></div>
					<div id="marginchart"></div>
				</div>
											<div class="chart-category pull-left ">
					<h2 class="chart-category-heading">Risky Deals (Deals < 30% Margin money)</h2>
					<div class="divider "></div>
					<div id="highriskdeals"></div>
				</div>
											<div class="chart-category pull-left no-border">
					<h2 class="chart-category-heading">Religion</h2>
					<div class="divider clearboth"></div>
					<div id="religion"></div>
				</div>
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
