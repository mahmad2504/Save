<?php
class Jira extends MongoCollection implements ITicket
{
	private $curl=null;
	private $colname='jira';
	function __construct()
	{
		global $db;
		parent::__construct($db,$this->colname);
		$this->curl = curl_init();
		curl_setopt_array($this->curl, array(
			CURLOPT_USERPWD => 'himp:hmip',
			CURLOPT_RETURNTRANSFER => true
		));
	}
	function  Get($cve_number)
	{
		return $this->Find(["summary"=>$cve_number]);
	}
	function GetResource($resource) 
	{
		$curl = $this->curl;
		$url = 'http://jira.alm.mentorg.com:8080/rest/api/latest/'.$resource;
		//echo $url;
		curl_setopt($curl, CURLOPT_URL,$url);
		$result = curl_exec($curl);
		//var_dump($result);
		$ch_error = curl_error($curl); 
		if ($ch_error) 
		{ 
			$msg = $ch_error;
			SendConsole(time(),$msg);
			return null;
		} 
		if (strpos($result, 'Unauthorized') !== false) 
		{
			$msg = "Jira error :: ".$result;
			SendConsole(time(),$msg);
			return null;
		}
		$returnvalue = json_decode($result,true);
		if(isset($returnvalue["issues"]))
		{
			if(count($returnvalue["issues"])==0)
				return null;
		}
		if(isset($returnvalue["errorMessages"]))
		{
			$msg = "Jira error :: ".$returnvalue["errorMessages"][0];
			SendConsole(time(),$msg);
			return null;
		}
		return $returnvalue;
	}
	function Search($query,$maxresults,$fields) 
	{
		$query = str_replace(" ","%20",$query);
		$resource="search?jql=".$query.'&maxResults='.$maxresults.'&fields='.$fields;
		$issues = $this->GetResource($resource);
		$retval = array();
		if(isset($issues['issues']))
		{
			foreach ($issues['issues'] as $entry) 
			{
				$issue = new StdClass();
				$issue->key = $entry['key'];
				//var_dump($entry);
				foreach($entry['fields'] as $field=>$value)
				{
					//var_dump($field);
					//var_dump($value);
					switch($field)
					{
						case 'labels':
							foreach($value as $label)
							{
								$keyvalue = explode(":",$label);
								if(count($keyvalue)>1)
								{
									$prop = strtolower($keyvalue[0]);
									if(!isset($issue->$prop))
										$issue->$prop = array();
									$issue->$prop[] = $keyvalue[1];
								}
							}
							break;
						case 'timespent':
							$issue->timespent = $value/(60*60);
							break;
						case 'summary':
							$issue->summary = $value;
							break;
						case 'status':
							$issue->status = $value['name'];
							break;
						case 'timeoriginalestimate':
							$issue->estimate = $value/(60*60);
							break;
					}
				}
				$retval[] = $issue;
			}
		}
		return $retval;
	}
	function Sync()
	{
		$fields = 'summary,key,status,timeoriginalestimate,timespent,labels';
		
		$lastupdatedon = '';
		$criteria = ["type"=>"updatedon"];
		$record = $this->Find($criteria);
		if(count($record)>0)
			$lastupdatedon = " and updated >=".$record[0]->updatedon->toDateTime()->format('Y-m-d');
		
		$issues = $this->Search('project=VUL'.$lastupdatedon,1000,$fields);
		
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
?>