<?php
// src/Service/RedsysService.php

namespace App\Service;

// Carga la clase desde fuera de src/
require_once dirname(__DIR__, 2) . '/rest_API_PHP/ApiRedsysREST/initRedsysApi.php';

class RedsysService
{
    private string $claveSecreta;

    public function __construct(string $claveSecreta)
    {
        $this->claveSecreta = $claveSecreta;
    }

    public function generarParametros(array $datos): array
    {
        $miObj = new \apiRedsysREST(); // Usar el nombre correcto de la clase

        $miObj->setParameter("DS_MERCHANT_AMOUNT", $datos['amount']);
        $miObj->setParameter("DS_MERCHANT_ORDER", $datos['order']);
        $miObj->setParameter("DS_MERCHANT_MERCHANTCODE", $datos['merchantCode']);
        $miObj->setParameter("DS_MERCHANT_CURRENCY", $datos['currency']);
        $miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $datos['transactionType']);
        $miObj->setParameter("DS_MERCHANT_TERMINAL", $datos['terminal']);
        $miObj->setParameter("DS_MERCHANT_MERCHANTURL", $datos['merchantUrl']);
        $miObj->setParameter("DS_MERCHANT_URLOK", $datos['urlOk']);
        $miObj->setParameter("DS_MERCHANT_URLKO", $datos['urlKo']);

        // Datos codificados
        $params = $miObj->createMerchantParameters();

        // Firma
        $signature = $miObj->createMerchantSignature($this->claveSecreta);

        return [
            'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
            'Ds_MerchantParameters' => $params,
            'Ds_Signature' => $signature,
        ];
    }
}