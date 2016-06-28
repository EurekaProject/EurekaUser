<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/include/');

if (isset($_REQUEST["site"]))
	session_name($_REQUEST["site"]);
require_once "dbuser.class.php";
$user = new dbuser();
if (isset($_REQUEST["logout"]))
	$user->logout();

if (isset($_REQUEST["login"]) && isset($_REQUEST["password"]))
{
	$user->login($_REQUEST["login"], $_REQUEST["password"]);
}
if (isset($_REQUEST["url"]))
	$redirect = $_REQUEST["url"];
else
{
	$redirect = "";
	error_log("query string ".$_SERVER["QUERY_STRING"]);
}

if ($user->is_authenticated())
{
	if (isset($_REQUEST["where"]))
		header("Location: ".$_REQUEST["where"]);
	else if (isset($_REQUEST["url"]))
		header("Location: ".$redirect);
	else
		echo "Login done";
	die;
}
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
		<style>
		.modal-header, h4, .close {
				text-align: center;
				font-size: 30px;
		}
		.modal-footer {
				background-color: #f9f9f9;
		}
		</style>
	</head>
	<body>
		<div class="container-fluid">
			<!-- Modal -->
			<div class="modal fade" id="signin" role="dialog">
				<div class="modal-dialog">
				
					<!-- Modal content-->
					<div class="modal-content">
						<form id="loginform" role="form" method="POST" action="<?=$_SERVER["PHP_SELF"]?>?site=<?=session_name()?>&url=<?=$redirect?>" onsubmit="checkform();">
							<div class="modal-header bg-primary" style="padding:35px 50px;">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4><span class="glyphicon glyphicon-lock"></span> <?=_("Login")?></h4>
							</div>
							<div class="modal-body" style="padding:40px 50px;">
								<div class="form-group">
									<label for="login"><span class="glyphicon glyphicon-user"></span> <?=_("Username")?></label>
									<input type="text" class="form-control" name="login" placeholder="Enter email">
								</div>
								<div class="form-group">
									<label for="password"><span class="glyphicon glyphicon-eye-open"></span> <?=_("Password")?></label>
									<input type="password" class="form-control" name="password" placeholder="Enter password">
								</div>
								<div class="checkbox">
									<label><input type="checkbox" name="remember" value="" checked>Remember me</label>
								</div>
								<input type="hidden" name="where" value="<?=$redirect?>" />
								<button type="submit" class="btn btn-primary btn-block"><span class="glyphicon glyphicon-ok-circle"></span> <?=_("Login")?></button>
							</div>
						</form>
						<div class="modal-footer">
							<button type="button" class="btn btn-danger btn-default pull-left" data-dismiss="modal"><span class="glyphicon glyphicon-remove-circle"></span> <?=_("Cancel")?></button>
							<p>Not a member? <a href="./signup.php">Sign Up</a></p>
							<p>Forgot <a href="./lostpwd.php">Password?</a></p>
							<p>Manage <a href="#" onclick="redirect('settings.php?site=<?=session_name()?>&url=<?=$redirect?>');">Account</a> </p>
						</div>
					</div>
					
				</div>
			</div> 
		</div>
		 
		<script>
		var checkform = function()
		{
			return true;
		}
		var redirect = function(where)
		{
			$("#loginform").find("input[name='where']").val(where).change();
			$("#loginform").submit();
		}
		$(document).ready(function(){
			$("#signin").modal();
		});
		</script>
	</body>
</html>
