<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use App\DataFixtures\TaskFixtures;

final class TaskControllerTest extends WebTestCase
{    

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        
        // Получаем EntityManager
        $em = $client->getContainer()->get('doctrine')->getManager();
        
        // Очистка базы данных
        $purger = new ORMPurger($em);
        $purger->purge();
        
        // Загрузка фикстур
        $executor = new ORMExecutor($em, $purger);
        $executor->execute([new TaskFixtures()]);
    }
    
    protected function tearDown(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\Task')->execute();
        parent::tearDown();
    }

    public function testIndex(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/api/tasks');

        self::assertResponseIsSuccessful();
    }   

    public function testFilterByStatus()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        
        // Фильтруем по статусу
        $client->request('GET', '/api/tasks?status=completed');
        
        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);        
        
        $this->assertEquals('completed', $response['data'][0]['status']);
    }

    // tests/Controller/TaskControllerTest.php

    public function testUpdateValidation()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        
        // Сначала создаем валидную задачу
        $client->request(
            'POST',
            '/api/tasks',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Valid Task',
                'status' => 'new'
            ])
        );
        
        $taskId = json_decode($client->getResponse()->getContent(), true)['id'];
        
        // Пробуем обновить с невалидным статусом
        $client->request(
            'PUT',
            '/api/tasks/'.$taskId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'status' => 'invalid_status'
            ])
        );
        
        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('status', $response['errors']);
    }    
}
