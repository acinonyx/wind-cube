<?
/* ----------------------- AWMN BGP Links ----------------------- */
/*              feel free to edit the code to meet your needs :-) */
/*                   writen by Cha0s (cha0s@cha0s.gr) (2008-2009) */
/*  parts of code where used from Looking Glass & Nister's BGPMap */
/* -------------------------------------------------------------- */
//Uncomment for normal error reporting
error_reporting(E_ALL & ~E_NOTICE);
//Uncomment for full error reporting
//error_reporting(E_ALL);


define("ROOT_PATH","/var/www/html/wind/");
require ("../../config/config.php");

//Connect to MySQL Server
$db = mysql_connect($config['db']['server'], $config['db']['username'], $config['db']['password']);
//Select Database
mysql_select_db($config['db']['database'], $db);


echo "\n".logtime() . " [DB] -> Truncating Temporary Links Table for fresh data...\n";
mysql_query ( "TRUNCATE TABLE live_links_temp ", $db);


function bgppaths2array ($router, $PRINT=TRUE){

	global $db;

	$command = "show ip bgp";
	$address = $router["address"];

	if (!empty ($router["password"])){
		$password = $router["password"];
	}else{
		$password = 'awmn';
	}

	$link = @fsockopen ($address, $router["port"], $errno, $errstr, 1);
	if (!$link){
		if ($PRINT == TRUE){
			echo  logtime() . ' [ERROR] -> NO LINK FOR '.$address."!\n";
		}
		//mysql_query("UPDATE `nodes_routers` SET `status` = 'inactive' WHERE `id` = '".$router["id"]."' ", $db);
		return FALSE;
	}

	socket_set_timeout ($link, 5);

	$readbuf = '';
	$continue = FALSE;

	while (!feof($link)) {
		$readbuf = fread ($link, 8192);
		if (strstr($readbuf, 'User Access Verification')){
			if ($PRINT == TRUE){
				echo  logtime() . " [BGP] -> Sending Password...\n";
			}
			fputs ($link, "{$password}\n");
		}elseif (strstr($readbuf, 'Password:')){
			fputs ($link, "\n");
			fputs ($link, "\n");
			if ($PRINT == TRUE){
				echo  logtime() . ' [ERROR] -> INVALID PASSWORD FOR '.$address."!\n";
			}
			//mysql_query("UPDATE `nodes_routers` SET `status` = 'inactive' WHERE `id` = '".$router["id"]."' ", $db);
			fclose ($link);
			return FALSE;
		}else{
			$continue = TRUE;
			break;
		}
	}

	if ($continue == TRUE){

		if ($PRINT == TRUE){
		echo  logtime() . " [BGP] -> Sending Command...\n";
		}
		fputs ($link, "terminal length 0\n{$command}\n");

		// let daemon print bulk of records uninterrupted
		if (empty ($argument)){
			sleep (1);
		}

		fputs ($link, "quit\n");

		while (!feof ($link)){
			$readbuf = $readbuf . fgets ($link, 256);
		}

		$start = strpos ($readbuf, $command);
		$len = strpos ($readbuf, "quit") - $start;

		while ($readbuf[$start + $len] != "\n") {
			$len--;
		}

		$BGPDATA = substr($readbuf, $start, $len);
		$BGPLINES = explode ("\n", $BGPDATA);
		//echo $BGPDATA;

		fclose ($link);

		return $BGPLINES;
	}else{
		return FALSE;
	}
}


function logtime (){
	return "[" . date("M d H:i:s") . "]";
}


function detect_prepends ($AS1, $AS2, $AS_PATH, $IS_PREPEND, $PRINT, $ROUTERAS){

	global $db, $IS_PREPEND;
	
	if ($AS_PATH[$AS1] == $AS_PATH[$AS2]){
		$ASM1 = $AS1 - 1;
		
		//print_r ($AS_PATH);
		$IS_PREPEND = TRUE;
		detect_prepends ($ASM1, $AS2, $AS_PATH, $IS_PREPEND, $PRINT, $ROUTERAS);
	}elseif ($IS_PREPEND == TRUE){
		if ($AS1 == '-1'){
			$AS_PATH[$AS1] = $ROUTERAS;
		}

		if ($PRINT == TRUE){
			echo  logtime() . " [BGP] -> PREPEND DETECTED - Ignoring Link ". $AS_PATH[$AS1+1] ."-". $AS_PATH[$AS2] ." originated by ". $AS_PATH[$AS1] ."\n";
		}

		$INSERT_PREPEND = mysql_query ("INSERT INTO live_prepends (nodeid, parent_nodeid) VALUES ('". $AS_PATH[$AS2] ."', '". $AS_PATH[$AS1] ."' ) ",$db);

		return "PREPEND";

	}else{
		return 'NOPREPEND';
	}

}

//Utility Function to detect wheather the ASes to come are inside a BGP Confederation.
function detect_confed ($AS, $MODE, $PRINT=FALSE){

	global $CONFED;

	if ($MODE == 'start'){

		if (strstr($AS, '(')){
			if ($PRINT == TRUE){
				echo  logtime() . "[BGP] --> CONFED STARTED!\n";
			}
			return TRUE;
		}else{
			return $CONFED;
		}
	}

	if ($MODE == 'end'){

		if (strstr($AS, ')')){
			if ($PRINT == TRUE){
				echo  logtime() . " [BGP] --> CONFED END!\n";
			}
			return FALSE;
		}else{
		return $CONFED;
		}
	}

}


//Utility Function to INSERT or UPDATE database.
function add2db ($AS1, $AS2, $PRINT=FALSE){
	global $db;

	if ($AS1 == $AS2){
		echo  "\n\n" .logtime() . " [ERROR] -> SOMETHING WENT BAD! $AS1 - $AS2 shouldn't be sent here!\n\n";
	}

	$SELECT_LINK_a = mysql_query ("SELECT id FROM links WHERE node_id = '" . $AS1 ."' AND peer_node_id = '" . $AS2 ."'", $db);
	$SELECT_LINK_b = mysql_query ("SELECT id FROM links WHERE node_id = '" . $AS2 ."' AND peer_node_id = '" . $AS1 ."'", $db);
	
	$SELECT_LINK_DOWN_a = mysql_query ("SELECT id FROM links WHERE node_id = '" . $AS1 ."' AND peer_node_id = '" . $AS2 ."' AND live = 'inactive' ", $db);
	$SELECT_LINK_DOWN_b = mysql_query ("SELECT id FROM links WHERE node_id = '" . $AS2 ."' AND peer_node_id = '" . $AS1 ."' AND live = 'inactive' ", $db);

	if (mysql_num_rows($SELECT_LINK_a) == 0 && mysql_num_rows($SELECT_LINK_b) == 0 ){

		$INSERT_a = mysql_query ("INSERT INTO links
					( node_id, peer_node_id, date_in, status, live, ssid, type, protocol )
					VALUES
					( '" . $AS1 ."', '" . $AS2 . "', '".date_now()."', 'active', 'active', 'awmn-".$AS1."-".$AS2."', 'p2p', 'IEEE 802.11a' )", $db);

		$INSERT_b = mysql_query ("INSERT INTO links
					( node_id, peer_node_id, date_in, status, live, ssid, type, protocol )
					VALUES
					( '" . $AS2 ."', '" . $AS1 . "', '".date_now()."', 'active', 'active', 'awmn-".$AS1."-".$AS2."', 'p2p', 'IEEE 802.11a' )", $db);

		if ($INSERT_a && $INSERT_b && $PRINT == TRUE){
			echo  logtime() . " [LINK] -> '" . $AS1 ."-" . $AS2 . "' successfuly inserted.\n";
		}

	}elseif (mysql_num_rows($SELECT_LINK_DOWN_a) > '0' && mysql_num_rows($SELECT_LINK_DOWN_b) > '0' ){

		$UPDATE_a = mysql_query (  "UPDATE links  SET  status='active', live='active' WHERE node_id = '" . $AS1 ."' AND peer_node_id = '" . $AS2 ."' ", $db);
		$UPDATE_b = mysql_query (  "UPDATE links  SET  status='active', live='active' WHERE node_id = '" . $AS2 ."' AND peer_node_id = '" . $AS1 ."' ", $db);
		
		if ($UPDATE_a && $UPDATE_b && $PRINT == TRUE){
			echo  logtime() . " [LINK] -> '" . $AS1 ."-" . $AS2 . "' successfuly updated.\n";
		}

	}
/*
	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM links WHERE node1 = '" . $AS2 ."' AND node2 = '" . $AS1 ."' AND state = 'down' ", $db);
	if (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
		if (mysql_query (  "UPDATE links  SET  `date` = UNIX_TIMESTAMP( ), state='up', byrouter=".$ROUTERAS."  WHERE node1 = '" . $AS2 ."' AND node2 = '" . $AS1 ."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " [LINK] -> '" . $AS2 ."-" . $AS1 . "' successfuly updated.\n";
			}
		}
	}
*/
}

//Utility Function to INSERT or UPDATE database.
function add2tempdb ($AS1, $AS2, $PRINT=FALSE){
	global $db;
	mysql_query (  "INSERT INTO live_links_temp  ( node1, node2) VALUES ( '" . $AS1 ."', '" . $AS2 . "' )", $db);
}

function date_now() {
	return date("Y-m-d H:i:s");
}


###################################################
######## BEGIN GATHERING DATA FROM ROUTERS ########
###################################################

//$SELECT_ROUTERS = mysql_query("SELECT Name, Address, Service, Password FROM routers WHERE Active = '1' ORDER BY id ASC", $db);

$SELECT_ROUTERS = mysql_query ("
		SELECT nodes_routers.id, nodes.name, nodes.id AS nodes__id, ip_addresses.ip, nodes_routers.port,  nodes_routers.password 
		FROM nodes_routers
		LEFT JOIN nodes on nodes_routers.node_id = nodes.id
		LEFT JOIN ip_addresses ON ip_addresses.id = nodes_routers.ip_id 
		WHERE nodes_routers.status = 'active'
		ORDER BY nodes_routers.id ASC", $db);
		//print_r($ROUTERS);

$ROUTERS_TOTAL = mysql_num_rows($SELECT_ROUTERS);
$RO = 0;
$r = 0;
//$PAIRS = FALSE;
while ($ROUTERS = mysql_fetch_array($SELECT_ROUTERS)){

	$data = array();
	$mpos = '';
	$npos = '';
	$RO++;

	$router["id"]    = $ROUTERS['id'];
	$router["address"]  = long2ip($ROUTERS['ip']);
	$router["port"]  = $ROUTERS['port'];
	$router["password"] = $ROUTERS['password'];
	$ROUTERAS = $ROUTERS['nodes__id'];
	
	echo  "\n" . logtime() . " [ROUTER ".$RO."/".$ROUTERS_TOTAL."] -> Reading BGP Table from router " . $ROUTERS['name'] ." #".$ROUTERAS. " (".$router["address"].":".$router["port"].")\n";
	$BGPLINES = bgppaths2array($router);
	echo logtime() . " [BGP] -> Got Data, going to processing...\n";
	//print_r($BGPLINES);

	$lineno = count($BGPLINES);

	$bestrouteno = 0;
	$bestroute = Array();

	$nodeset=Array();
	$m = 0;
	$routerlabel = 'local router ID is ';

	for ($n=0;$n<$lineno;$n++) {
		$buffer = $BGPLINES[$n];
		$rpos = strpos($buffer,$routerlabel);
		if ($rpos===false) {}else{
			$rp = $rpos + strlen($routerlabel);
			$rl = strlen($buffer);
			$routerid = trim(substr($buffer,$rp,$rl));
		}
		$pos = strpos($buffer,'Network');
		if ($pos===false) {}else{
			$npos = strpos($buffer,'Network');
			$hpos = strpos($buffer,'Next Hop');
			$ppos = strpos($buffer,'Path');
		}
		if ($buffer[0]=='*') {
			$sl = strlen($buffer);
			$NextHop = trim(substr($buffer, $hpos, $mpos-$hpos));
			if (($NextHop=='0.0.0.0')||($NextHop=='')) {}else{
				$data[$m]['Network'] = trim(substr($buffer, $npos, $hpos-$npos));
				$data[$m]['pathstr'] = trim(substr($buffer, $ppos));
				$m++;
			}
		}
	}

	###################################################
	####### PROCESS GATHERED DATA FROM ROUTERS ########
	###################################################
	$bgproutes = count ($data);
	for ( $i=0; $i< $bgproutes; $i++ )  {
		$ases = explode (" ", $data[$i]['pathstr'] );
		//print_r($ases);
		$CONFED = FALSE;
		$PREFIX_ASES = count($ases);
		for ( $e=0; $e< $PREFIX_ASES; $e++ )  {
			$ep1 = $e + 1;
			$em1 = $e - 1;
			if ($PREFIX_ASES <= 20){
				if (( $ases[$e] != 'i' || $ases[$e] != '' ) ){
					if ( ($ases[$ep1]  == 'i'  || $ases[$ep1] == '?' || stristr($ases[$ep1], '.') ) && $ases[$em1] == ''){
						$CONFED = detect_confed($ases[$e], 'start', TRUE);
						if ($CONFED == FALSE){
							add2db($ROUTERAS, $ases[$e], TRUE);
							add2tempdb($ROUTERAS, $ases[$e], TRUE);
						}else{
							echo  logtime() . " [LINK] --> IN CONFED - Ignoring AS ".$ases[$e]."\n";
						}
						$CONFED = detect_confed($ases[$e], 'end', TRUE);

					}elseif ($ases[$ep1]  != '') {
						$CONFED = detect_confed($ases[$e], 'start', TRUE);
						if ($CONFED == FALSE){
							//print_r ($ases);

							$IS_PREPEND = FALSE;
							$PREPEND_CHECK = detect_prepends($e, $ep1, $ases, FALSE, FALSE, $ROUTERAS);

							if ($PREPEND_CHECK === 'NOPREPEND' ){

								if ( ( $ases[$e] == 'i' || $ases[$e] == '' || $ases[$e] == '?'  || stristr($ases[$e], '.')  ) || ( $ases[$ep1] == 'i' || $ases[$ep1] == '' || $ases[$ep1] == '?' || stristr($ases[$ep1], '.')  ) ){}else{
									add2db($ases[$e], $ases[$ep1], $ROUTERAS, TRUE);
									add2tempdb($ases[$e], $ases[$ep1], TRUE);
								}
							}
						}else{
							echo  logtime() . " [LINK] --> IN CONFED - Ignoring AS ".$ases[$e]."\n";
						}
						$CONFED = detect_confed($ases[$e], 'end', FALSE);
					}
				}
			}
		}
	}
	//reset vars
	$data     = FALSE;
	$BGPLINES = FALSE;
	$ases     = FALSE;
}



$SQL = "SELECT links.id, links.node_id, links.peer_node_id, links.live
FROM links LEFT JOIN live_links_temp ON ( (links.node_id = live_links_temp.node1 OR links.node_id = live_links_temp.node2)  AND  (links.peer_node_id = live_links_temp.node2 OR links.peer_node_id = live_links_temp.node1))
WHERE links.live ='active' AND links.type = 'p2p' AND (live_links_temp.node1 IS NULL OR live_links_temp.node2 IS NULL)";
$SELECT = mysql_query($SQL, $db);
echo mysql_error();

if (mysql_num_rows($SELECT) ){

	echo  logtime() . " -> DISABLING NON ACTIVE LINKS.\n";
	$t = 0;
	while ($DAT = mysql_fetch_array($SELECT)){
		$IDs[$t] =  $DAT['id'];
		echo  logtime() . " [LINK] --> ".$DAT['node_id'] . "-" . $DAT['peer_node_id']." is set to be disabled.\n";
		$t++;
	}

	mysql_query("UPDATE links SET live = 'inactive' WHERE id IN (".join (",", $IDs).")", $db);
	echo  logtime() . " [LINK] -->  Disabled non active links.\n\n";
}


echo  logtime() . " ---> DATA GATHERING COMPLETE!\n\n\n";


?>
