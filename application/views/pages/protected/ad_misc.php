<div class='admin_left'>
<?php
	if (strlen($blog_feed_links)>0)
	{
		?>
	    <div class='panel'>
	        <h2>
	            <span id='view_feeds_heading' class='ad_heading_text noselect' onclick='close_height("view_feeds")'>View Feeds</span>
	            <span id='view_feeds_show' class='sprite panel_close noselect' onclick='close_height("view_feeds")'></span>
	        </h2>
	        <div id='view_feeds_panel' class='panel_details'>
	        	<?php echo $blog_feed_links; ?>
	    	</div>
	    </div>
		<?php
	}
	if (strlen($google_product_links)>0)
	{
		?>
	    <div class='panel'>
	        <h2>
	            <span id='view_google_heading' class='ad_heading_text noselect' onclick='close_height("view_google")'>View Google PRoduct Lists</span>
	            <span id='view_google_show' class='sprite panel_close noselect' onclick='close_height("view_google")'></span>
	        </h2>
	        <div id='view_google_panel' class='panel_details'>
	        	<?php echo $google_product_links; ?>
	    	</div>
	    </div>
		<?php
	}
	if (strlen($sitemap_link)>0)
	{
		?>
	    <div class='panel'>
	        <h2>
	            <span id='view_sitemap_heading' class='ad_heading_text noselect' onclick='close_height("view_sitemap")'>View Site Map</span>
	            <span id='view_sitemap_show' class='sprite panel_close noselect' onclick='close_height("view_sitemap")'></span>
	        </h2>
	        <div id='view_sitemap_panel' class='panel_details'>
	        	<?php echo $sitemap_link; ?>
	    	</div>
	    </div>
		<?php
	}
?>
</div>

<div class='admin_instructions'>
	<p>
		here you can click to view the feeds associated with your site, including the site map, rss feed
		and google product feed
	</p>
</div>