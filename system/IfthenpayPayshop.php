<?php

require_once(dirname(__FILE__). '/PayshopHtml.php');
require_once(dirname(__FILE__). '/PayshopSql.php');

class IfthenpayPayshop {

    use PayshopHtml, PayshopSql;
    
    private static $api = 'https://ifthenpay.com/api/payshop/get';

    public function makeValidade($validade) {
        return (new DateTime(date("Ymd")))->modify('+' . $validade . 'day')
          ->format('Ymd');
    }

    public function convertValidade($validade)
    {
        return date('d-m-Y',strtotime($validade));
    }

    public static function formatReferencia($referencia)
    {
        return substr($referencia, 0, 3) . ' ' . substr($referencia, 3, 3) . ' ' . substr($referencia, 6, 3) . ' ' . substr($referencia, 9, 4);
    }

    public function makePayment($payshopKey, $order_id, $valor, $validade) 
    {
        //$validade = isset($validade) ? $validade : self::makeValidade($validade);
        
      // Get cURL resource
      $curl = curl_init();

      // Set some options - we are passing in a useragent too here
      curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => self::$api . '?payshopkey=' . $payshopKey . '&id=' . $order_id . '&valor=' . $valor . '&validade=' . self::makeValidade($validade),
        CURLOPT_USERAGENT => 'Ifthenpay Payshop Client'
      ));

      // Send the request & save response to $resp
      $resp = curl_exec($curl);
      // Close request to clear up some resources
      curl_close($curl);

      return json_decode($resp);
    }
    
    public function checkCallback($antiPhishingDb, $chave, $referencia, $idCliente, $idTransacao, $valor, $estado, $order, $payshopDatabaseRow)
    {
      if ($chave === $antiPhishingDb) {
          if ($idCliente === $order['order_id']) {
              if (round($valor, 2) === round($order['total'], 2)) {
                if ($idTransacao === $payshopDatabaseRow['id_transacao']) {
                    if ($referencia === $payshopDatabaseRow['referencia']) {
                        if (strtolower($estado) === 'pago') {
                            return true;
                        } else {
                            echo 'Encomenda não está paga';
                            http_response_code(403);
                        }
                    }
                } else {
                    echo 'Id de transação é inválido';
                    http_response_code(403);        
                }
              } else {
                echo 'Valor da encomenda não é válido';
                http_response_code(404);
              }
          } else {
            echo 'Encomenda inválida';
            http_response_code(404);      
          }

      } else {
        echo 'Chave anti-pishing inválida';
        http_response_code(403);
      }  
    }

    public function validation($key, $validade)
	{
        $error = array('emptyKey' => false, 'invalidKey' => false, 'validade' => false);
	    if (!$key) {
            $error['emptyKey'] = true;
        } else if (strlen($key) !== 10) {
            $error['invalidKey'] = true;
        } 
        if ((int)$validade < 0) {
            $error['validade'] = true;
      }
      return $error;
	}
}
