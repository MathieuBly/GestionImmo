<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Company;
use App\Form\RegistrationType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/registercompany', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Créer une entreprise liée
            $company = new Company();
            $company->setName($form->get('companyName')->getData());
            $company->setCreatedAt(new \DateTimeImmutable());

            // Associer l'entreprise à l'utilisateur via setIdCompany
            $user->setIdCompany($company);
            $user->setRoles(['ROLE_USER_ADMIN']);

            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Persister
            $em->persist($company);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_property_index');
        }

        return $this->render('company/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
