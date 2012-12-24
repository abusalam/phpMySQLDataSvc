<?php
require_once('MySQLServer.php');
class DB {
	public $conn;
	public $result;
	public $Debug;
	public $RowCount;
	public $ColCount;
	private $NoResult;
	private function do_connect()
	{
		//$this->Debug=1;
		$this->conn=mysql_connect(HOST_Name,MySQL_User,MySQL_Pass);
		if(!$this->conn)
		{
			die('Could not Connect: '.mysql_error()."<br><br>");
		}
		mysql_select_db(MySQL_DB) or die('Cannot select database (database.php): '.mysql_error()."<br><br>");
		$this->NoResult=1;
	}
	public function SqlSafe($StrValue)
	{
		$this->do_connect();
		return mysql_real_escape_string($StrValue);
	}
	public function do_ins_query($querystr)
	{
		$this->do_connect();
		$this->result = mysql_query($querystr,$this->conn);
		if (!$this->result)
		{
			$message = 'Error(database): ' . mysql_error();
  			//$message .= 'Whole query: ' . $querystr."<br>";
			if($this->Debug)
				echo $message;
			$this->RowCount=0;
			return 0;
		}
		$this->NoResult=1;
		$this->RowCount=mysql_affected_rows($this->conn);
		return $this->RowCount;
	}
	
	public function do_sel_query($querystr)
	{
		$this->do_connect();
		$this->result = mysql_query($querystr,$this->conn);
		if (mysql_errno($this->conn))
		{
			if($this->Debug)
				echo mysql_error($this->conn);
			$this->NoResult=1;
			$this->RowCount=0;
			return 0;
		}
		$this->NoResult=0;
		$this->RowCount=mysql_num_rows($this->result);
		$this->ColCount=mysql_num_fields($this->result);
		return $this->RowCount;
	}
	
	public function get_row()
	{
		if(!$this->NoResult)
			return mysql_fetch_assoc($this->result);
	}

	public function get_n_row()
	{
		if (!$this->NoResult)
			return mysql_fetch_row($this->result);
	}
	public function GetFieldName($ColPos)
	{
		if(mysql_errno())
			return "ERROR!";
		else if($this->ColCount>$ColPos)
			return mysql_field_name($this->result,$ColPos);
		else
			return "Offset Error!";
	}
	
	public function GetTableName($ColPos)
	{
		if(mysql_errno())
			return "ERROR!";
		else if($this->ColCount>$ColPos)
			return mysql_field_table($this->result,$ColPos);
		else
			return "Offset Error!";
	}
	public function do_max_query($Query)
	{
		$this->do_sel_query($Query);
		$row= $this->get_n_row();
		//echo "Whole Row: ".$row[0].$row[1];
		if ($row[0]==null)
			return 0;
		else
			return $row[0];
	}
	public function do_close()
	{
		// Free resultset 
		if(!$this->NoResult)
			mysql_free_result($this->result);		
		// Closing connection
		if(isset($this->conn))
			mysql_close($this->conn);
		//echo "<br />LastQuery: ".$LastQuery;
	}
	public function __sleep()
	{
    	$this->do_close(); 
		return array('conn','result','Debug');
  	}	
  	public function __wakeup()
	{
    	$this->do_connect();
  	}
}  	
?>