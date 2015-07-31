// JavaScript Document
var j_query = jQuery.noConflict();

function ge(ele){
	return document.getElementById(ele);
}

function initializeMenu(){
	var el = ge('module-menu');
	var elements = j_query('li', el);
	var nested = null
	for (var i=0; i<elements.length; i++)
	{
		var element = elements[i];
		element.addEventListener('mouseover', function(){ this.classList.add('hover');});
		element.addEventListener('mouseout', function(){ this.classList.remove('hover');});
	}
}

function PrintElement(elem){
	Popup(j_query('#ls-content-box')[0].outerHTML);
}

function Popup(data) {
	var mywindow = window.open('', 'my div', 'height=400,width=600');
	
	mywindow.document.write('<html><head><title>Print</title>');
	mywindow.document.write('<style>td, th {border:1px solid #ccc;border-width:1px 0 0 1px} table {border:1px solid #ccc;border-width:0 1px 1px 0} .textleft{text-align:left} .textright{text-align:right}</style>');
	mywindow.document.write('</head><body >');
	mywindow.document.write(data);
	mywindow.document.write('</body></html>');

	mywindow.document.close(); // necessary for IE >= 10
	mywindow.focus(); // necessary for IE >= 10

	mywindow.print();
	mywindow.close();

	return true;
}


function checkAll(from, till, fldName) {
  if (!fldName) {
     fldName = 'cb-';
  }
  if(ge('toggle').checked){
	for(i=from; i <= till; i++){
		if(ge(fldName+i)){
			ge(fldName+i).checked = true;
		}
	}
  }else{
	 for(i=from; i <= till; i++){
		if(ge(fldName+i)){
			ge(fldName+i).checked = false;
		}
	 } 
  }
}

function sort(field){
	if(ge('sval').value == field){
		if(ge('stype').value == 'asc')
			ge('stype').value = 'desc';
		else
			ge('stype').value = 'asc';
	}
	else{
		ge('sval').value = field;
	}
}

function refresh(){	

	if(ge('hpdt'))	 hpdt = ge('hpdt').value; else hpdt = 0;
	if(ge('pt_nac'))	 pt_nac = ge('pt_nac').value; else pt_nac = 0;
	if(ge('pt_ecs'))	 pt_ecs = ge('pt_ecs').value; else pt_ecs = 0;
	if(ge('pt_pdc'))	 pt_pdc = ge('pt_pdc').value; else pt_pdc = 0;
	if(ge('nacind'))	 nacind = ge('nacind').value; else nacind = 0;
	if(ge('ecsind'))	 ecsind = ge('ecsind').value; else ecsind = 0;
	if(ge('pdcind'))	 pdcind = ge('pdcind').value; else pdcind = 0;
	if(ge('bucket'))	 bucket = ge('bucket').value; else bucket = "";
	if(ge('duedt'))	 duedt = ge('duedt').value; else duedt = "";
	
	if(ge('centre')) 	centre = ge('centre').value; else centre = "";
	if(ge('salesmanid')) 	salesmanid = ge('salesmanid').value; else salesmanid = 0;
	if(ge('period')) 	period = ge('period').value; else period = 0;
	if(ge('city')) 	city = ge('city').value; else city = "";
	if(ge('status')) 	status = ge('status').value; else status = 0;
	if(ge('fromdt')) 	fromdt = ge('fromdt').value; else fromdt = "";
	if(ge('todt')) 	todt = ge('todt').value; else todt = "";

	if(ge('index')) 	index = ge('index').value; else index = 0;
	if(ge('sval')) 	sval = ge('sval').value; else sval = 'rid';
	if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
	if(ge('limit')) 	limit = ge('limit').value; else limit = 30;
	if(ge('page')) page = ge('page').value;

	var url = btoa("hpdt="+ hpdt +"&pt_nac=" + pt_nac +"&pt_ecs=" + pt_ecs +"&pt_pdc=" + pt_pdc + "&pdcind=" + pdcind +"&nacind=" + nacind +"&ecsind=" + ecsind +"&pdcind=" + pdcind +"&bucket=" + bucket + "&duedt=" + duedt+ "&centre=" + centre + "&salesmanid="+ salesmanid + "&period="+ period  + "&city=" + city + "&status=" + status + "&fromdt=" + fromdt + "&todt=" + todt 
	+ "&index=" + index + "&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=generic&url="+url);
}

function callAddComment(dealid){
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=dealcomment&dealid="+dealid);
}

function saveStatus(dealid){
	if(ge('rec')) rec = ge('rec').value; else rec = 0;
	if(ge('tagid')) tagid = ge('tagid').value; else tagid = 0;
	if(ge('comment')) comment = ge('comment').value.trim(); else comment = '';

	if(rec==0 && tagid == 0){
		alert("Please choose problem");
		return false;
	}
	if(rec==0 && tagid == -1 && (comment == '' ||  comment == 'Comments')){
		alert("Please write comments");
			return false;
	}

	if(comment == 'Comments') comment = '';

	j_query("#content").empty().html('<center>&nbsp;<br><img src="/images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	var url = btoa("submit=1&rec=" + rec + "&tagid=" + tagid + "&comment="+ escape(comment));
	window.location.assign("index.php?task=dealcomment&dealid="+dealid+"&url="+url);
}


function callListOfDeals(){
	if(ge('search'))	 search = ge('search').value; else search = "";
	if(ge('centre')) 	centre = ge('centre').value; else centre = "";
	if(ge('salesmanid')) 	salesmanid = ge('salesmanid').value; else salesmanid = 0;
	if(ge('period')) 	period = ge('period').value; else period = 0;
	if(ge('city')) 	city = ge('city').value; else city = "";
	if(ge('status')) 	status = ge('status').value; else status = 0;
	if(ge('fromdt')) 	fromdt = ge('fromdt').value; else fromdt = "";
	if(ge('todt')) 	todt = ge('todt').value; else todt = "";

	if(ge('sval')) 	sval = ge('sval').value; else sval = 'rid';
	if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
	if(ge('limit')) 	limit = ge('limit').value; else limit = 30;
	if(ge('page')) page = ge('page').value;

	var url = btoa("search=" + search + "&centre=" + centre + "&salesmanid="+ salesmanid + "&period="+ period  + "&city=" + city + "&status=" + status + "&fromdt=" + fromdt + "&todt=" + todt 
	+ "&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=deallist&url="+url);
}

function refreshDashboard(){
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	j_query.ajax({
		type: "POST",
		url: "index.php",
		data: "task=refreshDashbord",
		dataType: "html",
		success: function(data){
			window.location.assign("index.php?task=dashboard");
		} ,
		error: function (XMLHttpRequest, textStatus, errorThrown) {
		      alert(XMLHttpRequest.status);
		      alert(XMLHttpRequest.responseText);
		}
	});
}

function callSEReport(){
	if(ge('centre'))	centre = ge('centre').value; else centre = '';
	if(ge('salesmanid')) 	salesmanid = ge('salesmanid').value; else salesmanid = 0;
	if(ge('zeroDeals')) 	zeroDeals = ge('zeroDeals').value; else zeroDeals = 0;
	if(ge('type')) 	type = ge('type').value; else type = 0;
	
	var url = btoa("&centre=" + centre + "&salesmanid="+ salesmanid  + "&zeroDeals=" + zeroDeals + "&type=" + type);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=se_report&url="+url);
}

function callDLReport(){
	if(ge('centre'))	centre = ge('centre').value; else centre = '';
	if(ge('brkrid')) 	brkrid = ge('brkrid').value; else brkrid = 0;
	if(ge('zeroDeals')) 	zeroDeals = ge('zeroDeals').value; else zeroDeals = 0;
	if(ge('type')) 	type = ge('type').value; else type = 0;
	
	var url = btoa("&centre=" + centre + "&brkrid="+ brkrid  + "&zeroDeals=" + zeroDeals + "&type=" + type);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=dl_report&url="+url);
}

function call_per_field(){
	if(ge('by'))	by = ge('by').value; else by = 0;
	if(ge('type'))	type = ge('type').value; else type = 0;
	if(ge('ason'))	ason = ge('ason').value; else ason = 0;
	if(ge('hpdt'))	hpdt = ge('hpdt').value; else hpdt = 2;
	if(ge('centre'))	centre = ge('centre').value;	else centre = "";
	if(ge('bucket'))	bucket = ge('bucket').value; else bucket = -1;
	if(ge('expired'))	expired = ge('expired').value; else expired = 0;
	if(ge('sratag'))	sratag = ge('sratag').value; else sratag = 0;
	if(ge('callertag'))	callertag = ge('callertag').value; else callertag = 0;


	if(ge('compare'))	compare = ge('compare').value; else compare= 0;
	if(ge('dd'))	dd = ge('dd').value; else dd = -1;
	if(ge('sraid'))	sraid= ge('sraid').value; else sraid= "";    
	if(ge('rc_sraid'))	rc_sraid= ge('rc_sraid').value; else rc_sraid= "";

	if(ge('sval')) sval = ge('sval').value; else sval = "";
	if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
	if(ge('limit')) limit = ge('limit').value; else limit = 30;
	if(ge('page')) page = ge('page').value; else page = 1;

	var url = btoa("&hpdt=" + hpdt + "&centre=" + centre + "&sraid="+ sraid + "&rc_sraid="+ rc_sraid + "&dd=" + dd + "&ason=" + ason + "&by=" + by + "&type=" + type + "&bucket=" + bucket +"&expired=" + expired + "&sratag="+ sratag + "&callertag="+ callertag + "&compare=" + compare + "&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=per_field&url="+url);
}

function call_per_caller(){
	if(ge('type'))	type = ge('type').value; else type = 0;
	if(ge('ason'))	ason = ge('ason').value; else ason = 0;
	if(ge('hpdt'))	hpdt = ge('hpdt').value; else hpdt = 2;
	if(ge('centre'))	centre = ge('centre').value;	else centre = "";
	if(ge('bucket'))	bucket = ge('bucket').value; else bucket = -1;
	if(ge('expired'))	expired = ge('expired').value; else expired = 0;
	if(ge('sratag'))	sratag = ge('sratag').value; else sratag = 0;
	if(ge('callertag'))	callertag = ge('callertag').value; else callertag = 0;

	if(ge('compare'))	compare = ge('compare').value; else compare= 0;
	if(ge('dd'))	dd = ge('dd').value; else dd = -1;
	if(ge('callerid'))	callerid= ge('callerid').value; else callerid= "";    
	if(ge('rc_sraid'))	rc_sraid= ge('rc_sraid').value; else rc_sraid= "";

	if(ge('sval')) sval = ge('sval').value; else sval = "";
	if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
	if(ge('limit')) limit = ge('limit').value; else limit = 30;
	if(ge('page')) page = ge('page').value; else page = 1;

	var url = btoa("&hpdt=" + hpdt + "&centre=" + centre + "&callerid="+ callerid + "&rc_sraid="+ rc_sraid + "&dd=" + dd + "&ason=" + ason + "&type=" + type + "&bucket=" + bucket +"&expired=" + expired + "&sratag="+ sratag + "&callertag="+ callertag +"&compare=" + compare  
	+ "&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=per_caller&url="+url);
}


function callODReport(){
	if(ge('search'))	search = ge('search').value; else search = "";
	if(ge('centre'))	centre = ge('centre').value;	else centre = "";
	if(ge('salesmanid'))	salesmanid = ge('salesmanid').value; else salesmanid = 0;
	if(ge('month'))	month = ge('month').value; else month =""
	if(ge('zeroDeals'))	zeroDeals = ge('zeroDeals').value; else zeroDeals = 0;
	if(ge('period'))	period = ge('period').value; else period = 2;
	if(ge('od_from'))	od_from = ge('od_from').value; else od_from = 0;
	if(ge('od_amt'))	od_amt = ge('od_amt').value; else od_amt = 1000;
	if(ge('disburse'))	disburse = ge('disburse').value; else disburse = "";
	if(ge('type'))	type = ge('type').value; else type = 0;
	if(ge('bucket'))	bucket = ge('bucket').value; else bucket = -1;

	if(ge('sval')) sval = ge('sval').value; else sval = "";
	if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
	if(ge('limit')) limit = ge('limit').value; else limit = 30;
	if(ge('page')) page = ge('page').value; else page = 1;

	var url = btoa("&period=" + period + "&centre=" + centre + "&salesmanid="+ salesmanid + "&zeroDeals=" + zeroDeals + "&od_from=" + od_from + "&od_amt=" + od_amt + "&disburse=" + disburse + "&type=" + type + "&bucket=" + bucket 
	+ "&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=od_report&url="+url);
}

function callRecoveryReport(){
	if(ge('centre'))	centre = ge('centre').value;	else centre = "";
	if(ge('sraid'))	sraid = ge('sraid').value; else sraid = 0;
	if(ge('period'))	period = ge('period').value; else period =0;
	if(ge('hp'))	hp = ge('hp').value; else hp = 0;
	if(ge('mode'))	mode = ge('mode').value; else mode = 1;
	if(ge('by'))	by = ge('by').value; else by = 0;
	if(ge('type'))	type = ge('type').value; else type = 0;

	if(ge('sval')) sval = ge('sval').value; else sval = "";
	if(ge('stype')) 	stype = ge('stype').value; else stype = 'desc';
	if(ge('limit')) limit = ge('limit').value; else limit = 30;
	if(ge('page')) page = ge('page').value; else page = 1;

	var url = btoa("&period=" + period + "&centre=" + centre + "&sraid="+ sraid + "&mode=" + mode + "&by=" + by + "&type=" + type + "&hp=" + hp 
	+ "&page=" + page + "&limit=" + limit +"&sval=" + sval + "&stype=" + stype);
	j_query("#content-table").empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	window.location.assign("index.php?task=re_report&url="+url);
}






















































/***************************************************




function editTest(){
	var from = ge('from').value;
	var till = ge('till').value;
	var count=0;
	for(var i=from; i<=till; i++){
		if(ge('cb-'+i)){
			if(ge('cb-'+i).checked){
				count++;
			}
			if(count==1){
				var edit = ge('cb-'+i).value;
			}
		}
	}
	if(count==0){
		alert('Please make a selection from the list to edit');
		return false;
	}else{
		window.location = "index.php?task=edittest&tid="+edit;
		return false;
	}
}

function publishTest(type){
	var from = ge('from').value;
	var till = ge('till').value;
	var count=0;
	var tid = 0;
	var parm = "";
	for(var i=from; i<=till; i++){
		if(ge('cb-'+i)){
			if(ge('cb-'+i).checked){
				tid = ge('cb-'+i).value;
				count++;
				if(count==1) parm = tid;
				if(count>1) parm += ',' + tid;
			}
		}
	}
	if(count==0){
		 var prt = (type=='Y')? "Publish" : "Unpublish"; 
		alert('Please make a selection from the list to '+prt);
		return false;
	}else{
		//alert(parm);
		j_query.get( 'index.php', { task:ajaxrequest, callid:15, tids:parm, type:type }, function(html){
				if(html==1)	callListOfTests();
 		});
		return false;
	}
}
function deleteTest(){
	var from = ge('from').value;
	var till = ge('till').value;
	var count=0;
	var tid = 0;
	var parm = "";
	for(var i=from; i<=till; i++){
		if(ge('cb-'+i)){
			if(ge('cb-'+i).checked){
				tid = ge('cb-'+i).value;
				count++;
				if(count==1) parm = tid;
				if(count>1) parm += ',' + tid;
			}
		}
	}
	if(count==0){
		alert('Please make a selection from the list to publish');
		return false;
	}else{
		var ret=confirm("Are you sure to delete the selected tests");
		if(ret){
			j_query.get( 'index.php', { task:ajaxrequest, callid:16, tids:parm}, function(html){
				if(html==1)	callListOfTests();
			});
			return false;
		}else return false;
	}
}
function getPartnerStudentTestList(user){
    var stream = '';
	if(ge('stream')!="")
        stream = ge('stream').value;
	j_query('#partner_tests').empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
    j_query.ajax({
        type: "POST",
        url: "index.php",
        data: "task=partnerStudentTestList&stream_id=" + stream+"&user="+user,
        dataType: "html",
        success: function(data){
            j_query("#partner_tests").empty();
            j_query("#partner_tests").append(" " + data);
        } ,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.status);
              alert(XMLHttpRequest.responseText);
          }
    });
}

function getPartnerTestList(){
    var stream = '';
	if(ge('stream')!="")
        stream = ge('stream').value;
	j_query('#partner_tests').empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
    j_query.ajax({
        type: "POST",
        url: "index.php",
        data: "task=partnerTestList&stream_id=" + stream,
        dataType: "html",
        success: function(data){
            j_query("#partner_tests").empty();
            j_query("#partner_tests").append(" " + data);
        } ,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.status);
              alert(XMLHttpRequest.responseText);
          }
    });
}

function callListOfBilling(sortType){
	if(sortType==undefined){
		sortType = 'asc';
			if(ge('stype')){
				if(ge('stype')!="")
					sortType = ge('stype').value;
			}
	}
    var limit = 20;
    var pagination = 1;
    var sval = "date";
    if(ge('sortvalue')){
        if(ge('sortvalue')!="")
            sval = ge('sortvalue').value;
    }
    if(ge('limit')) 	limit = ge('limit').value;
    if(ge('page')) 	pagination = ge('page').value;
    j_query('#listbilling').empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
    j_query.ajax({
        type: "POST",
        url: "index.php",
        data: "task=billinglist&page=" + pagination + "&limit=" + limit + "&stype=" + sortType +"&sval=" + sval ,
        dataType: "html",
        success: function(data){
            j_query("#listbilling").empty();
            j_query("#listbilling").append(" " + data);
        } ,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.status);
              alert(XMLHttpRequest.responseText);
          }
    });
}

function callListOfLeads(sortType){
	if(sortType==undefined){
		sortType = 'asc';
			if(ge('stype')){
				if(ge('stype')!="")
					sortType = ge('stype').value;
			}
	}
    var limit = 20;
    var pagination = 1;
    var sval = "date";
    if(ge('sortvalue')){
        if(ge('sortvalue')!="")
            sval = ge('sortvalue').value;
    }
    if(ge('limit')) 	limit = ge('limit').value;
    if(ge('page')) 	pagination = ge('page').value;
    j_query('#listleads').empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
    j_query.ajax({
        type: "POST",
        url: "index.php",
        data: "task=leadlist&page=" + pagination + "&limit=" + limit + "&stype=" + sortType +"&sval=" + sval ,
        dataType: "html",
        success: function(data){
            j_query("#listleads").empty();
            j_query("#listleads").append(" " + data);
        } ,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.status);
              alert(XMLHttpRequest.responseText);
          }
    });
}



function callListOfCustomerStudents(sortType){
	if(sortType==undefined){
		sortType = 'asc';
			if(ge('stype')){
				if(ge('stype')!="")
					sortType = ge('stype').value;
			}
	}
    var exam = 0;
    var limit = 20;
    var pagination = 1;
    var sval = "user_id";
	var cid =0;
    if(ge('sortvalue')){
        if(ge('sortvalue')!="")
            sval = ge('sortvalue').value;
    }
	if(ge('cid')) 	cid = ge('cid').value;
    if(ge('exam')) 	exam = ge('exam').value;
    if(ge('limit')) 	limit = ge('limit').value;
    if(ge('page')) 	pagination = ge('page').value;
    j_query('#listofstudents').empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
    j_query.ajax({
        type: "POST",
        url: "index.php",
        data: "task=customerstudentlist&page=" + pagination + "&exam=" + exam + "&cid=" + cid +"&limit=" + limit + "&stype=" + sortType +"&sval=" + sval ,
        dataType: "html",
        success: function(data){
            j_query("#listofstudents").empty();
            j_query("#listofstudents").append(" " + data);
        } ,
        error: function (XMLHttpRequest, textStatus, errorThrown) {
              alert(XMLHttpRequest.status);
              alert(XMLHttpRequest.responseText);
          }
    });
}


function callListOfTests(sortType){
	if(sortType==undefined) sortType = 'asc';
	var srch = "";
	var isact = "";
	var stream = 0;
	var exam = 0;
	var duration = "";
	var limit = 20;
	var pagination = 1; 
	var sval = "upload_date";
	//var partid = ge('partid').value;
	//alert(partid);
	if(ge('sortvalue')){ 
		if(ge('sortvalue')!="")	 sval = ge('sortvalue').value;
	}
	if(ge('search'))	 srch = ge('search').value;
	if(ge('isactive')) 	isact = ge('isactive').value;
	if(ge('stream')) 	stream = ge('stream').value;
	if(ge('exam')) 	exam = ge('exam').value;
	if(ge('duration')) 	duration = ge('duration').value;
	if(ge('limit')) 	limit = ge('limit').value;
	if(ge('page')) 	pagination = ge('page').value; 
	//if(ge('partid')) 	pid = ge('partid').value; 
	j_query('#listoftests').empty().html('<center>&nbsp;<br><img src="images/ajax-loader2.gif" style="border:none;" /><br>&nbsp;</center>');
	j_query.ajax({
		type: "POST",
		url: "index.php",
		data: "task=testlist&srch=" + srch  + "&page=" + pagination + "&isact=" + isact + "&stream=" + stream + "&exam=" + exam + "&dur=" + duration + "&limit=" + limit + "&stype=" + sortType +"&sval=" + sval ,
		//contentType: "application/html; charset=utf-8",
    	dataType: "html",
		success: function(data){
			j_query("#listoftests").empty();
			j_query("#listoftests").append(" " + data);
		} ,
		error: function (XMLHttpRequest, textStatus, errorThrown) {
      		alert(XMLHttpRequest.status);
      		alert(XMLHttpRequest.responseText);
  		}
	});
}

function updateIsActive(tid, val){
		if(val=="Y")	var isact = "N";
		else var isact = "Y";
	if(tid>0){
			j_query.get( 'index.php', { task:ajaxrequest, callid:17, tid:tid, isact:isact}, function(html){
				if(html==1)	callListOfTests();
			});
			return false;
	}
}
function updateLaunch(tid, val){
		if(val=="Y")	var laun = "N";
		else var laun = "Y";
	if(tid>0){
			j_query.get( 'index.php', { task:ajaxrequest, callid:23, tid:tid, launch:laun}, function(html){
				if(html==1)	callListOfTests();
			});
			return false;
	}
}
function getStudent(div_id, userid){
    var el = ge(div_id);
	if( el.style.display == 'none' ){
        el.style.display = 'block';
        if(div_id!='blanket'){
            j_query.ajax({
                type: "POST",
                url: "index.php",
                data: "task=student_profile&userid=" + userid ,
                dataType: "html",
                success: function(data){
                    j_query("#"+div_id).empty();
                    j_query("#"+div_id).append(" " + data);
                } ,
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.status);
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
    }else {
        j_query("#"+div_id).empty();
        el.style.display = 'none';
    }
}
function toggle(div_id, tsid, userid) {
	var el = ge(div_id);
	if( el.style.display == 'none' ){
        el.style.display = 'block';
        if(div_id!='blanket'){
            j_query.ajax({
                type: "POST",
                url: "index.php",
                data: "task=scorecard&tsid=" + tsid + "&userid=" + userid ,
                dataType: "html",
                success: function(data){
                    j_query("#"+div_id).empty();
                    j_query("#"+div_id).append(" " + data);
                } ,
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.status);
                    alert(XMLHttpRequest.responseText);
                }
            });
        }
    }else {
        j_query("#"+div_id).empty();
        el.style.display = 'none';

    }
}
function blanket_size(popUpDivVar){
	var blanket = ge('blanket');
	blanket.style.height = (window.screen.height) + 'px';
	var popUpDiv = ge(popUpDivVar);
	popUpDiv_height=50;//(window.screen.height/2) - (185);
	popUpDiv.style.top = popUpDiv_height + 'px';
}
function window_pos(popUpDivVar){
	var popUpDiv = ge(popUpDivVar);
	window_width=(window.screen.width/2) - (350);
	popUpDiv.style.left = window_width + 'px';
}
function popup(windowname ,testid, userid) {
	blanket_size(windowname);
	window_pos(windowname);
	toggle('blanket');
	toggle(windowname, testid, userid);
}
function showStudentPopup(windowname, userid){
    blanket_size(windowname);
    window_pos(windowname);
    toggle('blanket');
    getStudent(windowname, userid);
}
function clearTestInstance(windowname,userid, tid){
	blanket_size(windowname);
	window_pos(windowname);
   j_query.ajax({
                type: "POST",
                url: "index.php",
                data: "task=clear&user=" + userid + "&tid="+tid,
                dataType: "html",
                success: function(data){
	                if(data == 1){
		                j_query.ajax({
			                type: "POST",
			                url: "index.php",
			                data: "task=student_profile&userid=" + userid ,
			                dataType: "html",
			                success: function(data){
				                j_query("#"+windowname).empty();
				                j_query("#"+windowname).append(" " + data);
				                j_query("#error_message").html('Successfully cleared!');
			                } ,
			                error: function (XMLHttpRequest, textStatus, errorThrown) {
				                alert(XMLHttpRequest.status);
				                alert(XMLHttpRequest.responseText);
			                }
		                });

	                }
	                else{
		                if(data == 0)
		                    j_query("#error_message").html('No Access');
		                else if (data == 2)
			                j_query("#error_message").html('No such User');
		                else if (data == 3)
			                j_query("#error_message").html('Some Error Occured! Contact Uniapply Support!');
		                else if (data == 4)
			                j_query("#error_message").html('This is not a demo user. Clear is not allowed!');
	                }
                } ,
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    alert(XMLHttpRequest.status);
                    alert(XMLHttpRequest.responseText);
                }
            });
}
***************************************************/