<?php

trait PayshopHtml {
    private static $logoPath = 'https://ifthenpay.com/img/payshop.png';
    private static $cttPath = 'https://ifthenpay.com/img/ctt.png';
    private $modulePath;
    private $moduleFormAction;

    

    public function renderCallbackInfo($modulePath, $antiPhishingKey)
    {
        if (isset($antiPhishingKey)) {
            return 
            '<div class="card">
                <div class="card-header">
                    <h2>Dados Callback</h3>
                </div>
                <div class="card-body">
                    <h4 class="card-title">Url Callback:</h3>
                    <p class="card-text-url">'. $modulePath . '&chave=[CHAVE_ANTI_PHISHING]&idcliente=[ID_CLIENTE]&idtransacao=[ID_TRANSACAO]&referencia=[REFERENCIA]&valor=[VALOR]&estado=[ESTADO]</p>
                    <h4 class="card-title">Chave Anti-Phishing:</h3>
                    <p class="card-text-chave">' . $antiPhishingKey .'</p>
                </div>
            </div>';
        }      
    }
    
    public function renderPayshopPaymentTable($referencia, $valor, $validade)
    {
        return 
        '<div class="div-payshopPayment-table">
            <table class="payshopPayment-table">
                <tr>
                    <td class="payshop-table-td-img-first"><img src="' . self::$logoPath . '"/></td>
                    <td class="payshop-table-td-img-second">
                        <img src="' . self::$cttPath . '"/>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="payshop-table-td-referencia">Referência Payshop: <b>' . $referencia . '</b></td>
                </tr>
                <tr>
                    <td colspan="2" class="payshop-table-td-valor">Valor: <b>' . $valor . '€</b></td>
                </tr>
                    <tr>
                        <td colspan="2" class="payshop-table-td-validade">Data limite: <b>' . $this->convertValidade($validade) . '</b></td>
                    </tr>
                <tr class="payshop-table-tr-info">
                    <td colspan="2" class="payshop-table-td-info">Pagável em qualquer Agente Payshop, Loja CTT ou Posto de Correios</td>
                </tr>
            </table>
        </div>';
    }
}