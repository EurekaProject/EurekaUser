<?php
require_once("common/user.class.php");
require_once("common/db.class.php");

class dbauth
{
	var $dbname="eureka_user";
	var $uid = false;
	function __construct()
	{
		$this->db = new db();
		if ($this->db->status == 0)
		{
			$this->debug = true;
			if ($this->db->select_db($this->dbname) == false)
			{
				$this->db->create_db($this->dbname);
				$this->db->import("../db/users.sql");    
			}
			$this->db->close();
		}
		return true;
	}
	
	protected function _login($name, $password)
	{
		$this->db = new db($this->dbname);
		if ($password !== "")
		{
			$password = $this->db->escape_string(sha1($password));
		}
		$sql = sprintf("SELECT id FROM `users` WHERE login = '%s' AND password = '%s'",
								$this->db->escape_string($name),
								$password);
		$result = $this->db->query($sql);
		if($result !== false && $result->num_rows() == 1)
		{
			$this->uid = $result->value();
			$ret = true;
		}
		else
		{
			$ret = false;
		}
		$this->db->close();
		return $ret;
	}
	protected function _logout()
	{
		$this->uid = false;
	}
	public function is_authenticated()
	{
		return ($this->uid != FALSE);
	}
}

class dbuser extends user
{
	var $dbname;
	var $dbhost;
	var $dbuser;
	var $dbpass;
	var $newUser = false;
	function __construct($eurekadb = "root:root@127.0.0.1/eureka_user", $login = false, $password = false)
	{
		list($this->dbuser,$this->dbhost) = explode('@',$eurekadb);
		list($this->dbuser,$this->dbpass) = explode(':',$this->dbuser);
		list($this->dbhost,$this->dbname) = explode('/',$this->dbhost);
		session_start();
		if (isset($_SESSION['eurekadb']))
		{
			$eurekadb = $_SESSION['eurekadb'];
			list($this->dbuser,$this->dbhost) = explode('@',$eurekadb);
			list($this->dbuser,$this->dbpass) = explode(':',$this->dbuser);
			list($this->dbhost,$this->dbname) = explode('/',$this->dbhost);
		}
		session_write_close();
		if (isset($_REQUEST['eurekadb']))
		{
			$eurekadb = $_REQUEST['eurekadb'];
			list($this->dbuser,$this->dbhost) = explode('@',$eurekadb);
			list($this->dbuser,$this->dbpass) = explode(':',$this->dbuser);
			list($this->dbhost,$this->dbname) = explode('/',$this->dbhost);
		}
		$this->db = new db("", $this->dbhost,$this->dbuser,$this->dbpass);
		if ($this->db->status === 0)
		{
			$this->debug = true;
			if ($this->db->select_db($this->dbname) === false)
			{
				$ret = $this->db->create_db($this->dbname);
			}

			$sql = "SHOW TABLES FROM `".$this->dbname."` LIKE 'users';";
			$result = $this->db->query($sql);
			if (!$result || $result->num_rows() < 1)
			{
				$this->db->import(dirname(__FILE__)."/../db/groups.sql");
				$this->db->import(dirname(__FILE__)."/../db/users.sql");
				$this->db->import(dirname(__FILE__)."/../db/capabilities.sql");
				$this->db->import(dirname(__FILE__)."/../db/groupscapabilities.sql");
				$this->db->close();
				$this->data["id"] = 0;
				$this->data["groupid"] = 0;
				$this->data["login"] = "Administrator";
				$this->data["is_administrator"] = 1;
				$this->data["capabilities"]["admin"] = true;
				$this->data["capabilities"]["chglogin"] = "750";
				$this->data["capabilities"]["chgpasswd"] = "750";
				$this->data["capabilities"]["chggroup"] = "750";
				$this->save();
				session_start();
				$_SESSION["Auth"] = $this->data["id"];
				$_SESSION['NgenUser'] = $this->serialize();
				session_write_close();
			}
			$this->db->close();
		}
		else
		{
			error_log("dbuser: db not accessible");
		}
		parent::__construct($login, $password);
	}
	protected function _initialize()
	{
	}

	protected function _login($name, $lpassword)
	{
		$this->db = new db($this->dbname, $this->dbhost,$this->dbuser,$this->dbpass);
		if ($lpassword !== "" )
		{
			if ($lpassword[0] === "$")
			{
				//password schema: $4$SALT$Password
				list($empty,$type,$salt,$password) = explode("$",$lpassword);
				switch($type)
				{
					case "1":
					break;
					case "4":
							error_log("Sha1 password ".$password);
					break;
				}
				$password=$lpassword;
			}
			else 
				$password = "$4$$".$this->db->escape_string(sha1($lpassword));
		}
		else
				$password=$lpassword;
		$sql = sprintf("SELECT * FROM `users` WHERE login = '%s' AND password = '%s'",
								$this->db->escape_string($name),
								$password);
		$ret = $this->_setuser($sql);
		if ($ret)
		{
			$this->data["capabilities"]["chgpasswd"] = "750";
			$this->data["capabilities"]["chggroup"] = "750";
		}
		$this->db->close();
		return $ret;
	}

	protected function _externlogin($name)
	{
		$this->db = new db($this->dbname, $this->dbhost,$this->dbuser,$this->dbpass);
		$this->data["login"] = $this->db->escape_string($name);
		$sql = sprintf("SELECT * FROM `users` WHERE login = '%s'",
						$this->db->escape_string($name));
		$ret = $this->_setuser($sql);
		if ($ret == false)
		{
			// duplicate the user with id = 3 (default user)
			$sql = sprintf("SELECT `groupid`, `lang`, `is_administrator` FROM `users` WHERE id = 3");
			$ret = $this->_setuser($sql);
			$ret = $this->_save($name);
			$sql = sprintf("SELECT * FROM `users` WHERE login = '%s'",
							$this->db->escape_string($name));
			$ret = $this->_setuser($sql);
		}
		$this->db->close();
		return $ret;
	}

	protected function _setuser($sql)
	{
		global $_SERVER;
		$result = $this->db->query($sql);
		if($result !== false && $result->num_rows() == 1)
		{
			$user = $result->fetch_array();
			$this->data = array_merge($this->data, $user);
			if ($user['is_administrator'] == 1)
	$this->data["capabilities"]['admin'] = TRUE;
			else
			{
	$this->data["capabilities"]['admin'] = FALSE;
			}
			$sql = "SHOW TABLES `".$this->dbname."` LIKE `capabilities`";
			$result = $this->db->query($sql);
			if ($result && $result->num_rows() >0)
			{
	$sql = "SELECT capabilities.name AS `name`, groupscapabilities.mode AS `mode` ".
			"FROM `capabilities`, `groupscapabilities` ".
			"WHERE groupscapabilities.capabilityid = capabilities.id AND ".
		"groupscapabilities.groupid = ".$user["groupid"] ;
	$result = $this->db->query($sql);
	if (!$result)
	{
		error_log(__FILE__."(".__LINE__.") : ".$this->db->error());
		error_log("error on ".$sql);
		return false;
	}
	for ($i = 0 ; $i < $result->num_rows(); $i++)
	{
			$capability = $result->fetch_array();
			$this->data["capabilities"][$capability["name"]] = $capability["mode"];
			error_log("capability :".$capability["name"]);
	}
			}
			// Some data are loaded once at login time. Get current time to know when those data were loaded.
			$this->clock = time();

			// monitoring successful authentication info in db
	//            $sql="INSERT INTO sessions (ip, login, page, action, description, success) VALUES ('".$_SERVER['REMOTE_ADDR']."', '".$user['login']."', 'login', 'LOGIN', '', 1)";
	//            $this->db->query($sql);

			$ret = $this->data["id"];
		}
		else
		{
				// monitoring login failed in db
//            $referer = isset($_SERVER['HTTP_REFERER'])?substr($_SERVER['HTTP_REFERER'], 0, 255):"";
//            $sql="INSERT INTO sessions (ip, login, page, referer, action, description, success) VALUES ('".$_SERVER['REMOTE_ADDR']."', '', '".$_SESSION['page']."', '$referer', 'LOGIN', '".mysql_real_escape_string(_("Authentication failed with login"))." \"".substr($this->db->mysql_real_escape_string($name), 0, 50)."\"', 0)";
//            $this->db->query($sql);
			error_log(__FILE__."(".__LINE__.") : ".$this->db->error());
			error_log("error on ".$sql);
			$ret = false;
		}
		return $ret;
	}

	protected function _logout()
	{
	}

	protected function _save()
	{
		$this->db = new db($this->dbname, $this->dbhost,$this->dbuser,$this->dbpass);
		$sql = "SELECT * FROM `users` WHERE id = ".$this->data["id"];
		$result = $this->db->query($sql);
		error_log("dbuser: ".$sql);
		if($result !== false && $result->num_rows() == 1)
		{
			if (isset($this->data["capabilities"]["chglogin"]) &&
					$this->data["capabilities"]["chglogin"] === "750" &&
					isset($this->data["login"]))
			{
				$login = "`login`= '".$this->data["login"]."', ";
			}
			if (isset($this->data["capabilities"]["chgpasswd"]) &&
					$this->data["capabilities"]["chgpasswd"] === "750" &&
					isset($this->data["password"]))
			{
				if ($this->data["password"][0] === "$")
				{
					$password = $this->data["password"];
				}
				else if ($this->data["password"] !== "" )
				{
					$password = "$4$$".$this->db->escape_string(sha1($this->data["password"]));
				}
				else
				{
					$password = "";
				}
				$password = "`password`= '".$password."', ";
			}
			else
			{
				$password = "*";
			}
			if (isset($this->data["capabilities"]["chggroup"]) &&
					$this->data["capabilities"]["chggroup"] === "750" )
			{
				$groupid = "`groupid`= '".$this->data["groupid"]."', ";
			}
			else
			{
				$groupid = "";
			}
			$sql = "UPDATE `users` SET ".
					$login.
					$groupid.
					$password.
					"`lang`= '".$this->data["lang"]."' ".
					"WHERE `id`=".$this->data["id"];
			$this->db->query($sql);
			$ret = true;
		}
		else
		{
			if (isset($this->data["password"]))
			{
				if ($this->data["password"][0] === "$")
					$password = $this->data["password"];
				else if ($this->data["password"] !== "" )
					$password = "$4$$".$this->db->escape_string(sha1($this->data["password"]));
				else
					$password = "";
			}
			else
			{
				$password = "*";
			}
			$sql = "INSERT INTO `users` (`groupid`, `is_administrator`, `lang`, `login`, `password`, `description`) VALUES ".
					"(".$this->data["groupid"].", ".$this->data["is_administrator"].", '".$this->data["lang"]."', '".$this->data["login"]."', '".$password."', '')";
			$result = $this->db->query($sql);
			if($result !== false)
			{
				$this->data["id"]=$result->value();
				$this->newUser=true;
				$ret = true;
			}
			else
			{
				error_log(__FILE__."(".__LINE__.") : ".$this->db->error());
				error_log("error on ".$sql);
				$ret = false;
			}
		}
		$this->db->close();
		return $ret;
	}

	function webservice()
	{
		return dirname($_SERVER["PHP_SELF"])."/ws/index.php";
	}
}

abstract class absuser_info implements Serializable
{
	var $data;
	public function serialize()
	{
		return serialize($this->data);
	}
	public function unserialize($data)
	{
		$this->data = unserialize($data);
	}
	function __construct($options)
	{
		$this->data = $options;
	}
	function get($key, $force=false)
	{
		return $this->data[$key];
	}
	function set($key, $value, $force = false)
	{
			return $this->data[$key] = $value;
	}
	function info($full=false)
	{
		return $this->data;
	}
};

class user_info extends absuser_info
{
};
class dbuser_service
{
	var $dbname;
	function __construct($dbname = "eureka_user", $dbhost = "127.0.0.1")
	{
		$this->dbname = $dbname;
		$this->db = new db("",$dbhost);
		if ($this->db->status == 0)
		{
			if ($this->db->select_db($this->dbname) == false)
			{
				$this->db->create_db($this->dbname);
			}
			$sql = "SHOW TABLES FROM `".$this->dbname."` LIKE 'users';";
			$result = $this->db->query($sql);
			if (!$result || $result->num_rows() < 1)
			{
				$this->db->import(dirname(__FILE__)."/../db/users.sql");
				$this->db->import(dirname(__FILE__)."/../db/groups.sql");
				$this->db->import(dirname(__FILE__)."/../db/capabilities.sql");
				$this->db->import(dirname(__FILE__)."/../db/groupscapabilities.sql");
			}
		}
	}

	function _destruct()
	{
		if ($this->db)
			$this->db->close();
	}
	function generatepasswd( $length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
			return substr( str_shuffle( $chars ), 0, $length );
	}
	function save($options = array())
	{
		$separators = ["WHERE","AND"];
		$separator = 0;
		$where="";
		if (isset($options["id"]) && $options["id"] !== "null" && $options["id"] !== "0")
		{
			$where .= $separators[$separator]." `id` = ".$options["id"]." ";
			$separator = 1;
		}
		else if (isset($options["login"]))
		{
				$where .= $separators[$separator]." `login` = '".$options["login"]."' ";
				$separator = 1;
		}
		if (isset($options["login"]))
		{
				$login = $options["login"];
		}
		if (isset($options["autopassword"]) && $options["autopassword"] === "true")
		{
				$password = $this->generatepasswd();
		}
		else if (isset($options["password"]))
		{
			$password = $options["password"];
		}
		if (isset($options["lang"]))
		{
				$lang = $options["lang"];
		}
		if (isset($options["description"]))
		{
				$description = $options["description"];
		}
		if (isset($options["groupid"]))
		{
				$groupid = $options["groupid"];
		}
		if (isset($options["administrator"]) && $options["administrator"] === "true")
		{
				$administrator = 1;
		}

		$sql = "SELECT * FROM `users` ".$where;
		$result = $this->db->query($sql);
		if ($result == false || $result->num_rows() == 0)
		{
			if (!isset($administrator)) $administrator = 0;
			if (!isset($login)) $login = "newer";
			if (!isset($description)) $description = "";
			if (!isset($groupid)) $groupid = 0;
			if (!isset($lang)) $lang = "fra";
			if (!isset($password))
				$crypt = "";
			else
				$crypt = "$4$$".$this->db->escape_string(sha1($password));
			$sql = "INSERT INTO `users` (`login`, `is_administrator`, `description`, `lang`, `password` , `groupid`)".
				"VALUES ('".$login."', ".$administrator.", '".$description."', '".$lang."', '".$crypt."', ".$groupid.");";
			$result = $this->db->query($sql);
			if ($result === false)
			{
				error_log("dbuser::create : internal error");
				return false;
			}
			$where = "WHERE `id` = ".$result->value(0)["id"];
		}
		else
		{
			$separators = ["SET",","];
			$separator = 0;
			$sql = "UPDATE `users` ";
			if (isset($login))
			{
				$sql .= $separators[$separator]." `login` = '".$login."' ";
				$separator = 1;
			}
			if (isset($lang))
			{
				$sql .= $separators[$separator]." `lang` = '".$lang."' ";
				$separator = 1;
			}
			if (isset($description))
			{
				$sql .= $separators[$separator]." `description` = '".$description."' ";
				$separator = 1;
			}
			if (isset($administrator))
			{
				$sql .= $separators[$separator]." `is_administrator` = ".$administrator." ";
				$separator = 1;
			}
			if (isset($groupid))
			{
				$sql .= $separators[$separator]." `groupid` = ".$groupid." ";
				$separator = 1;
			}
			if (isset($password))
			{
				if (!empty($password) && $password[0] !== "$")
					$crypt = "$4$$".$this->db->escape_string(sha1($password));
				else
					$crypt = $password;
				$sql .= $separators[$separator]." `password` = '".$crypt."' ";
				$separator = 1;
			}
			$sql .= " ".$where.";";
			$result = $this->db->query($sql);
			if ($result === false)
			{
				error_log($sql);
				error_log("resource::create : internal error");
				return false;
			}
		}
		$sql = "SELECT * FROM `users` ".$where;
		$result = $this->db->query($sql);
		$value = array();
		if ($result === false || $result->num_rows() !== 1)
		{
			error_log($sql);
			error_log("resource::create : internal error");
			return false;
		}
		$value = $result->fetch_array();
		$value["password"] = $password;
		$user = new user_info($value);
		
		return $user;
	}

	private function _search($options, $cmd="SELECT `users`.*, `groups`.name AS 'group' ")
	{
		$separators = array( "WHERE", "AND");
		$separator = 0;
		$where="";
		$order="";
		$userid="";
		$projectid="NULL";
		$workhours="";
		$directory="";
		if (isset($options["id"]))
		{
			$where .= $separators[$separator]." id = ".$options["id"]." ";
			$separator = 1;
		}
		if (isset($options["login"]))
		{
			$where .= $separators[$separator]." login = '".$options["login"]."' ";
			$separator = 1;
		}
		if (isset($options["administrator"]))
		{
			$where .= $separators[$separator]." `is_administrator` = ".$options["administrator"]." ";
			$separator = 1;
		}
		if (isset($options["groupid"]))
		{
			$where .= $separators[$separator]." `groupid` = ".$options["groupid"]." ";
			$separator = 1;
		}
		if (isset($options["password"]))
		{
			$where .= $separators[$separator]." password = '".$options["password"]."' ";
			$separator = 1;
		}
				$where .= $separators[$separator]." `groupid` = `groups`.id ";
				$separator = 1;
		if (isset($options["order"]))
		{
			$order = " ORDER BY `".$options["order"]."` ";
		}

		$sql = $cmd." FROM `users`, `groups` ".$where.$order.";";
		$result = $this->db->query($sql);
		if ($result === false)
		{
			error_log("error on ".$sql);
			return "no resource found";
		}
		return $result;
	}

	/**
	 * @brief : returns one or more tasks.
	 * 
	 * @return : false on error otherwise the terminal element created.
	 **/
	function get($options=array())
	{
		$result = $this->_search($options);
		if ($result !== false && gettype($result) !== "string")
		{
			$idx=-1;
			if (isset($options["idx"]))
			{
				$idx = $options["idx"];
			}
			$nbrows = $result->num_rows();

			if ($nbrows === 1)
				$idx = 0;

			if ($idx == -1)
			{
				$users = array();
				for($i = 0; $i < $nbrows; $i++)
				{
					$value = $result->fetch_array();
					$user = new user_info($value);
					$users[] = $user;
				}
				return $users;
			}
			else
			{
				$value = $result->value($idx);
				$user = new user_info($value);
				return $user;
			}
		}
		return "user not found";
	}

	function rm($options=array())
	{
		$separators = array( "WHERE", "AND");
		$separator = 0;
		$where="";
		if (isset($options["id"]))
		{
			$where .= $separators[$separator]." id = ".$options["id"]." ";
			$separator = 1;
		}
		if (isset($options["login"]))
		{
			$where .= $separators[$separator]." login = '".$options["login"]."' ";
			$separator = 1;
		}
		if (isset($options["password"]))
		{
			$where .= $separators[$separator]." password = '".$options["password"]."' ";
			$separator = 1;
		}
		$sql = "DELETE FROM `users` ".$where.";";
		$result = $this->db->query($sql);
		if ($result === false)
		{
			error_log("error on ".$sql);
			return "no user found";
		}
		return true;
	}
};
?>
