=== Visitor map generator ===
Contributors: Stefan Aichholzer S.
Donate link: http://www.aichholzer.name/donate/2874480
Tags: maps, google, visitors, generator
Requires at least: 2.6
Tested up to: 2.7.1
Stable tag: trunk

Grabs your visitor IP addresses and generates a map for those visits. You can define most parameters from it's options page.
 

== Description ==
The plug-in will grab your current visitor IP address, store it and generate a Google map
displaying all those visits. You can customize which marker you want to use, a text for the
marker infowindow and more.

You may take a look at http://aichholzer.name to see how it works there.

This plug-in includes IP-Geo information files and therefor the plug-in will be larger
than the average plug-in you'll find. This download will be about 40Mb in size.

This plug-in can't be upgraded using the automatic upgrade options within Wordpress, since the download
is too big you will get a time-out error. Please upgrade manually.

If you like this plug-in please vote it or at least leave a comment on the plug-in's home page.
You can also leave the url from the site where you're using it. (I'd like to see how it
fits into different layouts) ;)
You could also talk about it on your blog and/or post a trackback link to the plug-in's page.

This plug-in requires PHP version 5 and above.


*Feature List*

* NEW: Multi-lingual support
* NEW: Upload and use your own markers
* Most map options can be customized
* Can be inserted in any page (through template code)
* Can be inserted into posts (through identifiers)
* Back-end (map options page) statistics


== Installation ==
The usual way:

1. Upload the plug-in folder into to the `/wp-content/plugins/` directory.
2. Activate `Visitor maps generator` through the 'Plugins' menu in WordPress.
3. Go to the plug-in options at `Options\Visitor maps by IP` and customize the plug-in
4. Use the plug-in:

  4.1 Insert the plug-in into your pages (templates) using the PHP code provided.
  	  Use this code: `<?php if(function_exists('ip_tracker_draw_map')) ip_tracker_draw_map(); ?>`

  4.2 Insert the plug-in into your pages (templates) and don't show the map.
  	  Use this code: `<?php if(function_exists('ip_tracker_draw_map')) ip_tracker_draw_map('track_only'); ?>`

  4.3 Insert the plug-in into your posts. Simply use any of the identifiers to display
      the map where you want it to. Valid identifiers are:
      
      [vm] [visits] [visitormap]
      
      Note that the plug-in will not record IP addresses if inserted into posts, this
      should be used for displaying visits only.

5. Enjoy and show the world who's visiting you.


== Contributors/Changelog ==
   
    0.4.2	2009/01/18	Fixed the installer issue
						Now the plug-in creates a table to store
						it's data on plug-in activation.
    					
    0.4.3	2009/01/18	Fixed the installer issue
						Now uses the WP data object to create table
						on first install.
    					
    0.4.4	2009/01/18	Better JS performance.
    
    0.4.5	2009/01/18	All JS is now included in the template header,
						where it actually belongs.
    					
    0.4.6	2009/01/19	You can now define the amount of markers to display.
						Current selected marker will be highlighted when you open
						the options page.
						When you select other markers the highlight will follow the
						one you click.
						
    0.4.8	2009/01/19	Maps can now be inserted into posts.
    
    0.4.9	2009/01/19	You can now set the language for the map controls.
						If you would like to see any particular language listed
						please let me know.

    0.5.0	2009/01/20	It's now possible to set custom CSS for the map
						right from the options page.

    0.5.1	2009/01/21	Make code a little backwards compatible.
						Thanks go to the guys who reported this
						on the plug-in's page.

    0.5.2	2009/01/21	This was actually not a bug, I rather call it a feature request.
						The plug-in can now collect data if it's inserted in a post.
						This has to be set in the plug-in's options page.
						By default it will only collect data if inserted in your
						template using the PHP code provided.
						I also fixed other minor issues.

    0.5.3	2009/01/21	Fixed conflict with other plug-in
						Thanks to KaRLiK for reporting it.

    0.5.4	2009/01/22	After it being a major request it's now possible to insert
    					a map (using the PHP code) and have it only to collect data, in this
						case it won't render the map.
						Read the options page for more details.
						
    0.6.5	2009/01/26	New user interface in options page
    					The plug-in has a logo (Thanks to David Bugeja)
    					You can now get statistics in the options page
    					Re-wrote some of the code (Better performance)
    					Fixed some minor JS issues
    					...probably more things that I can't remember.
    					Thank you guys for all the feedback, specially the one you
    					leave on the plug-in's page.

    0.6.8	2009/01/29	Fixed some minor JS issues
						Optimized JS performance even more
						Now the plug-in only loads required files when they are
						actually required in the admin panel.
						Plug-in now checks for the required PHP version.
						Fix small IP translation issue
						
    0.6.9	2009/01/29	Fix small IP translation issue which in some cases
    					could affect JS performance.

    0.7.0	2009/01/30	Multi-lingual support.
						As for now only English and spanish are available
						but more languages to come.
						
    0.7.3	2009/01/31	Uploading and using custom markers is now possible.
    					Top visiting countries in statistics page
    					Flag icon for each country

    0.7.4	2009/02/04	Newest binary GeoIP file
		
	0.7.5	2009/02/17	Quick JS fix when inserting maps into posts
						This issue affected only IE, it shall improve performance on FF also
						Thanks to David Bugeja for reporting it
						
	0.7.6	2009/02/19	Closed an open (missing) html tag
						Not really an issue but it was just wrong.
						
    0.7.7	2009/03/25	Newest binary GeoIP file
	
    0.7.8	2009/04/07	Newest binary GeoIP file and small fix
	
    0.7.9	2009/04/21	Fixed the issue about not seeing the pins for the visits in the map.


Please read the indications in the plug-in options page for more details.

Please send me an e-mail with any suggestions or ideas.
If anything goes wrong for you, please let me know.
