<?php

trait PayshopSql {

    public function createTable($dbPrefix, $foreinKey) {
     return 'CREATE TABLE IF NOT EXISTS `' . $dbPrefix . 'payshop` (
        `id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_transacao` VARCHAR( 20 ),
        `referencia` VARCHAR(13),
        `validade` VARCHAR(8),
        `error` VARCHAR(250),
        `'.$foreinKey.'` INT(11) unsigned NOT NULL,
        PRIMARY KEY (`id`)
        )';   
    }

    public function deleteTable($dbPrefix)
    {
        return 'DROP TABLE IF EXISTS ' . $dbPrefix . 'payshop';
    }

    public function countReferencia($dbPrefix, $foreinKey, $order_id)
    {
        return 'SELECT COUNT(*) FROM ' . $dbPrefix . 'payshop ' . 'WHERE ' . $foreinKey . '=' . $order_id .'';
    }

    public function insertInTable($dbPrefix, $foreinKey, $order_id, $validade, $payshopResult) 
    {
        $error = $payshopResult->Code === "0" ? null : $payshopResult->Message;
        return 'INSERT INTO ' . $dbPrefix . 'payshop (id_transacao, referencia, validade, error, '. $foreinKey .') 
        VALUES ("'. $payshopResult->RequestId .'", "' . $payshopResult->Reference . '", "' .$this->makeValidade($validade) . '","' .$error . '", ' . $order_id . ')';
    }

    public function updateReferencia($dbPrefix, $foreinKey, $order_id, $payshopResult)
    {
        return 'UPDATE ' . $dbPrefix . 'payshop SET id_transacao="' . $payshopResult['id_transacao'] . '",referencia="'. $payshopResult['referencia'] .'" ,validade="' . $payshopResult['validade'] . '" WHERE ' . $foreinKey . '=' . $order_id . '';
    }
    public function getReferenciaPayshop($dbPrefix, $foreinKey, $order_id) 
    {
        return 'SELECT * FROM ' . $dbPrefix . 'payshop WHERE ' . $foreinKey . '=' . $order_id . '';
    }
}

