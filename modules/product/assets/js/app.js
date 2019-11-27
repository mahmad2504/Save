$(function() 
{
	"use strict";
	console.log("Starting JS");
	$('#download').click(function(){
		table.download("csv", "data.csv");
	});
	var settings = 
	{
		layout:"fitColumns",
		columnVertAlign:"bottom", 
		pagination:"local",
		paginationSize:16,
		//height:105, // set height of table (in CSS or here), this enables the Virtual DOM and improves render speed dramatically (can be any valid css height value)
		ajaxURL:"?data=data.php", //ajax URL
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
			{resizable: false,title:"",formatter:"rownum", align:"center", width:"3%", headerSort:false},
			{ title:"Package", 
				columns:
				[
					{resizable: false,title:"Name",field:"name", width:"15%", formatter:"html",
						formatter:function(cell, formatterParams)
						{
							var value = cell.getValue();
							var row = cell.getRow();
							return '<a href="'+row._row.data.product+"/"+row._row.data.name+'">'+value+'</a>';
					
							return value;
						}
					},
					{resizable: false, title:"Version", field:"version", width:"20%",
						formatter:function(cell, formatterParams)
						{
							var value = cell.getValue();
							var row = cell.getRow();
							return '<a href="'+row._row.data.product+"/"+row._row.data.name+"/"+row._row.data.version+'">'+value+'</a>';
							return value;
						}
					},
					{title:"Product", field:"product",width:"20%"}
				]
			},
			{ title:"Vulnerabilities", 
				columns:
				[
					{resizable: false,title:"Open", field:"open", align:"left", sorter:"number", width:"10%",
						formatter:function(cell, formatterParams)
						{
							var value = cell.getValue();
							var row = cell.getRow();
							var style = '';
							if(row._row.data.openconflict > 0)
								style="outline: none;border-color: #ff0000;box-shadow: 0 0 10px #98FB98;"
					
							var badge = '<span style="'+style+'" class="badge badge-info">'+value+'</span>';
							if(value > 0)
								return '<a  href="'+row._row.data.product+"/"+row._row.data.name+"/"+row._row.data.version+'?status=open">'+badge+'</a>';
							return '';
						}
					},
					{resizable: false,title:"Identified", field:"fix", align:"left", sorter:"number", width:"10%",
						formatter:function(cell, formatterParams)
						{
							var value = cell.getValue();
							var row = cell.getRow();
							var style = '';
							if(row._row.data.fixconflict > 0)
								style="outline: none;border-color: #ff0000;box-shadow: 0 0 10px #0000ff;"
					
							var badge = '<span style="'+style+'" class="font-weight-bold badge badge-warning">'+value+'</span>';
							if(value > 0)
								return '<a href="'+row._row.data.product+"/"+row._row.data.name+"/"+row._row.data.version+'?status=fix">'+badge+'</a>';
							return '';
						}
					},
					{resizable: false,title:"Fixed", field:"fixed", align:"left", sorter:"number", width:"10%",
						formatter:function(cell, formatterParams)
						{
							var value = cell.getValue();
							var row = cell.getRow();
							var style = '';
							if(row._row.data.fixedconflict > 0)
								style="outline: none;border-color: #ff0000;box-shadow: 0 0 10px #ff0000;"
					
							var badge = '<span style="'+style+'" class="badge badge-success">'+value+'</span>';
							if(value > 0)
								return '<a  href="'+row._row.data.product+"/"+row._row.data.name+"/"+row._row.data.version+'?status=fixed">'+badge+'</a>';
							return '';
						}
					},
					{resizable: false,title:"Ignored", field:"ignored", align:"left", sorter:"number", width:"10%",
						formatter:function(cell, formatterParams)
						{
							var value = cell.getValue();
							var row = cell.getRow();
							var style = '';
							if(row._row.data.ignoredconflict > 0)
								style="outline: none;border-color: #ff0000;box-shadow: 0 0 10px #ff0000;"
					
							var badge = '<span style="'+style+'" class="badge badge-secondary">'+value+'</span>';
							if(value > 0)
								return '<a  href="'+row._row.data.product+"/"+row._row.data.name+"/"+row._row.data.version+'?status=ignore">'+badge+'</a>';
							return '';
						}
					},
				]
			}
		]
	};
	var table = new Tabulator("#table1", settings);
})
