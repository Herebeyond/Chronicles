<?php

namespace App\Controller;

use App\Repository\WorldEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorldEventsController extends AbstractController
{
    #[Route('/events', name: 'world_events_index')]
    public function index(WorldEventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAllChronological();
        
        return $this->render('world_events/index.html.twig', [
            'events' => $events
        ]);
    }
}
