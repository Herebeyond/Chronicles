<?php

namespace App\Command;

use App\Entity\Character;
use App\Entity\Race;
use App\Entity\Species;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-data',
    description: 'Populate the database with sample species, races, and characters',
)]
class PopulateDataCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Clear existing data
        $io->note('Clearing existing data...');
        $this->entityManager->createQuery('DELETE FROM App\Entity\Character')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Race')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Species')->execute();

        // Create species data
        $speciesData = [
            [
                'name' => 'Humains',
                'description' => 'Espèce dominante connue pour sa diversité culturelle et son adaptabilité. Les humains excellent dans tous les domaines et forment la majorité des populations urbaines.',
                'races' => [
                    'Nobles' => 'Aristocratie dirigeante avec accès à l\'éducation supérieure et aux ressources.',
                    'Bourgeois' => 'Classe marchande prospère, moteur de l\'économie et de l\'innovation.',
                    'Artisans' => 'Maîtres de leur métier, créateurs d\'objets de qualité et gardiens des traditions.',
                    'Paysans' => 'Travailleurs de la terre, fondation de l\'agriculture et de l\'approvisionnement.'
                ]
            ],
            [
                'name' => 'Elfes',
                'description' => 'Êtres immortels liés à la nature et à la magie. Leur sagesse millénaire et leur grâce naturelle en font d\'excellents mages et gardiens des forêts ancestrales.',
                'races' => [
                    'Elfes des Bois' => 'Gardiens des forêts anciennes, maîtres de l\'archerie et de la furtivité.',
                    'Hauts Elfes' => 'Maîtres de la magie arcanique, vivant dans des tours de cristal et d\'argent.',
                    'Elfes Noirs' => 'Proscrits vivant dans les souterrains, experts en magie noire et en alchimie.'
                ]
            ],
            [
                'name' => 'Nains',
                'description' => 'Peuple robuste des montagnes et des profondeurs. Maîtres forgerons et mineurs, ils créent les plus beaux objets en métal et pierre.',
                'races' => [
                    'Nains des Montagnes' => 'Gardiens des hauts pics, experts en métallurgie et en guerre de siège.',
                    'Nains des Profondeurs' => 'Mineurs et explorateurs des abysses, chasseurs de créatures souterraines.',
                    'Nains du Feu' => 'Forgerons légendaires maîtrisant les flammes éternelles et les métaux magiques.'
                ]
            ],
            [
                'name' => 'Dragons',
                'description' => 'Créatures anciennes et puissantes, incarnations de la magie primordiale. Chaque dragon possède une sagesse millénaire et des pouvoirs élémentaires.',
                'races' => [
                    'Dragons Rouges' => 'Maîtres du feu et de la destruction, territoriaux et orgueilleux.',
                    'Dragons Bleus' => 'Gardiens de la foudre et des tempêtes, sages et manipulateurs.',
                    'Dragons Verts' => 'Seigneurs des forêts et des poisons, rusés et patients.',
                    'Dragons Dorés' => 'Protecteurs de la justice et de l\'ordre, nobles et bienveillants.'
                ]
            ],
            [
                'name' => 'Fées',
                'description' => 'Êtres magiques éthérés liés aux émotions et aux rêves. Leur nature changeante reflète les saisons et les cycles naturels.',
                'races' => [
                    'Fées du Printemps' => 'Gardiennes de la croissance et du renouveau, joyeuses et pleines d\'espoir.',
                    'Fées de l\'Été' => 'Maîtresses de la passion et de l\'énergie, charismatiques et flamboyantes.',
                    'Fées de l\'Automne' => 'Protectrices de la sagesse et des récoltes, contemplatives et généreuses.',
                    'Fées de l\'Hiver' => 'Gardiennnes du repos et de la mort, mystérieuses et implacables.'
                ]
            ]
        ];

        $io->note('Creating species and races...');
        $speciesCount = 0;
        $raceCount = 0;
        $characterCount = 0;

        foreach ($speciesData as $speciesInfo) {
            $species = new Species();
            $species->setName($speciesInfo['name'])
                    ->setDescription($speciesInfo['description']);
            
            $this->entityManager->persist($species);
            $speciesCount++;

            // Create races for this species
            foreach ($speciesInfo['races'] as $raceName => $raceDescription) {
                $race = new Race();
                $race->setName($raceName)
                     ->setDescription($raceDescription)
                     ->setSpecies($species);
                
                $this->entityManager->persist($race);
                $raceCount++;

                // Create some sample characters for each race
                $sampleCharacters = $this->getSampleCharacters($raceName, $speciesInfo['name']);
                foreach ($sampleCharacters as $charData) {
                    $character = new Character();
                    $character->setName($charData['name'])
                             ->setDescription($charData['description'])
                             ->setGender($charData['gender'])
                             ->setAge($charData['age'])
                             ->setOccupation($charData['occupation'])
                             ->setTraits($charData['traits'])
                             ->setSpecies($species)
                             ->setRace($race);

                    $this->entityManager->persist($character);
                    $characterCount++;
                }
            }
        }

        $this->entityManager->flush();

        $io->success([
            'Database populated successfully!',
            "Created $speciesCount species",
            "Created $raceCount races", 
            "Created $characterCount characters"
        ]);

        return Command::SUCCESS;
    }

    private function getSampleCharacters(string $raceName, string $speciesName): array
    {
        $characters = [];
        
        // Generate sample characters based on race
        $names = $this->getCharacterNames($raceName);
        $occupations = $this->getCharacterOccupations($raceName);
        $traits = $this->getCharacterTraits($raceName);

        for ($i = 0; $i < 2; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $age = $this->getRandomAge($speciesName);
            
            $characters[] = [
                'name' => $names[array_rand($names)],
                'description' => "Un·e {$raceName} de l'espèce {$speciesName}. " . $this->getRandomDescription($raceName),
                'gender' => $gender,
                'age' => $age,
                'occupation' => $occupations[array_rand($occupations)],
                'traits' => array_slice(array_unique(array_merge(
                    [$traits[array_rand($traits)]], 
                    [$traits[array_rand($traits)]]
                )), 0, rand(2, 4))
            ];
        }

        return $characters;
    }

    private function getCharacterNames(string $raceName): array
    {
        $names = [
            'Nobles' => ['Aldric de Montclair', 'Lyanna Duvernois', 'Roderick de Belmont', 'Isabelle de Rothschild'],
            'Bourgeois' => ['Marcel Durand', 'Catherine Mercier', 'Antoine Lecomte', 'Marie Dubois'],
            'Artisans' => ['Pierre le Forgeron', 'Agnès la Tisserande', 'Thomas le Charpentier', 'Margot la Potière'],
            'Paysans' => ['Jean le Meunier', 'Berthe la Fermière', 'Guillaume le Berger', 'Rosine la Boulangère'],
            'Elfes des Bois' => ['Thalorin Feuillargent', 'Eilenora Chantelune', 'Galadrien Vertcœur', 'Silvana Briseven'],
            'Hauts Elfes' => ['Aelindra Etoiledoree', 'Valandil Lumecrystal', 'Celebrian Lunargent', 'Elrond Clairevue'],
            'Elfes Noirs' => ['Drizzt Ombreglace', 'Vierna Veninnoir', 'Malice Lacrimosa', 'Zaknafein Chasseombre'],
            'Nains des Montagnes' => ['Thorin Barbe-de-Fer', 'Dain Poing-de-Pierre', 'Borin Hache-Vaillante', 'Nala Pierre-Dure'],
            'Nains des Profondeurs' => ['Grimm Sonde-Abîme', 'Vera Mine-Profonde', 'Balin Cherche-Or', 'Nora Perce-Roc'],
            'Nains du Feu' => ['Durgan Forge-Ardente', 'Thora Flamme-Bleue', 'Borin Brasier-Eternel', 'Kira Métal-Rouge'],
            'Dragons Rouges' => ['Pyrothax le Destructeur', 'Ignis la Brasier', 'Flammeroth l\'Ancien', 'Embria la Terrible'],
            'Dragons Bleus' => ['Voltarix le Sage', 'Tempestia l\'Oracle', 'Azurion le Patient', 'Electra la Divine'],
            'Dragons Verts' => ['Venomius le Rusé', 'Sylvestria la Patiente', 'Chloros l\'Empoisonneur', 'Verdania la Maîtresse'],
            'Dragons Dorés' => ['Solarius le Juste', 'Aurellia la Pure', 'Luminos le Protecteur', 'Celestia la Bienveillante']
        ];

        return $names[$raceName] ?? ['Nom Générique'];
    }

    private function getCharacterOccupations(string $raceName): array
    {
        $occupations = [
            'Nobles' => ['Duc', 'Comte', 'Baron', 'Chevalier', 'Diplomate'],
            'Bourgeois' => ['Marchand', 'Banquier', 'Maître de guilde', 'Négociant'],
            'Artisans' => ['Forgeron', 'Menuisier', 'Tailleur', 'Orfèvre', 'Maçon'],
            'Paysans' => ['Fermier', 'Berger', 'Meunier', 'Bûcheron', 'Chasseur'],
            'Elfes des Bois' => ['Gardien forestier', 'Archer royal', 'Druide', 'Éclaireur'],
            'Hauts Elfes' => ['Archimage', 'Conseiller royal', 'Bibliothécaire', 'Enchanteur'],
            'Elfes Noirs' => ['Assassin', 'Alchimiste', 'Espion', 'Nécromancien'],
            'Nains des Montagnes' => ['Forgeron royal', 'Garde montagnard', 'Mineur chef', 'Guerrier'],
            'Nains des Profondeurs' => ['Explorateur', 'Chasseur de monstres', 'Mineur expert', 'Guide souterrain'],
            'Nains du Feu' => ['Maître forgeron', 'Gardien des flammes', 'Artisan légendaire'],
            'Dragons Rouges' => ['Seigneur de guerre', 'Collectionneur de trésors', 'Tyran'],
            'Dragons Bleus' => ['Oracle', 'Sage', 'Conseiller mystique', 'Gardien du savoir'],
            'Dragons Verts' => ['Maître des poisons', 'Seigneur des forêts', 'Manipulateur'],
            'Dragons Dorés' => ['Protecteur divin', 'Juge suprême', 'Gardien de la justice']
        ];

        return $occupations[$raceName] ?? ['Aventurier'];
    }

    private function getCharacterTraits(string $raceName): array
    {
        return ['Courageux', 'Intelligent', 'Rusé', 'Fort', 'Agile', 'Charismatique', 'Sage', 'Patient', 'Loyal', 'Mystérieux', 'Noble', 'Humble', 'Fier', 'Généreux', 'Prudent'];
    }

    private function getRandomAge(string $speciesName): int
    {
        $ageRanges = [
            'Humains' => [18, 80],
            'Elfes' => [100, 1000],
            'Nains' => [50, 400],
            'Dragons' => [500, 5000],
            'Fées' => [50, 500]
        ];

        $range = $ageRanges[$speciesName] ?? [20, 100];
        return rand($range[0], $range[1]);
    }

    private function getRandomDescription(string $raceName): string
    {
        $descriptions = [
            'Nobles' => 'Élevé·e dans les fastes de la cour, maîtrisant l\'art de la diplomatie.',
            'Bourgeois' => 'Habile en affaires et respecté·e dans sa communauté.',
            'Artisans' => 'Maître de son art, créant des œuvres reconnues pour leur qualité.',
            'Paysans' => 'Travailleur·se de la terre, connaissant les secrets de la nature.',
            'Elfes des Bois' => 'En harmonie avec la forêt, gardien·ne des anciens secrets.',
            'Hauts Elfes' => 'Maître de la magie arcanique et des arts érudits.',
            'Elfes Noirs' => 'Mystérieux·se habitant·e des souterrains, expert·e en arts interdits.',
            'Nains des Montagnes' => 'Robuste et fier·e, gardien·ne des traditions ancestrales.',
            'Nains des Profondeurs' => 'Explorateur·trice intrépide des profondeurs mystérieuses.',
            'Nains du Feu' => 'Maître·sse des flammes et créateur·trice d\'artefacts légendaires.',
            'Dragons Rouges' => 'Être de pouvoir et de colère, collectionneur de trésors.',
            'Dragons Bleus' => 'Sage ancien détenteur de secrets millénaires.',
            'Dragons Verts' => 'Manipulateur·trice rusé·e, maître·sse des intrigues.',
            'Dragons Dorés' => 'Noble protecteur·trice de la justice et de l\'ordre.'
        ];

        return $descriptions[$raceName] ?? 'Être unique aux multiples facettes.';
    }
}
