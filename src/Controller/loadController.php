<?php
// src/Controller/LoadController.php

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
use Faker\Factory as FakerFactory;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoadController extends AbstractController
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
            $customer->setFechCreacion($faker->dateTimeBetween('-1 year', 'now'));
            $customer->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
            $customer->setActivo(1);
            $customer->setNotas($faker->text(100));
            $customer->setChecking(true);
            $customer->setTestat($faker->dateTimeBetween('now', '+1 month'));
            $customer->setMailauth(0);
            $this->em->persist($customer);

            // Usuarios por cliente
            for ($j = 0; $j < 5; $j++) {
                $user = new User();
                $user->setNombre($faker->firstName);
                $user->setApellidos($faker->lastName);
                $user->setNif($this->generarDni());
                $user->setEmail($faker->email);
                $user->setTelefono($faker->phoneNumber);
                $user->setActivo(1);
                $user->setSuper(random_int(0, 2));
                $user->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));
                $user->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                $user->setImagen($faker->imageUrl(200, 200));
                $user->setCliente($customer);
                $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
                $user->setPassword($hashedPassword);
                $this->em->persist($user);
            }

            // Estados por cliente
            for ($k = 0; $k < 5; $k++) {
                $estado = new Estados();
                $estado->setNombre($faker->word);
                $estado->setCliente($customer);
                $estado->setColor($faker->hexColor);
                $estado->setOrden($k + 1);
                $this->em->persist($estado);
            }

            // Tareas por cliente
            for ($l = 0; $l < 20; $l++) {
                $tarea = new Tareas();
                $tarea->setTitulo($faker->sentence(3));
                $tarea->setDescripcion($faker->paragraph);
                $tarea->setEstado($faker->randomElement($this->em->getRepository(Estados::class)->findAll()));
                $tarea->setUsuario($faker->randomElement($this->em->getRepository(User::class)->findAll()));
                $tarea->setFechaCreacion($faker->dateTimeBetween('-1 year', 'now'));
                $tarea->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                $tarea->setFechaini($faker->dateTimeBetween('-1 month', 'now'));
                $tarea->setFechafin($faker->dateTimeBetween('now', '+1 month'));
                $tarea->setCliente($customer);
                $this->em->persist($tarea);

                // Hilos por tarea
                for ($m = 0; $m < 3; $m++) {
                    $hilo = new Hilos();
                    $hilo->setTarea($tarea);
                    $hilo->setUsuario($faker->randomElement($this->em->getRepository(User::class)->findAll()));
                    $hilo->setNotas($faker->paragraph);
                    $hilo->setFechaCreacion($faker->dateTimeBetween('-1 month', 'now'));
                    $hilo->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                    $this->em->persist($hilo);
                }
            }

            // Productos
            for ($n = 0; $n < 10; $n++) {
                $producto = new Productos();
                $producto->setNombre($faker->word);
                $producto->setDescripcion($faker->paragraph);
                $producto->setPrecio($faker->randomFloat(2, 10, 1000));
                $producto->setIva($faker->randomElement([4, 10, 21]));
                $producto->setImagen($faker->imageUrl(200, 200));
                $producto->setFechacreacion($faker->dateTimeBetween('-1 year', 'now'));
                $producto->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                $this->em->persist($producto);
            }

            // Detalles
            for ($o = 0; $o < 10; $o++) {
                $detalle = new Detalles();
                $detalle->setProducto($faker->randomElement($this->em->getRepository(Productos::class)->findAll()));
                $detalle->setCantidad($faker->numberBetween(1, 10));
                $detalle->setPrecio($faker->randomFloat(2, 10, 1000));
                $detalle->setIva($faker->randomElement([4, 10, 21]));
                $detalle->setDescuento($faker->randomFloat(2, 0, 50));
                $detalle->setTotal($detalle->getPrecio() * $detalle->getCantidad() * (1 - $detalle->getDescuento()/100) * (1 + $detalle->getIva()/100));
                $detalle->setFechacreacion($faker->dateTimeBetween('-1 year', 'now'));
                $detalle->setModificacion($faker->dateTimeBetween('-1 week', 'now'));
                $this->em->persist($detalle);
            }

            // Presupuestos
            $presupuesto1 = new Presupuestos();
            $presupuesto1->setDireccion($faker->address);
            $presupuesto1->setFecha($faker->dateTimeBetween('-1 year', 'now'));
            $presupuesto1->setTipo(rand(0, 1));
            $presupuesto1->setDetalle($detalle);
            $presupuesto1->setNumref(1);
            $presupuesto1->setEstado('Abierto');
            $this->em->persist($presupuesto1);

            $presupuesto2 = new Presupuestos();
            $presupuesto2->setDireccion($faker->address);
            $presupuesto2->setFecha($faker->dateTimeBetween('-1 year', 'now'));
            $presupuesto2->setTipo(rand(0, 1));
            $presupuesto2->setDetalle($detalle);
            $presupuesto2->setNumref(2);
            $presupuesto2->setEstado('Cerrado');
            $this->em->persist($presupuesto2);

            $presupuesto3 = new Presupuestos();
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