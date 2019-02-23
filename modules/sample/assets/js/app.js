$(function() 
{
	"use strict";
	console.log("Starting JS");
	google.charts.load('current', {
	callback: drawChart,
	packages: ['table']
	});	
})

function drawChart() 
{
	//var params = { <?php $api->PopulateParams() ?> };
	console.log("Google Charts Loaded");
	data = { "params":"1",
	"param":"2"};
    GetResource(0,null,'data=data',params,data,HandleResponse);
}
function HandleResponse(jsonData)
{
	if(jsonData.status !="success")
	{
		console.log("Fail");
		return;
	}
	
	jsonData = jsonData.data;
	datatable = new google.visualization.DataTable(jsonData);
	var view = new google.visualization.DataView(datatable);
	for(var i=0;i<jsonData.rows.length;i++)
	{
		var id = jsonData.rows[i].c[0].v;
		datatable.setProperty(i, 0, 'style', 'color:red;');
	}
	view.setColumns([0,1]);

	var options = {
		showRowNumber: true,
		width: '100%', 
		height: '100%',
		sortAscending: false,
		allowHtml:true,
	};
	// Instantiate and draw our chart, passing in some options.
	var chart = new google.visualization.Table(document.getElementById('chart_div'));
	//chart.draw(datatable, options);
	chart.draw(view, options);
}
