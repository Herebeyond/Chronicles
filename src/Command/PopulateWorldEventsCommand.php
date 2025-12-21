<?php

namespace App\Command;

use App\Entity\WorldEvent;
use App\Repository\WorldEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:populate-world-events',
    description: 'Populate the database with predefined world events'
)]
class PopulateWorldEventsCommand extends Command
{
    private const EVENTS = [
        ['title' => 'The Great Beginning', 'year' => -5000, 'duration' => 100, 'color' => '#8e44ad', 'description' => 'The origin of all things'],
        ['title' => 'The Pantheon', 'year' => -4900, 'duration' => 200, 'color' => '#f39c12', 'description' => 'Formation of the divine pantheon'],
        ['title' => 'The First Ascended […]', 'year' => -4700, 'duration' => 50, 'color' => '#3498db', 'description' => 'The first mortals ascend to divinity'],
        ['title' => 'Beyond the sky', 'year' => -4500, 'duration' => 150, 'color' => '#1abc9c', 'description' => 'Discovery of realms beyond the mortal plane'],
        ['title' => 'The Beings In The Stars', 'year' => -4350, 'duration' => 100, 'color' => '#34495e', 'description' => 'First contact with celestial beings'],
        ['title' => 'The ninth Ascended […]', 'year' => -4200, 'duration' => 30, 'color' => '#9b59b6', 'description' => 'The ninth and final ascension'],
        ['title' => 'The First Slumber', 'year' => -4000, 'duration' => 500, 'color' => '#2c3e50', 'description' => 'The gods withdraw from the mortal realm'],
        ['title' => 'The Rupture (Opening Of The Rift)', 'year' => -3500, 'duration' => 1, 'color' => '#c0392b', 'description' => 'A tear in reality opens'],
        ['title' => 'Voices from the outer worlds', 'year' => -3499, 'duration' => 200, 'color' => '#e74c3c', 'description' => 'Strange entities speak through the rift'],
        ['title' => 'In The Heart Of Darkness', 'year' => -3300, 'duration' => 100, 'color' => '#000000', 'description' => 'Exploration of the darkest realms'],
        ['title' => 'The day Time stopped', 'year' => -3000, 'duration' => 1, 'color' => '#95a5a6', 'description' => 'Tyrnos enferme Chaos et verrouille la manipulation du temps'],
        ['title' => 'The Great White One', 'year' => -2800, 'duration' => 1, 'color' => '#ecf0f1', 'description' => 'Samaël devient Satan'],
        ['title' => 'The Time Of The Blight', 'year' => -2799, 'duration' => 300, 'color' => '#7f8c8d', 'description' => 'Guerre contre Satan'],
        ['title' => 'Heavy Is The Head That Wears The Crown', 'year' => -2400, 'duration' => 50, 'color' => '#f1c40f', 'description' => 'The burden of divine leadership'],
        ['title' => 'Plus dure est la chute/le reigne de la peur', 'year' => -2350, 'duration' => 100, 'color' => '#e67e22', 'description' => 'Le règne de la peur commence'],
        ['title' => 'Le cri du ciel', 'year' => -2250, 'duration' => 10, 'color' => '#3498db', 'description' => 'Un appel désespéré résonne dans les cieux'],
        ['title' => 'La grande guerre', 'year' => -2240, 'duration' => 200, 'color' => '#c0392b', 'description' => 'The greatest conflict of the age'],
        ['title' => '… (fin de la guerre, début de la guerre froide)', 'year' => -2040, 'duration' => 500, 'color' => '#bdc3c7', 'description' => 'Fin de la guerre, début de la guerre froide'],
        ['title' => 'From The Abyss', 'year' => -1800, 'duration' => 50, 'color' => '#34495e', 'description' => 'Something emerges from the depths'],
        ['title' => 'The Lost Empire', 'year' => -1750, 'duration' => 200, 'color' => '#16a085', 'description' => 'A great civilization falls into obscurity'],
        ['title' => 'The Nameless', 'year' => -1500, 'duration' => 30, 'color' => '#95a5a6', 'description' => 'The entity without a name appears'],
        ['title' => 'City Of Blood', 'year' => -1470, 'duration' => 5, 'color' => '#8b0000', 'description' => 'A city drowns in bloodshed'],
        ['title' => 'The Eternal Emperor', 'year' => -1400, 'duration' => 600, 'color' => '#d4af37', 'description' => 'The reign of the immortal emperor begins'],
        ['title' => 'The Graveyard Of Empires (Pilpisoil)', 'year' => -1200, 'duration' => 100, 'color' => '#8b4513', 'description' => 'Where empires come to die'],
        ['title' => 'Years Without Summer', 'year' => -1000, 'duration' => 10, 'color' => '#708090', 'description' => 'A volcanic winter grips the world'],
        ['title' => 'The Black Fellowship', 'year' => -900, 'duration' => 50, 'color' => '#000000', 'description' => 'Formation of a dark alliance'],
        ['title' => 'Godly descent', 'year' => -800, 'duration' => 20, 'color' => '#ffd700', 'description' => 'The gods walk among mortals once more'],
        ['title' => 'The Precipice Of Annihilation', 'year' => -700, 'duration' => 5, 'color' => '#ff0000', 'description' => 'The world teeters on the edge of destruction'],
        ['title' => 'Moon Landing', 'year' => -695, 'duration' => 1, 'color' => '#c0c0c0', 'description' => 'First expedition to the moon'],
        ['title' => 'First Starfall', 'year' => -600, 'duration' => 1, 'color' => '#4169e1', 'description' => 'Celestial bodies rain from the sky'],
        ['title' => 'The Great Entombment', 'year' => -599, 'duration' => 50, 'color' => '#654321', 'description' => 'When most Dwarves Were Forced To Live In Caves'],
        ['title' => 'The Great Holy War', 'year' => -500, 'duration' => 100, 'color' => '#daa520', 'description' => 'Religious conflict engulfs the world'],
        ['title' => 'Between Shadows', 'year' => -400, 'duration' => 80, 'color' => '#696969', 'description' => 'Secret wars fought in darkness'],
        ['title' => 'Lasting victory', 'year' => -320, 'duration' => 50, 'color' => '#228b22', 'description' => 'A hard-won peace is achieved'],
        ['title' => 'The Red Death', 'year' => -250, 'duration' => 10, 'color' => '#dc143c', 'description' => 'A plague sweeps across the lands'],
        ['title' => 'The fifth scripture', 'year' => -200, 'duration' => 20, 'color' => '#deb887', 'description' => 'Sacred texts are revealed'],
        ['title' => 'Burning sky', 'year' => -150, 'duration' => 5, 'color' => '#ff4500', 'description' => 'The heavens themselves seem to ignite'],
        ['title' => 'The time of isolation', 'year' => -100, 'duration' => 80, 'color' => '#556b2f', 'description' => 'When monsters and dangers roamed the lands, making traveling outside of cities very dangerous'],
        ['title' => 'The Great Hunt', 'year' => -20, 'duration' => 15, 'color' => '#8b4513', 'description' => 'A massive campaign to reclaim the wilds'],
        ['title' => 'Secret War', 'year' => 0, 'duration' => 30, 'color' => '#2f4f4f', 'description' => 'A conflict hidden from history'],
        ['title' => 'Rogue Titan', 'year' => 50, 'duration' => 10, 'color' => '#a9a9a9', 'description' => 'A colossal being breaks free from its bonds'],
        ['title' => 'The Devourer', 'year' => 60, 'duration' => 5, 'color' => '#8b0000', 'description' => 'An entity that consumes all in its path'],
        ['title' => 'Days Of Peace', 'year' => 100, 'duration' => null, 'color' => '#87ceeb', 'description' => 'An ongoing era of relative tranquility'],
        ['title' => 'Shattered Fate', 'year' => 150, 'duration' => 20, 'color' => '#9370db', 'description' => 'Prophecies fail and destinies unravel'],
        ['title' => 'The Blue Days', 'year' => 200, 'duration' => 30, 'color' => '#4682b4', 'description' => 'A period of melancholy and reflection'],
        ['title' => 'Spirit War', 'year' => 250, 'duration' => 40, 'color' => '#6a5acd', 'description' => 'Conflict in the ethereal planes'],
        ['title' => 'The Silent Scream', 'year' => 300, 'duration' => 1, 'color' => '#191970', 'description' => 'A psychic wave of terror'],
        ['title' => 'Amber Nations', 'year' => 350, 'duration' => null, 'color' => '#ffbf00', 'description' => 'Rise of new kingdoms and alliances'],
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private WorldEventRepository $eventRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'clear',
            null,
            InputOption::VALUE_NONE,
            'Clear existing events before populating'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Clear existing events if requested
        if ($input->getOption('clear')) {
            $io->warning('Clearing all existing world events...');
            $existingEvents = $this->eventRepository->findAll();
            foreach ($existingEvents as $event) {
                $this->entityManager->remove($event);
            }
            $this->entityManager->flush();
            $io->success(sprintf('Cleared %d existing events', count($existingEvents)));
        }

        $io->title('Populating World Events');
        
        foreach (self::EVENTS as $eventData) {
            $event = new WorldEvent();
            $event->setTitle($eventData['title']);
            $event->setDescription($eventData['description']);
            
            // Set start date (month 1, day 1 for simplicity)
            $event->setStartYear($eventData['year']);
            $event->setStartMonth(1);
            $event->setStartDay(1);
            
            // Set end date if duration is specified
            if ($eventData['duration'] !== null) {
                $endYear = $eventData['year'] + intdiv($eventData['duration'], 12);
                $endMonth = ($eventData['duration'] % 12) ?: 12;
                
                $event->setEndYear($endYear);
                $event->setEndMonth($endMonth);
                $event->setEndDay(30); // End of month
            }
            // If duration is null, event is ongoing (no end date)
            
            $event->setColor($eventData['color']);
            
            $this->entityManager->persist($event);
            
            $io->writeln(sprintf(
                '<info>✓</info> %s (Year %d%s)',
                $event->getTitle(),
                $event->getStartYear(),
                $event->isOngoing() ? ' - Ongoing' : ''
            ));
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully populated %d world events!', count(self::EVENTS)));
        $io->note('You can view and edit these events at /admin/events');

        return Command::SUCCESS;
    }
}
