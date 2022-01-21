<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TodayTaskViewTest extends AbstractApiTest
{
    public function testTodayTaskView(): void
    {
        $client1 = $this->createAuthClient(self::USER1);
        //Check today tasks for user1
        $user1Tasks = $this->getTodayTasks($client1)->toArray();
        $this->assertSame(4, $user1Tasks['hydra:totalItems']);

        //Check today tasks for user2
        $client2 = self::createAuthClient(self::USER2);
        $user2Tasks = $this->getTodayTasks($client2)->toArray();
        $this->assertSame(0, $user2Tasks['hydra:totalItems']);

        //Create task for user2
        $response = $client2->request('POST', '/tasks', [
            'body' => json_encode(['name' => 'Today task for user2'])
        ]);
        $this->assertSame(201, $response->getStatusCode());

        //Check today tasks for user2
        $user2TasksAfterAdding = $this->getTodayTasks($client2)->toArray();
        $this->assertSame(1, $user2TasksAfterAdding['hydra:totalItems']);
        $this->assertFalse($user2TasksAfterAdding['hydra:member'][0]['completed']);

        //Complete new task
        $response = $client2->request('POST', '/task_histories', [
            'body' => json_encode(['task' => '/tasks/' . $user2TasksAfterAdding['hydra:member'][0]['uuid']])
        ]);
        $this->assertSame(201, $response->getStatusCode());

        //Check completed status
        $tasks = $this->getTodayTasks($client2)->toArray();
        $this->assertTrue($tasks['hydra:member'][0]['completed']);
    }

    private function getTodayTasks(Client $client): ResponseInterface
    {
        $response = $client->request('GET', '/today_tasks');
        $this->assertSame(200, $response->getStatusCode());

        return $response;
    }
}
