<?php

namespace App\Tests\DataTransformer;

use App\DataTransformer\TaskHistoryOutputDataTransformer;
use App\DataTransformer\TaskOutputDataTransformer;
use App\Dto\TaskHistoryInput;
use App\Dto\TaskHistoryOutput;
use App\Dto\TaskInput;
use App\Dto\TaskOutput;
use App\Entity\Task;
use App\Entity\TaskHistory;
use App\Utils\WeekScheduler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TaskOutputDataTransformerTest extends TestCase
{
    public function testSupportsTransformations(): void
    {
        $transformer = new TaskOutputDataTransformer();

        $this->assertTrue($transformer->supportsTransformation(
            $this->createTaskMock(),
            TaskOutput::class
        ));
        $this->assertFalse($transformer->supportsTransformation(
            $this->createTaskMock(),
            TaskInput::class
        ));
        $this->assertFalse($transformer->supportsTransformation(
            new TaskOutput(),
            TaskOutput::class
        ));
    }

    public function testTransform(): void
    {
        $currentDate = new \DateTime();
        $currentDateImmutable = new \DateTimeImmutable();
        $taskUuid = Uuid::v4();
        $weekDays = [1,2,3,4,5,6];

        $task = $this->createTaskMock();
        $task->expects(self::once())->method('getUuid')->willReturn($taskUuid);
        $task->expects(self::once())->method('getName')->willReturn('Test');
        $task->expects(self::once())->method('getDescription')->willReturn('Test desc');
        $task->expects(self::once())->method('isEnabled')->willReturn(true);
        $task->expects(self::once())->method('getPriority')->willReturn(1);
        $task->expects(self::once())->method('isAlways')->willReturn(true);
        $task->expects(self::once())->method('getOneDay')->willReturn($currentDate);
        $task->expects(self::exactly(2))->method('getWeekSchedule')->willReturn(WeekScheduler::convertWeekDaysListToBitMask($weekDays));
        $task->expects(self::once())->method('getCreatedAt')->willReturn($currentDateImmutable);
        $task->expects(self::once())->method('getUpdatedAt')->willReturn($currentDate);

        $transformer = new TaskOutputDataTransformer();
        $output = $transformer->transform($task, TaskOutput::class);

        $this->assertSame($taskUuid, $output->uuid);
        $this->assertSame('Test', $output->name);
        $this->assertSame('Test desc', $output->description);
        $this->assertSame(true, $output->enabled);
        $this->assertSame(1, $output->priority);
        $this->assertSame(true, $output->always);
        $this->assertSame($currentDate, $output->oneDay);
        $this->assertSame($weekDays, $output->weekSchedule);
        $this->assertSame($currentDateImmutable, $output->createdAt);
        $this->assertSame($currentDate, $output->updatedAt);
    }

    private function createTaskMock(): MockObject
    {
        return $this->getMockBuilder(Task::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
