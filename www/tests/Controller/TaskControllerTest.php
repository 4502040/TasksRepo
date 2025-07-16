<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use App\DataFixtures\TaskFixtures;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
final class TaskControllerTest extends WebTestCase
{    
    private $token = "";

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

        $container = self::getContainer();
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        // Создаем пользователя напрямую
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
                
        $em->persist($user);
        $em->flush();

        $this->token = $this->authenticate($client,'test@example.com','password');
    }
    
    protected function tearDown(): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->createQuery('DELETE FROM App\Entity\Task')->execute();
        $em->createQuery('DELETE FROM App\Entity\User')->execute();
        parent::tearDown();
    }

    public function testIndex(): void
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $client->request('GET', '/api/tasks',
        [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->token]);
        
        self::assertResponseIsSuccessful();
    }   

    public function testFilterByStatus()
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        
        // Фильтруем по статусу    
        $client->request('GET', '/api/tasks?status=completed',
        [], [], ['HTTP_AUTHORIZATION' => 'Bearer '.$this->token] );
        
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
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$this->token],
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

    private function authenticate(KernelBrowser $client, string $email, string $password): string
    {
        $client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $email,
                'password' => $password
            ])
        );
        
        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'];
    }
}
