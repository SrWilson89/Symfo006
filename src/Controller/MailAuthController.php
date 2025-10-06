<?php
// src/Controller/MailAuthController.php

namespace App\Controller;

use App\Entity\Cliente;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security; // O use Symfony\Bundle\SecurityBundle\Security;

class MailAuthController extends AbstractController
{
    #[Route('/mail/configure', name: 'app_mail_auth_configure')]
    public function configure(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // 1. Verificar si el usuario está autenticado
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // 2. Intentar obtener la entidad Cliente a través de la relación del objeto User
        $client = $user->getCliente();

        // Manejo si $client es null
        if (!$client instanceof Cliente) {
            // En lugar de throw, agregar mensaje flash y redirigir
            $this->addFlash('error', 'No se ha encontrado el cliente asociado al usuario. Por favor, contacta al administrador.');
            return $this->redirectToRoute('app_index'); // O a una página de error personalizada
        }

        // ... resto de la lógica de configuración de mail usando $client

        return $this->render('mail_auth/configure.html.twig', [
            'client' => $client,
            // ...
        ]);
    }
}