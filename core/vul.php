<?php
define('VUL_COLLECTION_NAME','vuls');
class Vuls extends MongoCollection
{
	private $colname = 'vuls';
	private $statuscolname = 'vulstatus';
	private $updatetime = null;
	private $count=0;
	private $signature;
	function __construct()
	{
		global $db;
		$this->updatetime = time();
		parent::__construct($db,$this->colname);
		$this->count = $this->CountDocs();
		$criteria = ["type"=>"updatedon"];
		$records = $this->Find($criteria);
		$this->signature = $records[0]->updatedon;
		//var_dump($records);
	}
	function GetUpdateTime()
	{
		return $this->signature;
	}
	function GetVulByProduct($product)
	{
		return $this->FindRegEx('product.name',$product);
	}
	function UpdateDb($packages,$cvecol)
	{
		//$this->Drop();
		
		$source = $cvecol->Name();
		SendConsole(time(),"Updating vulnerabilities");
		foreach($packages as $package)
		{
			foreach($package->versions as $version)
			{
				foreach($version->products as $product)
				{
					foreach($package->cves as $cve)
					{
						$query_entries[] = $cve->cve->CVE_data_meta->ID; 
					}
					if(!isset($package->aliases))
						$package->aliases = array();
					
					$cves = $cvecol->GetCVEMatches($query_entries,$package->name,$version->number,$package->aliases);
					//var_dump($cves);
					$this->CreateVul($cves,$product,$package->name,$version->number,$source);
				}
			}
		}
		$this->CreateTextIndex(["product.name","package"]);
	}
	function CreateVul($cves,$product,$packagename,$versionnumber,$source)
	{
		global $vulcollection;
		global $cvdcoll;

		foreach($cves as $cve)
		{
			$vul = new StdClass();
			//$vul->cve = $cve->cve->CVE_data_meta->ID;
			$vul->cve = $cve->cve;
			$vul->cvssVersion = $cve->cvssVersion;
			$vul->baseScore = $cve->baseScore;
			$vul->baseSeverity = $cve->baseSeverity;
			$vul->source = $source;
			$vul->product = $product;
			$vul->package = $packagename;
			$vul->version = $versionnumber;
			
			//$vul->type = $this->DetermineVulType($cve,$packagename,$versionnumber);
			$vul->type = $cve->type;
		
			//echo $vul->type->version_match;
			if((strlen($vul->type->package_match)>0)&&(strlen($vul->type->version_match)>0))
			{
				$this->UpdateVulInDb($vul);
			}
		}
		$this->UpdateUpdatedOn();
		
	}
	function UpdateUpdatedOn()
	{
		$collection = $this->GetHandle();
		$updatedon =  $this->updatetime;
		$criteria = ["type"=>"updatedon"];
        $newdata =['$set'=>["updatedon"=>$updatedon]];
        $options = ["upsert"=>true,"multiple"=>true];
 
        $ret = $collection->updateOne(
            $criteria,
            $newdata,
            $options
        );
	}
	function UpdateVulInDb($vul)
	{
		global $vulstatuscoll;
		$menifest = $vul->product->menifest;
		$cve = $vul->cve;
		$package = $vul->package;
		$version = $vul->version;
		
		//$this->ChangeCol($this->statuscolname);
	
		$infields = ['$and' => [
						['cve' => $cve],
						['product.menifest' => $menifest],
						['package' => $package],
						['version' => $version]
						]];
		$tickets = $this->Find($infields);
		
		if(count($tickets)==0)
		{
			$vul->updated = $this->updatetime;
			$vul->status = 'OPEN';
			$this->Insert([$vul]);
			$this->count++;
		}
		else
		{
			$this->Update($infields, ['updated' => $this->updatetime]);
		}
	}
}


//$records = $vulcol->GetVulByProduct('MEL');
//var_dump($records);
?>