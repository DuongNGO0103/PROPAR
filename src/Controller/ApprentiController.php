<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Form\RegistrationOperationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApprentiController extends AbstractController
{
    /**
     * @Route("/apprenti", name="app_apprenti")
     */

    public function tableauDeBord(CommandeRepository $repository): Response
    {
        $commandeProfil = $repository->findOperationUserEncours($this->getUser());
        return $this->render('apprenti/index.html.twig', [
            'commandeProfil' => $commandeProfil,
            'controller_name' => 'ExpertController'
        ]);
    }

    /**
     * @Route("/apprenti/operations", name="app_apprenti_operations")
     */
    public function ajouterUneOperation(CommandeRepository $repository): Response
    {
        $commandeEnAttente = $repository->findBy(
            array('statut' => 'En attente'),
            array('date' => 'desc'),
            null,
            null
        );
        return $this->render('apprenti/operations.html.twig', [
            'commandeEnAttente' => $commandeEnAttente,
        ]);
    }


    /**
     * @Route("/apprenti/operations/{id}", name="apprenti_operations", methods="POST|GET")
     */
    public function comfirmerOperation(Commande $commandes = null, Request $request, EntityManagerInterface $entityManager, CommandeRepository $repository): Response
    {
        if (!$commandes) {
            $commandes = new Commande();
        }

        $form = $this->createForm(RegistrationOperationType::class, $commandes);
        $form->handleRequest($request);

        $compteurExpert = $repository->findUserCompteur($this->getUser());
        if (count($compteurExpert) < 1) {
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($commandes);
                $entityManager->flush();
                $this->addFlash("success", "La commande a bien été confirmée");
                return $this->redirectToRoute("app_apprenti");
            }
        } else {
            $this->addFlash("wrong", "Vous avez atteint le nombre maximum d'opérations ! Veuillez terminer une opération afin de pouvoir en traiter une nouvelle.");
            return $this->redirectToRoute("app_apprenti_operations");
        }
        return $this->render('apprenti/operationAjoutCommande.html.twig', [
            "commande" => $commandes,
            "form" => $form->createView(),

        ]);
    }
}