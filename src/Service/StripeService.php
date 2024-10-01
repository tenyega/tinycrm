<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeService
{

    public function makePayment(?string $apiKey, ?int $amount, ?string $product, ?string $email)
    {
        Stripe::setApiKey($apiKey);
        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'https://tinycrm.test';

        $checkout_session = Session::create([
            'customer_email' => $email,
            'submit_type' => 'pay',
            'billing_address_collection' => 'required',
            'line_items' => [[
                'price_data' => [ // Section des données de produit
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product, // Nom de l'offre
                    ],
                    'unit_amount' => $amount * 100, // Montant en centimes
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/payment/success',
            'cancel_url' => $YOUR_DOMAIN . '/payment/cancel',
            // 'automatic_tax' => [
            //     'enabled' => true,
            // ],
        ]);

        header("HTTP/1.1 303 See Other");
        return $checkout_session->url; // On récupère le lien de paiement
    }
}
