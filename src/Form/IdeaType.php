<?php

namespace App\Form;

use App\Entity\Idea;
use App\Repository\IdeaCategoryRepository;
use App\Repository\IdeaRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdeaType extends AbstractType
{
    private IdeaCategoryRepository $categoryRepository;
    private IdeaRepository $ideaRepository;

    public function __construct(
        IdeaCategoryRepository $categoryRepository,
        IdeaRepository $ideaRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->ideaRepository = $ideaRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $this->categoryRepository->findAllOrdered();
        $categoryChoices = [];
        foreach ($categories as $category) {
            $displayName = str_replace('_', ' ', $category->getName());
            $categoryChoices[$displayName] = $category->getName();
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Title',
                'attr' => [
                    'placeholder' => 'Enter idea title',
                    'class' => 'form-input'
                ],
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Category',
                'choices' => $categoryChoices,
                'attr' => ['class' => 'form-input'],
            ])
            ->add('certaintyLevel', ChoiceType::class, [
                'label' => 'Certainty Level',
                'choices' => [
                    'Idea' => 'Idea',
                    'Not Sure' => 'Not_Sure',
                    'Developing' => 'Developing',
                    'Established' => 'Established',
                    'Canon' => 'Canon',
                ],
                'attr' => ['class' => 'form-input'],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => 'Draft',
                    'Need Correction' => 'Need_Correction',
                    'In Progress' => 'In_Progress',
                    'Review' => 'Review',
                    'Finalized' => 'Finalized',
                    'Archived' => 'Archived',
                ],
                'required' => false,
                'attr' => ['class' => 'form-input'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr' => [
                    'placeholder' => 'Describe your idea in detail...',
                    'rows' => 8,
                    'class' => 'form-input'
                ],
            ])
            ->add('tagsString', TextType::class, [
                'label' => 'Tags',
                'mapped' => false,
                'required' => false,
                'data' => $options['data']?->getTagsAsString(),
                'attr' => [
                    'placeholder' => 'Enter tags separated by commas: magic, demons, reality',
                    'class' => 'form-input'
                ],
                'help' => 'Separate multiple tags with commas'
            ])
            ->add('parentIdea', EntityType::class, [
                'label' => 'Parent Idea',
                'class' => Idea::class,
                'choice_label' => 'title',
                'placeholder' => 'None (Root Idea)',
                'required' => false,
                'query_builder' => function (IdeaRepository $repo) use ($options) {
                    // Get current idea and its descendants to exclude them
                    $currentIdea = $options['data'];
                    $excludeIds = [];
                    
                    if ($currentIdea && $currentIdea->getId()) {
                        // Exclude the current idea itself
                        $excludeIds[] = $currentIdea->getId();
                        
                        // Exclude all descendants (children, grandchildren, etc.)
                        $descendantIds = $currentIdea->getAllDescendantIds();
                        $excludeIds = array_merge($excludeIds, $descendantIds);
                    }
                    
                    $qb = $repo->createQueryBuilder('i')
                        ->orderBy('i.title', 'ASC');
                    
                    if (!empty($excludeIds)) {
                        $qb->where('i.id NOT IN (:excludeIds)')
                           ->setParameter('excludeIds', $excludeIds);
                    }
                    
                    return $qb;
                },
                'attr' => ['class' => 'form-input'],
                'help' => 'Select a parent idea to create a hierarchical relationship. Cannot select this idea or any of its children to prevent circular references.'
            ])
            ->add('inspirationSource', TextType::class, [
                'label' => 'Inspiration Source',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Where did this idea come from?',
                    'class' => 'form-input'
                ],
            ])
            ->add('comments', TextareaType::class, [
                'label' => 'Comments',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Additional notes and comments...',
                    'rows' => 4,
                    'class' => 'form-input'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Idea::class,
        ]);
    }
}
