<?php

class sitemap_Handler {

	const MAX_URLS_PER_SITEMAP = 50000;

	/* deletes all urls for a host */
	static public function purgeUrlsForHost($db, $host) {

			$db->delete('sitemap', 'host = %s', $host);
	}

	/* adds a url to the sitemap table for inclusion next time the sitemap is generated */
	static public function writeUrl(&$db, $loc, $lastMod = null, $changeFreq = null, $priority = null, $tableName = 'sitemap') {

		$loc = trim($loc);
		$checksum = crc32($loc);

		/* get the host name from url */
		preg_match('@^(?:http://)?([^/]+)@i', $loc, $matches);
		$host = $matches[1];

		$row = $db->select_1('select * from sitemap where checksum = %i AND loc = %s', $checksum, $loc);

		/* new location */
		if(is_null($row)) {
			$db->insert(
				'sitemap',
				'checksum = %i', 		$checksum,
				'host = %s', 			$host,
				'loc = %s', 			$loc,
				'last_mod = %T', 		$lastMod,
				'change_freq = %S', 	$changeFreq,
				'priority = %l', 		is_null($priority)?'NULL':$priority
			);

		/* update as details have changed */
		} elseif($row->t_last_mod != $lastMod || $row->s_change_freq != $changeFreq || $row->s_priority != $priority) {
			$db->update_1(
				'sitemap',
				'checksum = %i AND loc = %s', 	$row->i_checksum, $loc,
				'last_mod = %T', 		$lastMod,
				'change_freq = %S', 	$changeFreq,
				'priority = %l', 		is_null($priority)?'NULL':$priority
			);

		}

		/* nulling used variables (big sitemaps need every scrap of memory!) */
		$row = null;
		$host = null;
		$checksum = null;
		$matches = null;
	}

	/* gets total number of urls for host */
	static public function getUrlCount(&$db, $host, $tablename = 'sitemap') {

		return $db->select_1_i('select count(*) from %l where host = %s', $tablename, $host);

	}

	/* writes sitemap files to disk */
	static public function writeXml(&$db, $host, $xmlFilePath, $xmlUrlRoot, $maxUrlsPerSitemap = null, $compress = true, $tablename = 'sitemap') {

		/* clean up old sitemap files */
		foreach(glob($xmlFilePath."sitemap*.xml*") as $filename)
			unlink($filename);

		if(is_null($maxUrlsPerSitemap)) $maxUrlsPerSitemap = self::MAX_URLS_PER_SITEMAP;

		/* how many items required */
		$count = self::getUrlCount($db, $host, $tablename);

		/* array of sitemap files */
		$siteMapArr = array();

		/* itterate through required sitemap files writing them & building sitemap array */
		for($i = 1;$i <= ceil($count/$maxUrlsPerSitemap); $i++) {
			$filename = sf('sitemap%s.xml%s', $i, $compress?'.gz':'');
			$siteMapArr[] = $xmlUrlRoot.$filename;
			self::writeSiteMap($db, $host, $filename, $xmlFilePath,($i-1)*$maxUrlsPerSitemap, $maxUrlsPerSitemap, $compress, $tablename);
		}

		/* write the index file */
		self::writeSiteMapIndex($siteMapArr, $xmlFilePath);

	}

	/* writes an sitemap index file */
	static public function writeSiteMapIndex($siteMapArr, $xmlFilePath) {

		/* setup the document */
		$sitemapIndex = new DOMDocument;
		$sitemapIndex->formatOutput = true;

		/* create root element */
		$root = $sitemapIndex->createElement("sitemapindex");
		$sitemapIndex->appendChild($root);

		$root_attr = $sitemapIndex->createAttribute('xmlns');
		$root->appendChild($root_attr);

		$root_attr_text = $sitemapIndex->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9');
		$root_attr->appendChild($root_attr_text);

		/*  additional headers to calidate sitemap against schema */
		$root_attr2 = $sitemapIndex->createAttribute('xmlns:xsi');
		$root->appendChild($root_attr2);

		$root_attr_text2 = $sitemapIndex->createTextNode('http://www.w3.org/2001/XMLSchema-instance');
		$root_attr2->appendChild($root_attr_text2);

		$root_attr3 = $sitemapIndex->createAttribute('xsi:schemaLocation');
		$root->appendChild($root_attr3);

		$root_attr_text3 = $sitemapIndex->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd');
		$root_attr3->appendChild($root_attr_text3);


		foreach($siteMapArr as $sitemapUrl){

				// create child element
				$sitemap = $sitemapIndex->createElement("sitemap");
				$root->appendChild($sitemap);


				$loc = $sitemapIndex->createElement("loc");
				$sitemap->appendChild($loc);
				$url_text = $sitemapIndex->createTextNode($sitemapUrl);
				$loc->appendChild($url_text);

				$lastmod = $sitemapIndex->createElement("lastmod");
				$sitemap->appendChild($lastmod);
				$lastmod_text = $sitemapIndex->createTextNode(date('c', time()));
				$lastmod->appendChild($lastmod_text);


		}

		/* write the file */
		if(!is_dir($xmlFilePath)) {
			mkdir($xmlFilePath, 0777, true);
		}
		$fh = fopen($xmlFilePath.'sitemap.xml', 'w+') or die("Can't open the sitemap file.");
		fwrite($fh, $sitemapIndex->saveXML());
		fclose($fh);

	}

	/* writes an indervidual sitemap file */
	static public function writeSiteMap(&$db, $host, $filename, $xmlFilePath, $offset, $limit, $compress = true, $tablename = 'sitemap') {

		/* setup the document */
		$sitemap = new DOMDocument;
		$sitemap->formatOutput = !$compress;

		/* create root element */
		$root = $sitemap->createElement("urlset");
		$sitemap->appendChild($root);

		$root_attr = $sitemap->createAttribute('xmlns');
		$root->appendChild($root_attr);

		$root_attr_text = $sitemap->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9');
		$root_attr->appendChild($root_attr_text);


		/*  additional headers to calidate sitemap against schema */
		$root_attr2 = $sitemap->createAttribute('xmlns:xsi');
		$root->appendChild($root_attr2);

		$root_attr_text2 = $sitemap->createTextNode('http://www.w3.org/2001/XMLSchema-instance');
		$root_attr2->appendChild($root_attr_text2);

		$root_attr3 = $sitemap->createAttribute('xsi:schemaLocation');
		$root->appendChild($root_attr3);

		$root_attr_text3 = $sitemap->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
		$root_attr3->appendChild($root_attr_text3);


		/* load the URLs */
		$urls = $db->select('select * from %l where host = %s order by date_added asc offset %i limit %i', $tablename, $host, $offset, $limit);
		foreach($urls as $urlRow){

				// create child element
				$url = $sitemap->createElement("url");
				$root->appendChild($url);


				$loc = $sitemap->createElement("loc");
				$url->appendChild($loc);
				$url_text = $sitemap->createTextNode($urlRow->s_loc);
				$loc->appendChild($url_text);

				if(!is_null($urlRow->t_last_mod)) {
					$lastmod = $sitemap->createElement("lastmod");
					$url->appendChild($lastmod);
					$lastmod_text = $sitemap->createTextNode(date('Y-m-d', $urlRow->t_last_mod));
					$lastmod->appendChild($lastmod_text);
				}

				if(!is_null($urlRow->s_change_freq)) {
					$changefreq = $sitemap->createElement("changefreq");
					$url->appendChild($changefreq);
					$changefreq_text = $sitemap->createTextNode($urlRow->s_change_freq);
					$changefreq->appendChild($changefreq_text);
				}

				if(!is_null($urlRow->f_priority)) {
					$priority = $sitemap->createElement("priority");
					$url->appendChild($priority);
					$priority_text = $sitemap->createTextNode($urlRow->f_priority);
					$priority->appendChild($priority_text);
				}

		}
		$urls = null; $url = null;

		/* prepare the file to be written to disk */
		$dataOut = $sitemap->saveXML();

		/* if compression enabled then pack it up */
		if($compress) $dataOut = gzencode($dataOut, 9);

		/* write the file */
		if(!is_dir($xmlFilePath)) {
			mkdir($xmlFilePath, 0777, true);
		}
		$fh = fopen($xmlFilePath.$filename, 'w+') or die("Can't open the sitemap file.");
		fwrite($fh, $dataOut);
		fclose($fh);

	}

	/* pings search engines with updated sitemap */
	static function pingSearchEngines($sitemapUrl) {

		$urls = array(
				sf('http://www.google.com/webmasters/sitemaps/ping?sitemap=%u', $sitemapUrl),
				sf('http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=%u', $sitemapUrl),
				sf('http://www.bing.com/webmaster/ping.aspx?siteMap=%u', $sitemapUrl),
				sf('http://submissions.ask.com/ping?sitemap=%u', $sitemapUrl)
			);

		foreach($urls as $url) {
			@file_get_contents($url);
			sleep(2);
		}
	}

}

?>
