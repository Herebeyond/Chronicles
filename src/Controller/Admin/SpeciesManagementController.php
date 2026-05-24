<?php

namespace App\Controller\Admin;

use App\Entity\Species;
use App\Entity\Race;
use App\Form\SpeciesType;
use App\Form\RaceType;
use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use App\Repository\CharacterRepository;
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
            
            // Preserve iframe parameter for modal context
            $params = ['id' => $species->getId(), 'saved' => 1];
            if ($request->query->get('iframe')) {
                $params['iframe'] = 1;
            }
            
            return $this->redirectToRoute('admin_species_edit', $params);
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
            
            // Preserve iframe parameter for modal context
            $params = ['id' => $race->getId(), 'saved' => 1];
            if ($request->query->get('iframe')) {
                $params['iframe'] = 1;
            }
            
            return $this->redirectToRoute('admin_race_edit', $params);
        }

        return $this->render('admin/species_management/edit_race.html.twig', [
            'form' => $form,
            'race' => $race,
        ]);
    }

    #[Route('/species/{id}/delete', name: 'admin_species_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteSpecies(Species $species, Request $request, EntityManagerInterface $entityManager, CharacterRepository $characterRepository): Response
    {
        // Verify CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_species_' . $species->getId(), $token)) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_species_management');
        }
        
        $speciesName = $species->getName();
        
        // Check if there are any characters directly associated with this species
        $charactersWithSpecies = $characterRepository->findBy(['species' => $species]);
        if (count($charactersWithSpecies) > 0) {
            $this->addFlash('error', 'Cannot delete species "' . $speciesName . '" because it has ' . count($charactersWithSpecies) . ' character(s) associated with it. Please reassign or delete those characters first.');
            return $this->redirectToRoute('admin_species_management');
        }
        
        // For each race of this species, check if there are characters and nullify their race_id
        foreach ($species->getRaces() as $race) {
            $charactersWithRace = $characterRepository->findBy(['race' => $race]);
            foreach ($charactersWithRace as $character) {
                $character->setRace(null);
            }
            
            // Delete race icon if it exists
            if ($race->getIcon()) {
                $iconPath = $this->getParameter('kernel.project_dir') . '/public/images/races/' . $race->getIcon();
                if (file_exists($iconPath)) {
                    unlink($iconPath);
                }
            }
        }
        
        // Delete species icon if it exists
        if ($species->getIcon()) {
            $iconPath = $this->getParameter('kernel.project_dir') . '/public/images/species/' . $species->getIcon();
            if (file_exists($iconPath)) {
                unlink($iconPath);
            }
        }
        
        $entityManager->remove($species);
        $entityManager->flush();
        
        $this->addFlash('success', 'Species "' . $speciesName . '" has been deleted successfully.');
        
        return $this->redirectToRoute('admin_species_management');
    }

    #[Route('/race/{id}/delete', name: 'admin_race_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteRace(Race $race, Request $request, EntityManagerInterface $entityManager, CharacterRepository $characterRepository): Response
    {
        // Verify CSRF token
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_race_' . $race->getId(), $token)) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('admin_species_management');
        }
        
        $raceName = $race->getName();
        
        // Nullify race_id on all characters that have this race
        $charactersWithRace = $characterRepository->findBy(['race' => $race]);
        foreach ($charactersWithRace as $character) {
            $character->setRace(null);
        }
        
        // Delete icon if it exists
        if ($race->getIcon()) {
            $iconPath = $this->getParameter('kernel.project_dir') . '/public/images/races/' . $race->getIcon();
            if (file_exists($iconPath)) {
                unlink($iconPath);
            }
        }
        
        $entityManager->remove($race);
        $entityManager->flush();
        
        $this->addFlash('success', 'Race "' . $raceName . '" has been deleted successfully.');
        
        return $this->redirectToRoute('admin_species_management');
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
