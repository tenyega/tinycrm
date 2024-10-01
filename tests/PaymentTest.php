<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentTest extends WebTestCase
{
    /**
     * Ce test nous montre comment mettre en place la vérification du statut de la réponse
     * suite à une requête HTTP. Ici, on vérifie que le statut de la réponse est bien 200.
     * Cependant, cette route étant protégée par un firewall, on doit se connecter avant
     * de se rendre sur cette page. Pour cela, on utilise fait appel au repository user
     * puis on le connecte avec la méthode loginUser() du client HTTP de WebTestCase
     */
    public function testPaymentRouteWhenLoggedIn(): void
    {
        self::ensureKernelShutdown(); // On coupe le kernel s'il est déjà démarré
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $adminUser = $userRepository->findOneBy(['email' => 'admin@admin.fr']);
        $client->loginUser($adminUser);
        $client->request('GET', '/payment/'); // Pensez au "/" final si pas de paramètres dans l'URL
        $this->assertResponseStatusCodeSame(200);
    }
}
