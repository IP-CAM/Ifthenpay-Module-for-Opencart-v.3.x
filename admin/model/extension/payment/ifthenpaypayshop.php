<?php

require_once( DIR_SYSTEM . 'IfthenpayPayshop.php');

class ModelExtensionPaymentIfthenpayPayshop extends Model {

	public function install() {
		$this->db->query((new IfthenpayPayshop())->createTable(DB_PREFIX, 'order_id'));
	}

	public function uninstall() {
		$this->db->query((new IfthenpayPayshop())->deleteTable(DB_PREFIX));
	}
}