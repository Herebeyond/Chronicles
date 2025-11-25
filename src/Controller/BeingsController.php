<?php

namespace App\Controller;

use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class BeingsController extends AbstractController
{
    #[Route('/beings', name: 'beings_index')]
    public function index(SpeciesRepository $speciesRepository, Request $request): Response
    {
        // Search and filter parameters
        $searchTerm = trim($request->query->get('search', ''));
        $filterSpecie = trim($request->query->get('specie', ''));
        
        // Pagination parameters
        $perPage = 12;
        $page = max(1, (int)$request->query->get('page', 1));
        
        // Get species based on search/filter
        if (!empty($searchTerm) || !empty($filterSpecie)) {
            $species = $speciesRepository->findBySearchAndFilter($searchTerm, $filterSpecie, $page, $perPage);
            $totalSpecies = $speciesRepository->countBySearchAndFilter($searchTerm, $filterSpecie);
        } else {
            $species = $speciesRepository->findAllWithRaceCountPaginated($page, $perPage);
            $totalSpecies = $speciesRepository->count([]);
        }
        
        $totalPages = ceil($totalSpecies / $perPage);
        $page = min($page, max(1, $totalPages));
        
        // Get all species names for filter dropdown
        $allSpecies = $speciesRepository->findAllNames();

        return $this->render('beings/index.html.twig', [
            'page_title' => 'Espèces et Races',
            'species' => $species,
            'allSpecies' => $allSpecies,
            'search' => $searchTerm,
            'filterSpecie' => $filterSpecie,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalSpecies' => $totalSpecies,
            'perPage' => $perPage,
        ]);
    }

    #[Route('/beings/{speciesId}/race/{raceId}', name: 'beings_display', requirements: ['speciesId' => '\d+', 'raceId' => '\d+'])]
    public function display(
        int $speciesId, 
        int $raceId, 
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository,
        CharacterRepository $characterRepository
    ): Response {
        $species = $speciesRepository->find($speciesId);
        $race = $raceRepository->find($raceId);
        
        if (!$species || !$race || $race->getSpecies()->getId() !== $speciesId) {
            throw $this->createNotFoundException('Species or Race not found');
        }

        $characters = $characterRepository->findByRace($raceId);

        return $this->render('beings/display.html.twig', [
            'page_title' => sprintf('%s - %s', $species->getName(), $race->getName()),
            'species' => $species,
            'race' => $race,
            'characters' => $characters,
        ]);
    }

    #[Route('/beings/{speciesId}', name: 'beings_species', requirements: ['speciesId' => '\d+'])]
    public function species(
        int $speciesId, 
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository,
        CharacterRepository $characterRepository
    ): Response {
        $species = $speciesRepository->find($speciesId);
        
        if (!$species) {
            throw $this->createNotFoundException('Species not found');
        }

        $races = $raceRepository->findBySpecies($speciesId);
        $characters = $characterRepository->findBySpecies($speciesId);

        return $this->render('beings/display.html.twig', [
            'page_title' => $species->getName(),
            'species' => $species,
            'race' => null,
            'races' => $races,
            'characters' => $characters,
        ]);
    }

    #[Route('/api/beings/races/{speciesId}', name: 'api_beings_races', requirements: ['speciesId' => '\d+'])]
    public function apiGetRaces(int $speciesId, RaceRepository $raceRepository): Response
    {
        $races = $raceRepository->findBySpecies($speciesId);
        
        $raceData = [];
        foreach ($races as $race) {
            $raceData[] = [
                'id' => $race->getId(),
                'name' => $race->getName(),
                'description' => $race->getDescription(),
                'icon' => $race->getIcon(),
                'character_count' => count($race->getCharacters()),
            ];
        }
        
        return $this->json([
            'success' => true,
            'races' => $raceData,
        ]);
    }

    #[Route('/api/beings/characters/{raceId}', name: 'api_beings_characters', requirements: ['raceId' => '\d+'])]
    public function apiGetCharacters(int $raceId, CharacterRepository $characterRepository): Response
    {
        $characters = $characterRepository->findByRace($raceId);
        
        $characterData = [];
        foreach ($characters as $character) {
            $characterData[] = [
                'id' => $character->getId(),
                'name' => $character->getName(),
                'description' => $character->getDescription(),
                'avatar' => $character->getAvatar(),
                'gender' => $character->getGender(),
                'age' => $character->getAge(),
                'occupation' => $character->getOccupation(),
            ];
        }
        
        return $this->json([
            'success' => true,
            'characters' => $characterData,
        ]);
    }
}