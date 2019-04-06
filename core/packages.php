<?php

require "modules/vendor/PhpSpreadsheet/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Style;

class Packages extends MongoCollection
{
	private $data_folder;
	private $packages;
	private $colname = 'packages';
	function __construct()
	{
		global $settings;
		global $db;
		parent::__construct($db,$this->colname);
		
		$this->data_folder = $settings->data_folder;	
		$datafiles = $this->GetMenifestFiles($settings->menifests->folder);
		$this->packages = $this->ReadProductData($settings->menifests->folder,$datafiles);
		$this->ReadCpeMapping($settings->cpemap->file);
	}
	function GetMenifestFiles($folder)
	{
		global $settings;
		$files  = ReadFiles($folder,'.xlsx');
		return $files ;
	}
	function UpdateDb()
	{
		$this->Drop();
		$this->Insert($this->packages);
		SendConsole(time(),"Updating packages");
	}
	function Get()
	{
		return $this->packages ;
	}
	
	function ReadProductData($folder,$files)
	{
		$packages = array();
		foreach($files as $filename)
		{
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
			$spreadsheet = $reader->load($folder."/".$filename);
			$sheetData = $spreadsheet->getActiveSheet()->toArray();
			SendConsole(time(),"Importing ".$folder."/".$filename);
			$product = new StdClass();
			$product->menifest = $filename;
			
			$product->name = multiexplode(["_",".xlsx"],$filename);
			
			$product->name = strtolower($product->name[1]);
		
			for($i=1;$i<count($sheetData);$i++)
			{
				$row = $sheetData[$i];
				$package = new StdClass();
				$pname = $row[0];
				$pversion = $row[1];
				if(isset($packages[$pname]))
				{
					$package = $packages[$pname];
					if(isset($package->versions[$pversion]))
					{
						$version = $package->versions[$pversion];
						if(!isset($version->products[$product->menifest]))
						{
							$nproduct = new StdClass();
							$nproduct->name = $product->name;
							$nproduct->menifest = $product->menifest;
							$version->products[$product->menifest] = $nproduct;
						}					
					}
					else
					{
						$version = new StdClass();
						$version->number = $pversion;
						$version->products = array();
						$package->versions[$pversion] = $version;
						if(!isset($version->products[$product->menifest]))
						{
							$nproduct = new StdClass();
							$nproduct->name = $product->name;
							$nproduct->menifest = $product->menifest;
							$version->products[$product->menifest] = $nproduct;
						}
						//$version->products[$product->name] = $product->name;
					}
				}
				else
				{
					$package = new StdClass();
					$package->name = $pname;
					$package->versions = array();
					
					$version = new StdClass();
					$version->number = $pversion;
					$version->products = array();
					
					$nproduct = new StdClass();
					$nproduct->name = $product->name;
					$nproduct->menifest = $product->menifest;
					
					$version->products[$nproduct->menifest] = $nproduct;
					
					$package->versions[$pversion] = $version;
					$packages[$pname] = $package;
				}
				
			}
		}
		foreach($packages as $package)
		{
			foreach($package->versions as $version)
				$version->products = array_values($version->products);
			$package->versions = array_values($package->versions);
		}
		
		return $packages;
	}
	function ReadCpeMapping($filename)
	{
		$packages = $this->packages;
		SendConsole(time(),"Importing ".$filename); 
			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		$spreadsheet = $reader->load($filename);
			$sheetData = $spreadsheet->getActiveSheet()->toArray();
			for($i=1;$i<count($sheetData);$i++)
			{
				$row = $sheetData[$i];
				$package = $row[0];
				//echo $package."<br>";
				if(isset($packages[$package]))
				{
					//echo "Found<br>";
					$packages[$package]->aliases = array();
					//var_dump($packages[$package]);
					for($j=1;$j<count($row);$j++)
					{
						//echo $row[$j]."<br>";
						if(strlen(trim($row[$j]))>0)
							$packages[$package]->aliases[] = $row[$j];
					}
					
				}
			}
		$this->packages = array_values($packages);
		return $this->packages;
	}
}
?>