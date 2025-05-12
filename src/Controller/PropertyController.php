<?php

namespace App\Controller;

use App\Entity\Property;
use App\Form\PropertyType;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/property')]
final class PropertyController extends AbstractController
{
    #[Route(name: 'app_property_index', methods: ['GET'])]
    public function index(PropertyRepository $propertyRepository, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $company = $this->getUser()->getIdCompany();

        $qb = $propertyRepository->createQueryBuilder('p')
            ->where('p.id_company = :company')
            ->setParameter('company', $company);

        if ($search) {
            $qb->andWhere('p.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $properties = $qb->getQuery()->getResult();

        return $this->render('property/index.html.twig', [
            'properties' => $properties,
            'search' => $search
        ]);
    }

    #[Route('/new', name: 'app_property_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $property = new Property();
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $property->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image : ' . $e->getMessage());
                }
            }

            $property->setIdCompany($this->getUser()->getIdCompany());

            $entityManager->persist($property);
            $entityManager->flush();

            return $this->redirectToRoute('app_property_index');
        }

        return $this->render('property/new.html.twig', [
            'property' => $property,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_property_show', methods: ['GET'])]
    public function show(Property $property): Response
    {
        if ($property->getIdCompany() !== $this->getUser()->getIdCompany()) {
            throw $this->createAccessDeniedException("Ce bien ne vous appartient pas.");
        }

        return $this->render('property/show.html.twig', [
            'property' => $property,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_property_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Property $property, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($property->getIdCompany() !== $this->getUser()->getIdCompany()) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas modifier ce bien.");
        }

        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $property->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l’image : ' . $e->getMessage());
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le bien a été modifié avec succès.');
            return $this->redirectToRoute('app_property_index');
        }

        return $this->render('property/edit.html.twig', [
            'form' => $form->createView(),
            'property' => $property,
        ]);
    }

    #[Route('/{id}', name: 'app_property_delete', methods: ['POST'])]
    public function delete(Request $request, Property $property, EntityManagerInterface $entityManager): Response
    {
        if ($property->getIdCompany() !== $this->getUser()->getIdCompany()) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas supprimer ce bien.");
        }

        if ($this->isCsrfTokenValid('delete' . $property->getId(), $request->get('_token'))) {
            $entityManager->remove($property);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_property_index');
    }
}
