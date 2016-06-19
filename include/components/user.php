<?php
$input_address = "readonly";
if (isset($_REQUEST['action']))
{
	switch($_REQUEST['action'])
	{
	case 'save':
		if (isset($_REQUEST['lang']) && $_REQUEST['lang'] != "")
			$user->data["lang"] = $_REQUEST['lang'];
		if (isset($_REQUEST['login']) && $_REQUEST['login'] != "")
			$user->data["login"] = $_REQUEST['login'];
		if (isset($_REQUEST['password']) && $_REQUEST['password'] != "")
			$user->data["password"] = $_REQUEST['password'];

		$user->save();
		session_start();
		if (isset($_SESSION["continue"]))
		{
			$_SESSION["Auth"] = $user->id();
			header("Location: ".$_SESSION["continue"]);
			die;
		}
		session_write_close();
	break;
	case "create":
		if (isset($_SERVER["HTTP_REFERER"]))
		{
			session_start();
			$_SESSION["continue"] = $_SERVER["HTTP_REFERER"];
			session_write_close();
		}
		$input_address = "";
	break;
	}
}
?>
						<div class="container-fluid">
							<script type="text/javascript">
	function checkform()
	{
		var login = $("input[name='login']").val();
		if (login === "")
		{
			alert("Login must be set !!!");
			return false;
		}
		return cryptpassword();
	}
	function cryptpassword()
	{
		var password = $("input[type='password']#first").val();
		var confirm = $("input[type='password']#second").val();
		if (password !== confirm)
		{
			alert("Password and Confimation differents !!!");
			return false;
		}
		var shaObj = new jsSHA(password, 'TEXT');
		var hash = shaObj.getHash("SHA-1", "HEX");
		$("input[name='password']").val("$4$$"+hash).change();
		return true;
	}
	$(document).ready(function()
	{
		if (typeof($.require) === "object")
		$.require.js("lib/js/sha1.js");
		$("#UserSetting").find("input[name='lang']").val("<?=$user->language()?>");
	});
							</script>
							<form method="POST" class='form-horizontal' id="UserSetting" onsubmit="return checkform();">
								<div class="row">
									<input type="hidden" name="userid" class="form-control" value="<?=$userid?>" />
									<input type="hidden" name="action" class="form-control" value="save" />
									<div class="form-group">
										<label for="login" class="control-label col-sm-3"><?=_("Email address")?></label>
										<div class="col-sm-5">
											<input type="text" name="login" class="form-control" value="<?=$user->name()?>" <?=$input_address?> />
										</div>
										<label for="lang" class="control-label col-sm-2"><?=_("Language")?></label>
										<div class="col-sm-2">
											<select name="lang" class="form-control">
												<option value="fra">fran&ccedil;ais</option>
												<option value="eng">english</option>
												<option value="deu">deutch</option>
												<option value="spa">espa&ntilde;ol</option>
												<option value="ita">italiano</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label for="password" class="control-label col-sm-3"><?=_("New Password")?></label>
										<div class="col-sm-9">
											<input  type="password" id="first" value="" class="form-control" />
										</div>
									</div>
									<div class="form-group">
										<label for="password" class="control-label col-sm-3"><?=_("Confirm New Password")?></label>
										<div class="col-sm-9">
											<input  type="password" id="second" value="" class="form-control" />
										</div>
										<input type="hidden" name="password" value="" />
									</div>
<?php
	if ($user->has_capability("chggroup"))
	{
?>
									<div class="form-group">
										<label for="groupid" class="control-label col-sm-3"><?=_("Group")?></label>
										<div class="col-sm-9">
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
										</div>
									</div>
								</div>
<?php
	}
?>
								<div class="row">
									<div class="col-md-4"></div>
									<div class="col-md-4">
										<div class="form-group">
											<button type="submit" class="btn btn-primary btn-block"><span class="glyphicon glyphicon-floppy-save"></span> <?=_("Save")?></button>
										</div>
									</div>
									<div class="col-md-4">
										<a href="./signin.php?logout&site=<?=$_REQUEST["site"]?>&url=<?=$_REQUEST["url"]?>" class="btn btn-primary"><span class="glyphicon glyphicon-log-out"></span> <?=_("Logout")?></a>
									</div>
								</div>
							</form>
						</div>
