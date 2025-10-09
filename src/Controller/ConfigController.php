<?php
// src/Controller/ConfigController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
    // Puedes reusar app_profile si está en otro controller, o redefinirla si es necesario.
    
    // Ruta para las notificaciones
    #[Route('/config/notifications', name: 'app_config_notifications')]
    public function notifications(): Response
    {
        return $this->render('config/notifications.html.twig', [
            // ... variables si las necesitas
        ]);
    }
}