<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $statuses = ['new', 'in_progress', 'completed'];
        
        for ($i = 1; $i <= 20; $i++) {
            $task = new Task();
            $task->setTitle("Test Task $i");
            $task->setDescription("Description for task $i");
            $task->setStatus($statuses[array_rand($statuses)]);
            $task->setCreatedAt(new \DateTime());
            
            $manager->persist($task);
        }

        $manager->flush();
    }
}
