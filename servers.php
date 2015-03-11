<?php
	$pageTitle = "Servers";
	// 60 second cache
	header("Cache-Control: public, max-age=60");
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	$syncexternalJS = array( '/scripts/jquery.tablesorter.min.js', '/scripts/jquery.tablesorter.widgets.min.js' );
	include_once('includes/header.php');
	include_once('includes/GameQ.php');
	include_once('includes/paths.php');
	$Servers = file( $serversList );

?>
		<h1 class="text-center">Game Servers</h1>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">About</h3>
			</header>
			<div class="panel-body">
				<p>Below you can find a list of our currently active game servers. Where possible, live information for the current map, number of players, etc. will be shown.</p>
				<p>If you would like to host a server for SteamLUG, or help manage our existing ones,<br>please contact <a href = 'http://twitter.com/steamlug'>@steamlug</a>.</p>
			</div>
		</article>
		<article class="panel panel-default">
			<header class="panel-heading">
				<h3 class="panel-title">Servers</h3>
			</header>
			<div class="panel-body panel-body-table">
				<table id="servers" class="table table-striped table-hover tablesorter">
					<thead>
						<tr>
							<th>
							<th><i class="fa fa-shield"></i>
							<th><i class="fa fa-lock"></i>
							<th>Game
							<th>Servers
							<th>Players
							<th>Map
							<th>
						</tr>
					</thead>
					<tbody>
<?php
	flush(); /* visitor should get better indication that the page is actually loading now */

	foreach ( $Servers as $Server )
	{
		if ( strlen( $Server ) > 11 and strrpos($Server, '#', -strlen($Server)) === False ) {
			list ( $ServerHost[], $Ports[], $GameType[] ) = preg_split ( "/(:|,)/", $Server );
		}
	}
	$gq = new GameQ();
	foreach ( $ServerHost as $Index => $Host)
	{
		$gq->addServer(array(
			'type' => trim($GameType[$Index]),
			'host' => trim($Host) . ":" . trim($Ports[$Index]),
			));
	}

	$results = $gq->setOption('timeout', 1)
				->setFilter('normalise')
				->requestData();

	foreach ( $results as $id => $data )
	{
		if (!$data['gq_online'])
		{
			echo <<<SERVERSTRING
			<tr class="unresponsive">
				<td></td>
				<td></td>
				<td></td>
				<td><em>Server Unresponsive</em></td>
				<td><em>{$data['gq_address']}:{$data['gq_port']}</em></td>
				<td><em>0 ⁄ 0</em></td>
				<td><em>N/A</em></td>
				<td><span class="text-danger"><i class="fa fa-circle-o"></i></span></td>
			</tr>
SERVERSTRING;
		} else {
			/* this block of code should be better… TODO it please */
			$serverLoc	= geoip_country_code_by_name($data['gq_address']);
			$serverSec	= !empty($data['secure']) ? '<i class="fa fa-shield"></i>' : '';
			$serverPass	= !empty($data['gq_password']) ? '<i class="fa fa-lock"></i>' : '';
			$serverDesc	= !empty($data['gq_name']) ? $data['gq_name'] : '';
			$serverNum	= (!empty($data['gq_numplayers']) ? $data['gq_numplayers'] : '0') . ' ⁄ ' . $data['gq_maxplayers'];
			$serverMap	= substr( $data['gq_mapname'], 0, 18 );
			$connectPort	= (!empty($data['port']) ? $data['port'] : (isset($data['gameport']) ? $data['gameport'] : $data['gq_port']));
			$serverHost	= $data['gq_address'] . ":" . $connectPort;
			echo <<<SERVERSTRING
			<tr>
				<td><span style="display:none">{$serverLoc}</span><img src="/images/flags/{$serverLoc}.png" alt="Hosted in {$serverLoc}"></td>
				<td>{$serverSec}</td>
				<td>{$serverPass}</td>
				<td>{$serverDesc}</td>
				<td><a href="steam://connect/{$serverHost}">{$data['gq_hostname']}</a>
				<td>{$serverNum}</td>
				<td>{$serverMap}</td>
				<td><span class="text-success"><i class="fa fa-circle"></i></span></td>
			</tr>
SERVERSTRING;
		}
	}
?>
					</tbody>
				</table>
			</div>
		</article>
<script>
		$(document).ready
		(
$(function() {

  $.extend($.tablesorter.themes.bootstrap, {
	table		: '',
    caption		: 'caption',
    header		: 'bootstrap-header',	// give the header a gradient background
    sortNone	: 'fa fa-unsorted',
    sortAsc		: 'fa fa-sort-up',		// includes classes for Bootstrap v2 & v3
    sortDesc	: 'fa fa-sort-down',	// includes classes for Bootstrap v2 & v3
  });
  $("#servers").tablesorter({
    theme : "bootstrap",
    headerTemplate : '{content} {icon}',
    widgets : [ "uitheme" ],
	headers: {
		1: { sorter: false },
		2: { sorter: false },
	},
	sortList: [[7,1],[5,1],[0,0],[4,0]]
  })
}));
</script>
<?php include_once('includes/footer.php'); ?>
