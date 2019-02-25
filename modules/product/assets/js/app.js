$(function() 
{
	"use strict";
	console.log("Starting JS");
	console.log(resource);

	var settings = 
	{
		layout:"fitColumns",
		columnVertAlign:"bottom", 
		//height:105, // set height of table (in CSS or here), this enables the Virtual DOM and improves render speed dramatically (can be any valid css height value)
		ajaxURL:resource+"?data=data.php", //ajax URL
		//autoColumns:true,
		ajaxResponse:function(url, params, response)
		{
			console.log(response);
			//url - the URL of the request
			//params - the parameters passed with the request
			//response - the JSON object returned in the body of the response.
			if(response.data === undefined)
				return [];//return the tableData property of a response json object
			return response.data;
		},
		columns:
		[
			{resizable: false,title:"",formatter:"rownum", align:"center", width:"5%", headerSort:false},
			{ title:"Package", 
				columns:
				[
					{resizable: false,title:"Name",field:"name", width:"15%", formatter:"html"},
					{resizable: false, title:"Version", field:"version", width:"20%"},
					{title:"Product", field:"product",width:"20%"}
				]
			},
			{ title:"Vulnerabilities", 
				columns:
				[
					{resizable: false,title:"Open", field:"open", align:"left", sorter:"number", width:"10%"},
					{resizable: false,title:"Idnetified", field:"fix", align:"left", sorter:"number", width:"10%"},
					{resizable: false,title:"Fixed", field:"fixed", align:"left", sorter:"number", width:"10%"},
					{resizable: false,title:"Ignored", field:"ignored", align:"left", sorter:"number", width:"10%"},
				]
			}
		]
	};
	var table = new Tabulator("#table1", settings);
})
