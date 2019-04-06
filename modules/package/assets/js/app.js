$(function() 
{
	"use strict";
	var fixopenfiltervalue = ["FIX","OPEN"];
	console.log("Starting Package Js");

	$('#download').click(function(){
		table.download("csv", "data.csv");
	});
	$('#checkbox_viewall').click(function(){
		table.setFilter(customFilter, {});
	});
	$(document).on('click','.otherfixed',function(event){
		mscConfirm("Delete?",function(){
			alert("Post deleted");
		});
	});

	$(document).on('click','.delete',function(event){
		//console.log($(this).data('id'));
		//console.log(event.target);
		params.id = $(this).data('id');
		//console.log(cur_row);
		mscConfirm("Delete?",function(){
			//console.log(params);
			GetResource(0,resource,'data=deletevul',params,null,successcb) ;
			cur_row.delete();
		});	
	});

	var url= [] ;
	url['jira'] = 'http://jira.alm.mentorg.com:8080/browse';
	url['nvd'] = 'https://nvd.nist.gov/vuln/detail';
	
	var cur_row = null;
	var settings = 
	{
		layout:"fitDataFill",
		columnVertAlign:"bottom", 
		pagination:"local",
		paginationSize:16,
		tooltips:true,
		//height:105, // set height of table (in CSS or here), this enables the Virtual DOM and improves render speed dramatically (can be any valid css height value)
		ajaxURL:resource+"?data=data.php", //ajax URL
		//autoColumns:true,
		ajaxResponse:function(url, params, response)
		{
			//console.log(response);
			//url - the URL of the request
			//params - the parameters passed with the request
			//response - the JSON object returned in the body of the response.
			if(response.data === undefined)
				return [];//return the tableData property of a response json object
			//console.log(response.data);
			return response.data;
		},
		columns:
		[
			{resizable: false, title:"P", field:"valid",mutator:validMutator,
				cellClick:function(e, cell)
				{
					cur_row = cell.getRow();
				},
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if(value != '')
					{
						var id=row._row.data.id;
						return '<i data-id="'+id+'" class="delete fa fa-exclamation-triangle" style="color:red"></i>';
					}
					return '';
				}
			},	
			{resizable: false,title:"CVE",field:"cve",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					var urlt = url[row._row.data.source]+"/"+value;
					return "<a href='"+urlt+"'>"+value+"</a>";
				}
			
			},
			{resizable: false, title:"Package", field:"package"},
			{resizable: false, title:"Version", field:"version"},	
			{resizable: false, title:"Product", field:"product"},
			{resizable: false, title:"Weight", field:"weight",sorter:"number", visible:false},
			
			{resizable: false, title:"CVSS", field:"baseScore"},
			{resizable: false, title:"Severity", field:"baseSeverity"},
			
			{resizable: false, title:"Match", field:"version_match",
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					if(value.length > 0)
					{
						if((value=='-')||(value=='*'))
							return 'PARTIAL';
						return 'EXACT';
					}
					
					return value;
				}
			},
			{tooltip:false, resizable: false, title:"Tickets", field:"vultickets",mutator:ticketMutator,formatter:'html'},
			{resizable: false, title:"Progress", field:"progress", mutator:progressMutator,formatter:"progress",
				formatterParams:{
					min:0,
					max:100,
					color:function(value){ if (value < 100) return "#98FB98"; return "#A9A9A9";},
					legendColor:"#000000",
					legendAlign:"center",
					legend:function(value){if (value == 0) return '';return  '<span style="font-size:8px;">'+value+'%</span>'},
				}
			},
			{tooltip:false, resizable: false, title:"Other Fixes", field:"othertickets",mutator:ticketMutator,formatter:'html'},
			
			{resizable: false, title:"Status", field:"status",editor:"select",
				editorParams:paramLookup,
				cellClick:function(e, cell)
				{
					cur_row = cell.getRow();
				},
				/*formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					var row = cell.getRow();
					if((row._row.data.vultickets.length>0)&&(value == 'OPEN'))
						return 'FIX';
					else
					return value;
				}*/
			},
			{resizable: false, title:"Comments", field:"comment", width:"20%", editor:"input",
				cellClick:function(e, cell)
				{
					cur_row = cell.getRow();
				},
				formatter:function(cell, formatterParams)
				{
					var value = cell.getValue();
					return value;
				}
			},
			{resizable: false, title:"Pub", field:"publish", width:"5%", align:"center", editor:true, formatter:"tickCross",
				editable:function(cell)
				{
					//cell - the cell component for the editable cell
					//get row data
					var data = cell.getRow().getData();
					return ((data.status == 'FIX')||(data.status == 'FIXED')); // only allow the name cell to be edited if the age is over 18
				},
				cellClick:function(e, cell)
				{
					cur_row = cell.getRow();
				},
				
			},
		],
		rowClick:function(e, row){
			//e - the click event object
			//row - row component
			cur_row = row;
		
		},
		rowSelected:function(row){
			//row - row component for the selected row
			//cosole.log(row);
		},
		dataEdited:function(data){
			//data - the updated table data
			//console.log(cur_row._row.data);
			if(cur_row != null)
			{
				params.id = cur_row._row.data.id;
				console.log(params.publish);
				GetResource(0,resource,'data=updatevul',params,cur_row._row.data,successcb) ;
				cur_row.reformat();
			}
			//this.rowFormatter ();
		},
		/*rowFormatter:function(row)
		{
			//console.log(row.getData().status);
			//console.log("---"+row.getElement().style.color);
			
			if(row.getData().status == "FIX")
			{
				row.getElement().style.color = "#FF0000";
				return;
			}
			else if(row.getData().status == "IGNORE")
			{
				//setTimeout(function()
				//{ 
				//	row.delete(); 
				//}, 2000);
			}
			
			row.getElement().style.color = "grey";
		},*/
		initialSort:
		[
			{column:"baseScore", dir:"desc"}
		],
		/*initialFilter:
		[
			{field:"status", type:"in", value:["FIX","OPEN"]},
			{field:"version_match", type:"!=", value:''}
		],*/
		tooltips:function(cell)
		{
			//function should return a string for the tooltip of false to hide the tooltip
			return   cell.getValue(); //return cells "field - value";
		}
	};
	function validMutator(value, data, type, params, component)
	{
		if(value == false)
			return 'No More a Vaulnerability';
		else 
			return '';
		
	}
	function progressMutator(value, data, type, params, component)
	{
		var tickets  = value;
		if(typeof(tickets) === 'undefined')
			return 0;
		
		var rowproduct  = data.product;
		var count = 0;
		var progress = 0;
		for(var i=0;i<tickets.length;i++)
		{
			var ticket = tickets[i];
			if(typeof(ticket.product) === 'undefined')
				continue;
			
			for(var j=0;j<ticket.product.length;j++)
			{
				if(ticket.product[j] == rowproduct)
				{
					count++;
					progress += ticket.progress;
				}
			}
		}
		//console.log("-------->"+progress+count);
		if(count == 0)
			return 0;
		return progress/count;
	}
	function fixedvulsMutator(value, data, type, params, component)
	{
		var retval = '';
		var del = '';
		for(var i=0;i<value.length;i++)
		{
			retval += del+value[0].product.name;
			//console.log(value[0].product.name);
			del = ",";
			
		}
		return retval;
	}
	function ticketMutator(value, data, type, params, component)
	{
		var tickets  = value;
		if(typeof(tickets) === 'undefined')
			return '';
		var rowproduct  = data.product;
		var html = '';
		var del = '';
		for(var i=0;i<tickets.length;i++)
		{
			var ticket = tickets[i];
			var urlt = url[ticket.source]+"/"+ticket.key;
			var found = 0;
		
			if(typeof(ticket.product) === 'undefined')
			{
				if(ticket.status == 'done')
					html += del+'<a class="badge" href="'+urlt+'"><del>'+ticket.key+'</del></a>';
				else
					html += del+'<a class="badge" href="'+urlt+'">'+ticket.key+'</a>';
				del = '&nbsp';
				continue;
			}
			for(var j=0;j<ticket.product.length;j++)
			{
				if(ticket.product[j] == rowproduct)
					found = 1;
			}

			var badge = '';
			if(found == 0)
			{
				if(ticket.status == 'done')
					html += del+'<a class="badge" href="'+urlt+'"><del>'+ticket.key+'</del></a>';
				else
					html += del+'<a class="badge"href="'+urlt+'">'+ticket.key+'</a>';
				
				del = '&nbsp';
			}
			else
			{
			
				var label = ticket.key;
				if(ticket.status == 'open')
					badge =  'badge-primary';
				else if(ticket.status  == 'in progress')
					badge =  'badge-success';
				else if(ticket.status == 'done')
				{
					badge =  'badge-secondary';
					label = "<del>"+ticket.key+"</del>";
				}
				else
					badge =  'badge-warning';
				html += del+ '<a href="'+urlt+'">'+'<span class="badge '+badge+'">'+label+'</span>'+'</a>';
				del = '&nbsp';
			}
		}
		return html;
	}
	function successcb(jsonData)
	{
		
	}
	function paramLookup(cell)
	{
		//cell - the cell component
		//do some processing and return the param object
		var row = cell.getRow();
		//console.log("hello");
	
		if(row._row.data.progress == 100)
			return {"values":{"OPEN":"OPEN", "FIX":"FIX","FIXED":"FIXED"}};
		if(row._row.data.vultickets.length > 0)
			return {"values":{"OPEN":"OPEN", "FIX":"FIX"}};
		return {"values":{"OPEN":"OPEN", "FIX":"FIX","IGNORE":"IGNORE"}};
	}
	var table = new Tabulator("#table1", settings);
	//table.addFilter("status","!=",'IGNORE');
	//table.addFilter("version_match","!=",'');
	table.setFilter(customFilter, {});
	function customFilter(data, filterParams)
	{
		var viewall = $('#checkbox_viewall').prop('checked');
		if(viewall == true) // checked 
			return true;
		
		if(typeof(params.status)!== 'undefined')
		{
			if(params.status.toLowerCase() =='open')
			{
				if(data.status == 'OPEN')
					return true;
				return false;
			}
			else if(params.status.toLowerCase() =='fix')
			{
				if(data.status == 'FIX')
					return true;
				return false;
			}
			else if(params.status.toLowerCase() =='fixed')
			{
				if(data.status == 'FIXED')
					return true;
				return false;
			}
			else if(params.status.toLowerCase() =='ignore')
			{
				if(data.status == 'IGNORE')
					return true;
				return false;
			}
		}
		var viewall = $('#checkbox_viewall').prop('checked');
		if(!viewall)
		{
			if((data.status == 'OPEN')||(data.status == 'FIX'))
			{
				return true;
			}
			return false;
		}
		return true;
		
	}

})
