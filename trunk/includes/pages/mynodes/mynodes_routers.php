<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005 Nikolaos Nikalexis <winner@cube.gr>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 dated June, 1991.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

class mynodes_routers {

	var $tpl;
	
	function mynodes_routers() {
		
	}
	
	function form_routers() {
		global $db, $vars;
		$form_routers = new form(array('FORM_NAME' => 'form_routers'));
		$form_routers->db_data(
			'nodes_routers.id, nodes_routers.node_id, nodes.id AS nodes__id, nodes_routers.ip_id, nodes_routers.password, nodes_routers.port, nodes_routers.status',
			'nodes_routers',
			'',
			'',
			"");

		//$form_routers->db_data_enum('nodes_routers.id', $db->get("id AS value, title AS output", "routers", "", "", "title ASC"));

		$ips = $db->get("ip_addresses.id AS value, ip_addresses.hostname AS hostname, ip_addresses.ip AS ip",
						"ip_addresses " .
						"INNER JOIN subnets ON subnets.node_id = ip_addresses.node_id AND ip_addresses.ip <= subnets.ip_end AND ip_addresses.ip >= subnets.ip_start", 
						"ip_addresses.node_id = ".intval(get('node'))." AND subnets.type = 'local'",
						"subnets.ip_start ASC, ip_addresses.ip ASC");
		foreach ((array) $ips as $key => $value) {
			$ips[$key]['output'] = $ips[$key]['hostname']." [".long2ip($ips[$key]['ip'])."]";
		}
		$form_routers->db_data_enum('nodes_routers.ip_id', $ips);

		$form_routers->db_data_values("nodes_routers", "id", get('router'));
		if (get('router') != 'add') {
			$form_routers->db_data_pickup('nodes_routers.node_id', "nodes", $db->get("nodes_routers.node_id AS value, CONCAT(nodes.name, ' (#', nodes.id, ')') AS output", "nodes_routers, nodes", "nodes_routers.node_id = nodes.id AND nodes_routers.id = ".get("router")));
		} else {
			$form_routers->db_data_pickup('nodes_routers.node_id', "nodes", $db->get("nodes.id AS value, CONCAT(nodes.name, ' (#', nodes.id, ')') AS output", "nodes", "nodes.id = ".get("node")));
		}
		$form_routers->db_data_remove('nodes_routers__id');
		return $form_routers;
	}
	
	function output() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name'])) return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		global $construct;
		$this->tpl['routers_method'] = (get('router') == 'add' ? 'add' : 'edit' );
		$this->tpl['form_routers'] = $construct->form($this->form_routers(), __FILE__);
		return template($this->tpl, __FILE__);
	}

	function output_onpost_form_routers() {
		global $construct, $main, $db;
		$form_routers = $this->form_routers();
		$router = get('router');
		$ret = TRUE;
		$_POST['nodes_routers__url'] = url_fix($_POST['nodes_routers__url']);
		$ret = $form_routers->db_set(array(), "nodes_routers", "id", $router);
		
		if ($ret) {
			$main->message->set_fromlang('info', 'insert_success', makelink(array("page" => "mynodes", "node" => $_POST['nodes_routers__node_id'])));
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>