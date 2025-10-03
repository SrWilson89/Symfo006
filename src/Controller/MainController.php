<?php

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

final class MainController extends SuperController
{
    /**
     * Controlador principal para el Dashboard.
     */
    #[Route('/', name: 'app_index')]
    public function main(
        ClientesRepository $clienteRepository,
        UserRepository $userRepository,
        TareasRepository $tareaRepository,
        HilosRepository $hilosRepository,
        PresupuestosRepository $presupuestoRepository,
        Request $rq,  
    ): Response {
        $user = $this->getUser();
        $super = $user->getSuper(); // 0 o 1 o 2

        $filters = [];
        $opentasks = [];
        $hilos = [];

        $this->globals['nombre'] = 'Dashboard';
        $this->template = 'index';
        $method = $rq->getMethod();

        // Generar etiquetas de los últimos 12 meses
        $labels = [];
        $now = new \DateTime('first day of this month');
        // Clonamos para evitar modificar $now original si no es necesario
        $tempNow = clone $now; 
        for ($i = 11; $i >= 0; $i--) {
            // Utilizamos modify para ir hacia atrás en el tiempo
            $labels[] = $tempNow->modify("-$i months")->format('Y-m');
            // Revertimos la modificación para la siguiente iteración
            $tempNow->modify("+$i months");
        }

        // Normalizador para rellenar meses vacíos con 0
        $normalize = fn($data) => array_map(fn($m) => $data[$m] ?? 0, $labels);

        if ($method === 'POST') {
            $filters = $rq->request->all('filters', []);
        } 

        switch ($super) {
            case 0: // Administrador
            case 1: // Usuario con privilegios (asumiendo que 1 es similar a 0 para el dashboard principal, si no, separamos la lógica)
                $this->template = 'index_admin';
                $opentasks = $tareaRepository->findTareasNoFinalizadas();
                $hilos = $hilosRepository->findByTareas($opentasks);

                $this->resume['customerSupport'] = 0;
                $this->resume['sales'] = $presupuestoRepository->countFacturasUltimas24Horas();
                $this->resume['newCustomers'] = $clienteRepository->countClientesUltimas24Horas();
                $this->resume['online'] = $userRepository->countUsersConectados();
                $this->resume['onlinePercentage'] = $userRepository->getPorcentajeUsersConectados();
                $this->resume['newTasks'] = $tareaRepository->findTareasNoFinalizadas(); // Esto parece ser redundante con $opentasks
                $this->resume['opentasks'] = $opentasks;
                $this->resume['countopentasks'] = count($opentasks);


                // Datos para el gráfico
                $this->resume['grafico']['labels'] = $labels;
                $this->resume['grafico']['newCustomers'] = $normalize($clienteRepository->countByMonthLastYear());
                $this->resume['grafico']['newUsers'] = $normalize($userRepository->countByMonthLastYear());
                $this->resume['grafico']['sales'] = $normalize($userRepository->countByMonthLastYear()); // Revisar si debería usar otro repo/método para ventas
                $this->resume['grafico']['newTasks'] = $normalize($tareaRepository->countByMonthLastYear());
                
                break;
        
            case 2: // Usuario normal
                $this->template = 'index_user';
                $opentasks = $tareaRepository->findTareasNoFinalizadas($this->getUser());
                $this->resume['countopentasks'] = count($opentasks);
                $hilos = $hilosRepository->findByTareas($opentasks);
                
                // Inicialización de otras variables si es necesario para el template 'index_user'
                $this->resume['customerSupport'] = 0;
                $this->resume['sales'] = 0;
                $this->resume['newCustomers'] = $clienteRepository->countClientesUltimas24Horas();
                $this->resume['online'] = $userRepository->countUsersConectados();
                $this->resume['onlinePercentage'] = $userRepository->getPorcentajeUsersConectados();

                break;

            default:
                // Caso por defecto (opcional)
                break;
        }

        return $this->render('index.html.twig', [
            'resume' => $this->resume,
            'globals' => $this->globals,
            'template' => $this->template,
            'super' => $super,
            'hilos' => $hilos,
            'user' => $user,
            'filters' => $filters,
            'opentasks' => $opentasks,
        ]);
    }
    
    /**
     * Ruta DUMMY temporal para evitar el error 404 de /mail-auth.
     * Esto debería eliminarse una vez que se verifique que todos los enlaces 
     * en los templates (especialmente base.html.twig) han sido corregidos y la caché limpiada.
     */
    #[Route('/mail-auth', name: 'app_mail_auth_configure_dummy', methods: ['GET'])]
    public function mailAuthDummy(): Response
    {
        // Redirigir a la página de índice para resolver el 404 sin funcionalidad.
        return $this->redirectToRoute('app_index');
    }
}
