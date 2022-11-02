<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CallAjaxController extends AbstractController
{
    /**
     * @Route("/call/ajax/operationsListe", name="app_call_ajax")
     */
    public function index(CommandeRepository $repository, NormalizerInterface $normalizer): JsonResponse
    {
        $commandeProfil = $repository->searchBar($this->getUser());
        $result = $normalizer->normalize($commandeProfil, 'json', ['groups' => 'show_product']);
        return $this->json(
            $result,
            200,
            ['Content-Type', 'application/json']
        );
    }
}
