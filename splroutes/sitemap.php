<?php


// Sitemap generate secret url

$app->get('/'.$adminPath.'/'.$sitemapPath.'/sitemap', function() use ($app, $template_arr) {
	$time = explode(" ",microtime());
	$time = $time[1];

	// create object
	$sitemap = new SitemapGenerator("http://myopenletter.in/", "");

	// will create also compressed (gzipped) sitemap
	$sitemap->createGZipFile = true;

	// determine how many urls should be put into one file
	$sitemap->maxURLsPerSitemap = 10000;

	// sitemap file name
	$sitemap->sitemapFileName = "sitemap.xml";

	// sitemap index file name
	$sitemap->sitemapIndexFileName = "sitemap-index.xml";

	// robots file name
	$sitemap->robotsFileName = "robots.txt";

	$urls = array(
		array("http://myopenletter.in", date('c'), 'daily', '1'),
		array("http://myopenletter.in/about", date('c'), 'weekly', '0.5'),
		array("http://myopenletter.in/latest", date('c'), 'daily', '0.5'),
		array("http://myopenletter.in/explore", date('c'), 'daily', '0.5'),
		array("http://myopenletter.in/tos", date('c'), 'weekly', '0.25'),
		);

	// add many URLs at one time
	$sitemap->addUrls($urls);
	
	$letters = Model::factory('Letter')->order_by_desc('timestamp')->find_many();
	
	foreach ($letters as $letter)
	{
		$url = BASE_URL.$letter->id.'/from/'.$letter->from_slug.'/to/'.$letter->to_slug;
		$sitemap->addUrl($url, date('c'), 'weekly', '0.25');
	}

	try {
		// create sitemap
		$sitemap->createSitemap();

		// write sitemap as file
		$sitemap->writeSitemap();

		// update robots.txt file
		$sitemap->updateRobots();

		// submit sitemaps to search engines
		$result = $sitemap->submitSitemap(".LMSz1rV34GDa1qfE1_sErhTQf9ShcySCFyFVjO7ViC2RnePKkmPxbumXrp4upnlVwCKR2pBGQ--");
		// shows each search engine submitting status
		//echo "<pre>";
		//print_r($result);
		//echo "</pre>";
		
	}
	catch (Exception $exc) {
		echo $exc->getTraceAsString();
	}

	echo "Memory peak usage: ".number_format(memory_get_peak_usage()/(1024*1024),2)."MB";
	$time2 = explode(" ",microtime());
	$time2 = $time2[1];
	echo "<br>Execution time: ".number_format($time2-$time)."s";
	
});
