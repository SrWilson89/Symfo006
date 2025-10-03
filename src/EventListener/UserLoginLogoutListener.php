<?php
// src/EventListener/UserLoginLogoutListener.php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class UserLoginLogoutListener
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Se ejecuta después de un inicio de sesión exitoso.
     * Marca al usuario como online y actualiza el último login.
     * Además, comprueba la fecha de expiración del cliente (testat).
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var User $user */
        $user = $event->getPassport()->getUser();

        if ($user instanceof User) {
            $user->setIsOnline(true);
            $user->setLastLogin(new \DateTime());
            
            $cliente = $user->getCliente();

            // 1. Verificar si el usuario está asociado a un cliente antes de acceder a sus métodos
            if ($cliente !== null) {
                $now = new \DateTime();
                
                // 2. Verificar si la fecha de prueba (testat) del cliente ha expirado
                if ($cliente->getTestat() !== null && $cliente->getTestat() < $now) {
                    $cliente->setActivo(false);
                    // Opcional: Podrías querer desactivar también al usuario si el cliente expira.
                    // $user->setActivo(false); 
                }
            }

            $this->em->flush();
        }
    }

    /**
     * Se ejecuta después de cerrar sesión.
     * Marca al usuario como offline.
     */
    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if ($token === null) {
            return;
        }

        /** @var User $user */
        $user = $token->getUser();

        if ($user instanceof User) {
            $user->setIsOnline(false);
            $this->em->flush();
        }
    }
}
