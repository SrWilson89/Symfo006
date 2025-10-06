<?php
// src/Controller/EmpresaController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmpresaController extends AbstractController
{
    #[Route('/empresas/crear', name: 'app_create_empresa')]
    public function create(): Response
    {
        return $this->render('empresa/create.html.twig');
    }
}