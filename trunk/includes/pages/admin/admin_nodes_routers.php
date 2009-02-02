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


class admin_nodes_routers {

	var $tpl;
	
	function admin_nodes_routers() {
	}

	function form_search_routers_edit() {
		global $db;
		$form_search_routers_edit = new form(array('FORM_NAME' => 'form_search_routers_edit'));
		$form_search_routers_edit->db_data('nodes.id, nodes.name');
		//$form_search_routers_edit->db_data_enum('nodes_routers.ip_id', $db->get("id AS value, ip AS output", "ip_addresses", "", "", ""));
		$form_search_routers_edit->db_data_search();
		return $form_search_routers_edit;
	}

	function table_routers() {
		global $construct, $db, $main, $lang;
		$form_search_routers_edit = $this->form_search_routers_edit();
		$where = $form_search_routers_edit->db_data_where();
		$table_routers = new table(array('TABLE_NAME' => 'table_routers', 'FORM_NAME' => 'table_routers'));
		$table_routers->db_data(
			'nodes_routers.id, nodes.name, nodes.id AS nodes__id, ip_addresses.ip, nodes_routers.port, nodes_routers.date_in,  nodes_routers.status',
			'nodes_routers
			LEFT JOIN nodes on nodes_routers.node_id = nodes.id
			LEFT JOIN ip_addresses ON ip_addresses.id = nodes_routers.ip_id',
			$where,
			'',
			"nodes_routers.date_in DESC");
		$table_routers->db_data_search($form_search_routers_edit);
		foreach( (array) $table_routers->data as $key => $value) {
			if ($key != 0) {
				if ($table_routers->data[$key]['ip']) {
					$table_routers->data[$key]['ip'] = long2ip($table_routers->data[$key]['ip']);
				}
				$table_routers->data[$key]['name'] .= " (#".$table_routers->data[$key]['nodes__id'].")";
				$table_routers->info['EDIT'][$key] = makelink(array("page" => "mynodes", "subpage" => "routers", "node" => $table_routers->data[$key]['nodes__id'], "router" => $table_routers->data[$key]['id']));
			}
		}
		$table_routers->info['EDIT_COLUMN'] = 'name';
		$table_routers->db_data_translate('nodes_routers__status');
		$table_routers->db_data_multichoice('nodes_routers', 'id');
		$table_routers->info['MULTICHOICE_LABEL'] = 'delete';
		$table_routers->db_data_remove('id', 'nodes__id');
		return $table_routers;
	}
	
	function output() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name'])) return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		global $construct;
		$this->tpl['form_search_routers_edit'] = $construct->form($this->form_search_routers_edit(), __FILE__);
		$this->tpl['table_routers'] = $construct->table($this->table_routers(), __FILE__);
		return template($this->tpl, __FILE__);
	}

	function output_onpost_table_routers() {
		global $db, $main;
		$ret = TRUE;
		foreach( (array) $_POST['id'] as $key => $value) {
			$ret = $ret && $db->del("nodes_routers", "id = '".$value."'");
		}
		if ($ret) {
			$main->message->set_fromlang('info', 'delete_success', makelink("",TRUE));
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>