<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/include/');
if (isset($_REQUEST["site"]))
	session_name($_REQUEST["site"]);
if (!isset($user))
{
	require_once "dbuser.class.php";
	$user = new dbuser();
}

$userid = $user->id();
?>
<html>
  <head>
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
	<body>
		<div class="container-fluid">
			<div class="col-md-6">
				<div id="AdministratorManager" class="panel panel-primary" >
					<div class="panel-heading text-center">
						<h4>
							<span class=""><?=_("Administrator Settings")?></span>
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
			<div class="col-md-6">
				<div id="UsersManager" class="panel panel-primary">
					<div class="panel-heading text-center">
						<h4>
							<span class=""><?=_("Users")?></span>
						</h4>
					</div>
<?php
if ($user->is_administrator())
{
?>
					<div class="panel-body">
						<div class="container-fluid">
							<script type="text/javascript">
	var adduser = function()
	{
		var options = {};
		var id = $("#addUser").find("input[name='id']").val();
		if (id !== undefined && id !== null && id !== "" && id > 0)
			options.id = id;
		options.login = $("#addUser").find("input[name='login']").val();
		options.groupid = $("#addUser").find("select[name='groupid']").val();
		options.autopassword = $('#addUser').find("input[name='autopassword']").is(":checked");
		if (!options.autopassword)
		{
			options.password = $("#addUser").find("input[name='password']").val();
		}
		perform("<?=$user->webservice()."?site=".$_REQUEST["site"]?>", "save",options,function(){if (this.password)alert("password set "+this.password);},function(){refreshusers();});
	}
	var setuser = function(userid)
	{
		if (userid !== undefined)
		{
			$("#addUser").find("input[name='id']").val(userid);
			var login = $("#UsersManager").find("#"+userid+" div[data-login]").text();
			$("#addUser").find("input[name='login']").val(login);
			$("#addUser").find("input[name='autopassword']").prop("checked",false);
			var groupid = $("#UsersManager").find("#"+userid+" div[data-groupid]").data("groupid");
			$("#addUser").find("select[name='groupid']").val(groupid);
//		var administrator = $("#UsersManager").find("#"+userid+" input[name='administrator']").val();
//		$("#addUser").find("select[name='administrator']").val(lang);
		}
		else
		{
			$("#addUser").find("input[name='id']").val(0);
		}
	}
	var displayusers = function()
	{
		var userslist = "";
		var insertuserrow = function()
		{
			var htmlstring = "<div class='container-fluid' id='"+this.id+"'>";
			htmlstring += "<div class='col-xs-5' data-login='true'>"+this.login+"</div>";
			htmlstring += "<div class='col-xs-4' data-groupid='"+this.groupid+"'>"+this.group+"</div>";
			htmlstring += "<div class='col-xs-1 pull-right'><a href='#' class='glyphicon glyphicon-pencil' data-toggle='modal' data-target='#addUser' onclick='setuser("+this.id+");'></a></div>";
			htmlstring += "</div>";
			return htmlstring;
		}
		if (this.users !== undefined)
		{
			$(this.users).each(function ()
			{
				userslist += insertuserrow.call(this);
			});
		}
		else if (this.id !== undefined)
		{
			userslist += insertuserrow.call(this);
		}
		var MainDiv = $('#UsersManager');
		$(MainDiv).find("#userslist").prepend(userslist);
	}
	var refreshusers = function()
	{
		$('#UsersManager').find("#userslist").html("");
		perform("<?=$user->webservice()."?site=".$_REQUEST["site"]?>", "get",{all:"true"},displayusers,{});
	}
	$(document).ready(function()
	{
		refreshusers();
	});
							</script>
							<div id="addUser" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header"><strong><?=_("Add user")?></strong><a href="#" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>
										<div class="modal-body">
											<div class="form-group">
												<div class="input-group">
													<input type="hidden" name="id" class="form-control" />
													<div class="input-group-addon"><?=_("login")?></div>
													<input type="text" name="login" class="form-control" />
													<div class="input-group-addon"></div>
													<select name="groupid" class="form-control">
<?php
		require_once "dbgroup.class.php";
		$group_service = new dbgroup_service();
		$groups = $group_service->get(array("all"=>true));
		foreach ($groups as $group)
		{
?>
														<option value="<?=$group->get("id")?>"><?=$group->get("name")?></option>
<?php
		}
?>
													</select>
													<span class="input-group-btn">
														<button class="btn btn-default" data-dismiss="modal" type="button" onclick="adduser();"><i class="glyphicon glyphicon-ok-circle text-success"></i><span class="sr-only sr-only-focusable" aria-hidden="true"><?=_("New")?></span></button>
														<button class="btn btn-default" data-dismiss="modal" type="button"><i class="glyphicon glyphicon-remove-circle text-danger"></i><span class="sr-only sr-only-focusable" aria-hidden="true"><?=_("Cancel")?></span></button>
													</span>
												</div>
											</div>
											<div class="form-group">
												<div class="input-group">
													<script>
	var editpassword = function()
	{
		var autopassword = $('#addUser').find("input[name='autopassword']").is(":checked");
		$('#addUser').find("input[name='password']").prop("disabled",autopassword);		
	}
													</script>
													<div class="input-group-addon"><?=_("password")?></div>
													<input type="text" class="form-control" name="password" />
													<span class="input-group-addon">
														automatic password
														<input type="checkbox" name="autopassword" aria-label="autopassword" onchange="editpassword();">
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class='container-fluid'>
								<div class='row'><div class="container-fluid"><div class='col-xs-5'><?=_("Login")?></div><div class='col-xs-4'><?=_("Group")?></div><div class='col-xs-1'></div></div></div>
								<div class='row' id="userslist">
								</div>
								<div class='row'><div class="container-fluid"><div class='col-xs-5'><a class="" data-toggle="modal" data-target="#addUser" role="button" onclick="setuser();"><i class="glyphicon glyphicon-plus-sign text-success"></i><span class="sr-only sr-only-focusable" aria-hidden="true"><?=_("Add User")?></span></a></div></div></div>
							</div>
						</div>
					</div>
<?php
}
?>
				</div>
			</div>
		</div>
	</body>
</html>
