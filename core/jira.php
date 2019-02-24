<?php
class Jira extends MongoCollection implements ITicket
{
	private $curl=null;
	private $colname='jira';
	private $jirarest = null;
	function __construct()
	{
		global $db;
		parent::__construct($db,$this->colname);
		$this->jirarest = CreateJiraRest();
	}
	function InitCurl($user,$pass)
	{
		curl_reset($this->curl);
		curl_setopt_array($this->curl, array(
		CURLOPT_USERPWD => $user.':'.$pass,
			CURLOPT_RETURNTRANSFER => true
		));
	}
	function  Get($cve_number)
	{
		return $this->Find(["summary"=>$cve_number]);
	}
	
	function Sync()
	{
		$fields = 'summary,key,status,timeoriginalestimate,timespent,labels';
		
		$lastupdatedon = '';
		$criteria = ["type"=>"updatedon"];
		$record = $this->Find($criteria);
		if(count($record)>0)
			$lastupdatedon = " and updated >=".$record[0]->updatedon->toDateTime()->format('Y-m-d');
		
		$issues = $this->jirarest->Search('project=VUL'.$lastupdatedon,1000,$fields);
		//var_dump($issues);
		$collection = $this->GetHandle();
		$updatedon =  StringDateToMongoDate(Date('Y-m-d'));
		$criteria = ["type"=>"updatedon"];
        $newdata =['$set'=>["updatedon"=>$updatedon]];
        $options = ["upsert"=>true,"multiple"=>true];
 
        $ret = $collection->updateOne(
            $criteria,
            $newdata,
            $options
        );
		foreach($issues as $issue)
		{
			SendConsole(time(),"Syned ".$issue->key);
			$issue->status = $this->MapStatus($issue->status);
			$issue->progress = 0;
			if($issue->estimate > 0)
			{
				if($issue->status  == 'done')
					$issue->progress = 100;
				else
					$issue->progress = $issue->timespent/$issue->estimate * 100;
				if($issue->progress > 100)
					$issue->progress = 100;
			}
			$issue->source = 'jira';
			if(!isset($issue->product))
				$issue->product = [];
			//var_dump($issue);
			$criteria = ["key"=>$issue->key];
			$newdata = ['$set' => json_decode(json_encode($issue))];
			$options = ["upsert"=>true];
			$ret = $collection->updateOne(
            $criteria,
            $newdata,
            $options
			);
		}
		//var_dump($issues);
	}
	function MapStatus($status)
	{
		$status = strtolower($status);
		if(($status == 'todo')||($status == 'open')||($status == "in analysis"))
			return 'open';
		if(($status == 'in progress')||($status == 'in review'))
			return 'in progress';
		if(($status == 'resolved')||($status == 'closed')||($status == 'done'))
			return 'done';
	}
}
/*
$fields = 'summary,key,status,timeoriginalestimate,timespent,labels';
$jira = CreateJiraRest();
$issues = $jira->Search('project=VUL',1000,$fields,'mentor');
$jira->GETStructureInfo(749);
$jira->GetStructure(749);*/

?>