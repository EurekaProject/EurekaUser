<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/include/');
if (!isset($insert))
{
?>
<!DOCTYPE html>
<html>
  <head>
		<link rel="stylesheet" href="./lib/css/normalize.css" >
    <script language="javascript" src="./lib/js/jquery-2.1.1.min.js"></script>
		<link rel="stylesheet" href="./lib/css/bootstrap.min.css" >
		<script language="javascript" src="./lib/js/bootstrap.min.js" ></script >
		<script language="javascript" src="./lib/js/sha1.js" ></script >
		<!--<link rel="stylesheet" type="text/css" href="css/common.css">-->
	</head>
	<body>
<?php
}
if (!isset($user))
{
	require_once "dbuser.class.php";
	$user = new dbuser();
}
$username = $user->name();
$userid = $user->id();
?>
		<div class="container-fluid">
			<div class="panel panel-primary">
				<div class="panel-heading text-center">
					<h4>
						<span class=""><?=_("User Settings")?>: </span>
						<span class=""><?=$username?></span>
					</h4>
				</div>
				<div class="panel-body">
<?php
{
	include("components/user.php");
}
?>
				</div>
			</div>
		</div>
<?php
if (!isset($insert))
{
?>
	</body>
</html>
<?php
}
?>
