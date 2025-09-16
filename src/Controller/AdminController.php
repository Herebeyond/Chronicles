<?php

namespace App\Controller;

use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function dashboard(
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository,
        CharacterRepository $characterRepository
    ): Response {
        // Get statistics for admin dashboard
        $speciesStats = $speciesRepository->getStatistics();
        $characterStats = $characterRepository->getStatistics();
        $recentCharacters = $characterRepository->findRecent(5);
        $topSpecies = $speciesRepository->findSpeciesWithMostRaces(5);

        return $this->render('admin/dashboard.html.twig', [
            'page_title' => 'Tableau de bord administrateur',
            'stats' => [
                'species_count' => $speciesStats['speciesCount'],
                'races_count' => $speciesStats['totalRaces'],
                'characters_count' => $speciesStats['totalCharacters'],
                'average_age' => round($characterStats['averageAge'] ?? 0, 1),
                'male_count' => $characterStats['maleCount'],
                'female_count' => $characterStats['femaleCount'],
                'other_count' => $characterStats['otherCount']
            ],
            'recent_characters' => $recentCharacters,
            'top_species' => $topSpecies,
        ]);
    }

    #[Route('/characters', name: 'admin_characters')]
    public function characters(
        CharacterRepository $characterRepository,
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository
    ): Response {
        $characters = $characterRepository->findGroupedBySpecies();
        $species = $speciesRepository->findAll();
        $races = $raceRepository->findAll();

        return $this->render('admin/characters.html.twig', [
            'page_title' => 'Gestion des personnages',
            'characters' => $characters,
            'species' => $species,
            'races' => $races,
        ]);
    }

    #[Route('/species', name: 'admin_species')]
    public function species(
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository
    ): Response {
        $species = $speciesRepository->findAllWithRaceCount();
        $races = $raceRepository->findAllWithCharacterCount();

        return $this->render('admin/species.html.twig', [
            'page_title' => 'Gestion des espèces',
            'species' => $species,
            'races' => $races,
        ]);
    }
}