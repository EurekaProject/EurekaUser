<?php
require_once("common/user.class.php");
require_once("common/db.class.php");


abstract class absgroup_info implements Serializable
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

class group_info extends absgroup_info
{
};
class dbgroup_service
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
			$sql = "SHOW TABLES FROM `".$this->dbname."` LIKE 'groups';";
			$result = $this->db->query($sql);
			if (!$result || $result->num_rows() < 1)
			{
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
		else if (isset($options["name"]))
		{
				$where .= $separators[$separator]." `name` = '".$options["name"]."' ";
				$separator = 1;
		}
		if (isset($options["name"]))
		{
				$name = $options["name"];
		}
		if (isset($options["description"]))
		{
				$description = $options["description"];
		}
		$sql = "SELECT * FROM `groups` ".$where;
		$result = $this->db->query($sql);
		if ($result == false || $result->num_rows() == 0)
		{
			if (!isset($name)) $name = "newer";
			if (!isset($description)) $description = "";
			$sql = "INSERT INTO `groups` (`name`, `description`)".
				"VALUES ('".$name."','".$description."');";
			$result = $this->db->query($sql);
			if ($result === false)
			{
				error_log("error on ".$sql);
				error_log("dbgroup::create : internal error");
				return false;
			}
			$where = "WHERE `id` = ".$result->value(0)["id"];
		}
		else
		{
			$separators = ["SET",","];
			$separator = 0;
			$sql = "UPDATE `groups` ";
			if (isset($name))
			{
				$sql .= $separators[$separator]." `name` = '".$name."' ";
				$separator = 1;
			}
			if (isset($description))
			{
				$sql .= $separators[$separator]." `description` = '".$description."' ";
				$separator = 1;
			}
			if ($separator == 1)
			{
				$sql .= " ".$where.";";
				$result = $this->db->query($sql);
				if ($result === false)
				{
					error_log("error on ".$sql);
					error_log("dbgroup::create : internal error");
					return false;
				}
			}
		}
		$sql = "SELECT * FROM `groups` ".$where;
		$result = $this->db->query($sql);
		if ($result === false || $result->num_rows() !== 1)
		{
			error_log("error on ".$sql);
			error_log("dbgroup::create : internal error");
			return false;
		}
		$group = $result->fetch_array();
		if (isset($options["capabilities"]) && gettype($options["capabilities"]) === "array")
		{
			foreach ($options["capabilities"] as $key => $value)
			{
				$sql = "SELECT * FROM `capabilities` WHERE name = '".$key."';";
				$result2 = $this->db->query($sql);
				if ($result2 && $result2->num_rows() > 0)
				{
					$capability = $result2->fetch_array();
					$sql = "INSERT INTO `groupscapabilities` (`groupid`,`capabilityid`, `mode`) VALUE (".$group["id"].",".$capability["id"].",".$value.");";
					$result3 = $this->db->query($sql);
					if (!$result3)
					{
						error_log("error on ".$sql);
						error_log("dbgroup::create : internal error");
					}
				}
			}
		}
		$ret = new group_info($group);
		
		return $ret;
	}

	private function _search($options, $cmd="SELECT * FROM `groups` ")
	{
		$separators = array( "WHERE", "AND");
		$separator = 0;
		$where="";
		$order="";
		if (isset($options["id"]))
		{
			$where .= $separators[$separator]." id = ".$options["id"]." ";
			$separator = 1;
		}
		if (isset($options["name"]))
		{
			$where .= $separators[$separator]." name = '".$options["name"]."' ";
			$separator = 1;
		}
		if (isset($options["order"]))
		{
			$order = " ORDER BY `".$options["order"]."` ";
		}

		$sql = $cmd." ".$where.$order.";";
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
		return "group not found";
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
		if (isset($options["name"]))
		{
			$where .= $separators[$separator]." name = '".$options["name"]."' ";
			$separator = 1;
		}
		$sql = "DELETE FROM `groups` ".$where.";";
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
