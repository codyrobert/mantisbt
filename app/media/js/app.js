var app = {
	
	load: function() 
	{
		var name = document.querySelector("html").dataset.controller;
		
		if (app.controllers.hasOwnProperty(name) && app.controllers[name].hasOwnProperty("load") && typeof app.controllers[name].load === "function")
		{
			app.controllers[name].load();
		}
	},

	controllers: {
		home: {
			load: function()
			{
				document.querySelector("#ticket-nav").addEventListener("nav-item-selected", function(e) {
					
					var visible_status;
					
					if (e.detail.href == "/#/open_tickets")
					{
						visible_status = "open";
					}
					else if (e.detail.href == "/#/recently_closed")
					{
						visible_status = "closed";
					}
					
					var rows = document.querySelectorAll("#tickets-table > *");
					
					for (var i = 0; i < rows.length; i++)
					{
						if (rows[i].dataset.status == visible_status)
						{
							rows[i].classList.remove("hide");
						}
						else
						{
							rows[i].classList.add("hide");
						}
					}
					
				});
			}
		}
	}
	
};

window.addEventListener("load", app.load);