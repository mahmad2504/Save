<?php
class JiraRest
{
	private $curl=null;
	function __construct()
	{
		$this->curl = curl_init();
	}
	function InitCurl($resource,$server,$postdata=null)
	{
		global $settings;
		curl_reset($this->curl);
		if($server == 'default')
			$jconf = current((Array)$settings->jira->conf);
		else
			$jconf = $settings->jira->conf->$server;
		
		$url = $jconf->url.$resource;
		
			//CURLOPT_URL => self::$url . '/rest/structure/2.0/poll?loggedIn=true',
		//CURLOPT_POSTFIELDS => $jdata,
		
		curl_setopt_array($this->curl, array(
			CURLOPT_USERPWD => $jconf->cred->user.':'.$jconf->cred->pass,
			CURLOPT_HTTPHEADER => array('Content-type: application/json'),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL =>  $url
		));
		if($postdata != null)
		{
			curl_setopt($this->curl,CURLOPT_POSTFIELDS,$postdata);
			curl_setopt($this->curl,CURLOPT_POST,1);
		}
	}
	function GetResource($resource,$server,$postdata=null) 
	{
		$this->InitCurl($resource,$server,$postdata);
		$curl = $this->curl;
		$result = curl_exec($curl);
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
		
		return $result;
		
	}
	function GETStructureInfo($structid,$server='default') 
	{
		$resource="/rest/structure/2.0/structure/".$structid;
		$result = $this->GetResource($resource,$server);

		$xml=null;
		libxml_use_internal_errors(true);
		$xml = simplexml_load_string($result);
		
		if (false === $xml) 
		{
			$msg = "Jira structure does not exist";
			SendConsole(time(),$msg);
			$xml = null;
		}
		return $xml;
	}
	function GetStructure($structid,$server='default') 
	{
		$jdata = '{"forests":[{"spec":{"type":"clipboard"},"version":{"signature":898732744,"version":0}},{"spec":{"structureId":'.$structid.',"title":true},"version":{"signature":0,"version":0}}],"items":{"version":{"signature":-157412296,"version":43401}}}';
		$resource="/rest/structure/2.0/poll?loggedIn=true";
		$result = $this->GetResource($resource,$server,$jdata);
		$json = json_decode($result);
		if(isset($json->forestUpdates[1]->error))
		{
			$msg = "Jira structure does not exist";
			SendConsole(time(),$msg);
			return [];
		}
		$formula_array = explode(",",$json->forestUpdates[1]->formula);
		$objects = array();
		foreach($formula_array as $formula)
		{
			$detail = explode(":",$formula);
			$obj = new StdClass();
			
			$obj->rwoid = $detail[0];
			$obj->level = $detail[1];
			$obj->taskid = $detail[2];
			if(strpos($detail[2], "/")>0)
			{}
			else
			{
				$objects[] = $obj;
			}
		}
		return $objects;
	}	
	function Search($query,$maxresults,$fields,$server='default') 
	{
		$query = str_replace(" ","%20",$query);
		$resource="/rest/api/latest/search?jql=".$query.'&maxResults='.$maxresults.'&fields='.$fields;
		//echo $resource;
		$result = $this->GetResource($resource,$server);
		
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
		$issues = $returnvalue ;
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
						default:
							SendConsole(time(),'Jira field '.$field." not handled");
							break;
					}
				}
				$retval[] = $issue;
			}
		}
		return $retval;
	}
}
?>