<?php
// src/Controller/MainController.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClientesRepository;
use App\Repository\UserRepository;
use App\Repository\TareasRepository;
use App\Repository\HilosRepository;
use App\Repository\PresupuestosRepository;
use App\Entity\Tareas;
use Psr\Log\LoggerInterface;

final class MainController extends SuperController
{
    #[Route('/', name: 'app_index')]
    public function main(
        ClientesRepository $clienteRepository,
        UserRepository $userRepository,
        TareasRepository $tareaRepository,
        HilosRepository $hilosRepository,
        PresupuestosRepository $presupuestoRepository,
        Request $rq,
        LoggerInterface $logger
    ): Response {
        $user = $this->getUser();
        $super = $user ? $user->getSuper() : null;

        $logger->debug('Usuario autenticado: ' . ($user ? $user->getEmail() : 'No autenticado'));
        $logger->debug('Super: ' . ($super !== null ? $super : 'No definido'));

        $filters = [];
        $opentasks = [];
        $hilos = [];
        $resume = [
            'countopentasks' => 0,
            'sales' => 0,
            'onlinePercentage' => 0,
            'customerSupport' => 0,
            'newCustomers' => 0,
            'online' => 0,
            'grafico' => [
                'labels' => [],
                'newCustomers' => [],
                'newUsers' => [],
                'sales' => [],
                'newTasks' => [],
            ],
        ];

        $this->globals['nombre'] = 'Dashboard';
        $this->template = 'index';

        // Generar etiquetas de los últimos 12 meses
        $labels = [];
        $now = new \DateTime('first day of this month');
        $tempNow = clone $now;
        for ($i = 11; $i >= 0; $i--) {
            $labels[] = $tempNow->modify("-$i months")->format('Y-m');
            $tempNow->modify("+$i months");
        }
        $resume['grafico']['labels'] = $labels;

        // Normalizador para rellenar meses vacíos con 0
        $normalize = fn($data) => array_map(fn($m) => $data[$m] ?? 0, $labels);

        if ($rq->getMethod() === 'POST') {
            $filters = $rq->request->all('filters', []);
        }

        if (!$user) {
            $this->addFlash('error', 'Debes iniciar sesión para ver el dashboard.');
            return $this->redirectToRoute('app_login');
        }

        switch ($super) {
            case 0: // Administrador
            case 1: // Usuario con privilegios
                $this->template = 'index';
                $resume['sales'] = $presupuestoRepository->getTotalVentasUltimas24Horas() ?? 0;
                $resume['newCustomers'] = $clienteRepository->countClientesUltimas24Horas() ?? 0;
                $resume['online'] = $userRepository->countUsersConectados() ?? 0;
                $resume['onlinePercentage'] = $userRepository->getPorcentajeUsersConectados() ?? 0;
                $resume['grafico']['newCustomers'] = $normalize($clienteRepository->countByMonthLastYear() ?? []);
                $resume['grafico']['newUsers'] = $normalize($userRepository->countByMonthLastYear() ?? []);
                $resume['grafico']['sales'] = $normalize($presupuestoRepository->sumByMonthLastYear() ?? []);
                $opentasks = $tareaRepository->findTareasNoFinalizadas($user) ?? [];
                $resume['countopentasks'] = count($opentasks);
                $hilos = $hilosRepository->findByTareas($opentasks) ?? [];
                $resume['grafico']['newTasks'] = $normalize($tareaRepository->countByMonthLastYear() ?? []);
                break;

            case 2: // Usuario normal
                $this->template = 'index';
                $opentasks = $tareaRepository->findTareasNoFinalizadas($user) ?? [];
                $resume['countopentasks'] = count($opentasks);
                $hilos = $hilosRepository->findByTareas($opentasks) ?? [];
                $resume['newCustomers'] = $clienteRepository->countClientesUltimas24Horas() ?? 0;
                $resume['online'] = $userRepository->countUsersConectados() ?? 0;
                $resume['onlinePercentage'] = $userRepository->getPorcentajeUsersConectados() ?? 0;
                break;

            default:
                $this->addFlash('error', 'Rol de usuario no válido.');
                return $this->redirectToRoute('app_login');
        }

        $pendingTasks = count($opentasks);
        $tasks = array_map(
            fn($t) => ['nombre' => $t->getTitulo(), 'progreso' => $t->getEstado()->getNombre() === 'finalizado' ? 100 : rand(10, 90)],
            $opentasks
        );
        $unreadMessages = 4; // Simulado, ajustar con lógica real
        $messages = [
            ['sender' => ['nombre' => 'Joseph Doe'], 'preview' => 'Lorem ipsum...'],
            ['sender' => ['nombre' => 'Joseph Junior'], 'preview' => 'Truncated message...'],
        ];
        $alerts = [
            ['title' => 'Server is Down!', 'time' => 'Just now'],
            ['title' => 'User Locked', 'time' => '15 minutes ago'],
        ];
        $upcomingTasks = array_map(
            fn($t) => ['fecha' => $t->getFechaini() ?? new \DateTime(), 'nombre' => $t->getTitulo()],
            $opentasks
        );
        $teamMembers = $userRepository->findBy(['cliente' => $user->getCliente()]) ?? [];

        return $this->render('index.html.twig', [
            'resume' => $resume,
            'globals' => $this->globals,
            'template' => $this->template,
            'super' => $super,
            'hilos' => $hilos,
            'user' => $user,
            'filters' => $filters,
            'opentasks' => $opentasks,
            'pendingTasks' => $pendingTasks,
            'tasks' => $tasks,
            'unreadMessages' => $unreadMessages,
            'messages' => $messages,
            'alerts' => $alerts,
            'upcomingTasks' => $upcomingTasks,
            'teamMembers' => array_map(
                fn($m) => ['nombre' => $m->getNombre(), 'apellidos' => $m->getApellidos(), 'estado' => $m->getActivo() ? 'Activo' : 'Inactivo'],
                $teamMembers
            ),
        ]);
    }
}