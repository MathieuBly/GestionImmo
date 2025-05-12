<?php

namespace App\Controller;

use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entreprise')]
final class CompanyController extends AbstractController
{
    #[Route('', name: 'app_company_dashboard')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        // Vérifie que l'utilisateur a bien une entreprise
        if (!$user || !$user->getCompany()) {
            throw $this->createAccessDeniedException("Vous n'êtes lié à aucune entreprise.");
        }

        $company = $user->getCompany();

        return $this->render('company/dashboard.html.twig', [
            'company' => $company,
            'properties' => $company->getProperties(),
            'employees' => $company->getUsers(),
        ]);
    }
}
