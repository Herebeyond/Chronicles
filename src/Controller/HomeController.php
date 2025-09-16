<?php

namespace App\Controller;

use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository,
        CharacterRepository $characterRepository
    ): Response {
        // Get statistics for homepage
        $speciesStats = $speciesRepository->getStatistics();
        $characterStats = $characterRepository->getStatistics();
        $recentCharacters = $characterRepository->findRecent(5);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'page_title' => 'Chronicles - Fictional Universe Management',
            'stats' => [
                'species_count' => $speciesStats['speciesCount'],
                'races_count' => $speciesStats['totalRaces'],
                'characters_count' => $speciesStats['totalCharacters'],
                'average_age' => round($characterStats['averageAge'] ?? 0, 1)
            ],
            'recent_characters' => $recentCharacters
        ]);
    }
}