<?php

namespace App\Search;

interface HubSearchProviderInterface
{
    public function getPageKey(): string;

    public function getPageLabel(): string;

    public function getPageRouteName(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNodes(): array;
}
