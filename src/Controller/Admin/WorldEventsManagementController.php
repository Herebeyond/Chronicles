<?php

namespace App\Controller\Admin;

use App\Entity\WorldEvent;
use App\Repository\WorldEventRepository;
use App\Repository\CalendarMonthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events')]
#[IsGranted('ROLE_ADMIN')]
class WorldEventsManagementController extends AbstractController
{
    #[Route('', name: 'admin_world_events_index')]
    public function index(
        WorldEventRepository $repository,
        CalendarMonthRepository $calendarRepository
    ): Response {
        $events = $repository->findAllByDisplayOrder();
        $calendarMonths = $calendarRepository->findAllOrdered();
        
        return $this->render('admin/world_events/index.html.twig', [
            'events' => $events,
            'calendarMonths' => $calendarMonths
        ]);
    }

    #[Route('/{id}/update-field', name: 'admin_world_events_update_field', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateField(
        WorldEvent $event,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;
        
        if (!$field) {
            return new JsonResponse(['success' => false, 'message' => 'Champ manquant'], 400);
        }
        
        try {
            switch ($field) {
                case 'title':
                    $event->setTitle($value);
                    break;
                case 'description':
                    $event->setDescription($value);
                    break;
                case 'color':
                    $event->setColor($value);
                    break;
                default:
                    return new JsonResponse(['success' => false, 'message' => 'Champ invalide'], 400);
            }
            
            $event->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}/update-date', name: 'admin_world_events_update_date', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateDate(
        WorldEvent $event,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $dateType = $data['dateType'] ?? null;
        $year = $data['year'] ?? null;
        $month = $data['month'] ?? null;
        $day = $data['day'] ?? null;
        
        if (!$dateType) {
            return new JsonResponse(['success' => false, 'message' => 'Type de date manquant'], 400);
        }
        
        try {
            if ($dateType === 'start') {
                $event->setStartYear($year);
                $event->setStartMonth($month);
                $event->setStartDay($day);
            } else {
                // If year is null, mark as ongoing
                if ($year === null) {
                    $event->setEndYear(null);
                    $event->setEndMonth(null);
                    $event->setEndDay(null);
                } else {
                    $event->setEndYear($year);
                    $event->setEndMonth($month);
                    $event->setEndDay($day);
                }
            }
            
            $event->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();
            
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/create', name: 'admin_world_events_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $event = new WorldEvent();
        
        if ($request->isMethod('POST')) {
            $event->setTitle($request->request->get('title'));
            $event->setDescription($request->request->get('description'));
            $event->setStartYear((int) $request->request->get('start_year'));
            $event->setStartMonth((int) $request->request->get('start_month'));
            $event->setStartDay((int) $request->request->get('start_day'));
            
            $endYear = $request->request->get('end_year');
            if ($endYear) {
                $event->setEndYear((int) $endYear);
                $event->setEndMonth((int) $request->request->get('end_month'));
                $event->setEndDay((int) $request->request->get('end_day'));
            }
            
            $event->setColor($request->request->get('color', '#3498db'));
            $event->setSignificance($request->request->get('significance'));
            
            $em->persist($event);
            $em->flush();
            
            $this->addFlash('success', 'Événement créé avec succès');
            return $this->redirectToRoute('admin_world_events_index');
        }
        
        return $this->render('admin/world_events/form.html.twig', [
            'event' => $event,
            'isEdit' => false
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_world_events_edit', requirements: ['id' => '\d+'])]
    public function edit(WorldEvent $event, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $event->setTitle($request->request->get('title'));
            $event->setDescription($request->request->get('description'));
            $event->setStartYear((int) $request->request->get('start_year'));
            $event->setStartMonth((int) $request->request->get('start_month'));
            $event->setStartDay((int) $request->request->get('start_day'));
            
            $endYear = $request->request->get('end_year');
            if ($endYear) {
                $event->setEndYear((int) $endYear);
                $event->setEndMonth((int) $request->request->get('end_month'));
                $event->setEndDay((int) $request->request->get('end_day'));
            } else {
                $event->setEndYear(null);
                $event->setEndMonth(null);
                $event->setEndDay(null);
            }
            
            $event->setColor($request->request->get('color', '#3498db'));
            $event->setSignificance($request->request->get('significance'));
            $event->setUpdatedAt(new \DateTimeImmutable());
            
            $em->flush();
            
            $this->addFlash('success', 'Événement modifié avec succès');
            return $this->redirectToRoute('admin_world_events_index');
        }
        
        return $this->render('admin/world_events/form.html.twig', [
            'event' => $event,
            'isEdit' => true
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_world_events_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(WorldEvent $event, EntityManagerInterface $em): Response
    {
        $em->remove($event);
        $em->flush();
        
        $this->addFlash('success', 'Événement supprimé avec succès');
        return $this->redirectToRoute('admin_world_events_index');
    }
}
