
var MAPSJS = new Class({

	initialize : function()
	 {
		var markicons = $$('.icons_container img');
	 	if(markicons)
	 	 {
			markicons.each(function(item)
			 {
				item.addEvent('click', function()
				 {
				 	markicons.each(function(item) { item.setStyle('border', '2px solid #FFF'); });
				 	$('ip_maps_icon').value = this.name;
				 	this.setStyle('border', '2px solid #666');
				 	return false;
				 })
		 	 });
		 }
	 }

 });

window.addEvent('load', function() { new MAPSJS(); } );
