<?php

require_once( DIR_SYSTEM . 'IfthenpayPayshop.php');


class ControllerExtensionPaymentIfthenpayPayshop extends Controller {

	public function index() {
		$this->load->language('extension/payment/ifthenpaypayshop');

		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/ifthenpaypayshop', $data);
	}

	public function confirm() {

		$json = array();

		if ($this->session->data['payment_method']['code'] == 'ifthenpaypayshop') {
			$this->load->model('checkout/order');
            $this->load->model('extension/payment/ifthenpaypayshop');
            $ifthenpayPayshop = new IfthenpayPayshop();

            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $orderValue = $this->currency->format($order_info['total'],  $order_info['currency_code'], 
            $order_info['currency_value'], false);
            $validade = $this->config->get('payment_ifthenpaypayshop_payshopValidade');
            
            if ((int)$this->model_extension_payment_ifthenpaypayshop->countOrder($this->session->data['order_id']) === 0) {
                $result = $ifthenpayPayshop->makePayment(
                    $this->config->get('payment_ifthenpaypayshop_payshopKey'),
                    $this->session->data['order_id'],
                    $orderValue, 
                    $validade
                );
                
                if ($result->Code === '0') {
                    $comment = $ifthenpayPayshop->renderPayshopPaymentTable(
                        $ifthenpayPayshop->formatReferencia($result->Reference), 
                        $orderValue, 
                        $ifthenpayPayshop->makeValidade($validade)
                    );
                } else {
                    $comment = '<strong>Erro:</strong> ' . $result->Message . '';
                    $this->model_extension_payment_ifthenpaypayshop->insertOrder($order_info['order_id'], $result); 
                }
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_ifthenpaypayshop_order_status_id'), $comment, true);

                $this->model_extension_payment_ifthenpaypayshop->insertOrder($order_info['order_id'], $validade, $result);
                $this->session->data['payment_method']['comment'] = $comment;
                
			    $json['redirect'] = $this->url->link('checkout/success');
            }
        
			//$teste = $this->url->link('common/home');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}

	public function callback()
	{
		$this->load->model('checkout/order');
		$order = $this->model_checkout_order->getOrder($this->request->get['idcliente']);
		$chaveAntiPishing = $this->config->get('payment_ifthenpaypayshop_ap');
		$this->load->model('extension/payment/ifthenpaypayshop');
		if ($this->model_extension_payment_ifthenpaypayshop->processCallback($order, $this->request, $chaveAntiPishing)) {
			$this->model_checkout_order->addOrderHistory($order['order_id'], $this->config->get('payment_ifthenpaypayshop_order_status_complete_id'), date("d-m-Y H:m:s"), true);
			echo "Encomenda PAGA";
			http_response_code(200);
		}
	}
}
?>