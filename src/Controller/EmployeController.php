<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/employes')]
class EmployeController extends AbstractController
{
    #[Route('', name: 'app_employe_index')]
    public function index(): Response
    {
        $company = $this->getUser()->getIdCompany(); // 🔁 Correction ici
        $employees = $company->getUsers()->filter(fn(User $user) => in_array('ROLE_USER_EMPLOYE', $user->getRoles()));

        return $this->render('employe/index.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/new', name: 'app_employe_new')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $employe = new User();
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $employe->setIdCompany($this->getUser()->getIdCompany()); // 🔁 Correction ici
            $employe->setRoles(['ROLE_USER_EMPLOYE']);
            
            $hashedPassword = $hasher->hashPassword($employe, $employe->getPassword());
            $employe->setPassword($hashedPassword);

            $em->persist($employe);
            $em->flush();

            $this->addFlash('success', 'Employé ajouté avec succès.');
            return $this->redirectToRoute('app_employe_index');
        }

        return $this->render('employe/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_employe_delete', methods: ['POST'])]
    public function delete(User $employe, EntityManagerInterface $em, Request $request): Response
    {
        if ($employe->getIdCompany() !== $this->getUser()->getIdCompany()) { // 🔁 Correction ici
            throw $this->createAccessDeniedException('Accès interdit.');
        }

        if ($this->isCsrfTokenValid('delete'.$employe->getId(), $request->request->get('_token'))) {
            $em->remove($employe);
            $em->flush();
            $this->addFlash('success', 'Employé supprimé.');
        }

        return $this->redirectToRoute('app_employe_index');
    }
}
