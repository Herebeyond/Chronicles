<?php

namespace App\Controller\Admin;

use App\Repository\SpeciesRepository;
use App\Repository\RaceRepository;
use App\Repository\CharacterRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tables')]
#[IsGranted('ROLE_ADMIN')]
final class DatabaseTableController extends AbstractController
{
    #[Route('/{table}', name: 'admin_table_view', requirements: ['table' => 'species|races|characters|users'])]
    public function tableView(
        string $table,
        Request $request,
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository,
        CharacterRepository $characterRepository,
        UserRepository $userRepository
    ): Response {
        $data = [];
        $totalCount = 0;
        $tableConfig = [];

        // Get counts for all tables for navigation
        $allCounts = [
            'species' => count($speciesRepository->findAll()),
            'races' => count($raceRepository->findAll()),
            'characters' => count($characterRepository->findAll()),
            'users' => count($userRepository->findAll()),
        ];

        // Filter parameters
        $filters = [
            'species' => $request->query->get('species'),
            'race' => $request->query->get('race'),
            'search' => $request->query->get('search'),
        ];

        switch ($table) {
            case 'species':
                $data = $speciesRepository->findAllWithRelatedCounts();
                $totalCount = count($data);
                $tableConfig = [
                    'title' => 'Species Table',
                    'icon' => '🧬',
                    'description' => 'Database view of all species with related counts',
                    'columns' => ['id', 'icon', 'name', 'description', 'races', 'characters', 'created_at', 'updated_at', 'actions'],
                    'entity' => 'species'
                ];
                break;

            case 'races':
                if ($filters['species']) {
                    $data = $raceRepository->findBySpecies((int)$filters['species']);
                    $speciesEntity = $speciesRepository->find((int)$filters['species']);
                    $speciesName = $speciesEntity ? $speciesEntity->getName() : 'Unknown';
                    $tableDescription = "Races belonging to species: {$speciesName}";
                } else {
                    $data = $raceRepository->findAllWithRelatedData();
                    $tableDescription = 'Database view of all races with species and character counts';
                }
                $totalCount = count($data);
                $tableConfig = [
                    'title' => 'Races Table',
                    'icon' => '🎭',
                    'description' => $tableDescription,
                    'columns' => ['id', 'icon', 'name', 'species', 'description', 'characters', 'created_at', 'updated_at', 'actions'],
                    'entity' => 'race'
                ];
                break;

            case 'characters':
                if ($filters['species']) {
                    $data = $characterRepository->findBySpecies((int)$filters['species']);
                    $speciesEntity = $speciesRepository->find((int)$filters['species']);
                    $speciesName = $speciesEntity ? $speciesEntity->getName() : 'Unknown';
                    $tableDescription = "Characters belonging to species: {$speciesName}";
                } elseif ($filters['race']) {
                    $data = $characterRepository->findByRace((int)$filters['race']);
                    $raceEntity = $raceRepository->find((int)$filters['race']);
                    $raceName = $raceEntity ? $raceEntity->getName() : 'Unknown';
                    $tableDescription = "Characters belonging to race: {$raceName}";
                } else {
                    $data = $characterRepository->findAllWithRelatedData();
                    $tableDescription = 'Database view of all characters with species and race information';
                }
                $totalCount = count($data);
                $tableConfig = [
                    'title' => 'Characters Table',
                    'icon' => '⚔️',
                    'description' => $tableDescription,
                    'columns' => ['id', 'avatar', 'name', 'species', 'race', 'gender', 'age', 'occupation', 'created_at', 'updated_at', 'actions'],
                    'entity' => 'character'
                ];
                break;

            case 'users':
                $data = $userRepository->findAllWithDetails();
                $totalCount = count($data);
                $tableConfig = [
                    'title' => 'Users Table',
                    'icon' => '👥',
                    'description' => 'Database view of all users with roles and activity status',
                    'columns' => ['id', 'username', 'email', 'first_name', 'last_name', 'roles', 'is_active', 'created_at', 'last_login_at', 'actions'],
                    'entity' => 'user'
                ];
                break;

            default:
                throw $this->createNotFoundException('Invalid table');
        }

        return $this->render('admin/tables/table_view.html.twig', [
            'table' => $table,
            'data' => $data,
            'total_count' => $totalCount,
            'all_counts' => $allCounts,
            'config' => $tableConfig,
            'filters' => $filters,
        ]);
    }

    #[Route('/{table}/{id}', name: 'admin_table_detail', requirements: ['table' => 'species|races|characters|users', 'id' => '\d+'])]
    public function tableDetail(
        string $table,
        int $id,
        SpeciesRepository $speciesRepository,
        RaceRepository $raceRepository,
        CharacterRepository $characterRepository,
        UserRepository $userRepository
    ): Response {
        $entity = null;
        $config = [];

        switch ($table) {
            case 'species':
                $entity = $speciesRepository->findWithFullDetails($id);
                $config = ['title' => 'Species Details', 'entity_type' => 'species'];
                break;
            case 'races':
                $entity = $raceRepository->findWithFullDetails($id);
                $config = ['title' => 'Race Details', 'entity_type' => 'race'];
                break;
            case 'characters':
                $entity = $characterRepository->findWithFullDetails($id);
                $config = ['title' => 'Character Details', 'entity_type' => 'character'];
                break;
            case 'users':
                $entity = $userRepository->findWithDetails($id);
                $config = ['title' => 'User Details', 'entity_type' => 'user'];
                break;
        }

        if (!$entity) {
            throw $this->createNotFoundException(ucfirst($table) . ' not found');
        }

        return $this->render('admin/tables/detail_view.html.twig', [
            'entity' => $entity,
            'table' => $table,
            'config' => $config,
        ]);
    }
}