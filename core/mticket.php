<?php
class Mticket extends MongoCollection implements ITicket
{
	private $colname='mticket';
	function __construct()
	{
		global $db;
		parent::__construct($db,$this->colname);
	}
	function Sync()
	{
	
    } 
	function Get($cve_number)
	{
		return $this->Find(["summary"=>$cve_number]);
	}
}
?>