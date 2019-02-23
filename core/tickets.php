<?php
class Tickets
{
	private $modules = [];
	function __construct()
	{
		$this->modules[] = new Jira();
		$this->modules[] = new Mticket();
	}
	function Get($cvenumber)
	{
		$tickets = [];
		foreach($this->modules as $module)
		{
			$ntickets = $module->Get($cvenumber);
			$tickets = array_merge($tickets,$ntickets);
		}
		return $tickets;
	}
}
?>