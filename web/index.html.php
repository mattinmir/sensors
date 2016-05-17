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
		<!-- Sensor Types -->
		<div class="col-lg-3">
			 <div class="form-group">
				<h3>Sensor Types</h3>
				<div class="checkbox">
					<label>
						<input type="checkbox" value=""><h4>Lighting</h4>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" value=""><h4>Temperature</h4>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" value=""><h4>Humidity</h4>
					</label>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox" value=""><h4>Occupancy</h4>
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
		

		
		<div class="col-lg-3">	
			<form action="index.php" method ="post">
				<!--Sensor ID-->
				<div class="row">
					<div class="col-lg-8">
						<div class="input-group input-group-lg">
							<span class="input-group-addon">
								Sensor ID
							</span>
						   
								<input type="text" name="sensorid" id="sensorid" class="form-control">
								
						</div>
					</div>
				</div>
				<!--End Sensor ID-->

				<br>

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
				
				<br>
				
				<div>
					<input type="submit" value="Submit Query"/>
				</div>
			</form>
			
		
		</div>
	

		<div class="col-lg-3">
			<!-- Notifications-->
			<div class="panel panel-primary">
				<div class="panel-heading">
					<i class="fa fa-bell fa-fw"></i>Notifications Panel
				</div>

				<div class="panel-body">
					<div class="list-group">
						<div  class="list-group-item">
							<i class="fa fa-bolt fa-fw"></i>Sensor ID 12345 Failed!
										<span class="pull-right text-muted small"><em>11:13 AM</em>
										</span>
						</div>
						<div class="list-group-item">
							<i class="fa fa-warning fa-fw"></i>Abnormal sounds from floor 3!
										<span class="pull-right text-muted small"><em>10:57 AM</em>
										</span>
						</div>

					</div>
				</div>

			</div>
			<!--End Notifications-->
		</div>
	</div>
	
	<div class="col-lg-12">
		<button id="btn-export">Export To Excel</button>
		<div class="row">
            <!-- Advanced Tables -->
            <div class="panel panel-default">
                <div class="panel-heading">
                    Advanced Tables
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                            <thead>
                            <tr>
								<?php    
									echo $output;
								?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!--End Advanced Tables -->
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
        $('#dataTables-example').dataTable();
    });
</script>

<script type="text/javascript">
    jQuery(function ($) {
        $("#btn-export").click(function () {
            // parse the HTML table element having an id=dataTables-example
            var dataSource = shield.DataSource.create({
                data: "#dataTables-example",
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
                                            type: Number,
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
