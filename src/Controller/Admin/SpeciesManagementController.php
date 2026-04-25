<?php

namespace App\Controller\Admin;

use App\Entity\Species;
use App\Entity\Race;
use App\Form\SpeciesType;
use App\Form\RaceType;
use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/species-management')]
#[IsGranted('ROLE_ADMIN')]
final class SpeciesManagementController extends AbstractController
{
    #[Route('', name: 'admin_species_management')]
    public function index(SpeciesRepository $speciesRepository, RaceRepository $raceRepository): Response
    {
        $species = $speciesRepository->findAllWithRaceCount();
        $races = $raceRepository->findAllWithCharacterCount();

        return $this->render('admin/species_management/index.html.twig', [
            'species' => $species,
            'races' => $races,
        ]);
    }

    #[Route('/species/new', name: 'admin_species_new')]
    public function newSpecies(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $species = new Species();
        $form = $this->createForm(SpeciesType::class, $species);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle icon file upload
            $iconFile = $form->get('iconFile')->getData();
            if ($iconFile) {
                $newFilename = $this->handleFileUpload($iconFile, $slugger, 'species');
                if ($newFilename) {
                    $species->setIcon($newFilename);
                }
            }
            
            $entityManager->persist($species);
            $entityManager->flush();
            
            $this->addFlash('success', 'Species "' . $species->getName() . '" has been created successfully.');
            
            return $this->redirectToRoute('admin_species_management');
        }

        return $this->render('admin/species_management/new_species.html.twig', [
            'form' => $form,
            'species' => $species,
        ]);
    }

    #[Route('/species/{id}/edit', name: 'admin_species_edit', requirements: ['id' => '\d+'])]
    public function editSpecies(Species $species, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(SpeciesType::class, $species);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle icon file upload
            $iconFile = $form->get('iconFile')->getData();
            if ($iconFile) {
                // Delete old icon if it exists
                if ($species->getIcon()) {
                    $oldIconPath = $this->getParameter('kernel.project_dir') . '/public/images/species/' . $species->getIcon();
                    if (file_exists($oldIconPath)) {
                        unlink($oldIconPath);
                    }
                }
                
                $newFilename = $this->handleFileUpload($iconFile, $slugger, 'species');
                if ($newFilename) {
                    $species->setIcon($newFilename);
                }
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Species "' . $species->getName() . '" has been updated successfully.');
            
            return $this->redirectToRoute('admin_species_edit', ['id' => $species->getId()]);
        }

        return $this->render('admin/species_management/edit_species.html.twig', [
            'form' => $form,
            'species' => $species,
        ]);
    }

    #[Route('/race/new', name: 'admin_race_new')]
    public function newRace(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $race = new Race();
        $form = $this->createForm(RaceType::class, $race);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle icon file upload
            $iconFile = $form->get('iconFile')->getData();
            if ($iconFile) {
                $newFilename = $this->handleFileUpload($iconFile, $slugger, 'races');
                if ($newFilename) {
                    $race->setIcon($newFilename);
                }
            }
            
            $entityManager->persist($race);
            $entityManager->flush();
            
            $this->addFlash('success', 'Race "' . $race->getName() . '" has been created successfully.');
            
            return $this->redirectToRoute('admin_species_management');
        }
        
        // Debug: Add error messages for failed form submission
        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', 'Form Error: ' . $error->getMessage());
            }
            
            // Check specific field errors
            if ($form->get('iconFile')->getErrors()->count() > 0) {
                foreach ($form->get('iconFile')->getErrors() as $error) {
                    $this->addFlash('error', 'File Upload Error: ' . $error->getMessage());
                }
            }
            
            if ($form->get('species')->getErrors()->count() > 0) {
                foreach ($form->get('species')->getErrors() as $error) {
                    $this->addFlash('error', 'Species Selection Error: ' . $error->getMessage());
                }
            }
        }

        return $this->render('admin/species_management/new_race.html.twig', [
            'form' => $form,
            'race' => $race,
        ]);
    }

    #[Route('/race/{id}/edit', name: 'admin_race_edit', requirements: ['id' => '\d+'])]
    public function editRace(Race $race, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RaceType::class, $race);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle icon file upload
            $iconFile = $form->get('iconFile')->getData();
            if ($iconFile) {
                // Delete old icon if it exists
                if ($race->getIcon()) {
                    $oldIconPath = $this->getParameter('kernel.project_dir') . '/public/images/races/' . $race->getIcon();
                    if (file_exists($oldIconPath)) {
                        unlink($oldIconPath);
                    }
                }
                
                $newFilename = $this->handleFileUpload($iconFile, $slugger, 'races');
                if ($newFilename) {
                    $race->setIcon($newFilename);
                }
            }
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Race "' . $race->getName() . '" has been updated successfully.');
            
            return $this->redirectToRoute('admin_race_edit', ['id' => $race->getId()]);
        }

        return $this->render('admin/species_management/edit_race.html.twig', [
            'form' => $form,
            'race' => $race,
        ]);
    }

    private function handleFileUpload($file, SluggerInterface $slugger, string $directory): ?string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '_' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/images/' . $directory,
                $newFilename
            );
            return $newFilename;
        } catch (FileException $e) {
            $this->addFlash('error', 'Error uploading file: ' . $e->getMessage());
            return null;
        }
    }
}
