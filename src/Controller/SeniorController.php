<?php

namespace App\Controller;

use Dompdf\Dompdf;
use App\Entity\Commande;
use App\Service\MailerService;
use App\Repository\CommandeRepository;
use App\Form\RegistrationOperationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\RegistrationOperationTerminerType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SeniorController extends AbstractController
{
    /**
     * @Route("/senior", name="app_senior")
     */
    public function tableauDeBord(CommandeRepository $repository): Response
    {
        $commandeProfil = $repository->findOperationUserEncours($this->getUser());
        return $this->render('senior/index.html.twig', [
            'commandeProfil' => $commandeProfil,
        ]);
    }

    /**
     * @Route("/senior{id}", name="senior_operations_terminer", methods="POST|GET")
     */
    public function terminerOperation(KernelInterface $kernelInterface, MailerService $mailer, Commande $commandes = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        // stockage du template de la facure dans la variable html
        $html =  $this->renderView('pdf/basepdf.html.twig', [
            "commande" => $commandes,
        ]);
        // creation d'un nouveau pdf 
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $result = $dompdf->output();

        // creation du fichier facture.pdf dans le dossier public/pdf/
        $fs = new Filesystem();
        $FilePdf = $kernelInterface->getProjectDir() . "/public/pdf";
        $pdf = $FilePdf . "/facture.pdf";
        $fs->dumpFile($pdf, $result);

        if (!$commandes) {
            $commandes = new Commande();
        }
        //  modifie le statut, ajoute l'id de l'utilisateur actuel et insere en bdd
        $commandes->setUser($this->getUser());
        $commandes->setStatut("Terminer");

        //recuperation de l'email du client
        $email = $commandes->getClient()->getEmail();

        //insertion en bdd
        $entityManager->persist($commandes);
        $entityManager->flush();

        // Envoi de l'email au client <<<<<<<<<<<
        $mailer->sendEmail($email);

        //message de validation
        $this->addFlash("success", "L'operation est terminé, un email de confirmation a été envoyé au client.");

        //redirection vers la page actuelle
        return $this->redirectToRoute("app_senior");
    }

    /**
     * @Route("/senior/operations", name="app_senior_operations")
     */
    public function ajouterUneOperation(CommandeRepository $repository): Response
    {
        $commandeEnAttente = $repository->findBy(
            array('statut' => 'En attente'),
            array('date' => 'desc'),
            30,
            null
        );
        return $this->render('senior/operations.html.twig', [
            'commandeEnAttente' => $commandeEnAttente,
        ]);
    }

    /**
     * @Route("/senior/operationsliste", name="app_liste_operations_senior")
     */
    public function listerMesOperations(CommandeRepository $repository): Response
    {
        $commandeProfil = $repository->findBy(
            array('user' =>  $this->getUser()),
            array('id' => 'desc'),
            10,
            null
        );
        return $this->render('senior/operationsListe.html.twig', [
            'commandeProfil' => $commandeProfil,
        ]);
    }

    /**
     * @Route("/senior/operations/{id}", name="senior_operations", methods="POST|GET")
     */
    public function comfirmerOperation(Commande $commandes = null, Request $request, EntityManagerInterface $entityManager, CommandeRepository $repository): Response
    {
        if (!$commandes) {
            $commandes = new Commande();
        }
        // recuperation de donné grace a la methode findUserCompteur qui indique combien de commande sont 'en cours'
        //  de l'utilisateur actuel = $this->getUser() 
        $compteurCommande = $repository->findUserCompteur($this->getUser());
        // compteurCommande indique donc un tableau avec les reponses des commande = en cours 
        // la fonction count est utilisé afin de convertir le resultat en tableau en integer afin de comparer
        $compteurCommande = count($compteurCommande);
        // on compare donc cette donnée a 5 pour l'expert 
        if ($compteurCommande < 3) {
            //modification en settant l'utilisateur actuel et le statut en cours 
            $commandes->setUser($this->getUser());
            $commandes->setStatut("En cours");
            $entityManager->persist($commandes);
            $entityManager->flush();

            //message de validation 
            $this->addFlash("successs", "L'operation a bien été ajouté.");
            //redirection
            return $this->redirectToRoute("app_senior_operations");
        } else {
            //si la comparaion n'est pas plus petit que 5 alors elle affiche message d'erreur et redirige
            $this->addFlash("wrong", "Vous avez atteint le nombre maximum d'operations ! veuillez terminer une opération afin de pouvoir en traiter une nouvelle.");
            return $this->redirectToRoute("app_senior_operations");
        }
    }
}
