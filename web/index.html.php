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
	<div class="row">
		<form action="index.php" method ="post">
			
			<!-- Sensor Types -->
			<div class="col-lg-3">
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
			<div class="col-lg-3">	
				<div class="form-group">
					<h3>Locations</h3>
					<div class="checkbox">
						<label>
							<input type="checkbox" value=""><h4>Lifts</h4>
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input type="checkbox" value=""><h4>Corridors</h4>
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input type="checkbox" value=""><h4>Stairwells</h4>
						</label>
					</div>
					<div class="checkbox">
						<label>
							<input type="checkbox" value=""><h4>Parking</h4>
						</label>
					</div>
				</div>
			</div>	
			<!-- End Locations -->
			
			<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">	
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
				
				<div>
					<input type="submit" value="Submit Query"/>
				</div>
			</div>
		</form>
		
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
		<div class="panel panel-primary row col-lg-3 col-md-3 col-sm-3 col-xs-3">
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
		<button id="btn-export">Export To Excel</button>
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
							echo '<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="databaseTable">
								<thead><tr>'.current($output).'</tbody></table></div>';
						}
						else
						{
							$tabout = '<div class="panel-body"><ul class="nav nav-tabs">';
							$tableout = '<div class="tab-content">';
							
							if(array_key_exists("Lux", $output))
							{
								$tabout .= '<li><a href="#lighting" data-toggle="tab">Lighting</a></li>';
								$tableout .= '<div class="tab-pane fade" id="lighting">
							<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tableLux">
									<thead><tr>'.$output["Lux"].'</tbody></table></div></div>';
							}
							
							if(array_key_exists("Temperature", $output))
							{
								$tabout .= '<li><a href="#temperature" data-toggle="tab">Temperature</a></li>';
								$tableout .= '<div class="tab-pane fade" id="temperature">
							<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tableTemperature">
									<thead><tr>'.$output["Temperature"].'</tbody></table></div></div>';
							}
							
							if(array_key_exists("Humidity", $output))
							{
								$tabout .= '<li><a href="#humidity" data-toggle="tab">Humidity</a></li>';
								$tableout .= '<div class="tab-pane fade" id="humidity">
							<div class="table-responsive"><table class="table table-striped table-bordered table-hover" id="tableHumidity">
									<thead><tr>'.$output["Humidity"].'</tbody></table></div></div>';
							}
							echo $tabout."</ul>";
							echo $tableout."</div>";
						}
					?>
					</div>				
                </div>
            </div>
            <!--End Database output -->
        </div>
    </div>
</div>


<!-- Core Scripts - Include with every page -->
<script src="assets/plugins/jquery-1.10.2.js"></script>
<script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
<script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="assets/plugins/pace/pace.js"></script>
<script src="assets/scripts/siminta.js"></script>
<!-- Page-Level Plugin Scripts-->
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="http://www.shieldui.com/shared/components/latest/js/shieldui-all.min.js"></script>
<script type="text/javascript" src="http://www.shieldui.com/shared/components/latest/js/jszip.min.js"></script>

<script>
    $(document).ready(function () {
        $('#databaseTable').dataTable();
		$('#tableTemperature').dataTable();
		$('#tableLux').dataTable();
		$('#tableHumidity').dataTable();
    });
</script>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#tabs').tab();
    });
	
    jQuery(function ($) {
        $("#btn-export").click(function () {
            // parse the HTML table element having an id=databaseTable
            var dataSource = shield.DataSource.create({
                data: "#databaseTable",
                schema: {
                    type: "table",
					// Add php here
                    fields: {
                        SensorID: { type: String },
                        Timestamp: { type: String },
                        Value: { type: String },
						Location: { type: Number },
						BuildingID: { type: String }
                    }
                }
            });

            // when parsing is done, export the data to Excel
            dataSource.read().then(function (data) {
                new shield.exp.OOXMLWorkbook({
                    author: "PrepBootstrap",
                    worksheets: [
                        {
                            name: "dataTables-example",
                            rows: [
                                {
                                    cells: [
                                        {
                                            style: {
                                                bold: true
                                            },
                                            type: String,
                                            value: "SensorID"
                                        },
                                        {
                                            style: {
                                                bold: true
                                            },
                                            type: String,
                                            value: "Timestamp"
                                        },
                                        {
                                            style: {
                                                bold: true
                                            },
                                            type: String,
                                            value: "Value"
                                        },
										{
                                            style: {
                                                bold: true
                                            },
                                            type: String,
                                            value: "Location"
                                        },
										{
                                            style: {
                                                bold: true
                                            },
                                            type: String,
                                            value: "BuildingID"
                                        }
                                    ]
                                }
                            ].concat($.map(data, function(item) {
                                return {
                                    cells: [
                                        { type: String, value: item.SensorID },
                                        { type: String, value: item.Timestamp },
                                        { type: String, value: item.Value },
										{ type: Number, value: item.Location },
										{ type: String, value: item.BuildingID }
                                    ]
                                };
                            }))
                        }
                    ]
                }).saveAs({
                    fileName: "Database_Data"
                });
            });
        });
    });
</script>

</body>

</html>
