<?php
require_once( DIR_SYSTEM . 'IfthenpayPayshop.php');

class ControllerExtensionPaymentIfthenpayPayshop extends Controller {
    private $error = array();
    private $ifthenpayPayshop;

    private function checkError($data, $arrayKeys)
    {
      foreach ($arrayKeys as $key => $value) {
        if (isset($this->error[$value])) {
          $data['error'] = $this->error[$value];
        } else {
            $data['error'] = '';
        }
      }
      return $data;
    }

    private function setValuesFromAdmin($data, $arrayKeys)
    {
      foreach ($arrayKeys as $key => $value) {
        if (isset($this->request->post[$value])) {
          $data[$value] = $this->request->post[$value];
        } else {
          $data[$value] = $this->config->get($value);
        }
      }
      return $data;
    }
    
    private function getUrlCallback()
    {
      return ($this->config->get('config_secure') ? rtrim(HTTP_CATALOG, '/') : rtrim(HTTPS_CATALOG, '/')) . '/index.php?route=extension/payment/ifthenpaypayshop/callback';
    }
    
    public function index() {
      $this->ifthenpayPayshop = new IfthenpayPayshop();
      $this->load->language('extension/payment/ifthenpaypayshop');
      $this->document->setTitle($this->language->get('heading_title'));
      $this->load->model('setting/setting');
      $this->load->model('extension/payment/ifthenpaypayshop');
      $this->model_extension_payment_ifthenpaypayshop->install();
      
      /* one can load JS like that: */    
      $this->document->addStyle('view/stylesheet/ifthenpayPayshop.min.css');
      $this->document->addStyle('view/stylesheet/admin.min.css');

      if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
  
              $post_info = $this->request->post;
              
              $this->model_setting_setting->editSetting('payment_ifthenpaypayshop',  $post_info);
  
              $this->session->data['success'] = $this->language->get('text_success');
  
        //$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        $this->response->redirect($this->url->link('extension/payment/ifthenpaypayshop', 'user_token=' . $this->session->data['user_token'], true));
      }

      $data['heading_title'] = $this->language->get('heading_title');
		  $data['text_enabled'] = $this->language->get('text_enabled');
      $data['text_disabled'] = $this->language->get('text_disabled');
      $data['text_all_zones'] = $this->language->get('text_all_zones');
      $data['text_success'] = (isset($this->session->data['success']) ? $this->session->data['success']:"");
      $data['entry_order_status'] = $this->language->get('entry_order_status');
      $data['entry_order_status_complete'] = $this->language->get('entry_order_status_complete');
      $data['entry_payshopKey'] = $this->language->get('entry_payshopKey');
      $data['entry_payshopValidade'] = $this->language->get('entry_payshopValidade');
      $data['payshopValidadeHelp'] = $this->language->get('payshopValidadeHelp');
      $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
      $data['entry_status'] = $this->language->get('entry_status');
      $data['entry_sort_order'] = $this->language->get('entry_sort_order');
      $data['entry_payshopCallback'] = $this->language->get('entry_payshopCallback');
      $data['entry_payshopUrl'] = $this->language->get('entry_payshopUrl');
      $data['entry_ap'] = $this->language->get('entry_ap');

      //send callback stuff
      $data['entry_button_send_cb'] = $this->language->get('entry_button_send_cb');
      $data['button_send_cb'] = $this->language->get('button_send_cb');
      $data['text_send_cb'] = $this->language->get('text_send_cb');
      $data['button_save'] = $this->language->get('button_save');
      $data['button_cancel'] = $this->language->get('button_cancel');
      $data['tab_general'] = $this->language->get('tab_general');

      //user token
      $data['user_token'] = $this->session->data['user_token'];
      $data['email_cb_sended'] = $this->config->get('payment_ifthenpaypayshop_cb_sent');
      $data['email_confirmation'] = $this->language->get('email_confirmation');
      $data['email_sended_info'] = $this->language->get('email_sended_info');
      $data['email_success_info'] = $this->language->get('email_success_info');
      $data['email_error_info'] = $this->language->get('email_error_info');


      //check if errors exist
      $data = $this->checkError($data, array('warning', 'payshopEmptyKey', 'payshopInvalidKey', 'payshopValidade'));

      $data['breadcrumbs'] = array();
      $data['breadcrumbs'][] = array(
          'text' => $this->language->get('text_home'),
          'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
      );
      $data['breadcrumbs'][] = array(
          'text' => $this->language->get('text_extension'),
          'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
      );
      $data['breadcrumbs'][] = array(
          'text' => $this->language->get('heading_title'),
          'href' => $this->url->link('extension/payment/ifthenpaypayshop', 'user_token=' . $this->session->data['user_token'], true)
      );

      $data['action'] = $this->url->link('extension/payment/ifthenpaypayshop', 'user_token=' . $this->session->data['user_token'], true);
      $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
      
      //set values from admin
      $data = $this->setValuesFromAdmin($data, array(
        'payment_ifthenpaypayshop_payshopKey',
        'payment_ifthenpaypayshop_payshopValidade',
        'payment_ifthenpaypayshop_order_status_id',
        'payment_ifthenpaypayshop_order_status_complete_id',
        'payment_ifthenpaypayshop_geo_zone_id',
        'payment_ifthenpaypayshop_status',
        'payment_ifthenpaypayshop_sort_order',
      ));
            
      $this->load->model('localisation/order_status');
      $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            
      $this->load->model('localisation/geo_zone');
      $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
      
      //set callback info
      $data['payment_ifthenpaypayshop_show_ap'] = true;
      $data['payment_ifthenpaypayshop_cb_sent'] = $this->config->get('payment_ifthenpaypayshop_cb_sent');
      
      if (isset($this->request->post['payment_ifthenpaypayshop_ap'])) {
        $data['payment_ifthenpaypayshop_ap'] = $this->request->post['payment_ifthenpaypayshop_ap'];
      } else {
        $anti_phishing = $this->config->get('payment_ifthenpaypayshop_ap');
        if(empty($anti_phishing)) {
          $anti_phishing = substr(hash('sha512', $this->config->get('config_name') . $this->config->get('config_title') . $this->config->get('config_owner') . $this->config->get('config_email') . date("D M d, Y G:i")), -50);
          $data['payment_ifthenpaypayshop_ap'] = $anti_phishing;
          $this->model_setting_setting->editSetting('payment_ifthenpaypayshop',  $data);
          $data['payment_ifthenpaypayshop_show_ap'] = !is_null($this->config->get('payment_ifthenpaypayshop_payshopKey')) ? true : false;
        } else {
          $data['payment_ifthenpaypayshop_ap'] = $anti_phishing;
        }
      }
      $data['callbackActivateInfo'] = $this->ifthenpayPayshop->renderCallbackInfo($this->getUrlCallback(), $anti_phishing);
      //$data['payment_ifthenpaypayshop_url'] = $this->getUrlCallback();
      
      $data['header'] = $this->load->controller('common/header');
      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');
      
      $this->response->setOutput($this->load->view('extension/payment/ifthenpayshop', $data));
  }

  public function install() {
		$this->load->model('extension/payment/ifthenpaypayshop');
    $this->model_extension_payment_ifthenpaypayshop->install();
	}

	public function uninstall() {
		$this->load->model('extension/payment/ifthenpaypayshop');
		$this->model_extension_payment_ifthenpaypayshop->uninstall();
  }
  
  private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/ifthenpaypayshop')) {
			$this->error['warning'] = $this->language->get('error_permission');
    }
    
    $result = $this->ifthenpayPayshop->validation(
      $this->request->post['payment_ifthenpaypayshop_payshopKey'], 
      $this->request->post['payment_ifthenpaypayshop_payshopValidade']);

      if ($result['emptyKey']) {
        $this->error['payshopEmptyKey'] = $this->language->get('error_payshopEmptyKey');
      } else if ($result['invalidKey']) {
        $this->error['payshopInvalidKey'] = $this->language->get('payshopInvalidKey');
      } 
      
      if ($result['validade']) {
        $this->error['payshopValidade'] = $this->language->get('payshopValidade');

      }
		return !$this->error;
  }
  
  public function activatecallback(){
		$json = array();
		$json['sended']=false;
		//load settings model
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('payment_ifthenpaypayshop'); 
		$payshop_key = $settings['payment_ifthenpaypayshop_payshopKey'];
		$url_cb = $this->getUrlCallback() . '&chave=[CHAVE_ANTI_PHISHING]&idcliente=[ID_CLIENTE]&idtransacao=[ID_TRANSACAO]&referencia=[REFERENCIA]&valor=[VALOR]&estado=[ESTADO]';
		$ap_key_cb = $settings['payment_ifthenpaypayshop_ap'];
		if(!empty($payshop_key) && !empty($url_cb) && !empty($ap_key_cb)){
			$store_name = $this->config->get('config_name');
			$msg = "Ativar Callback Payshop para loja Opencart \n\n";
			$msg .= "Payshop KEY: $payshop_key \n\n";
			$msg .= "Chave Anti-Phishing: $ap_key_cb \n\n";
			$msg .= "Url Callback:: $url_cb \n\n\n\n\n\n";
			$msg .= "Pedido enviado pelo sistema OpenCart da loja [$store_name]";
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
			$mail->setSubject("Ativar Callback Payshop");
			$mail->setText($msg);
			$mail->setTo("callback@ifthenpay.com");
			$mail->send();
			//atualizar settings
			$settings["payment_ifthenpaypayshop_cb_sent"] = true;
			$this->model_setting_setting->editSetting('payment_ifthenpaypayshop',  $settings);
			$json['sended']=true;
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?>
