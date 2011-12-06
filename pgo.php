<?php

$p1count = 40;
$p2count = 10;
$debug = 1;

if ( isset($argv[1]) )  {
	$SERVER = $argv[1];
}
else  {
	echo "Usage: pgo.php [<server>|printnum]";
	exit;
}

// Apps  ##################################################################################################### //
$apps = array( 'drupal', 'wordpress', 'mediawiki', 'joomla', 'phpbb3', 'other' );
$drupalP1 = array(
	'http://'.$SERVER.'/drupal/index.php',
	'http://'.$SERVER.'/drupal/?q=node/1',
	'http://'.$SERVER.'/drupal/?q=blog/1',
	'http://'.$SERVER.'/drupal/?q=node/2',
	'http://'.$SERVER.'/drupal/?q=forum',
	'http://'.$SERVER.'/drupal/?q=forum/1',
	'http://'.$SERVER.'/drupal/?q=node/3'
);
$drupalP2 = array(
	'http://'.$SERVER.'/drupal/?q=user/login&destination=node/1%23comment-form',
	'http://'.$SERVER.'/drupal/?q=user/register&destination=node/1%23comment-form',
	'http://'.$SERVER.'/drupal/?q=user/password',
	'http://'.$SERVER.'/drupal/?q=rss.xml'
);

$wordpressP1 = array(
	'http://'.$SERVER.'/wordpress/',
	'http://'.$SERVER.'/wordpress/?p=4',
	'http://'.$SERVER.'/wordpress/?p=1',
	'http://'.$SERVER.'/wordpress/?page_id=2',
	'http://'.$SERVER.'/wordpress/?cat=1'
);
$wordpressP2 = array(
	'http://'.$SERVER.'/wordpress/wp-login.php',
	'http://'.$SERVER.'/wordpress/?m=201111'
);

$mediawikiP1 = array(
	'http://'.$SERVER.'/mediawiki/index.php?title=Main_Page',
	'http://'.$SERVER.'/mediawiki/index.php?title=Talk%3AMain_Page',
	'http://'.$SERVER.'/mediawiki/index.php?title=Test_Page',
	'http://'.$SERVER.'/mediawiki/index.php?title=Talk%3ATest_Page&action=edit&redlink=1',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ANewPages'
);
$mediawikiP2 = array(
	'http://'.$SERVER.'/mediawiki/index.php?title=Main_Page&printable=yes',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3AWhatLinksHere/Main_Page',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ARecentChangesLinked/Main_Page',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ARecentChanges',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ASearch/Current_events',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3AUserLogin',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ARecentChangesLinked',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ABrokenRedirects',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ADeadendPages',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ADoubleRedirects',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3AProtectedPages',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3AWantedPages',
	'http://'.$SERVER.'/mediawiki/index.php?title=Special%3ASpecialPages'
);

$joomlaP1 = array(
	'http://'.$SERVER.'/joomla/',
	'http://'.$SERVER.'/joomla/index.php/using-joomla/extensions/components/content-component/article-category-list/24-joomla',
	'http://'.$SERVER.'/joomla/index.php/using-joomla/extensions/components/content-component/article-category-list/8-beginners',
	'http://'.$SERVER.'/joomla/index.php/sample-sites',
	'http://'.$SERVER.'/joomla/index.php/parks-home',
	'http://'.$SERVER.'/joomla/index.php/park-blog',
	'http://'.$SERVER.'/joomla/index.php/park-blog/17-first-blog-post',
	'http://'.$SERVER.'/joomla/index.php/image-gallery',
	'http://'.$SERVER.'/joomla/index.php/park-links',
	'http://'.$SERVER.'/joomla/index.php/image-gallery/animals',
	'http://'.$SERVER.'/joomla/index.php/image-gallery/scenery',
	'http://'.$SERVER.'/joomla/index.php/site-map',
	'http://'.$SERVER.'/joomla/index.php/site-map/articles',
	'http://'.$SERVER.'/joomla/index.php/site-map/contacts',
	'http://'.$SERVER.'/joomla/index.php/site-map/weblinks',
	'http://'.$SERVER.'/joomla/index.php/using-joomla/extensions/components',
	'http://'.$SERVER.'/joomla/index.php/getting-started',
	'http://'.$SERVER.'/joomla/index.php/using-joomla',
	'http://'.$SERVER.'/joomla/index.php/the-joomla-project',
	'http://'.$SERVER.'/joomla/index.php/the-joomla-community',
	'http://'.$SERVER.'/joomla/index.php?format=feed&type=rss',
	'http://'.$SERVER.'/joomla/index.php?format=feed&type=atom'
);
$joomlaP2 = array(
	'http://'.$SERVER.'/joomla/index.php/login',
	'http://'.$SERVER.'/joomla/administrator/',
	'http://'.$SERVER.'/joomla/index.php/using-joomla/extensions/components/users-component/password-reset',
	'http://'.$SERVER.'/joomla/index.php/using-joomla/extensions/components/users-component/username-reminder',
	'http://'.$SERVER.'/joomla/index.php/using-joomla/extensions/components/users-component/registration-form'
);

$phpbb3P1 = array (
	'http://'.$SERVER.'/phpbb3/index.php',
	'http://'.$SERVER.'/phpbb3/viewforum.php?f=2',
	'http://'.$SERVER.'/phpbb3/viewtopic.php?f=2&t=1',
	'http://'.$SERVER.'/phpbb3/search.php?search_id=unanswered',
	'http://'.$SERVER.'/phpbb3/search.php?search_id=active_topics'
);
$phpbb3P2 = array (
	'http://'.$SERVER.'/phpbb3/ucp.php?mode=login',
	'http://'.$SERVER.'/phpbb3/ucp.php?mode=register'
);

$otherp1 = array (
	'http://'.$SERVER.'/micro_bench.php'
);
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
foreach ( $apps as $app )  {
	echo "Testing P1 URIs in $app\n";
	for ( $i=0; $i < $p1count; $i++ )  {
		if ( empty(${"${app}P1"}) )  {  break;  }
		foreach ( ${"${app}P1"} as $url )  {
			($debug == 1) ? print "$url\n" : '';
			$stat = file_get_contents("$url");
			if ( $stat === FALSE )  {
				echo "Error retrieving $url\n";
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
			}
			$total++;
		}
	}
}
echo "Transactions: $total\n";


?>