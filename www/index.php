<?php
	// Use HTTP Strict Transport Security to force client to use secure connections only
	$use_sts = true;

	// iis sets HTTPS to 'off' for non-SSL requests
	if ($use_sts && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
		header('Strict-Transport-Security: max-age=31536000');
	} elseif ($use_sts) {
		header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], true, 301);
		// we are in cleartext at the moment, prevent further execution and output
		die();
	}
	session_start();
	if(isset($_SESSION['user']))
	{
		header( "Location: dashboard.php" ); 
		exit;
	}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Building Dashboard Login</title>
	<!-- Core CSS - Include with every page -->
	<link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet" />
	<link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
	<link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet" />
	<link href="assets/css/style.css" rel="stylesheet" />
	<link href="assets/css/main-style.css" rel="stylesheet" />
	<script src="assets/plugins/jquery.js"></script>
	<script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
	<script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
</head>

<body class="body-Login-back">

    <div class="container">
       
        <div class="row">
            <div class="col-md-4 col-md-offset-4 text-center logo-margin ">
              <h1>Building Dashboard Login</h1>
                </div>
            <div class="col-md-4 col-md-offset-4">
                <div class="login-panel panel panel-default">                  
                    <div class="panel-heading">
                        <h3 class="panel-title">Please Sign In</h3>
                    </div>
                    <div class="panel-body">
                        <form id="loginForm" method="POST">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Username" name="user" type="username" autofocus>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="pass" type="password" value="">
                                </div>
                                <!-- Change this to a button or input when using this as a form -->
                                <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
	<script type="text/javascript">
	$("#loginForm").on('submit', function(event) {
		event.preventDefault();
		$.ajax({
			type:'POST', 
			url:'scripts/auth.php', 
			data:$('#loginForm').serialize(),
			success: function(response)
			{
				window.location.href = "dashboard.php";
			},
			error: function()
			{
				$("h3.panel-title").text("Incorrect sign-in details").css("color","red").css("font-weight", "bold");
			}
		});
	});

	</script>
</body>

</html>
