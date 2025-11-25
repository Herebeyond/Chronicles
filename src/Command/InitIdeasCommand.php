<?php

namespace App\Command;

use App\Entity\Idea;
use App\Repository\IdeaCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-ideas',
    description: 'Initialize ideas system with default categories and optional sample data',
)]
class InitIdeasCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private IdeaCategoryRepository $categoryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        IdeaCategoryRepository $categoryRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
    }

    protected function configure(): void
    {
        $this
            ->addOption('with-samples', null, InputOption::VALUE_NONE, 'Add sample ideas')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Initialize default categories
        $io->section('Initializing default categories');
        $this->categoryRepository->initializeDefaultCategories();
        $io->success('Default categories initialized!');

        // Add sample ideas if requested
        if ($input->getOption('with-samples')) {
            $io->section('Creating sample ideas');
            $this->createSampleIdeas($io);
            $io->success('Sample ideas created!');
        }

        return Command::SUCCESS;
    }

    private function createSampleIdeas(SymfonyStyle $io): void
    {
        $sampleIdeas = [
            [
                'title' => 'Magic Origin - Sleeping Demon',
                'content' => 'La magie viendrait du démon endormi qui, en rêvant, projette volontairement ou non ses rêves dans le monde réel, ce qui produit la production de mana et l\'apparition d\'évènements et de créatures paranormales.',
                'category' => 'Magic_Systems',
                'certainty' => 'Developing',
                'tags' => ['magic', 'demons', 'mana', 'dreams', 'reality'],
            ],
            [
                'title' => 'Dragon Evolution',
                'content' => 'When some dragons started to live in forests, as time passed their wings became useless and began to disappear, making them slowly the first drakes.',
                'category' => 'Creatures',
                'certainty' => 'Canon',
                'tags' => ['dragons', 'drakes', 'evolution', 'forest'],
            ],
            [
                'title' => 'The Rift Chronicles Timeline',
                'content' => 'The main story takes place approximately 2000 years after the Great Cataclysm that shattered the world into different dimensions and realms.',
                'category' => 'Lore_History',
                'certainty' => 'Established',
                'tags' => ['timeline', 'cataclysm', 'history'],
            ],
            [
                'title' => 'Crystal Power Source',
                'content' => 'Ancient crystals found in the deepest dungeons contain raw magical energy. They can be used to power artifacts or enhance spellcasting abilities.',
                'category' => 'Items_Artifacts',
                'certainty' => 'Idea',
                'tags' => ['crystals', 'magic', 'artifacts', 'power'],
            ],
            [
                'title' => 'The Forgotten Worlds',
                'content' => 'Multiple parallel dimensions exist beyond the known realms. These forgotten worlds hold secrets of ancient civilizations and lost magic.',
                'category' => 'Dimensions_Realms',
                'certainty' => 'Canon',
                'tags' => ['dimensions', 'parallel worlds', 'ancient', 'secrets'],
            ],
        ];

        foreach ($sampleIdeas as $data) {
            $idea = new Idea();
            $idea->setTitle($data['title']);
            $idea->setContent($data['content']);
            $idea->setCategory($data['category']);
            $idea->setCertaintyLevel($data['certainty']);
            $idea->setStatus('Draft');
            $idea->setTags($data['tags']);

            $this->entityManager->persist($idea);
            $io->writeln(sprintf('Created: %s', $data['title']));
        }

        $this->entityManager->flush();
    }
}
