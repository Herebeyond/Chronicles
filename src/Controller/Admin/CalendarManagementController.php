<?php

namespace App\Controller\Admin;

use App\Entity\CalendarMonth;
use App\Repository\CalendarMonthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/calendar')]
#[IsGranted('ROLE_ADMIN')]
class CalendarManagementController extends AbstractController
{
    #[Route('', name: 'admin_calendar_index')]
    public function index(CalendarMonthRepository $repository): Response
    {
        $months = $repository->findAllOrdered();
        $totalDays = $repository->getTotalDaysInYear();
        
        return $this->render('admin/calendar/index.html.twig', [
            'months' => $months,
            'totalDays' => $totalDays
        ]);
    }

    #[Route('/month/{id}/edit', name: 'admin_calendar_edit', requirements: ['id' => '\d+'])]
    public function editMonth(CalendarMonth $month, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $month->setName($request->request->get('name'));
            $month->setDaysCount((int) $request->request->get('days_count'));
            $month->setDescription($request->request->get('description'));
            
            $em->flush();
            
            $this->addFlash('success', 'Mois modifié avec succès');
            return $this->redirectToRoute('admin_calendar_index');
        }
        
        return $this->render('admin/calendar/form.html.twig', [
            'month' => $month
        ]);
    }
}
