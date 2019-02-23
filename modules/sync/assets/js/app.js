var source =  null;
$(function() 
{
	"use strict";
	console.log("Loading Sync Module");
	$("#syncdb").click(SyncDb); 
	$("#syncjira").click(SyncJira); 
	$("#clear").click(Clear);
	$("#close").click(closeConnection);
})
function Clear()
{
	logger.clear()
}

function closeConnection() {
	if(source == null)
		return;
	
	source.close();
	logger.log('> Connection was closed');
	updateConnectionStatus('Disconnected', false);
}
function SyncJira() 
{
	console.log("Initiating Sync");
	source = new EventSource('sync?data=syncjira&console=1');
	source.addEventListener('message', function(event) {
	var data = JSON.parse(event.data);
	var d = new Date(data.id * 1e3);
	var timeStr = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
	logger.log('' + timeStr+' '+data.msg);
	}, false);

	source.addEventListener('open', function(event) 
	{
		logger.log('> Connected');
		updateConnectionStatus('Connected', true);
	}, false);

	source.addEventListener('error', function(event) 
	{
		if (event.eventPhase == 2) 
		{ //EventSource.CLOSED
			logger.log('> Disconnected');
			updateConnectionStatus('Disconnected', false);
			source.close();
		}
	}, false);
}
function SyncDb() 
{
	console.log("Initiating Sync");
	source = new EventSource('sync?data=syncdb&console=1');
	source.addEventListener('message', function(event) {
	var data = JSON.parse(event.data);
	var d = new Date(data.id * 1e3);
	var timeStr = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
	logger.log('' + timeStr+' '+data.msg);
	}, false);

	source.addEventListener('open', function(event) 
	{
		logger.log('> Connected');
		updateConnectionStatus('Connected', true);
	}, false);

	source.addEventListener('error', function(event) 
	{
		if (event.eventPhase == 2) 
		{ //EventSource.CLOSED
			logger.log('> Disconnected');
			updateConnectionStatus('Disconnected', false);
			source.close();
		}
	}, false);
}