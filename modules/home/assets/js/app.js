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
			{resizable: false,title:"Name",field:"name", width:"20%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					return '<a href="'+row._row.data.name+'">'+value+'</a>';
				}			
			}, 
			{resizable: false,title:"Packages",field:"packagescount", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all">'+value+'</a>';
					return value;
				}			
			},
			
			{resizable: false,title:"Open", field:"openvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=open">'+value+'</a>';
					return value;
				}
			},
			{resizable: false,title:"Idnetified", field:"fixvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=fix">'+value+'</a>';
					return value;
				}
			},
			{resizable: false,title:"Fixed", field:"fixedvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=fixed">'+value+'</a>';
					return value;
				}
			},
			{resizable: false,title:"Ignored", field:"ignoredvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=ignore">'+value+'</a>';
					return value;
				}
			}
		]
	};
	var table = new Tabulator("#table1", settings);
	//setTimeout(function(){ table.replaceData(); alert("Hello"); }, 3000);

})
