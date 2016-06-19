<?php
set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../include');
if (isset($_REQUEST["site"]))
	session_name($_REQUEST["site"]);
require_once("dbuser.class.php");
class users
{
	private $usersmng;

	public function __construct()
	{
		$this->usersmng = new dbuser_service();
	}

	private function _formatUser($entry, $full=true)
	{
		$data = array(
			"id"=>$entry->get("id"),
			"login"=>$entry->get("login"),
		);
		$data = array_merge($data, $entry->info($full));
		$data["status"] = "OK";
		return $data;
	}

	public function parse($message)
	{
		$response = array();
		$response["body"] = array();
		if (!isset($message["body"]))
			return $response;
		$actionlist = $message["body"];
		foreach ($actionlist as $action=>$argumentslist)
		{
			$response["body"][$action."Response"] = array();
			switch ($action)
			{
				case 'get':
				{
					$return = $this->usersmng->get($argumentslist);
					if (gettype($return) === "array")
					{
						$response["body"][$action."Response"]["users"] = array();
						foreach ($return as $entry)
						{
							$infos = $this->_formatUser($entry);
							$response["body"][$action."Response"]["users"][] = $infos;
						}
						$response["body"][$action."Response"]["result"] = 1;
					}
					else if ($return && gettype($return) === "object")
					{
						$infos = $this->_formatUser($return);
						$response["body"][$action."Response"] = array_merge($response["body"][$action."Response"], $infos);
						$response["body"][$action."Response"]["result"] = "OK";
						$response["body"][$action."Response"]["result"] = 1;
					}
					else
					{
						if (gettype($return) === "string")
							$response["body"][$action."Response"]["error"] = $return;
						$response["body"][$action."Response"]["result"] = 0;
					}
				}
				break;
				case 'save':
				{
					$return = $this->usersmng->save($argumentslist);
					if (gettype($return) === "array")
					{
						$response["body"][$action."Response"]["users"] = array();
						foreach ($return as $entry)
						{
							$infos = $this->_formatUser($entry);
							$response["body"][$action."Response"]["users"][] = $infos;
						}
						$response["body"][$action."Response"]["result"] = 1;
					}
					else if ($return && gettype($return) === "object")
					{
						$infos = $this->_formatUser($return);
						$response["body"][$action."Response"] = array_merge($response["body"][$action."Response"], $infos);
						$response["body"][$action."Response"]["result"] = "OK";
						$response["body"][$action."Response"]["result"] = 1;
					}
					else
					{
						if (gettype($return) === "string")
							$response["body"][$action."Response"]["error"] = $return;
						$response["body"][$action."Response"]["result"] = 0;
					}
				}
				break;
				case 'remove':
				{
					$return = $this->usersmng->rm($argumentslist);
					if (gettype($return) === "string")
					{
						$response["body"][$action."Response"]["error"] = $return;
						$response["body"][$action."Response"]["result"] = 0;
					}
					else if ($return)
					{
						$response["body"][$action."Response"]["result"] = "OK";
						$response["body"][$action."Response"]["result"] = 1;
					}
					else
						$response["body"][$action."Response"]["result"] = 0;
				}
				break;
			}
		}
		return $response;
	}
};

?>
