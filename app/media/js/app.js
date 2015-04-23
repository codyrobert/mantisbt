var app = {
	
	_listeners: {},
	
	params: {},
	
	addListener: function(type, fn)
	{
		if (!app._listeners.hasOwnProperty(type))
		{
			app._listeners[type] = [];
		}
		
		app._listeners[type].push(fn);
	},
	
	init: function()
	{
		window.addEventListener("load", app.load);
		app.listeners.hashChange();
	},
	
	load: function() 
	{
		window.addEventListener("hashchange", app.listeners.hashChange);
		
		var name = document.querySelector("html").dataset.controller;
		
		if (app.controllers.hasOwnProperty(name) && app.controllers[name].hasOwnProperty("load") && typeof app.controllers[name].load === "function")
		{
			app.controllers[name].load();
		}
	},
	
	listeners: {
		hashChange: function(e)
		{
			var hash = window.location.hash;
			var args = {};
			
			if (hash.indexOf("#/") == 0)
			{
				hash = hash.substring(2);
			}
			else if (hash.indexOf("#") == 0)
			{
				hash = hash.substring(1);
			}
			
			hash = hash.split("/");
			
			for (var i = 0; i < hash.length; i++)
			{
				var arg = hash[i].split(":");
				
				if (arg.length > 1)
				{
					args[arg[0]] = arg[1];
				}
				else if (arg.length == 1 && i == 0)
				{
					args.section = arg[0];
				}
			}
			
			app.params = args;
			
			if (app._listeners.hasOwnProperty("hashChange") && app._listeners.hashChange.length > 0)
			{
				for (var i = 0; i < app._listeners.hashChange.length; i++)
				{
					app._listeners.hashChange[i](e);
				}
			}
		}
	},

	controllers: {
		home: {
			tickets: null,
			load: function()
			{
				app.controllers.home.tickets = document.querySelectorAll("#tickets-table > *");
				app.controllers.home.setSection();
				
				app.addListener("hashChange", app.controllers.home.setSection);
			},
			setSection: function(e)
			{
				var rows = app.controllers.home.tickets;
				var params = app.params;
			
				if (params.section)
				{
					for (var i = 0; i < rows.length; i++)
					{
						if (rows[i].dataset.sections.indexOf(params.section) >= 0)
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
	}
	
};

app.init();