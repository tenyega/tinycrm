<?php

namespace App\Controller;

use App\Form\PaymentType;
use App\Entity\Transaction;
use App\Service\StripeService;
use Symfony\Component\Mime\Email;
use App\Repository\OffreRepository;
use App\Repository\ClientRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'app_payment')]
    public function index(
        Request $request,
        StripeService $stripeService,
        OffreRepository $offres,
        ClientRepository $clients,
        MailerInterface $mailer,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(PaymentType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $offre = $offres->findOneBy(['id' => $data['offre']->getId()]); // Offre à vendre (titre et montant)
            $client = $clients->findOneBy(['id' => $data['client']->getId()]);
            $apiKey = $this->getParameter('STRIPE_API_KEY_SECRET'); // Clé API secrète
            
            $link = $stripeService->makePayment(
                $apiKey,
                $offre->getMontant(),
                $offre->getTitre(),
                $client->getEmail()
            );

            $email = (new Email())
                ->from('hello@tinycrm.app')
                ->to($client->getEmail())
                ->priority(Email::PRIORITY_HIGH)
                ->subject('Merci de procéder au paiement de votre offre')
                ->html('<div style="background-color: #f4f4f4; padding: 20px; text-align: center; font-family: sans-serif;">
                        <h1>Bonjour '.$client->getNomComplet().'</h1><br><br>
                        <p>Voici le lien pour effectuer le règlement de votre offre :</p><br>
                        <a href="'.$link.'" target="_blank">Payer</a><br>
                        <hr>
                        <p>Ce lien est valable pour une durée limitée.</p><br></div>
                    ');
            $mailer->send($email);
            
            // Flash message

            $transaction = new Transaction();
            $transaction->setClient($data['client'])
                        ->addOffre($offre)
                        ->setMontant($offre->getMontant())
                        ->setStatut('En attente');
            $em->persist($transaction); // EntityMangerInterface
            $em->flush();
        }

        return $this->render('payment/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/success', name: 'payment_success')]
    public function success(
        TransactionRepository $transactions,
        EntityManagerInterface $em
    ): Response
    {
        // Get transaction details from Stripe (Webhook)
        // Update transaction status

        return $this->render('payment/success.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
    
    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
