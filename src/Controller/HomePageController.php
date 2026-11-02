<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_welcome')]
    public function index(TranslatorInterface $translator): Response
    {

        $CurentDate = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        return $this->render('home_page/index.html.twig', [
            'current_date' => $CurentDate->format('d/m/Y'),
        ]);
    }
    #[Route('/cv', name: 'app_cv')]
    public function CV(): Response
    {
        return $this->render('home_page/CV.html.twig');
    }
}
