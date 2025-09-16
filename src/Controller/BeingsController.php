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
        $search = $request->query->get('search', '');
        
        if ($search) {
            $species = $speciesRepository->findByNameSearch($search);
        } else {
            $species = $speciesRepository->findAllWithRaceCount();
        }

        return $this->render('beings/index.html.twig', [
            'page_title' => 'Espèces et Races',
            'species' => $species,
            'search' => $search,
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
}