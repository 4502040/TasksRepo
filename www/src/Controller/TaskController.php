<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Dto\CreateTaskDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

#[Route('/api/tasks', name: 'api_tasks_')]
final class TaskController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger
    ) {}
    
    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(TaskRepository $repository): Response
    {
        $tasks = $repository->findAll();
        return $this->json($tasks);
    }
    
    #[Route('/{id}', name: 'get_one', methods: ['GET'])]
    public function getOne(int $id, TaskRepository $repository): Response
    {
        $task = $repository->find($id);
        
        if (!$task) {
            
            $this->logger->error('Task not found', [
                'task_id' => $id,                
            ]);

            return $this->json([
                'status' => 'error',
                'error' => [
                    'route'=>'tasks_get_one',
                    'message'=>'Task not found',
                    'id'=>$id
                    ]
            ], Response::HTTP_BAD_REQUEST);
        }
        
        return $this->json($task);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
                    Request $request, 
                    EntityManagerInterface $em,
                    SerializerInterface $serializer, 
                    ValidatorInterface $validator): Response
    {       

        $dto = $serializer->deserialize(
            $request->getContent(),
            CreateTaskDto::class,
            'json'
        );

        // Валидация
        $errors = $validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json([
                'status' => 'error',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $task = new Task();
        $task->setTitle($dto['title'] ?? '');
        $task->setDescription($dto['description'] ?? null);
        $task->setStatus($dto['status'] ?? Task::STATUS_NEW);        

        $em->persist($task);
        $em->flush();

        return $this->json($task, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
                    int $id,
                    TaskRepository $repository,                     
                    Request $request, 
                    EntityManagerInterface $em, 
                    ValidatorInterface $validator): Response
    {
        // Проверка существования задачи
        $task = $repository->find($id);

        if (!$task) {
            
            $this->logger->error('Task not found', [
                'task_id' => $id,                
            ]);

            return $this->json([
                'status' => 'error',
                'error' => [
                    'route'=>'tasks_update',
                    'message'=>'Task not found',
                    'id'=>$id
                    ]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = json_decode($request->getContent(), true);
        
        // Частичное обновление полей
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }

        // Валидация
        $errors = $validator->validate($task);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json([
                'status' => 'error',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }
        
        $em->flush();

        return $this->json($task);

        } catch(\Exception $e){

            $this->logger->error('Task update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Task $task, EntityManagerInterface $em): Response
    {
        // Проверка существования задачи
        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $em->remove($task);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
