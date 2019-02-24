<?php
require "mongodb/vendor/autoload.php";
class Projection
{
	private $projection;
	function __construct($outfields=array(),$noid=true)
	{
		$projection_fields = array();
		if($noid)
			$projection_fields['_id'] = false;
		
		foreach($outfields as $field)
			$projection_fields[$field] = true;
		$this->projection =  ['projection'=>$projection_fields];
	}
	function Get()
	{
		return $this->projection;
	}	
}
class MongoCollection
{
	private $col = null;
	private $mongoclient = null;
	function __construct($mongoclient,$colname)
	{
		$this->col = $mongoclient->SelectCollection($colname);
		$this->mongoclient = $mongoclient;
		
	}
	function Delete($criteria)
	{
		$this->col->deleteOne($criteria);	
	}
	function GetHandle()
	{
		return $this->col;	
	}
	function ChangeCol($colname)
	{
		$this->col = $this->mongoclient->SelectCollection($colname);
	}
	function Drop()
	{
		$this->col->Drop();	
	}
	function Insert($array) //(["a"=>"1","b"=>"2"],["a"=>"2","b"=>"4"]]);
	{
		$this->col->insertMany($array);		
	}
	function CreateIndex($array) //(['field1','field2']);
	{
		$indexes = array();
		foreach($array as $field)
			$indexes[$field] = 1;

		//var_dump($indexes);
		$this->col->createIndex($indexes);
	}
	function CreateTextIndex($array) //(['field1','field2']);
	{
		$indexes = array();
		foreach($array as $field)
			$indexes[$field] = 'text';

		//var_dump($indexes);
		$this->col->createIndex($indexes);
	}
	function SearchText($searchdata,$projection=null) //('mumtaz', Object Projection)
	{
		$query = ['$text' => ['$search' => $searchdata]];
		if($projection == null)
			$cursor  = $this->col->find($query);
		else
			$cursor  = $this->col->find($query,$projection->Get());
		return $cursor->toArray();
	}
	function Find($infields,$projection=null,$operator='and') //( ['name'=>'Mumtaz','age'=>'45'],Object Projection, 'and/or' )
	{
		foreach($infields as $key=>$value)
		{
			$query = array(); 
			$query[$key] = $value;
			$queries[] = $query;	
		}
		$query = array();
		$query['$'.$operator]= $queries;

		if($projection == null)
			$cursor  = $this->col->find($query);
		else
			$cursor  = $this->col->find($query,$projection->Get());
		return $cursor->toArray();
	}
	function FindIn($field,$invalues,$projection=null) //('name',['mumtaz','fouzia'],Object Projection)
	{
		$query = [$field => ['$in' => $invalues]];
		if($projection == null)
			$cursor  = $this->col->find($query);
		else
			$cursor  = $this->col->find($query,$projection->Get());
		return $cursor->toArray();
	}
	function FindRegEx($field,$expression,$projection=null,$caseinsensitive=true)
	{
		if($caseinsensitive)
			$query = [ $field => [ '$regex' => $expression, '$options' => 'i' ] ];
		else
			$query = [ $field => [ '$regex' => $expression ] ];
		if($projection == null)
			$cursor  = $this->col->find($query);
		else
			$cursor  = $this->col->find($query,$projection->Get());
		return $cursor->toArray();
	}
	/*function Update($search,$property,$value)
	{
		$updatequery = ['$set' => [$property => $value]];
		$doc = $this->col->updateOne($search,$updatequery );
	}*/

	function Update($search,$fields)
	{
		return $this->col->updateOne($search,['$set' => $fields]);
	}
	function CountDocs()
	{
		return $this->col->count();
	}
	
}
class MongoClient
{
	private $client;
	private $db;
	function __construct($server=null,$dbname=null)
	{
		global $settings;
		
		if($server == null)
			$server = $settings->mongo->server;
		if($dbname == null)
			$dbname = $settings->mongo->db;
		
		$this->client = new MongoDB\Client($server);
		$this->db = $this->client->$dbname;
	}
	function SelectCollection($colname)
	{
		return $this->db->$colname;
	}
	

}
function StringDateToMongoDate($datestring) // Resets hours/minutes/seconds to zero
{
	$date = new DateTime($datestring);
	$date->setTime(0,0,0);
	$ts = $date->getTimestamp();
	return new MongoDB\BSON\UTCDateTime($ts*1000);
}
function ConvertId($id)
{
	return new MongoDB\BSON\ObjectId($id);
}	
$db = new MongoClient();


/*
$test =  new MongoClient($server,'test');
$usercol = new MongoCollection($test,'user');
$usercol->Drop();
$data = [ ["name"=>"mumtaz","age"=>45],["name"=>"fouzia","age"=>43] ];

$usercol->Insert($data);
$usercol->CreateTextIndex(['name','age']);

$records = $usercol->SearchText('mumtaz',new Projection(['name']));
var_dump($records);
$records = $usercol->Find(['name'=>'mumtasz','age'=>43],new Projection(['name']),'or');
var_dump($records);
$records = $usercol->FindIn('name',['mumtaz','fouzia'],new Projection(['name']));
var_dump($records);*/
?>