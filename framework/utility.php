<?php
function ArrayToObj($array,$keyvalue=true)
{
	$object = new stdClass();
	foreach ($array as $key => $value)
	{
		if($keyvalue)
			$object->$key = $value;
		else
			$object->$value = $value;
	}
	return $object;
}

?>