<?php 
// src/Controller/PagoController.php

namespace App\Controller; // <--- ¡CAMBIO CLAVE AQUÍ!

use App\Service\RedsysService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PagoController extends AbstractController
{
    #[Route('/pago', name: 'app_pago_22')]
    public function pago(RedsysService $redsys): Response
    {
        $params = $redsys->generarParametros([
            'amount' => 1234, // En céntimos: 12.34 €
            'order' => uniqid(), // Código de pedido único
            'merchantCode' => '999008881', // Código FUC del comercio
            'currency' => '978', // Euros
            'transactionType' => '0',
            'terminal' => '1',
            'merchantUrl' => 'https://tusitio.com/redsys/notify',
            'urlOk' => 'https://tusitio.com/redsys/ok',
            'urlKo' => 'https://tusitio.com/redsys/ko',
        ]);

        // Puedes renderizar un formulario oculto que se envíe automáticamente
        return $this->render('pago.html.twig', [
            'redsys_url' => 'https://sis-t.redsys.es/sis/realizarPago', // entorno test
            'params' => $params,
        ]);
    }
}