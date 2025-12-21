<?php

namespace App\Controller;

use App\Repository\CalendarMonthRepository;
use App\Repository\WorldEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorldEventsController extends AbstractController
{
    #[Route('/events', name: 'world_events_index')]
    public function index(
        WorldEventRepository $eventRepository,
        CalendarMonthRepository $calendarRepository
    ): Response {
        $events = $eventRepository->findAllChronological();
        $months = $calendarRepository->findAllOrdered();
        $dateRange = $eventRepository->getDateRange();
        
        // Calculate timeline data for each event
        $timelineData = [];
        $minYear = $dateRange['minYear'];
        $maxYear = $dateRange['maxYear'];
        $yearSpan = max(1, $maxYear - $minYear);
        
        foreach ($events as $event) {
            $startOffset = (($event->getStartYear() - $minYear) / $yearSpan) * 100;
            
            if ($event->isOngoing()) {
                $width = 100 - $startOffset;
                $isOngoing = true;
            } else {
                $endOffset = (($event->getEndYear() - $minYear) / $yearSpan) * 100;
                $width = $endOffset - $startOffset;
                $isOngoing = false;
            }
            
            $timelineData[] = [
                'event' => $event,
                'startOffset' => $startOffset,
                'width' => max(0.5, $width), // Minimum width for visibility
                'isOngoing' => $isOngoing
            ];
        }
        
        return $this->render('world_events/index.html.twig', [
            'events' => $events,
            'timelineData' => $timelineData,
            'months' => $months,
            'dateRange' => $dateRange,
            'yearSpan' => $yearSpan
        ]);
    }
}
