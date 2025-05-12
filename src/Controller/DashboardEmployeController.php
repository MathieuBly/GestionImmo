<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employe')]
class DashboardEmployeController extends AbstractController
{
    #[Route('', name: 'app_employe_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();

        return $this->render('employe/dashboard.html.twig', [
            'user' => $user,
            'company' => $company,
            'properties' => $company->getProperties(),
        ]);
    }
}
