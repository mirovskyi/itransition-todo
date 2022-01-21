<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TaskHistoriesTest extends AbstractApiTest
{
    public function tes2tGetCollectionOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get all tasks history
        $json = $this->getTaskHistories($client)->toArray();
        $this->assertSame(2, $json['hydra:totalItems']);

        //Check completed date filter
        $currentDate = new \DateTime();
        $json = $this->getTaskHistories($client, '?completedDate=' . $currentDate->format('Y-m-d'))->toArray();
        $this->assertSame(2, $json['hydra:totalItems']);

        $currentDate->modify('-1 days');
        $json = $this->getTaskHistories($client, '?completedDate=' . $currentDate->format('Y-m-d'))->toArray();
        $this->assertSame(0, $json['hydra:totalItems']);
    }

    public function testGetItemOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get al tasks history
        $tasks = $this->getTaskHistories($client)->toArray()['hydra:member'];

        //Get first task history from the list
        $response = $client->request('GET', $tasks[0]['@id']);
        $this->assertSame(200, $response->getStatusCode());
        $task = $response->toArray();
        $this->assertSame($tasks[0]['@id'], $task['@id']);
        $this->assertArrayHasKey('createdAt', $task);

        //Security check
        $response = self::createAuthClient(self::USER2)->request('GET', $task['@id']);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testPostOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get today active tasks
        $response = $client->request('GET', '/today_tasks');
        $this->assertSame(200, $response->getStatusCode());
        //Find not completed task
        $activeTaskUuid = null;
        foreach ($response->toArray()['hydra:member'] as $task) {
            if ($task['completed'] === false) {
                $activeTaskUuid = $task['uuid'];
                break;
            }
        }

        //Create new record
        $response = $client->request('POST', '/task_histories', [
            'body' => json_encode(['task' => '/tasks/' . $activeTaskUuid])
        ]);
        $this->assertSame(201, $response->getStatusCode());

        //Check today tasks
        $response = $client->request('GET', '/today_tasks');
        foreach ($response->toArray()['hydra:member'] as $task) {
            if ($task['uuid'] === $activeTaskUuid) {
                $this->assertTrue($task['completed']);
                break;
            }
        }
    }

    public function testDeleteOperation(): void
    {
        $client = self::createAuthClient(self::USER1);
        //Get history records
        $tasks = $this->getTaskHistories($client)->toArray();

        //Delete one task history
        $response = $client->request('DELETE', $tasks['hydra:member'][0]['@id']);
        $this->assertSame(204, $response->getStatusCode());

        //Check records count
        $tasksAfterDelete = $this->getTaskHistories($client)->toArray();
        $this->assertLessThan($tasks['hydra:totalItems'], $tasksAfterDelete['hydra:totalItems']);

        //Check security
        $response = self::createAuthClient(self::USER2)->request('DELETE', $tasks['hydra:member'][1]['@id']);
        $this->assertSame(403, $response->getStatusCode());

    }

    private function getTaskHistories(Client $client, $filter = ''): ResponseInterface
    {
        $response = $client->request('GET', '/task_histories' . $filter);
        $this->assertSame(200, $response->getStatusCode());

        return $response;
    }
}
