<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TasksTest extends AbstractApiTest
{
    use ReloadDatabaseTrait;

    public function testGetCollectionOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get all tasks ordered by priority
        $json = $this->getTasks($client, '?page=1&order[priority]=asc')->toArray();
        $this->assertSame('/tasks', $json['@id']);
        $this->assertSame(4, $json['hydra:totalItems']);
        for ($i = 0; $i < 4; $i++) {
            $this->assertSame('U1_Task' . ($i + 1), $json['hydra:member'][$i]['name']);
        }

        //Get all enabled tasks ordered by priority desc
        $json = $this->getTasks($client, '?page=1&enabled=true&order[priority]=desc')->toArray();
        $this->assertSame(3, $json['hydra:totalItems']);
        for ($i = 0; $i < 3; $i++) {
            $this->assertSame('U1_Task' . (3 - $i), $json['hydra:member'][$i]['name']);
        }

        //Get tasks for today only
        $currentDate = new \DateTime();
        $json = $this->getTasks($client, '?page=1&enabled=true&oneDay=' . $currentDate->format('Y-m-d'))->toArray();
        $this->assertSame(1, $json['hydra:totalItems']);

        //Get tasks for every day
        $json = $this->getTasks($client, '?page=1&enabled=true&always=true')->toArray();
        $this->assertSame(1, $json['hydra:totalItems']);

        //Get tasks for monday
        $json = $this->getTasks($client, '?page=1&enabled=true&weekSchedule=1')->toArray();
        $this->assertSame(1, $json['hydra:totalItems']);
    }

    public function testGetItemOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get every day tasks
        $tasks = $this->getTasks($client, '?page=1&always=true')->toArray()['hydra:member'];

        //Get first task
        $response = $client->request('GET', $tasks[0]['@id']);
        $this->assertSame(200, $response->getStatusCode());
        $task1 = $response->toArray();
        $this->assertSame($tasks[0]['@id'], $task1['@id']);
        //Check item additional fields
        $this->assertArrayHasKey('always', $task1);
        $this->assertArrayHasKey('createdAt', $task1);
        $this->assertArrayHasKey('updatedAt', $task1);

        //Check security
        $response = self::createAuthClient(self::USER2)->request('GET', $task1['@id']);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testPostOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Create new task
        $response = $client->request('POST', '/tasks', [
            'body' => json_encode([
                'name' => 'CreatedByTest',
                'enabled' => true,
                'priority' => 4,
            ])
        ]);
        $this->assertSame(201, $response->getStatusCode());
        $task = $response->toArray();

        //Check increasing tasks list count
        $json = $this->getTasks($client)->toArray();
        $this->assertSame(5, $json['hydra:totalItems']);

        //Check getting new task data
        $response = $client->request('GET', $task['@id']);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testPutOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get tasks for today only
        $currentDate = new \DateTime();
        $json = $this->getTasks($client, '?page=1&enabled=true&oneDay=' . $currentDate->format('Y-m-d'))->toArray();
        $task = $json['hydra:member'][0];

        //Update task data
        $response = $client->request('PUT', $task['@id'], [
            'body' => json_encode([
                'name' => 'ReplacedTask',
                'description' => 'Test PUT operation',
                'enabled' => true,
                'priority' => $task['priority'] + 1,
                'weekSchedule' => [5]
            ])
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $replacedTask = $response->toArray();
        //Check not changed params
        $this->assertSame($task['uuid'], $replacedTask['uuid']);
        //Check changes
        $this->assertSame('ReplacedTask', $replacedTask['name']);
        $this->assertSame($task['priority'] + 1, $replacedTask['priority']);
        $this->assertSame('Test PUT operation', $replacedTask['description']);
        $this->assertArrayNotHasKey('oneDay', $replacedTask);
        $this->assertArrayNotHasKey('always', $replacedTask);
        $this->assertSame([5], $replacedTask['weekSchedule']);

        //Update task data with only required fields, all other fields should be reset to default automatically
        $response = $client->request('PUT', $task['@id'], [
            'body' => json_encode(['name' => 'ReplacedTask2'])
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $replacedTask = $response->toArray();
        //Check not changed params
        $this->assertSame($task['uuid'], $replacedTask['uuid']);
        //Check changes
        $this->assertSame('ReplacedTask2', $replacedTask['name']);
        $this->assertSame(0, $replacedTask['priority']);
        $this->assertArrayNotHasKey('description', $replacedTask);
        //By default task is created for today only
        $this->assertSame($currentDate->format('Ymd'), (new \DateTime($replacedTask['oneDay']))->format('Ymd'));
        $this->assertArrayNotHasKey('always', $replacedTask);
        $this->assertArrayNotHasKey('weekSchedule', $replacedTask);

        //Security check
        $response = self::createAuthClient(self::USER2)->request('PUT', $task['@id'], [
            'body' => json_encode(['name' => 'U2_Task1'])
        ]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testPatchOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get every day tasks
        $json = $this->getTasks($client, '?page=1&enabled=true&always=true')->toArray();
        $task = $json['hydra:member'][0];

        //Update only task priority
        $response = $client->request('PATCH', $task['@id'], [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json'
            ],
            'body' => json_encode(['priority' => $task['priority'] + 1])
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $updatedTask = $response->toArray();
        //Check priority was changed
        $this->assertSame($task['priority'] + 1, $updatedTask['priority']);
        //Check other parameters wasn't changed
        $this->assertSame($task['name'], $updatedTask['name']);
        $this->assertSame($task['description'], $updatedTask['description']);
        $this->assertSame($task['enabled'], $updatedTask['enabled']);
        $this->assertArrayNotHasKey('oneDay', $updatedTask);
        $this->assertSame(true, $updatedTask['always']);
        $this->assertArrayNotHasKey('weekSchedule', $updatedTask);

        //Security check
        $response = self::createAuthClient(self::USER2)->request('PATCH', $task['@id'], [
            'body' => json_encode(['name' => 'U2_Task1'])
        ]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeleteOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get all tasks ordered by priority
        $tasks = $this->getTasks($client)->toArray();
        $tasksCount = $tasks['hydra:totalItems'];

        //Delete first task from the retrieved list
        $taskLink = $tasks['hydra:member'][0]['@id'];
        $response = $client->request('DELETE', $taskLink);
        $this->assertSame(204, $response->getStatusCode());

        //Check tasks count after deletion
        $json = $this->getTasks($client)->toArray();
        $this->assertSame($tasksCount - 1, $json['hydra:totalItems']);

        //Try to get deleted task
        $response = $client->request('GET', $taskLink);
        $this->assertSame(404, $response->getStatusCode());

        //Security check
        $response = self::createAuthClient(self::USER2)->request('DELETE', $tasks['hydra:member'][1]['@id']);
        $this->assertSame(404, $response->getStatusCode());
    }

    private function getTasks(Client $client, string $filters = ''): ResponseInterface
    {
        $response = $client->request('GET', '/tasks' . $filters);
        $this->assertSame(200, $response->getStatusCode());

        return $response;
    }
}
