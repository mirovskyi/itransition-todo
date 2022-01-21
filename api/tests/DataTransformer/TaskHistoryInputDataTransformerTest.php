<?php

namespace App\Tests\DataTransformer;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\DataTransformer\TaskHistoryInputDataTransformer;
use App\Dto\TaskHistoryInput;
use App\Entity\Task;
use App\Entity\TaskHistory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskHistoryInputDataTransformerTest extends TestCase
{
    public function testSupportsTransformations(): void
    {
        $transformer = new TaskHistoryInputDataTransformer($this->createMock(ValidatorInterface::class));
        $this->assertTrue($transformer->supportsTransformation(
            new TaskHistoryInput(),
            TaskHistory::class,
            ['input' => ['class' => 'App\Dto\TaskHistoryInput', 'name' => 'TaskHistoryInput']]
        ));
        $this->assertFalse($transformer->supportsTransformation(
            $this->createTaskHistoryMock(),
            TaskHistory::class,
            ['input' => ['class' => 'App\Dto\TaskHistoryInput', 'name' => 'TaskHistoryInput']]
        ));
        $this->assertFalse($transformer->supportsTransformation(
            new TaskHistoryInput(),
            Task::class,
            ['input' => ['class' => 'App\Dto\TaskHistoryInput', 'name' => 'TaskHistoryInput']]
        ));
    }

    public function testTransform()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate');
        $transformer = new TaskHistoryInputDataTransformer($validator);
        $taskHistoryInput = new TaskHistoryInput();
        $taskHistoryInput->task = $this->createTaskMock();
        $taskHistory = $transformer->transform($taskHistoryInput, TaskHistory::class);

        $this->assertInstanceOf(TaskHistory::class, $taskHistory);
        $this->assertNotNull($taskHistory->getTask());
        $this->assertNotNull($taskHistory->getCompletedDate());
        $this->assertNotNull($taskHistory->getCreatedAt());
        $this->assertNotNull($taskHistory->getUuid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $taskHistory->getCompletedDate());
        $this->assertSame($taskHistory->getCompletedDate()->format('Ymd'), (new \DateTimeImmutable())->format('Ymd'));
    }

    private function createTaskHistoryMock(): MockObject
    {
        return $this->getMockBuilder(TaskHistory::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function createTaskMock(): MockObject
    {
        return $this->getMockBuilder(Task::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
