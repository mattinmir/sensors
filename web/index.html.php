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

	<script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 
	<style>#flotTip {z-index:2000 !important;}</style>


</head>

<body>


<div class="container">
    <div class="row">
        <!-- Page Header -->
        <div class="col-lg-12 text-center">
            <h1 class="page-header">Dashboard</h1>
        </div>
        <!--End Page Header -->
    </div>
</div>

<div class="container-fluid">
	<div class="row ">
		<div class="col-lg-9">
			<div class="col-lg-12 alert alert-success">
				<form action="index.php" method ="post">
					<!-- Sensor Types -->
					<div class="col-xs-4">
						<div class="form-group">
							<h3>Sensor Types</h3>
							<div class="radio">
								<label>
									<input type="radio" name="tableref" value="Lux"><h4>Lighting</h4>
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="tableref" value="Temperature"><h4>Temperature</h4>
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="tableref" value="Humidity"><h4>Humidity</h4>
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="tableref" value="Location"><h4>Location</h4>
								</label>
							</div>
						</div>
					</div>
					<!-- End Sensor Types -->

					<!-- Locations -->
					<div class="col-xs-4">	
						<div class="form-group">
							<h3>Locations</h3>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="Lifts" value="Lifts"><h4>Lifts</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="Corridors" value="Corridors"><h4>Corridors</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="Stairwells" value="Stairwells"><h4>Stairwells</h4>
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="Parking" value="Parking"><h4>Parking</h4>
								</label>
							</div>
						</div>
					</div>	
					<!-- End Locations -->
					
					<div class="col-xs-4">	
						<!--Floors-->
						<div class="row">
							<div class="col-lg-8">
								<div class="input-group input-group-lg">
										<span class="input-group-addon">
										   Floors
										</span>
									<input type="text" name="floors" id="floors" class="form-control">
								</div>
							</div>
						</div>
						<!--End Floors-->
						
						<br/>
						
						<!--Date From-->
						<div class="row">
							<div class="col-lg-8">
								<div class="input-group input-group-lg">
										<span class="input-group-addon">
										   Date From
										</span>
									<!-- <input type="text" name="datefrom" id="datefrom" class="form-control"> -->
									<input type="text" id="daterange" value="01/01/2015 - 01/31/2015" />

								</div>
							</div>
						</div>
						<!--End Date From-->
						
						<!--Date To-->
						<div class="row">
							<div class="col-lg-8">
								<div class="input-group input-group-lg">
										<span class="input-group-addon">
										   Date To
										</span>
									<input type="text" name="dateto" id="dateto" class="form-control">
								</div>
							</div>
						</div>
						<!--End Date To-->
						
						<div>
							<input type="submit" value="Submit Query"/>
						</div>
						
					</div>
				</form>
			</div>
		</div>
		
		<!-- Notifications-->	
		<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<i class="fa fa-bell fa-fw"></i>Notifications Panel
				</div>

				<div class="panel-body">
					<div class="list-group">
						<?php echo $failure_output; ?>
					</div>
				</div>

			</div>
			<!--End Notifications-->
		</div>
	</div>
	
	<form action="index.php" method ="post">
		<!--Sensor ID-->
		<div class="alert alert-success row col-lg-3 col-md-3 col-sm-3 col-xs-3">
			<div class="input-group input-group-lg">
				<span class="input-group-addon">
					Sensor ID
				</span>
				<input type="text" name="sensorid" id="sensorid" class="form-control">
			</div>
		
		<!--End Sensor ID-->
			
			<div>
				<input type="submit" value="Submit Sensor ID"/>
			</div>
			<br>
		</div>
	</form>	
	
	<div class="col-lg-12">
		<div class="row">
            <!-- Database Output -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    Database Data
                </div>
					
					<?php 
						if(sizeof($output) == 1)
						{
							reset($output);
							echo '<div class="panel-body"><div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="databaseTable">
								<thead><tr>'.current($output).'</tbody></table></div>
								<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" id="graph'.key($output).'">Show Graph</button></div>';
						}
						else
						{
							$tabout = '<div class="panel-body"><ul class="nav nav-tabs">';
							$tableout = '<div class="tab-content"></br>';
							
							if(array_key_exists("Lux", $output))
							{
								$tabout .= '<li><a href="#lighting" data-toggle="tab">Lighting</a></li>';
								$tableout .= '<div class="tab-pane fade" id="lighting">
							<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tableLux">
									<thead><tr>'.$output["Lux"].'</tbody></table></div>
									<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" id="graphLux">Show Graph</button></div>';
							}
							
							if(array_key_exists("Temperature", $output))
							{
								$tabout .= '<li><a href="#temperature" data-toggle="tab">Temperature</a></li>';
								$tableout .= '<div class="tab-pane fade" id="temperature">
							<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tableTemperature">
									<thead><tr>'.$output["Temperature"].'</tbody></table></div>
									<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" id="graphTemperature">Show Graph</button></div>';
							}
							
							if(array_key_exists("Humidity", $output))
							{
								$tabout .= '<li><a href="#humidity" data-toggle="tab">Humidity</a></li>';
								$tableout .= '<div class="tab-pane fade" id="humidity">
							<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tableHumidity">
									<thead><tr>'.$output["Humidity"].'</tbody></table></div>
									<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" id="graphHumidity">Show Graph</button></div>';
								
							}
							echo $tabout."</ul>";
							echo $tableout."</div>";
						}
					?>
				</br><button id="btn-export">Export To Excel</button>  <!-- Trigger the modal with a button -->
				
				<!-- GRAPH MODAL -->
				<div class="modal fade" id="myModal" role="dialog">
					<div class="modal-dialog" style="width:80%;height:80%">
						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Modal Header</h4>
							</div>
							
							<div class="modal-body">
								<div id="graphdiv" style="width:100%;margin:0 auto;height:80%"></div>
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


<!-- Core Scripts - Include with every page -->
<script src="assets/plugins/jquery-1.10.2.js"></script>
<script language="javascript" type="text/javascript" src="../../jquery.flot.js"></script>
<script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
<script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="assets/plugins/pace/pace.js"></script>
<script src="assets/scripts/siminta.js"></script>
<!-- Page-Level Plugin Scripts-->
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="http://www.shieldui.com/shared/components/latest/js/shieldui-all.min.js"></script>
<script type="text/javascript" src="http://www.shieldui.com/shared/components/latest/js/jszip.min.js"></script>


<!-- Include Required Prerequisites 
<script type="text/javascript" src="http://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

 
<!-- Include Date Range Picker -->
<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>

<script>
    $(document).ready(function () {
        $('#databaseTable').dataTable();
		$('#tableTemperature').dataTable();
		$('#tableLux').dataTable();
		$('#tableHumidity').dataTable();
		$('input[name="daterange"]').daterangepicker();
    });
</script>-->

<script src="assets/plugins/flot.tooltip/js/jquery.flot.tooltip.js"></script>

    <script src="assets/plugins/flot/jquery.flot.js"></script>
    <script src="assets/plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script src="assets/plugins/flot/jquery.flot.resize.js"></script>
    <script src="assets/plugins/flot/jquery.flot.pie.js"></script>
	<script src="assets/plugins/flot/jquery.flot.time.js"></script>
	<script type="text/javascript" src="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
		<link rel="stylesheet" type="text/css" href="http://cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />


<script type="text/javascript">

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
	$('#databaseTable').dataTable();
	$('#tableTemperature').dataTable();
	$('#tableLux').dataTable();
	$('#tableHumidity').dataTable();
	$('#tabs').tab();
	
	var values = document.getElementById("JSON-datatable");
	var JSONvalue = values.textContent;
	JSONobj = $.parseJSON(JSONvalue);
	
	//alert(JSON.stringify(JSONobj));
	
	var arrcnt = -1;
	var currentSensorID = -1;			
	
	if(JSONobj.Temperature.length > 0)
	{
		FlotTemp = [];
		currentSensorID = -1;			
		arrcnt = -1;
		for (var i in JSONobj.Temperature)
		{
			if (currentSensorID != JSONobj.Temperature[i].SensorID)
			{
				currentSensorID = JSONobj.Temperature[i].SensorID;
				FlotTemp.push( {label:"SensorID " + currentSensorID.toString(), data:[]} );
				++arrcnt;
			}
			FlotTemp[arrcnt].data.push([JSONobj.Temperature[i].Timestamp *1000, JSONobj.Temperature[i].Value]);
		}	
	}
	
	if(JSONobj.Humidity.length > 0)
	{
		FlotHumidity = [];
		currentSensorID = -1;
		arrcnt = -1;			
		for (var i in JSONobj.Humidity)
		{
			if (currentSensorID != JSONobj.Humidity[i].SensorID)
			{
				currentSensorID = JSONobj.Humidity[i].SensorID;
				FlotHumidity.push( {label:"SensorID " + currentSensorID.toString(), data:[]} );
				++arrcnt;
			}
			FlotHumidity[arrcnt].data.push([JSONobj.Humidity[i].Timestamp *1000, JSONobj.Humidity[i].Value]);
		}	
	}
	
	if(JSONobj.Lux.length > 0)
	{
		FlotLux = [];
		currentSensorID = -1;
		arrcnt = -1;			
		for (var i in JSONobj.Lux)
		{
			if (currentSensorID != JSONobj.Lux[i].SensorID)
			{
				currentSensorID = JSONobj.Lux[i].SensorID;
				FlotLux.push( {label:"SensorID " + currentSensorID.toString(), data:[]} );
				++arrcnt;
			}
			FlotLux[arrcnt].data.push([JSONobj.Lux[i].Timestamp *1000, JSONobj.Lux[i].Value]);
		}	
	}
	flotplot = $.plot($("#graphdiv"), [ [[0.5,0.5], [2,5]] ], { xaxis:{ mode:"time",timeformat: "%Y/%m/%d %H:%M:%S" }, 
		series:{ points:{ symbol:"circle",show:"true" }, lines:{ show:"true"} }, grid: { hoverable: true }, tooltip:{ show:true, defaultTheme:false} } );
});

$( "#graphLux" ).click(function() {
	
	flotplot.setData(FlotLux);
	flotplot.setupGrid();
	flotplot.draw();
	//alert("hi");
});

$( "#graphTemperature" ).click(function() {
	flotplot.setData(FlotTemp);
	flotplot.setupGrid();
	flotplot.draw();
});

$( "#graphHumidity" ).click(function() {
	flotplot.setData(FlotHumidity);
	flotplot.setupGrid();
	flotplot.draw();
});
// Simple type mapping; dates can be hard
// and I would prefer to simply use `datevalue`
// ... you could even add the formula in here.

testTypes = {
    "SensorID": "Number",
    "Floor": "Number",
    "Location": "String",
    "Timestamp": "String",
    "Value": "Number"
};

emitXmlHeader = function () {
    var headerRow =  '<ss:Row>\n';
    for (var colName in testTypes) {
        headerRow += '  <ss:Cell>\n';
        headerRow += '    <ss:Data ss:Type="String">';
        headerRow += colName + '</ss:Data>\n';
        headerRow += '  </ss:Cell>\n';        
    }
    headerRow += '</ss:Row>\n';    
    return '<?xml version="1.0"?>\n' +
           '<ss:Workbook xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">\n' +
           '<ss:Worksheet ss:Name="Sheet1">\n' +
           '<ss:Table>\n\n' + headerRow;
};

emitXmlFooter = function() {
    return '\n</ss:Table>\n' +
           '</ss:Worksheet>\n' +
           '</ss:Workbook>\n';
};

jsonToSsXml = function (jsonObject) {
    var row;
    var col;
    var xml;
    var data = typeof jsonObject != "object" 
             ? JSON.parse(jsonObject) 
             : jsonObject;

    xml = emitXmlHeader();

    for (row = 0; row < data.length; row++) {
        xml += '<ss:Row>\n';

        for (col in data[row]) {
            xml += '  <ss:Cell>\n';
            xml += '    <ss:Data ss:Type="' + testTypes[col]  + '">';
            xml += data[row][col] + '</ss:Data>\n';
            xml += '  </ss:Cell>\n';
        }

        xml += '</ss:Row>\n';
    }

    xml += emitXmlFooter();
    return xml;  
};

download = function (content, filename, contentType) {
    if (!contentType) contentType = 'application/octet-stream';
    var a = document.getElementById('grid');
    var blob = new Blob([content], {
        'type': contentType
    });
    a.href = window.URL.createObjectURL(blob);
    a.download = filename;
};

download(jsonToSsXml(testJson), 'test.xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

</script>



</body>

</html>
