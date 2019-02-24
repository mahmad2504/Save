<?php
class Nvd extends MongoCollection
{
	private $data_folder;
	private $nvdurls = null;
	private $colname = 'nvd';
	private $name = 'nvd';
	function __construct()
	{
		global $settings;
		global $db;
		parent::__construct($db,$this->colname);
		
		$updatecvedb = false;
		$this->nvdurls = $settings->cve_sources->nvd;
		$this->data_folder = $settings->data_folder;
		foreach($this->nvdurls as $nvdurl)
		{
			$filename = str_replace('.zip','',basename($nvdurl));	
			if(!file_exists($this->data_folder."/".$filename))
			{
				$this->Download($nvdurl);
				$updatecvedb = true;
			}		
		}
		if($updatecvedb)
		{
			SendConsole(time(),"Updating cve database"); 
			$this->UpdateDatabase();
		}
	}
	function Name()
	{
		return $this->name;
	}
	function GetCVEMatches($cves,$packagename,$versionnumber,$aliases)
	{
		$projection = new Projection(['configurations','cve.CVE_data_meta.ID','impact']);
		$cves =  $this->FindIn('cve.CVE_data_meta.ID',$cves,$projection);
		$data_array =  array();
		foreach($cves as $cve)
		{
			$data = new StdClass();
			$data->cve = $cve->cve->CVE_data_meta->ID;
			if(isset($cve->impact->baseMetricV3))
			{
				$data->cvssVersion = 3.0;
				$data->baseScore = $cve->impact->baseMetricV3->cvssV3->baseScore;
				$data->baseSeverity = $cve->impact->baseMetricV3->cvssV3->baseSeverity;
			}
			else
			{
				$data->cvssVersion = 2.0;
				$data->baseScore = $cve->impact->baseMetricV2->cvssV2->baseScore;
				$data->baseSeverity = $cve->impact->baseMetricV2->severity;
			}
			$data->type = $this->DetermineVulType($cve,$packagename,$versionnumber,$aliases);
			//var_dump($data->type);
			//if((strlen($data->type->package_match)>0) &&(strlen($data->type->version_match)>0))
			$data_array[] = $data;
		}
		return $data_array;
	}
	function DetermineVulType($cve,$packagename,$versionnumber,$aliases)
	{
		$obj = new \StdClass();
		$obj->package_match = '';
		$obj->version_match = '';
		//echo $cve->cve->CVE_data_meta->ID." ".$packagename." ".$versionnumber."\n";
		//var_dump($cve->configurations);
		$debug=0;
		//if('CVE-2015-3395' == $cve->cve->CVE_data_meta->ID)
		//	$debug=1;
		for($i=0;$i < count($cve->configurations->nodes);$i++)
		{
			
			$node = $cve->configurations->nodes[$i];
			
			if($node->operator == 'OR')
			{
				$obj = $this->ProcessImpactNode($node->cpe_match,$packagename,$versionnumber,$obj,$aliases);
				if($obj->version_match != null)
					return $obj;
			}
			else if($node->operator == 'AND')
			{
				for($j=0;$j<count($node->children);$j++)
				{
					$obj = $this->ProcessImpactNode($node->children[$j]->cpe_match,$packagename,$versionnumber,$obj,$aliases);
					if($obj->version_match != null)
						return $obj;
				}
			}
		}
		return $obj;
	}
	function version_compare2($a, $b) 
	{ 
		$a = explode(".", rtrim($a, ".0")); //Split version into pieces and remove trailing .0 
		$b = explode(".", rtrim($b, ".0")); //Split version into pieces and remove trailing .0 
		foreach ($a as $depth => $aVal) 
		{ //Iterate over each piece of A 
			if (isset($b[$depth])) 
			{ //If B matches A to this depth, compare the values 
				if ($aVal > $b[$depth]) return 1; //Return A > B 
				else if ($aVal < $b[$depth]) return -1; //Return B > A 
				//An equal result is inconclusive at this point 
			} 
			else 
			{ //If B does not match A to this depth, then A comes after B in sort order 
				return 1; //so return A > B 
			} 
		} 
		//At this point, we know that to the depth that A and B extend to, they are equivalent. 
		//Either the loop ended because A is shorter than B, or both are equal. 
		return (count($a) < count($b)) ? -1 : 0; 
	} 
	function ProcessImpactNode($cpe_match,$package,$version,$obj,$aliases)
	{
		foreach($cpe_match as $cpe)
		{
			//var_dump($cpe);
			if($cpe->vulnerable == true)
			{
				$cpe_array = explode(":",$cpe->cpe23Uri);
				$cpepart = $cpe_array[2];
				$cpevendor = $cpe_array[3];
				$cpeproduct = $cpe_array[4];
				$cpeversion = $cpe_array[5];
				$cpeupdate =  $cpe_array[6];
				
			
				/*var_dump($package);
				var_dump($cpeproduct);
				var_dump($aliases);*/
				
				if(($package == $cpeproduct)||in_array($cpeproduct, $aliases))
				{
					$failed = 0;
					$passed = 0;
					$matched_versions = '';
					//var_dump($cpe);
					if(isset($cpe->versionStartExcluding))
					{
						if($this->version_compare2($version,$cpe->versionStartExcluding)>0)
						{
							$matched_versions = 'versionStartExcluding:'.$cpe->versionStartExcluding;
							$passed++;
						}
						else
							$failed++;
					}
					if(isset($cpe->versionStartIncluding))
					{
						if( ($this->version_compare2($version,$cpe->versionStartIncluding)==0)||
							($this->version_compare2($version,$cpe->versionStartIncluding)>0))
						{
							$matched_versions = 'versionStartIncluding:'.$cpe->versionStartIncluding;
							$passed++;
						}
						else
							$failed++;
					}
					if(isset($cpe->versionEndExcluding))
					{
						if($this->version_compare2($version,$cpe->versionEndExcluding)<0)
						{
							$matched_versions = 'versionEndExcluding:'.$cpe->versionEndExcluding;
							$passed++;
						}
						else
							$failed++;
					}
					if(isset($cpe->versionEndIncluding))
					{
						if( ($this->version_compare2($version,$cpe->versionEndIncluding)==0)||
							($this->version_compare2($version,$cpe->versionEndIncluding)<0))
						{
							$matched_versions = 'versionEndIncluding:'.$cpe->versionEndIncluding;
							$passed++;
						}
						else
							$failed++;
					}
					//echo "===========>".$failed." ".$passed." ".$version."<br>";
					
					if($failed > 0)
					{
						$obj->package_match = $cpeproduct;
						$obj->version_match = '';
						return $obj;
					}
					if($passed > 0)
					{
						$obj->package_match = $cpeproduct;
						$obj->version_match = $matched_versions;
						return $obj;
					}
					if($this->version_compare2($version,$cpeversion)==0)
					{
						$obj->package_match = $cpeproduct;
						$obj->version_match = $cpe->cpe23Uri;
						//$cpe->cpe23Uri
						return $obj;
					}
					$obj->package_match = $cpeproduct;
					$obj->version_match = '';
					//dvultype.package = 'MATCH';
					//dvultype.version = 'NOT_MATCH;
					if($obj->version_match != '')
						return $obj;
				}
			}
		}
		return $obj;
	}
	function UpdateDatabase()
	{
		$this->Drop();
		foreach($this->nvdurls as $nvdurl)
		{
			$filename = str_replace('.zip','',basename($nvdurl));
			$data = $this->PreProcess($this->data_folder."/".$filename);
			$this->Insert($data);	
		}
		SendConsole(time(),"Updating Search Indexes"); 
		$this->CreateTextIndex(["configurations.nodes.cpe_match.cpe23Uri","configurations.nodes.children.cpe_match.cpe23Uri"]);
		$this->CreateIndex(["cve.CVE_data_meta.ID"]);
	}
	function PreProcess($filename)
	{
		$json = json_decode(file_get_contents($filename));
		foreach($json->CVE_Items as $cve)
		{
			$date = new DateTime($cve->publishedDate);
			$date->setTime(0,0,0);
			$ts = $date->getTimestamp();
			$cve->publishedDate = new MongoDB\BSON\UTCDateTime($ts*1000);
		
			$date = new DateTime($cve->lastModifiedDate);
			$date->setTime(0,0,0);
			$ts = $date->getTimestamp();
			$cve->lastModifiedDate = new MongoDB\BSON\UTCDateTime($ts*1000);
			//$cve->publishedDate = new MongoDB\BSON\Timestamp(1, $ts);
			//echo $date->__toString();
			//echo $cve->publishedDate;
			//exit();
		}
		SendConsole(time(),"Updating ".$filename." data in database"); 
		return $json->CVE_Items;	
	}
	function Download($url)
	{
		$zip = new ZipArchive;
		$ch = curl_init(); 
		SendConsole(time(),'Downloading '.basename($url));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_SSLVERSION,3);
		$data = curl_exec ($ch);
		$error = curl_error($ch); 
		curl_close ($ch);

		$destination = $this->data_folder."/tmp.zip";
		$file = fopen($destination, "w");
		fputs($file, $data);
		fclose($file);
		//SendConsole(time(),'Unzipping '); 
		if ($zip->open($destination ) === TRUE) 
		{
			$zip->extractTo($this->data_folder."/");
			$zip->close();
			//SendConsole(time(),'Done '.basename($url) ); 
		} 
		else 
		{
			SendConsole(time(),'Failed '.basename($url) ); 
		}	
	}
	
}