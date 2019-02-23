<?php
/*
Copyright 2017-2018 Mumtaz Ahmad, ahmad-mumtaz1@hotmail.com
This file is part of Agile Gantt Chart, an opensource project management tool.
AGC is free software: you can redistribute it and/or modify
it under the terms of the The Non-Profit Open Software License version 3.0 (NPOSL-3.0) as published by
https://opensource.org/licenses/NPOSL-3.0
*/
require "modules/vendor/Tmilos/vendor/autoload.php";

use Tmilos\GoogleCharts\DataTable\Column;
use Tmilos\GoogleCharts\DataTable\ColumnType;
use Tmilos\GoogleCharts\DataTable\DataTable;
use Tmilos\GoogleCharts\DataTable\Row;
use Tmilos\Value\AbstractEnum;

$dataTable = new DataTable([
		Column::create(ColumnType::STRING())->setLabel('ID'),
		Column::create(ColumnType::STRING())->setLabel('Description')
	]);

$rowdata =  array();
for($i=0;$i<10;$i++)
{
	$row =  array();
	$row[] = $i;
	$row[] = 'Description';
	$rowdata[] = $row;	
}
$dataTable->addRows($rowdata);
SendResponse($dataTable);
?>