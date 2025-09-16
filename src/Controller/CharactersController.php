<?php

namespace App\Controller;

use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class CharactersController extends AbstractController
{
    #[Route('/characters', name: 'characters_index')]
    public function index(
        CharacterRepository $characterRepository, 
        SpeciesRepository $speciesRepository,
        Request $request
    ): Response {
        $search = $request->query->get('search', '');
        $speciesFilter = $request->query->get('species', '');
        
        if ($search) {
            $characters = $characterRepository->findByNameSearch($search);
        } elseif ($speciesFilter) {
            $characters = $characterRepository->findBySpecies((int)$speciesFilter);
        } else {
            $characters = $characterRepository->findGroupedBySpecies();
        }
        
        $species = $speciesRepository->findAll();

        return $this->render('characters/index.html.twig', [
            'page_title' => 'Personnages',
            'characters' => $characters,
            'species' => $species,
            'search' => $search,
            'species_filter' => $speciesFilter,
        ]);
    }

    #[Route('/characters/species/{speciesId}', name: 'characters_by_species', requirements: ['speciesId' => '\d+'])]
    public function bySpecies(
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

        // Group characters by race
        $charactersByRace = [];
        foreach ($characters as $character) {
            $raceKey = $character->getRace()?->getId() ?? 'no_race';
            if (!isset($charactersByRace[$raceKey])) {
                $charactersByRace[$raceKey] = [];
            }
            $charactersByRace[$raceKey][] = $character;
        }

        return $this->render('characters/display.html.twig', [
            'page_title' => 'Personnages - ' . $species->getName(),
            'species' => $species,
            'races' => $races,
            'characters_by_race' => $charactersByRace,
        ]);
    }

    #[Route('/characters/{characterId}', name: 'characters_show', requirements: ['characterId' => '\d+'])]
    public function show(
        int $characterId,
        CharacterRepository $characterRepository
    ): Response {
        $character = $characterRepository->find($characterId);
        
        if (!$character) {
            throw $this->createNotFoundException('Character not found');
        }

        return $this->render('characters/show.html.twig', [
            'page_title' => $character->getName(),
            'character' => $character,
        ]);
    }
}