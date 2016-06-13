<?php
	session_start();
	if(!isset($_SESSION['user']))
	{
		header( "refresh:2; url='index.php'" ); 
		echo "No valid credentials to access this page. Please log in again...";
		exit;
	}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Access and Sensor Status</title>
    <!-- Core CSS - Include with every page -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/main-style.css" rel="stylesheet" />

    <!-- Page-Level CSS -->
    <link href="assets/plugins/morris/morris-0.4.3.min.css" rel="stylesheet" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
	<link href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" rel="stylesheet" />
	
	
	<script type="text/javascript" src="assests/plugins/moment/moment.js"></script>
	
	 <!-- Core Scripts - Include with every page -->
	<script src="assets/plugins/jquery.js"></script>
	<script src="assets/plugins/jquery-ui/jquery-ui.js"></script>
	<script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
	<script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
	<script src="assets/plugins/pace/pace.js"></script>
	<!--<script src="assets/scripts/siminta.js"></script>-->
	<!-- Page-Level Plugin Scripts-->
	<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
	
	<script src="assets/plugins/flot/jquery.flot.js"></script>
	<script src="assets/plugins/flot/jquery.flot.time.js"></script>
	<script src="assets/plugins/flot/jquery.flot.resize.js"></script>
	<script src="assets/plugins/flot.tooltip/js/jquery.flot.tooltip.js"></script>
	
	<script src="assests/plugins/daterangepicker/daterangepicker.js"></script>
	<link rel="stylesheet" type="text/css" href="assests/plugins/daterangepicker/daterangepicker.css" />
		
	
	<style>.flotTip {
		z-index: 2000 !important;
		background-color: white;
		border: 1px solid #eaeaea;
		border-radius: 6px;
		padding: 3px;
		}	
	</style>
</head>

<body>


<div class="container">
    <div class="row">
        <!-- Page Header -->
        <div class="col-lg-12 text-center">
		<div style="position: absolute; float: right; z-index: 10; margin: 1em 0px 0.5em; right: 0px;"><a href="scripts/logoff.php" class="btn btn-lg btn-success btn-block">Log Off</a></div>
            <h1 class="page-header" style="margin:0.5em 0 0.5em 0 !important;">Dashboard</h1>
        </div>
        <!--End Page Header -->
    </div>
</div>

<div class="container-fluid">
	<div class="row" style="display:table">
		<div class="col-lg-7" style="display:table-cell">
			<div class="col-lg-12 alert alert-success">
				<form id="inputForm" method ="post">
					<!-- Sensor Types -->
					<div class="col-xs-2 formgrp">
						<div class="form-group">
							<h3>Sensor</h3>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Lux"><h4>Lighting</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Temperature"><h4>Temperature</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Humidity"><h4>Humidity</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Occupancy"><h4>Occupancy</h4>
								</label>
							</div>
						</div>
					</div>
					<div class="col-xs-2 formgrp">
						<div class="form-group">
						</br></br>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Lux"><h4>Lighting</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Temperature"><h4>Temperature</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Humidity"><h4>Humidity</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tableref[]" value="Occupancy"><h4>Occupancy</h4>
								</label>
							</div>
						</div>
					</div>
					<!-- End Sensor Types -->

					<!-- Locations -->
					<div class="col-xs-2 formgrp">	
						<div class="form-group">
							<h3>Locations</h3>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="locations[]" value="lifts"><h4>Lifts</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="locations[]" value="corridors"><h4>Corridor</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="locations[]" value="stairwells"><h4>Stairwell</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="locations[]" value="parking"><h4>Parking</h4>
								</label>
							</div>
						</div>
					</div>	
					<!-- End Locations -->
					
					<div class="col-xs-6">	
						<!--Floors-->
						<div class="row">
							<div class="col-lg-12">
								<div class="input-group">
										<span class="input-group-addon">
										   Floors
										</span>
									<input type="text" name="floors" id="floors" class="form-control">
								</div>
							</div>
						</div>
						<!--End Floors-->
						
						<br/>
						
						<!--Date Range-->
						<div class="row">
							<div class="col-lg-12">
								<div class="input-group">
										<span class="input-group-addon">
										   Dates
										</span>
									<input type="text" name="daterange" id="daterange" class="form-control" />
								</div>
							</div>
						</div>
						<!--End Date Range-->
						<br>
						<div align="right">
							<input id="inputFormSubmit" type="submit" class="btn" value="Submit Query"/>
						</div>
						
					</div>
				</form>
			</div>
		</div>
		
		<!--Sensor ID-->
		<div class="col-xs-2" style="display:table-cell">
			<div class="alert alert-success">
				<form id="sensorIDForm" method ="post">
					<div class="input-group">
						<span class="input-group-addon">
							ID
						</span>
						<input type="text" name="sensorid" id="sensorid" class="form-control">
					</div>
				
				<!--End Sensor ID-->
					<br>
					<div align=right>
						<input type="submit" id="sensorIDFormSubmit" class=btn value="Submit Sensor ID"/>
					</div>
					<br>
				</form>	
			</div>
			<input id="showSensors" class="btn" value="Show all sensors"/>
		</div>
	
	
		<!-- Notifications-->	
		<div class="col-xs-3">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<i class="fa fa-bell fa-fw"></i>Notifications Panel
				</div>

				<div class="panel-body">
					<div class="list-group" id="notificationList">
						
					</div>
				</div>

			</div>
			<!--End Notifications-->
		</div>
	</div>
	

	
	<div class="col-lg-12">
		<div class="row">
            <!-- Database Output -->
            <div id="outputPanel" class="panel panel-default">
                <div class="panel-heading">
                    Database Data
                </div>
				
				<div class="panel-body">
			<!-- #### TABS #### -->
					<ul class="nav nav-tabs" id="dataTabs">
						<!-- Add tabs with jQuery -->
					</ul>
					<div class="tab-content" id="dataTabsContent">
						</br>
			<!-- #### TAB CONTENT #### -->
				<!-- Add content with jQuery -->
					</div>
				</div>
				
				<!-- GRAPH MODAL -->
				<div class="modal fade" id="graphModal" role="dialog">
					<div class="modal-dialog" style="width:80%">
						<!-- Modal content-->
						<div class="modal-content" style="width:auto;height:auto;">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Modal Header</h4>
							</div>
							
							<div class="modal-body" style="height:500px;">
								<div id="graphdiv" style="width:100%;height:100%;"></div>
							</div>
							
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
				</div>
				
            </div>
			<div id="grid"></div>
            <!--End Database output -->
        </div>
    </div>
</div>




<script type="text/javascript">

var POSTarg = { "floors" : "", "daterange": moment().subtract(1, 'week').format('DD-MM-YYYY') + " - " + moment().format('DD-MM-YYYY') };
var responseJSON;
var flotplot;

$(function() {
	$('#daterange').daterangepicker(
	{
		locale: {
		  format: 'DD-MM-YYYY'
		},
		startDate: moment().subtract(1, 'week').format('DD-MM-YYYY'),
		endDate: moment().format('DD-MM-YYYY')
	});
});

jQuery(document).ready(function ($) {
	
// ====================== INIT TABLES =======================
	// Initialise the table on page open
	pollSQLdb();
	var refreshTableTimer = setInterval(pollSQLdb, 10000);
	
// ====================== FORM EVENT HANDLERS =======================

	// Sensor ID search
	$( "#sensorIDForm" ).on('submit', function(event){
		event.preventDefault();
		POSTarg = $('#sensorIDForm').serialize();
		clearInterval(refreshTableTimer);
		pollSQLdb();
		refreshTableTimer = setInterval(pollSQLdb, 10000);
	});

	// Filter form
	$( "#inputForm" ).on('submit', function(event){
		event.preventDefault();
		POSTarg = $('#inputForm').serialize();
		clearInterval(refreshTableTimer);
		pollSQLdb();
		refreshTableTimer = setInterval(pollSQLdb, 10000);

	});
	// Show all sensors
	$( "#showSensors" ).click(function()
	{
		$.ajax({
			type:'POST', 
			url:'AJAXrequest.php', 
			data:"location=1", 
			dataType:"json",
			success: function(response) {
				responseJSON = response;
				showLocations(responseJSON);
			}
		});
	});
	
	$("a[href='#showGraph']").click(function(event)
	{
		//event.preventDefault();
		//alert($("li.active a").text());
		alert("hi");
	});
	

	flotplot = $.plot($("#graphdiv"), [ [[0.5,0.5], [2,5]] ], { xaxis:{ mode:"time",timeformat: "%Y/%m/%d %H:%M:%S" }, 
		series:{ points:{ symbol:"circle",show:"true" }, lines:{ show:"true"} }, grid: { hoverable: true }, tooltip:{ show:true, defaultTheme:false} } );

		
});

function showGraph() 
{
	var sensorType = $("li.active a").html().toLowerCase();
	var currentID = responseJSON[sensorType][0].sensorID;
	//alert(responseJSON[sensorType][0].sensorID);
	var flotData = [ { label:"SensorID " + currentID.toString(), data:[] } ];
	var arrcnt = 0;
	for (var i in responseJSON[sensorType])
	{
		if (currentID != responseJSON[sensorType][i].sensorID)
		{
			currentID = responseJSON[sensorType][i].sensorID;
			flotData.push( { label:"SensorID " + currentID.toString(), data:[] } );
			++arrcnt;
		}
		flotData[arrcnt].data.push([responseJSON[sensorType][i].timestamp*1000, responseJSON[sensorType][i].value]);
	}
	
	//alert(JSON.stringify(flotData));
	flotplot.setData(flotData);
	flotplot.setupGrid();
	flotplot.draw();
	$("h4.modal-title").text("Graph of " + sensorType + " data");
	$("#graphModal").modal('show');
}

function updateTable(inputJSON)
{
	$("#outputPanel").css("height" ,$("#outputPanel").outerHeight());
	var activeTab = '#' + $("li.active a").attr('id');
	$("#dataTabs").empty();
	$("#dataTabsContent").empty();
	
	if($.isEmptyObject(inputJSON))
		$("#dataTabsContent").append("No data found.");
		
	$.each(inputJSON, function(i,j){
		$("#dataTabs").append('<li><a href="#tabContent_' + i + '" id="tab_' + i + '" data-toggle="tab">' + i.slice(0,1).toUpperCase() + i.slice(1) + '</a></li>');
		$("#dataTabsContent").append('<div style="padding:0 1em;" class="tab-pane fade" id="tabContent_' + i + '">' +
		'</br><table class="display" cellspacing="0" width="100%" id="table' + i + '"></div></table>');
		
		$('#table' + i).DataTable({
			data:j,
			columns: [
			{ title:"SensorID", data: 'sensorID' },
			{ title:"Floor", data: 'floor' },
			{ title:"Location", data: 'location' },
			{ title:"Timestamp", data: 'timestamp', render: function( data, type, full, meta ){return moment.unix(data); }},
			{ title:"Value", data: 'value' }
			],
			order: [3, 'desc']
		} );
	});
	
	$("#dataTabs").append('<li role="presentation" style="float:right;" id="showGraph"><a href="#" onclick="showGraph()">Show Graph</a></li>');
	$("#dataTabs").append('<li role="presentation" style="float:right;" id="showGraph"><a href="#" onclick="exportExcel()">Export Data</a></li>');
	if($(activeTab).length)
	{
		$(activeTab).tab('show');
	}
	else
	{
		$("#dataTabs li:first-child").addClass("active");
		$("#dataTabsContent div:first-child").addClass("in active");
	}
	$("#outputPanel").css("height", '');
}

function showLocations(inputJSON)
{
	$("#dataTabs").empty();
	$("#dataTabsContent").empty();
	
	if($.isEmptyObject(inputJSON))
		$("#dataTabsContent").append("No data found.");
	
	$("#dataTabsContent").append('<div style="padding:0 1em;">' +
	'</br><table class="display" cellspacing="0" width="100%" id="tableLoc"></div></table>');
	
	$('#tableLoc').DataTable( {
		data:inputJSON,
		columns: [
		{ title:"SensorID", data: 'sensorID' },
		{ title:"Floor", data: 'floor' },
		{ title:"Located", data: 'location' },
		{ title:"Active", data: 'active'},
		{ title:"Type", data: 'type' }
		]
	} );
}

function updateNotifications(inputJSON)
{
	$("#notificationList").empty();
	$.each(inputJSON, function(i,j)
	{
		var d = new Date(j["timestamp"]*1000);
		$("#notificationList").append('<div class="list-group-item" style="overflow:hidden"><i class="fa fa-bolt fa-fw"></i>Sensor ID '+
		j["sensorID"] + ' has failed!<span class="pull-right text-muted small"><em>'+ d.toLocaleString() +'</em></span></div>');
	});
}

function pollSQLdb()
{
	$.ajax({
		type:'POST', 
		url:'AJAXrequest.php', 
		dataType:"json",
		data:POSTarg,
		success: function(response) {
			responseJSON = response;
			updateTable(responseJSON);
		},
		error: function(response)
		{
			alert("Error 1: Did not recieve valid response from the database.");
		}
	});
	
	$.ajax({
		type:'POST', 
		url:'AJAXrequest.php', 
		dataType:"json",
		data:"notification=1",
		success: function(response) {
			updateNotifications(response);
		},
		error: function(response)
		{
			alert("Error 2: Did not recieve valid response from the database.");
		}
	});
	
	//setTimeout(pollSQLdb, 10000);
}

function exportExcel()
{
	var activeTab = $("li.active a").html().toLowerCase();
	//alert(activeTab);
	var csvContent = "data:text/csv;charset=utf-8,";
	//alert(JSON.stringify(responseJSON[activeTab]));
	
	$.each(responseJSON[activeTab][0], function(key)
	{
		csvContent += key + ",";
	});
	csvContent = csvContent.slice(0,-1).concat("\n");
	
	for (var i = 0; i < responseJSON[activeTab].length; ++i)
	{
		$.each(responseJSON[activeTab][i], function(key,value)
		{
			if(key=="timestamp")
			{
				var d = new Date(value*1000);
				csvContent += '"' + d.toLocaleString() + '"' + ",";
			}
			else
			{
				csvContent += value + ",";
			}
		});
		csvContent = csvContent.slice(0,-1).concat("\n");
	}
	window.open(encodeURI(csvContent));
}


</script>

</body>

</html>