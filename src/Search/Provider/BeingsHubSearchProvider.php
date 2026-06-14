<?php

namespace App\Search\Provider;

use App\Repository\RaceRepository;
use App\Repository\SpeciesRepository;
use App\Search\HubSearchProviderInterface;

final class BeingsHubSearchProvider implements HubSearchProviderInterface
{
    public function __construct(
        private readonly SpeciesRepository $speciesRepository,
        private readonly RaceRepository $raceRepository,
    ) {
    }

    public function getPageKey(): string
    {
        return 'beings';
    }

    public function getPageLabel(): string
    {
        return 'Beings';
    }

    public function getPageRouteName(): string
    {
        return 'beings_index';
    }

    public function getNodes(): array
    {
        $nodes = [];

        $speciesList = $this->speciesRepository->findAll();
        usort($speciesList, static fn($a, $b) => strcmp((string) $a->getName(), (string) $b->getName()));

        foreach ($speciesList as $species) {
            $nodes[] = [
                'uid' => 'species-' . $species->getId(),
                'label' => (string) $species->getName(),
                'type' => 'species',
                'typeLabel' => 'Species',
                'depth' => 1,
                'typePriority' => 300,
                'icon' => $species->getIcon() ? 'images/species/' . $species->getIcon() : 'images/icons/icon_default.png',
                'routeName' => 'beings_species',
                'routeParams' => ['speciesId' => $species->getId()],
            ];
        }

        $races = $this->raceRepository->findAllWithRelatedData();

        foreach ($races as $race) {
            $species = $race->getSpecies();
            if (!$species) {
                continue;
            }

            $nodes[] = [
                'uid' => 'race-' . $race->getId(),
                'label' => (string) $race->getName(),
                'type' => 'race',
                'typeLabel' => 'Race',
                'depth' => 2,
                'typePriority' => 200,
                'icon' => $race->getIcon() ? 'images/races/' . $race->getIcon() : 'images/icons/icon_default.png',
                'routeName' => 'beings_display',
                'routeParams' => [
                    'speciesId' => $species->getId(),
                    'raceId' => $race->getId(),
                ],
            ];
        }

        return $nodes;
    }
}
