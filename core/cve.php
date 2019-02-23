<?php
class CveCol
{
	private $db = null;
	private $col = null;
	function __construct($db)
	{
		$COLLECTION_NAME = 'cve';
		$this->db = $db;
		$this->col = $db->$COLLECTION_NAME;
	}
	function Drop()
	{
		$this->col->Drop();	
	}
	function Insert($array)
	{
		$this->col->insertMany($array);		
	}
	function CreateTextIndex($field)
	{
		$query =[
			$field=>"text"
		];
		$this->col->createIndex($query);
	}
	function FindText($searchdata,$fields_array) //['_id'=>FALSE,"cve.CVE_data_meta.ID" => true]
	{
		$projection =  ['projection'=>$fields_array];
		$query = ['$text' => ['$search' => $searchdata]];
		$cursor  = $this->col->find($query,$projection);
		return $cursor->toArray();
	}
	function Find($infield, $searchdata_array, $outfields)
	{
		
		
	}
	
}
$cvecol = new CveCol($db);
//$records = $vulcol->GetVulByProduct('MEL');
//var_dump($records);
?>