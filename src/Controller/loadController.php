<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Clientes;
use App\Entity\User;
use App\Entity\Estados;
use App\Entity\Tareas;
use App\Entity\Hilos;
use App\Entity\Productos;
use App\Entity\Presupuestos;
use App\Entity\Detalles;

use Doctrine\ORM\EntityManagerInterface;
//composer require fakerphp/faker
//https://fakerphp.github.io
use Faker\Factory as FakerFactory;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class loadController extends AbstractController
{
    private EntityManagerInterface $em;
    private const BATCH_SIZE = 100; // Ajusta tamaño del lote
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/load', name: 'app_dummy')]
    public function load(): Response
    {
        $faker = FakerFactory::create('es_ES');

        
        

       // Vaciar tablas
        $this->clearAll();


        // Crear clientes en lotes
        for ($i = 1; $i <= 10; $i++) {
            $customer = new Clientes();
            $customer->setNombre($faker->company);
            $customer->setCif($faker->vat);
            $customer->setDireccion($faker->address);
            $customer->setCodigoPostal($faker->postcode);
            $customer->setLocalidad($faker->city);
            $customer->setProvincia($faker->state);
            $customer->setPais('España');
            $customer->setFechCreacion($faker->dateTimeBetween('-1 year', 'now'));;
            $customer->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
            $customer->setActivo(1);
            $customer->setNotas($faker->text(100));

            $this->em->persist($customer);

            if (($i % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        $customers = $this->em->getRepository(Clientes::class,)->findAll();
        foreach ($customers as $customer){
            $plus30 = new \DateTime();
            $plus30->add(new \DateInterval('P30D'));
            $customer->setTestat($plus30);
            $this->em->persist($customer);
        }
        // Flush y clear final para los que quedan
        $this->em->flush();
        $this->em->clear();

        // Crear un usuario principal independiente (sin cliente)
        $cliente = $this->em->getRepository(Clientes::class,)->findOneById(1);

        $mainUser = new User();
        $mainUser->setNombre('Ivan');
        $mainUser->setCliente($cliente);
        $mainUser->setApellidos('De Rivia');
        $mainUser->setEmail('info@mail.es');
        $mainUser->setTelefono('659365124');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $mainUser,
            '123456'
        );
        $mainUser->setPassword($hashedPassword);
        $mainUser->setSuper(0);
        $mainUser->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));
        $mainUser->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
        $mainUser->setActivo(1);
        $mainUser->setRoles(['ROLE_ADMIN', 'ROLE_CUSTOMER', 'ROLE_USER']);
        $this->em->persist($mainUser);
        $this->em->flush();
        $this->em->clear();

        // Obtener IDs de clientes para asociar usuarios
        $connection = $this->em->getConnection();
        $sql = 'SELECT id FROM clientes';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $clienteIds = $resultSet->fetchAllAssociative();

        $count = 0;
        foreach ($clienteIds as $row) {
            // Obtener referencia gestionada a cliente sin cargar toda la entidad
            $cliente = $this->em->getReference(Clientes::class, $row['id']);

            // Crear usuario jefe
            $boss = new User();
            $boss->setNombre($faker->name);
            $boss->setApellidos($faker->lastname);
            $boss->setNif($this->generarDni());
            $boss->setEmail($faker->email);
            $boss->setTelefono($faker->phoneNumber);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $boss,
                '123456'
                );
            $boss->setPassword($hashedPassword);
            $boss->setSuper(1);
            $boss->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));
            $boss->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
            $boss->setActivo(rand(0,1));
            $boss->setIsOnline(rand(0,1));
            $boss->setRoles(['ROLE_CUSTOMER', 'ROLE_USER']); 
            $boss->setCliente($cliente);
            $this->em->persist($boss);

            // Crear usuarios normales asociados
            for ($i = 0; $i < rand(0,5); $i++) {
                $user = new User();
                $user->setNombre($faker->name);
                $user->setApellidos($faker->lastname);
                $user->setNif($this->generarDni());
                $user->setEmail($faker->email);
                $user->setTelefono($faker->phoneNumber);
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $user,
                    '123456'
                );
                $user->setPassword($hashedPassword);
                $user->setSuper(2);
                $user->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));
                $user->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                $user->setActivo(rand(0,1));
                $user->setIsOnline(rand(0,1));
                $user->setRoles(['ROLE_USER']);
                $user->setCliente($cliente);
                $this->em->persist($user);
            }

            $count++;
            if (($count % self::BATCH_SIZE) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }
        $this->em->flush();
        $this->em->clear();

        $connection = $this->em->getConnection();
        $sql = 'SELECT id FROM clientes';
        $stmt = $connection->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $clienteIds = $resultSet->fetchAllAssociative();

        foreach ($clienteIds as $row) {
            $cliente = $this->em->getReference(Clientes::class, $row['id']);
            
                
            $estado1 = New Estados();  
            $estado1->setNombre('activo');
            $estado1->setColor($faker->hexColor());
            $estado1->setCliente($cliente);
            $this->em->persist($estado1);

            $estado2 = New Estados();  
            $estado2->setNombre('inactivo');
            $estado2->setColor($faker->hexColor());
            $estado2->setCliente($cliente);
            $this->em->persist($estado2);

            $estado3 = New Estados();  
            $estado3->setNombre('pendiente');
            $estado3->setColor($faker->hexColor());
            $estado3->setCliente($cliente);
            $this->em->persist($estado3);

            $estado4 = New Estados();  
            $estado4->setNombre('finalizado');
            $estado4->setColor($faker->hexColor());
            $estado4->setCliente($cliente);
            $estado4->setFin(true);
            $this->em->persist($estado4);
            


            $this->em->flush();
            $this->em->clear();
        }
    
        /*
        * 1) Carga todos los clientes
        * 2) Hacer un foreach de clientes
        * 3) coges los usuarios de ese cliente
        * 4) Crea la tarea y asignas 1 tarea a cada usuario (de ese cliente)
        */

        $clientes = $this->em->getRepository(Clientes::class)->findAll();

        foreach ($clientes as $cliente) {
            $usuarios = $cliente->getUsuarios();
            $estados = $cliente->getEstados();

            if (count($estados) > 0)
            {
                $tarea = new Tareas();
                $tarea->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));
                $tarea->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                $tarea->setEstado($estados[rand(0,3)]);
                $tarea->setTitulo($faker->sentence());
                $tarea->setNotas($faker->text(100));
                $tarea->setUsuario($usuarios[0]);
                $this->em->persist($tarea);

                foreach ($usuarios as $usuario) {
                    $hilo = new Hilos();
                    $hilo->setTarea($tarea);
                    $hilo->setUsuario($usuario);
                    $hilo->setNotas($faker->text(100));
                    $this->em->persist($hilo);        
                }
            }
        }
        

        $this->em->flush();
        $this->em->clear();

         
            $producto1 = new Productos();
            $producto1->setNombre('Semanal');
            $producto1->setPrecio(7.99);
            $producto1->setIva(21);
            $producto1->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));;
            $producto1->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
            $this->em->persist($producto1);

            
            $producto2 = new Productos();
            $producto2->setNombre('Mensual');
            $producto2->setPrecio(35.99);
            $producto2->setIva(21);
            $producto2->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));;
            $producto2->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
            $this->em->persist($producto2);


            $producto3 = new Productos();
            $producto3->setNombre('Anual');
            $producto3->setPrecio(69.99);
            $producto3->setIva(21);
            $producto3->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));;
            $producto3->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
            $this->em->persist($producto3);

            
            $this->em->flush();
            $this->em->clear();

            $productos = $this->em->getRepository(Productos::class)->findAll();

            foreach ($productos as $producto) {
                $detalle = new Detalles();
            $detalle->setProducto($producto);
            $detalle->setPrecio($faker->randomFloat(2, 5, 100));
            $detalle->setIva($faker->randomFloat(2, 5, 100));
            $detalle->setCantidad(rand(0, 10));
            $this->em->persist($detalle);
            }
            $this->em->flush();
            $this->em->clear();
            
        
        

            $detalles = $this->em->getRepository(Detalles::class)->findAll();

            foreach ($detalles as $detalle){
            $presupuesto1 = New Presupuestos();  
            $presupuesto1->setDireccion($faker->address);
            $presupuesto1->setFecha($faker->dateTimeBetween('-1 year', 'now'));
            $presupuesto1->setTipo(rand(0, 1));
            $presupuesto1->setDetalle($detalle);
            $presupuesto1->setNumref(1);
            $presupuesto1->setEstado('Abierto');
            $this->em->persist($presupuesto1);

            $presupuesto2 = New Presupuestos();  
            $presupuesto2->setDireccion($faker->address);
            $presupuesto2->setFecha($faker->dateTimeBetween('-1 year', 'now'));
            $presupuesto2->setTipo(rand(0, 1));
            $presupuesto2->setDetalle($detalle);
            $presupuesto2->setNumref(2);
            $presupuesto2->setEstado('Cerrado');
            $this->em->persist($presupuesto2);

            $presupuesto3 = New Presupuestos();  
            $presupuesto3->setDireccion($faker->address);
            $presupuesto3->setFecha($faker->dateTimeBetween('-1 year', 'now'));
            $presupuesto3->setTipo(rand(0, 1));
            $presupuesto3->setDetalle($detalle);
            $presupuesto3->setNumref(3);
            $presupuesto3->setEstado('Facturado');
            $this->em->persist($presupuesto3);

            }
            $this->em->flush();
            $this->em->clear();



            
            

        

        

    
        return new Response('Datos cargados correctamente.');
}


    public function clearAll(): void
    {
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        // Obtener todas las entidades mapeadas
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();

        // Deshabilitar temporalmente las restricciones FK (MySQL ejemplo)
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($metaData as $meta) {
            $tableName = $meta->getTableName();

            // Truncar tabla con SQL nativo
            $sql = $platform->getTruncateTableSQL($tableName, true);
            $connection->executeStatement($sql);
        }

        // Volver a habilitar FK
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    function generarDni(): string
    {
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $numero = str_pad(strval(random_int(0, 99999999)), 8, '0', STR_PAD_LEFT);
        $letra = $letras[intval($numero) % 23];
        return $numero . $letra;
    }
}

