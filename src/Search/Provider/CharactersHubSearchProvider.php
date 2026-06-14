<?php

namespace App\Search\Provider;

use App\Repository\CharacterRepository;
use App\Search\HubSearchProviderInterface;

final class CharactersHubSearchProvider implements HubSearchProviderInterface
{
    public function __construct(private readonly CharacterRepository $characterRepository)
    {
    }

    public function getPageKey(): string
    {
        return 'characters';
    }

    public function getPageLabel(): string
    {
        return 'Characters';
    }

    public function getPageRouteName(): string
    {
        return 'characters_index';
    }

    public function getNodes(): array
    {
        $nodes = [];
        $characters = $this->characterRepository->findGroupedBySpecies();

        foreach ($characters as $character) {
            $nodes[] = [
                'uid' => 'character-' . $character->getId(),
                'label' => (string) $character->getName(),
                'type' => 'character',
                'typeLabel' => 'Character',
                'depth' => 3,
                'typePriority' => 100,
                'icon' => $character->getAvatar() ? 'images/characters/' . $character->getAvatar() : 'images/icons/icon_default.png',
                'routeName' => 'characters_show',
                'routeParams' => ['characterId' => $character->getId()],
            ];
        }

        return $nodes;
    }
}
