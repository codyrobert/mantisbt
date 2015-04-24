var app = {
	
	PUSH_STATE_ACTIVE: false,
	
	_listeners: {},
	
	params: {},
	state: {
		host: null,
		url: null,
		title: null,
		params: {}
	},
	
	addListener: function(type, fn, obj)
	{
		if (!app._listeners.hasOwnProperty(type))
		{
			app._listeners[type] = [];
		}
		
		app._listeners[type].push({
			fn: fn,
			obj: obj
		});
	},
	
	callListeners: function(type, e)
	{
		if (app._listeners.hasOwnProperty(type) && app._listeners[type].length > 0)
		{
			for (var i = 0; i < app._listeners[type].length; i++)
			{
				if (typeof app._listeners[type][i].obj !== "undefined")
				{
					app._listeners[type][i].fn.apply(app._listeners[type][i].obj, [e]);
				}
				else
				{
					app._listeners[type][i].fn.apply(app, [e]);
				}
			}
		}
	},
	
	getPathnameForURL: function(url)
	{
		if (url.indexOf("https://") == 0)
		{
			url = url.substr(8);
		}
		else if (url.indexOf("http://") == 0)
		{
			url = url.substr(7);
		}
		else if (url.indexOf("//") == 0)
		{
			url = url.substr(2);
		}
		
		if (url.indexOf(app.state.host) == 0)
		{
			url = url.substr(app.state.host.length);
		}
		
		if (url.indexOf("#") > 0)
		{
			url = url.substr(0, url.indexOf("#"));
		}
		
		if (url.indexOf("?") > 0)
		{
			url = url.substr(0, url.indexOf("?"));
		}
		
		if (url.substr(-1) == "/")
		{
			url = url.substr(0, url.length-1);
		}
		
		if (url.substr(0, 1) == "/")
		{
			url = url.substr(1);
		}
		
		return "/"+url;
	},
	
	init: function()
	{
		app.setupPushState();
		
		window.addEventListener("load", app.load);
		window.addEventListener("popstate", app.popState);
	},
	
	load: function() 
	{
		var name = document.querySelector("html").dataset.controller;
		
		if (app.controllers.hasOwnProperty(name) && app.controllers[name].hasOwnProperty("load") && typeof app.controllers[name].load === "function")
		{
			app.controllers[name].load();
		}
	},
	
	refreshPushState: function()
	{
		app.state.host = window.location.hostname;
		app.state.url = app.getPathnameForURL(window.location.pathname);
		app.updateStateParams();
	},
	
	setupPushState: function()
	{
		app.refreshPushState();
		
		if (typeof history.pushState !== 'undefined')
		{
			app.PUSH_STATE_ACTIVE = true;
			history.replaceState(app.state, app.state.title, app.state.url);
		}
	},
	
	pushState: function(state)
	{
		for (var i in state)
		{
			app.state[i] = state[i];
		}
		
		app.updateStateParams();
		history.pushState(app.state, app.state.title, app.state.url);
		
		app.callListeners("pushState", state);
	},
	
	popState: function(e)
	{
		app.refreshPushState();
		app.callListeners("popState", e);
	},
	
	updateStateParams: function()
	{
		var url = app.state.url;
		var args = {
			controller: ""
		};
		
		if (url.indexOf("/") == 0)
		{
			url = url.substring(1);
		}
		
		url = url.split("/");
		
		for (var i = 0; i < url.length; i++)
		{
			var arg = url[i].split(":");
			
			if (arg.length == 1 && args.length == 1)
			{
				args.controller = args.controller+"/"+arg[0];
			}
			else if (arg.length > 1)
			{
				args[arg[0]] = arg[1];
			}
			else
			{
				args[arg[0]] = null;
			}
		}
		
		app.state.params = args;
	},
	
	listeners: {
	},

	controllers: {
		home: {
			project: null,
			tickets: null,
			
			load: function()
			{
				app.controllers.home.tickets = document.querySelectorAll("#tickets-table > *");
				
				app.addListener("popState", app.controllers.home.setCategory);
				app.addListener("pushState", app.controllers.home.setCategory);
				
				document.querySelector("#filter_view_by_project").addEventListener("change", app.controllers.home.setProject);
			},
			setCategory: function(e)
			{
				if (!app.state.params.view)
				{
					app.state.params.view = "open";
				}
				
				app.controllers.home.filterView();
			},
			setProject: function(e)
			{
				app.controllers.home.project = e.target.options[e.target.selectedIndex].value;
				app.controllers.home.filterView();
			},
			filterView: function()
			{
				var project = app.controllers.home.project;
				var category = app.state.params.view;
				var rows = app.controllers.home.tickets;
				
				for (var i = 0; i < rows.length; i++)
				{
					if ((!project || rows[i].dataset.project == project) &&
						rows[i].dataset.category.indexOf(category) >= 0)
					{
						rows[i].classList.remove("hide");
					}
					else
					{
						rows[i].classList.add("hide");
					}
				}
			}
		}
	}
	
};

app.init();