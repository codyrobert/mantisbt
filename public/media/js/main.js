/*
 * Setup data attach points
 *
-------------------------------------------------------------- */
(function($) {

	var attachPingdomStatuses = function()
	{
		$.get("/api/allStatuses", {}, function(json) {
		
			if (json.status && json.status === "ok")
			{
				var domElements = $("[data-attach='pingdom.check.status']");
			
				domElements
					.removeClass("pingdom-check-status-down")
					.removeClass("pingdom-check-status-up")
					.each(function() {
				
						var pingdomId = $(this).data("id");
						
						if (json.data[pingdomId] && json.data[pingdomId] === "up")
						{
							$(this).addClass("pingdom-check-status-up");
						}
						else
						{
							$(this).addClass("pingdom-check-status-down");
						}
					});
			}
			
		});
	};
	
	$(document).ready(attachPingdomStatuses);
	
})(jQuery);


/*
 * Setup charts
 *
-------------------------------------------------------------- */
(function($) {

	var charts = [];
	
	var setupAverageResponseTimesChart = function()
	{
		$("#responseTimeAverages").each(function() 
		{
			var canvasContainer = $(this);
					
			$.get("/api/allAverages", {}, function(json) {
			
				if (json.status && json.status === "ok")
				{
					var maxResponseTime = 0;
					var minResponseTime = 9999;
					
					var lineChartData = {
						labels: [
							"Midnight", "1am", "2am", "3am", "4am", "5am", "6am", "7am", "8am", "9am", "10am", "11am", 
							"Noon", "1pm", "2pm", "3pm", "4pm", "5pm", "6pm", "7pm", "8pm", "9pm", "10pm", "11pm"
						],
						datasets : []
					};
					
					for (var i in json.data)
					{
						var label = json.data[i].label;
						var color = json.data[i].color;
						var checks = json.data[i].checks;
						
						for (var j in checks)
						{
							lineChartData.datasets.push({
								label: checks[j].label,
								fillColor: "rgba("+color[0]+","+color[1]+","+color[2]+",0)",
								strokeColor: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
								pointColor: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
								pointStrokeColor: "#fff",
								pointHighlightFill: "#fff",
								pointHighlightStroke: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
								data: checks[j].averages
							});
							
							for (var k in checks[j].averages)
							{
								if (checks[j].averages[k] > maxResponseTime)
								{
									maxResponseTime = checks[j].averages[k];
								}
								if (checks[j].averages[k] < minResponseTime)
								{
									minResponseTime = checks[j].averages[k];
								}
							}
						}
					}
					
					canvasContainer.empty().append(document.createElement("canvas"));
					
					var chartStep = 200;
					var ctx = $("canvas", canvasContainer)[0].getContext("2d");
					
					ctx.canvas.height = canvasContainer.height();
					ctx.canvas.width = canvasContainer.width();
					
					charts.push(new Chart(ctx).Line(lineChartData, {
						multiTooltipTemplate: "<%= datasetLabel %>: <%= value %> ms",
						tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %> ms",
						scaleLabel: "<%=value%> ms",
						scaleOverride: true,
						scaleSteps: Math.ceil(maxResponseTime / chartStep) - Math.floor(minResponseTime / chartStep),
						scaleStepWidth: chartStep,
						scaleStartValue: Math.floor(minResponseTime / chartStep) * chartStep
					}));
				}
			});
		});
	};
	
	var setupClientAverageResponseChart = function()
	{
		$("[data-chart=clientAvgResponse]").each(function() 
		{
			var canvasContainer = $(this);
			var client = $(this).data("client");
			
			$.get("/api/avgResponseTime/client:"+client, {}, function(json) 
			{
				if (json.status && json.status === "ok")
				{
					var maxResponseTime = 0;
					var minResponseTime = 9999;
					
					var lineChartData = {
						labels: [
							"Midnight", "1am", "2am", "3am", "4am", "5am", "6am", "7am", "8am", "9am", "10am", "11am", 
							"Noon", "1pm", "2pm", "3pm", "4pm", "5pm", "6pm", "7pm", "8pm", "9pm", "10pm", "11pm"
						],
						datasets : []
					};
					
					var label = json.data.label;
					var color = json.data.color;
					var checks = json.data.checks;
					
					for (var j in checks)
					{
						lineChartData.datasets.push({
							label: checks[j].label,
							fillColor: "rgba("+color[0]+","+color[1]+","+color[2]+",0)",
							strokeColor: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
							pointColor: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
							data: checks[j].averages
						});
						
						for (var k in checks[j].averages)
						{
							if (checks[j].averages[k] > maxResponseTime)
							{
								maxResponseTime = checks[j].averages[k];
							}
							if (checks[j].averages[k] < minResponseTime)
							{
								minResponseTime = checks[j].averages[k];
							}
						}
					}
					
					canvasContainer.empty().append(document.createElement("canvas"));
					
					var chartStep = 200;
					var ctx = $("canvas", canvasContainer)[0].getContext("2d");
					
					ctx.canvas.height = canvasContainer.height();
					ctx.canvas.width = canvasContainer.width();
					
					charts.push(new Chart(ctx).Line(lineChartData, {
						multiTooltipTemplate: "<%= datasetLabel %>: <%= value %> ms",
						tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %> ms",
						scaleLabel: "<%=value%> ms",
						scaleOverride: true,
						scaleSteps: Math.ceil(maxResponseTime / chartStep) - Math.floor(minResponseTime / chartStep),
						scaleStepWidth: chartStep,
						scaleStartValue: Math.floor(minResponseTime / chartStep) * chartStep
					}));
				}
			});
		});
	};
	
	var setupCheckAverageResponseChart = function()
	{
		$("[data-chart=checkAvgResponse]").each(function() 
		{
			var canvasContainer = $(this);
			var check = $(this).data("check");
			
			$.get("/api/avgResponseTime/check:"+check, {}, function(json) 
			{
				if (json.status && json.status === "ok")
				{
					var maxResponseTime = 0;
					var minResponseTime = 9999;
					
					var lineChartData = {
						labels: [
							"Midnight", "1am", "2am", "3am", "4am", "5am", "6am", "7am", "8am", "9am", "10am", "11am", 
							"Noon", "1pm", "2pm", "3pm", "4pm", "5pm", "6pm", "7pm", "8pm", "9pm", "10pm", "11pm"
						],
						datasets : []
					};
					
					var label = json.data.label;
					var color = json.data.color;
					var checks = json.data.checks;
					
					for (var j in checks)
					{
						lineChartData.datasets.push({
							label: checks[j].label,
							fillColor: "rgba("+color[0]+","+color[1]+","+color[2]+",0)",
							strokeColor: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
							pointColor: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
							pointStrokeColor: "#fff",
							pointHighlightFill: "#fff",
							pointHighlightStroke: "rgba("+color[0]+","+color[1]+","+color[2]+",1)",
							data: checks[j].averages
						});
						
						for (var k in checks[j].averages)
						{
							if (checks[j].averages[k] > maxResponseTime)
							{
								maxResponseTime = checks[j].averages[k];
							}
							if (checks[j].averages[k] < minResponseTime)
							{
								minResponseTime = checks[j].averages[k];
							}
						}
					}
					
					canvasContainer.empty().append(document.createElement("canvas"));
					
					var chartStep = 25;
					var ctx = $("canvas", canvasContainer)[0].getContext("2d");
					
					ctx.canvas.height = canvasContainer.height();
					ctx.canvas.width = canvasContainer.width();
					
					charts.push(new Chart(ctx).Line(lineChartData, {
						multiTooltipTemplate: "<%= datasetLabel %>: <%= value %> ms",
						tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %> ms",
						scaleLabel: "<%=value%> ms",
						scaleOverride: true,
						scaleSteps: Math.ceil(maxResponseTime / chartStep) - Math.floor(minResponseTime / chartStep),
						scaleStepWidth: chartStep,
						scaleStartValue: Math.floor(minResponseTime / chartStep) * chartStep
					}));
				}
			});
		});
	};
	
	var resizeCharts = function()
	{
		for (var i in charts)
		{
			charts[i].resize();
		}
	};
	
	$(document).ready(setupAverageResponseTimesChart);
	$(document).ready(setupClientAverageResponseChart);
	$(document).ready(setupCheckAverageResponseChart);
	//$(window).resize(resizeCharts);
	
})(jQuery);