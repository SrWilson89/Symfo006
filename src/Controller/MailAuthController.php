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

        // 1. Verificar si el usuario está autenticado (aunque el error 404 sugiere que sí lo está, pero falta el cliente)
        if (!$user) {
            // Este caso es poco probable si estás viendo la 404 del cliente, pero es una buena práctica.
            return $this->redirectToRoute('app_login'); 
        }

        // 2. Intentar obtener la entidad Cliente a través de la relación del objeto User
        $client = $user->getCliente();

        // Esta es la línea 35 donde se lanza la excepción si $client es null
        if (!$client instanceof Cliente) {
            throw $this->createNotFoundException('No se ha encontrado el cliente asociado al usuario.');
        }

        // ... resto de la lógica de configuración de mail usando $client

        return $this->render('mail_auth/configure.html.twig', [
            'client' => $client,
            // ...
        ]);
    }
}
