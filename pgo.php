<?php

## pgo.php
## Run through URLs to produce profiling data for PHP PGO builds
## Usage: pgo.php [<SERVER>|printnum] [PORT]

$p1count = 40;
$p2count = 10;
$debug = 1;

$SERVER = 'localhost';
$PORT = '80';
if ( isset($argv[1]) )  {
	$SERVER = $argv[1];
}
if ( isset($argv[2]) )  {
	$PORT = $argv[2];
}

// Apps  ##################################################################################################### //
$apps = array( 'drupal', 'wordpress', 'mediawiki', 'joomla', 'phpbb3', 'symfony2', 'other' );
$drupalP1 = array(
	'http://'.$SERVER.':'.$PORT.'/drupal/index.php',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=node/1',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=blog/1',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=node/2',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=forum',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=forum/1',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=node/3'
);
$drupalP2 = array(
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=user/login&destination=node/1%23comment-form',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=user/register&destination=node/1%23comment-form',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=user/password',
	'http://'.$SERVER.':'.$PORT.'/drupal/?q=rss.xml'
);

$wordpressP1 = array(
	'http://'.$SERVER.':'.$PORT.'/wordpress/',
	'http://'.$SERVER.':'.$PORT.'/wordpress/?p=4',
	'http://'.$SERVER.':'.$PORT.'/wordpress/?p=1',
	'http://'.$SERVER.':'.$PORT.'/wordpress/?page_id=2',
	'http://'.$SERVER.':'.$PORT.'/wordpress/?cat=1'
);
$wordpressP2 = array(
	'http://'.$SERVER.':'.$PORT.'/wordpress/wp-login.php',
	'http://'.$SERVER.':'.$PORT.'/wordpress/?m=201112'
);

$mediawikiP1 = array(
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Main_Page',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Talk%3AMain_Page',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Test_Page',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Talk%3ATest_Page&action=edit&redlink=1',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ANewPages'
);
$mediawikiP2 = array(
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Main_Page&printable=yes',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3AWhatLinksHere/Main_Page',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ARecentChangesLinked/Main_Page',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ARecentChanges',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ASearch/Current_events',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3AUserLogin',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ARecentChangesLinked',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ABrokenRedirects',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ADeadendPages',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ADoubleRedirects',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3AProtectedPages',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3AWantedPages',
	'http://'.$SERVER.':'.$PORT.'/mediawiki/index.php?title=Special%3ASpecialPages'
);

$joomlaP1 = array(
	'http://'.$SERVER.':'.$PORT.'/joomla/',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla/extensions/components/content-component/article-category-list/24-joomla',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla/extensions/components/content-component/article-category-list/8-beginners',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/sample-sites',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/parks-home',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/park-blog',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/park-blog/17-first-blog-post',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/image-gallery',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/park-links',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/image-gallery/animals',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/image-gallery/scenery',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/site-map',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/site-map/articles',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/site-map/contacts',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/site-map/weblinks',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla/extensions/components',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/getting-started',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/the-joomla-project',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/the-joomla-community',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php?format=feed&type=rss',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php?format=feed&type=atom'
);
$joomlaP2 = array(
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/login',
	'http://'.$SERVER.':'.$PORT.'/joomla/administrator/',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla/extensions/components/users-component/password-reset',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla/extensions/components/users-component/username-reminder',
	'http://'.$SERVER.':'.$PORT.'/joomla/index.php/using-joomla/extensions/components/users-component/registration-form'
);

$phpbb3P1 = array (
	'http://'.$SERVER.':'.$PORT.'/phpbb3/index.php',
	'http://'.$SERVER.':'.$PORT.'/phpbb3/viewforum.php?f=2',
	'http://'.$SERVER.':'.$PORT.'/phpbb3/viewtopic.php?f=2&t=1',
	'http://'.$SERVER.':'.$PORT.'/phpbb3/search.php?search_id=unanswered',
	'http://'.$SERVER.':'.$PORT.'/phpbb3/search.php?search_id=active_topics'
);
$phpbb3P2 = array (
	'http://'.$SERVER.':'.$PORT.'/phpbb3/ucp.php?mode=login',
	'http://'.$SERVER.':'.$PORT.'/phpbb3/ucp.php?mode=register'
);
$symfony2P1 = array (
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/pizza/list',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/pizza/create',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/order/index',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/order/list',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/order/show/12',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/pizza/list',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/customer/list',
	'http://'.$SERVER.':'.$PORT.'/symfony/web/app_dev.php/acme-pizza/pizza/update/79'
);
$symfony2P2 = array();

$otherP1 = array();
$otherP2 = array();
// ########################################################################################################### //

$total = 0;
foreach ( $apps as $app )  {
	$total += count( ${"${app}P1"} ) * $p1count;
	$total += count( ${"${app}P2"} ) * $p2count;
}
echo "Total Transactions: $total\n";
if ( isset($argv[1]) && ($argv[1] == 'printnum') )  {
	exit;
}

$total = 0;
$error = 0;
foreach ( $apps as $app )  {
	echo "Testing P1 URIs in $app\n";
	for ( $i=0; $i < $p1count; $i++ )  {
		if ( empty(${"${app}P1"}) )  {  break;  }
		foreach ( ${"${app}P1"} as $url )  {
			($debug == 1) ? print "$url\n" : '';
			$stat = file_get_contents("$url");
			if ( $stat === FALSE )  {
				echo "Error retrieving $url\n";
				$error++;
			}
			$total++;
		}
	}

	echo "Testing P2 URIs in $app\n";
	for ( $i=0; $i < $p2count; $i++ )  {
		if ( empty(${"${app}P2"}) )  {  break;  }
		foreach ( ${"${app}P2"} as $url )  {
			($debug == 1) ? print "$url\n" : '';
			$stat = file_get_contents("$url");
			if ( $stat === FALSE )  {
				echo "Error retrieving $url\n";
				$error++;
			}
			$total++;
		}
	}
}

echo "Transactions: $total, Errors: $error\n";

?>
