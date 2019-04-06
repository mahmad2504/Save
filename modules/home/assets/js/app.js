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
					var badge = '<span class="badge badge-dark">'+value+'</span>';
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all">'+badge+'</a>';
					return '';
				}			
			},
			
			{resizable: false,title:"Open", field:"openvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					var badge = '<span class="badge badge-info">'+value+'</span>';
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=open">'+badge+'</a>';
					return '';
				}
			},
			{resizable: false,title:"Identified", field:"fixvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					var badge = '<span class="badge badge-warning">'+value+'</span>';
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=fix">'+badge+'</a>';
					return '';
				}
			},
			{resizable: false,title:"Fixed", field:"fixedvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					var badge = '<span class="badge badge-success">'+value+'</span>';
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=fixed">'+badge+'</a>';
					return '';
				}
			},
			{resizable: false,title:"Ignored", field:"ignoredvulcount", align:"left", sorter:"number", width:"15%",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					var badge = '<span class="badge badge-secondary">'+value+'</span>';
					if(value > 0)
						return '<a href="'+row._row.data.name+'/all?status=ignore">'+badge+'</a>';
					return '';
				}
			}
		]
	};
	var table = new Tabulator("#table1", settings);
	//setTimeout(function(){ table.replaceData(); alert("Hello"); }, 3000);

})
