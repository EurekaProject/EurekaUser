<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/include/');
require_once "dbuser.class.php";
$users = new dbuser_service();
$administrator = $users->get(array("administrator"=>1));
?>
<!DOCTYPE html>
<html>
  <head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="./lib/css/normalize.css" >
    <script language="javascript" src="./lib/js/jquery-2.1.1.min.js"></script>
		<link rel="stylesheet" href="./lib/css/jquery-ui.min.css" >
    <script language="javascript" src="./lib/js/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="./lib/css/bootstrap.min.css" >
		<script language="javascript" src="./lib/js/bootstrap.min.js" ></script >
		<script language="javascript" src="./include/eureka.js" ></script >
		<link rel="stylesheet" href="./include/eureka.css" >
		<!--<link rel="stylesheet" type="text/css" href="css/common.css">-->
	</head>
	<body class="container">
		<div class="panel panel-primary">
			<div class="panel-heading text-center"><h4>signup</h4></div>
			<div class="panel-body">
				<div class="container-fluid">
					Please contact <?=$administrator->get("login")?> to open an account or use the following form
				</div>
				<div class="container-fluid">
<?php
		require_once('common/mailer.class.php');
		$mailer = new mailer($administrator->get("login"));
		$mailer->subject = "New account request";
		$mailer->message = "Please I need an account for :";
		$mailer->generateEditor();
?>
				</div>
			</div>
		</div>
	</body>
</html>
