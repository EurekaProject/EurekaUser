<?php
header("Access-Control-Allow-Origin: *");
require_once("users.class.php");

$users = new users();
if (isset($_REQUEST["envelope"]))
{
  $message = $_REQUEST["envelope"];
  $response = $users->parse($message);
  $message = json_encode($response);
  //error_log("webs : ".$message);
  echo $message;
}

?>
