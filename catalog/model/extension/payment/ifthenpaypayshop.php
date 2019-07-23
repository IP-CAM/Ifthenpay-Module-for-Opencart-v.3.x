<?php

require_once( DIR_SYSTEM . 'IfthenpayPayshop.php');


class ModelExtensionPaymentIfthenpayPayshop extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/ifthenpaypayshop');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_ifthenpaymbway_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('ifthenpaypayshop_total') > 0 && $this->config->get('ifthenpaypayshop_total') > $total) {
            $status = true;
        } elseif (!$this->config->get('payment_ifthenpaypayshop_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'ifthenpaypayshop',
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get('payment_ifthenpaypayshop_sort_order')
            );
        }
        return $method_data;
    }

    public function countOrder($orderId) {
		$this->db->query((new IfthenpayPayshop())->countReferencia(DB_PREFIX, 'order_id', $orderId) );
    }
    
    public function insertOrder($orderId, $validade, $result) {
		$this->db->query((new IfthenpayPayshop())->insertInTable(DB_PREFIX, 'order_id', $orderId, $validade, $result));
    }

    public function processCallback($order, $request, $antiPhishingDb)
    {
        $ifthenpayPayshop = (new IfthenpayPayshop());
        $result = $this->db->query($ifthenpayPayshop->getReferenciaPayshop(DB_PREFIX, 'order_id', $order['order_id']));
        return $ifthenpayPayshop->checkCallback(
            $antiPhishingDb,
            $request->get['chave'],
            $request->get['referencia'],
            $request->get['idcliente'],
            $request->get['idtransacao'],
            $request->get['valor'],
            $request->get['estado'],
            $order,
            $result->row
        );
    }
}
?>