<?php

namespace App\Tests\DataTransformer;

use App\DataTransformer\TaskHistoryOutputDataTransformer;
use App\Dto\TaskHistoryInput;
use App\Dto\TaskHistoryOutput;
use App\Entity\Task;
use App\Entity\TaskHistory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TaskHistoryOutputDataTransformerTest extends TestCase
{
    public function testSupportsTransformations(): void
    {
        $transformer = new TaskHistoryOutputDataTransformer();

        $this->assertTrue($transformer->supportsTransformation(
            $this->createTaskHistoryMock(),
            TaskHistoryOutput::class
        ));
        $this->assertFalse($transformer->supportsTransformation(
            $this->createTaskHistoryMock(),
            TaskHistoryInput::class
        ));
        $this->assertFalse($transformer->supportsTransformation(
            $this->createTaskMock(),
            TaskHistoryOutput::class
        ));
    }

    public function testTransform(): void
    {
        $currentDate = new \DateTimeImmutable();
        $taskHistoryUuid = Uuid::v4();

        $taskHistory = $this->createTaskHistoryMock();
        $taskHistory->expects(self::once())->method('getTask');
        $taskHistory->expects(self::once())->method('getUuid')->willReturn($taskHistoryUuid);
        $taskHistory->expects(self::once())->method('getCompletedDate')->willReturn($currentDate);
        $taskHistory->expects(self::once())->method('getCreatedAt')->willReturn($currentDate);
        $transformer = new TaskHistoryOutputDataTransformer();

        $output = $transformer->transform($taskHistory, TaskHistoryOutput::class);
        $this->assertSame($taskHistoryUuid, $output->uuid);
        $this->assertSame($currentDate, $output->completedDate);
        $this->assertSame($currentDate, $output->createdAt);
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
