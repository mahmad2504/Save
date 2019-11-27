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
			//var_dump($data->cve); 
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
		//SendConsole(time(),$cve->cve->CVE_data_meta->ID); 
		//echo $cve->cve->CVE_data_meta->ID." ".$packagename." ".$versionnumber."<br>";
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
				//var_dump($node);
				if(isset($node->cpe_match))
				{
					$obj = $this->ProcessImpactNode($node->cpe_match,$packagename,$versionnumber,$obj,$aliases);
					if($obj->version_match != null)
						return $obj;
				}
				else
				{
				for($j=0;$j<count($node->children);$j++)
				{
					$obj = $this->ProcessImpactNode($node->children[$j]->cpe_match,$packagename,$versionnumber,$obj,$aliases);
					if($obj->version_match != null)
						return $obj;
				}
			}
		}
		}
		return $obj;
	}
	function version_compare2($a, $b) 
	{ 
	$msg = "Comparing ".$a." with ".$b;
	//SendConsole(time(),$msg); 
		//$a = explode(".", str_replace(".0",'',$a)); //Split version into pieces and remove trailing .0 
		//$b = explode(".", str_replace(".0",'',$b)); //Split version into pieces and remove trailing .0 
	//echo rtrim($b, ".0");
	
	$adash = explode(".", $a); //Split version into pieces and remove trailing .0 
	if(end($adash)==0)
		$a = explode(".", rtrim($a, ".0")); //Split version into pieces and remove trailing .0 
    else
		$a =$adash;
	
	$bdash = explode(".", $b);
	if(end($bdash)==0)
		$b = explode(".", rtrim($b, ".0")); //Split version into pieces and remove trailing .0 
	else
		$b = $bdash;
	
	//SendConsole(time(),print_r($a)."--".print_r($b)); 
	
		foreach ($a as $depth => $aVal) 
		{ //Iterate over each piece of A 
			$aVal = trim($aVal);
			if (isset($b[$depth])) 
			{ //If B matches A to this depth, compare the values 
				$b[$depth] = trim($b[$depth]);
				if ($aVal > $b[$depth]) 
				{
					//echo "[".$aVal."]".">"."[".$b[$depth]."]\r\n";
					//echo gettype($aVal).">".gettype($b[$depth])."\r\n";
					//echo 'A > B \r\n';
					return 1; //Return A > B 
				}
				else if ($aVal < $b[$depth]) 
				{
					//echo 'A < B \r\n';
					return -1; //Return B > A 
				}
				//An equal result is inconclusive at this point 
			} 
			else 
			{ //If B does not match A to this depth, then A comes after B in sort order 
	
				//echo 'A >> B \r\n';
				return 1; //so return A > B 
			} 
		} 
		//At this point, we know that to the depth that A and B extend to, they are equivalent. 
		//Either the loop ended because A is shorter than B, or both are equal. 
		$retval = (count($a) < count($b)) ? -1 : 0; 
		/*if($retval == -1)
		{
			echo 'A < B \r\n';
		}
		else
		{
			echo 'A = B \r\n';
		}*/
		return $retval ;
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
					$rangecheckpresent = 0;
					//var_dump($cpe);
					if(isset($cpe->versionStartExcluding))
					{
						$rangecheckpresent = 1;
						if($this->version_compare2($version,$cpe->versionStartExcluding)>0)
						{
							$matched_versions = 'versionStartExcluding:'.$cpe->versionStartExcluding;
							//echo "First\r\n";
							$passed++;
						}
						else
							$failed++;
					}
					if(isset($cpe->versionStartIncluding))
					{
						$rangecheckpresent = 1;
						if( ($this->version_compare2($version,$cpe->versionStartIncluding)==0)||
							($this->version_compare2($version,$cpe->versionStartIncluding)>0))
						{
							$matched_versions = 'versionStartIncluding:'.$cpe->versionStartIncluding;
							//echo "Second\r\n";
							$passed++;
						}
						else
							$failed++;
					}
					if(isset($cpe->versionEndExcluding))
					{
						$rangecheckpresent = 1;
						//echo "-".$version."-".$cpe->versionEndExcluding."-<br>";
						//echo $this->version_compare2($version,$cpe->versionEndExcluding)."<br>";
						//echo version_compare($version,$cpe->versionEndExcluding)."<br>";
						
						if($this->version_compare2($version,$cpe->versionEndExcluding)<0)
						{
							$matched_versions = 'versionEndExcluding:'.$cpe->versionEndExcluding;
							//echo "Third\r\n";
							$passed++;
						}
						else
							$failed++;
					}
					if(isset($cpe->versionEndIncluding))
					{
						
						//SendConsole(time(),$version."--".$cpe->versionEndIncluding); 
						
						$rangecheckpresent = 1;
						if( ($this->version_compare2($version,$cpe->versionEndIncluding)==0)||
							($this->version_compare2($version,$cpe->versionEndIncluding)<0))
						{
							$matched_versions = 'versionEndIncluding:'.$cpe->versionEndIncluding;
							$passed++;
						}
						else
						{
							//echo " Failed ".$this->version_compare2($version,$cpe->versionEndIncluding)."\r\n";
							$failed++;
					}
					}
					//echo "===========>".$failed." ".$passed." ".$version."<br>";
					
					if($failed > 0)
					{
						$obj->package_match = $cpeproduct;
						$obj->version_match = '';
						//return $obj;
					}
					else if($passed > 0)
					{
						$obj->package_match = $cpeproduct;
						$obj->version_match = $matched_versions;
						return $obj;
					}
					if($rangecheckpresent == 0)
					{
					if($this->version_compare2($version,$cpeversion)==0)
					{
						$obj->package_match = $cpeproduct;
						$obj->version_match = $cpe->cpe23Uri;
						//$cpe->cpe23Uri
						return $obj;
					}
						if($cpeversion == '*')
						{
							
					$obj->package_match = $cpeproduct;
							$obj->version_match = '*';
							//$cpe->cpe23Uri
							return $obj;
						}
						if($cpeversion == '-')
						{
							
							$obj->package_match = $cpeproduct;
							$obj->version_match = '-';
							//$cpe->cpe23Uri
							//return $obj;
						}
					}
					//$obj->package_match = $cpeproduct;
					//$obj->version_match = '';
					//dvultype.package = 'MATCH';
					//dvultype.version = 'NOT_MATCH;
					//if($obj->version_match != '')
					//	return $obj;
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