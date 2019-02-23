function GetResource(identity,cmd,resource,cparam,jsondata,successcb) 
{
	var identity = identity;
	var param = resource;
	var del = '';
	var paramstr = '';
	Object.keys(cparam).forEach(function(key) 
	{
		if((key=='data')||(key=='view')||(key=='test'))
		{
		}
		else
		{
			paramstr += del+key+"="+cparam[key];
			del='&';
		}
	});
	if(paramstr.length > 0)
		param = resource+'&'+paramstr;
	
	loc = window.location.href;
	if(cmd != null)
	{
		var parts = window.location.href.split('/');
		var loc = '';
		var del = ''
		for(var i=0;i<parts.length-1;i++)
		{
			loc = loc+del+parts[i];
			del ='/';
		}
		loc = loc+"/"+cmd;
	}
	console.log(loc);
	if(loc[loc.length-1] == '/')
		loc = loc.substring(0, loc.length - 1);
	var url = loc.split('?')[0]+"?"+param;
	$.ajax(
	{     
		headers: { 
			Accept : "text/json; charset=utf-8",
			"identity":identity,
		},
		type: "POST",
		url : url,
		dataType: 'json',
		'processData': false,
		contentType: "application/json; charset=utf-8",
		//data: param, 	
		data: JSON.stringify(jsondata), // Our valid JSON string
		success : function(d) 
		{
			successcb(d);
		},
		complete: function() {},
		error: function(xhr, textStatus, errorThrown) 
		{
			console.log('ajax loading error...');
			return false;
		}
	})
}

function ConvertJsDateFormat(datestr)
{
	var d = new Date(datestr);
	if(d == 'Invalid Date')
		return '';
	
	dateString = d.toUTCString();
	dateString = dateString.split(' ').slice(0, 4).join(' ').substring(5);
	return dateString;
}