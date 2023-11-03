<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use DateTimeImmutable;
use DateTime;
use App\Entity\WorkEntry;

class HomeController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/index', name: 'home')]
    public function index(): Response
    {
        $userId = $this->getUser()->getId();
        
        return $this->render('home/index.html.twig', [
            'userId' => $userId,
        ]);
    }

    #[Route('/dataWorkEntry', name: 'dataWorkEntry')]
    public function dataWorkEntry(): Response
    {
        $userId = $this->getUser()->getId();

        $workEntryUser = $this->em->getRepository(WorkEntry::class)->findByUserId($userId);
        $resultEnd = [];

        foreach($workEntryUser as $work) {
            $id = $work->getId();
            $start = $work->getStartDate();
            $end = $work->getEndDate();
            $interval = $start->diff($end);
            $formatDate = $interval->format('%a días %H horas %i minutos %s segundos');

            $resultEnd[] = [
                'id' => $id,
                'startDate' => $start->format('d/m/Y H:i:s'),
                'endDate' => $end->format('d/m/Y H:i:s'),
                'formatDate' => $formatDate 
            ];  
        }

        $array = [
            "data" => $resultEnd
        ];
        
        return new Response(json_encode($array));   
    }

    #[Route('/saveWorkEntryInit', name: 'saveWorkEntryInit')]
    public function saveWorkEntryInit(Request $request): Response
    {
        $userId = $this->getUser()->getId();
        $date = new DateTimeImmutable();
        $status = 1;
        $msg = 'Entrada guardada correctamente';

        $workEntry = new WorkEntry();
        $workEntry->setUserId($userId);
        $workEntry->setStatus($status);
        $workEntry->setStartDate($date);
        $workEntry->setEndDate($date);
        $workEntry->setCreateAt($date);
        $workEntry->setUpdatedAt($date);
        $workEntry->setDeletedAt($date);
        
        $this->em->persist($workEntry);
        $this->em->flush();

        $resultEnd = [
            "msg" => $msg,
            "status" => $status
        ];

        return new JsonResponse($resultEnd);
    }

    #[Route('/saveWorkEntryFinish', name: 'saveWorkEntryFinish')]
    public function saveWorkEntryFinish(Request $request): Response
    {
        $userId = $this->getUser()->getId();
        $date = new DateTimeImmutable();
        $status = 0;
        $msg = 'Salida guardada correctamente';

        $lastWorkEntry = $this->em->getRepository(WorkEntry::class)->findOneByRow($userId);

        if($lastWorkEntry->getStatus() == 1){
            $lastWorkEntry->setStatus($status);
            $lastWorkEntry->setEndDate($date);
            $lastWorkEntry->setUpdatedAt($date);

            $this->em->flush();
        } else {
            $msg = 'Tienes que registrar la hora de entrada';
            $status = 1;
        }
        
        $resultEnd = [
            "msg" => $msg,
            "status" => $status
        ];

        return new JsonResponse($resultEnd); 
    }

    #[Route('/deleteWorkEntry', name: 'deleteWorkEntry')]
    public function deleteWorkEntry(Request $request): Response
    {
        $id = $request->request->get('id');
        $msg = '';

        if(isset($id) && is_numeric($id)) {
            $msg = 'El registro se ha borrado correctamente';
            $workEntry = $this->em->getRepository(WorkEntry::class)->find($id);
            $this->em->remove($workEntry);
            $this->em->flush();

            $resultEnd = [
                "msg" => $msg,
                "ok" => 'ok'
            ];

            return new JsonResponse($resultEnd);
            
        } else {
            $msg = 'Error al borrar el registro';
            $resultEnd = [
                "msg" => $msg,
                "ko" => 'ko'
            ];

            return new JsonResponse($resultEnd);
        }
    }

    #[Route('/modalUpdate', name: 'modalUpdate')]
    public function modalUpdate(Request $request): Response
    {
        $modalId = $request->request->get('id');
        
        return $this->render('home/modalUpdateWorkEntry.html.twig', [
            'modalId' => $modalId,
        ]);
    }

    #[Route('/saveUpdateWorkEntry', name: 'saveUpdateWorkEntry')]
    public function saveUpdateWorkEntry(Request $request): Response
    {
        $id = $request->request->get('id');
        $startDate = new DateTime($request->request->get('startDate'));
        $endDate = new DateTime($request->request->get('endDate'));
        $date = new DateTimeImmutable();
        $startDateImmutable = DateTimeImmutable::createFromMutable($startDate);
        $endDateImmutable = DateTimeImmutable::createFromMutable($endDate);
        $msg = '';

        if(!empty($id) || !empty($startDate) || !empty($endDate)) {
            $msg = 'El registro se ha actualizado correctamente';
            $workEntry = $this->em->getRepository(WorkEntry::class)->find($id);
            $workEntry->setStartDate($startDateImmutable);
            $workEntry->setEndDate($endDateImmutable);
            $workEntry->setUpdatedAt($date);
            $this->em->persist($workEntry);
            $this->em->flush();

            $resultEnd = [
                "msg" => $msg,
                "ok" => 'ok'
            ];

            return new JsonResponse($resultEnd);
        } else {
            $msg = 'Error al actualizar el registro';
            $resultEnd = [
                "msg" => $msg,
                "ko" => 'ko'
            ];

            return new JsonResponse($resultEnd);
        }
    }
}
