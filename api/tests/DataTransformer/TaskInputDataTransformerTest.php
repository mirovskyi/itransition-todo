<?php

namespace App\Tests\DataTransformer;

use ApiPlatform\Core\Bridge\Symfony\Validator\Validator;
use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\DataTransformer\TaskInputDataTransformer;
use App\Dto\TaskInput;
use App\Entity\Task;
use App\Utils\WeekScheduler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class TaskInputDataTransformerTest extends TestCase
{
    public function testSupportsTransformations(): void
    {
        $transformer = new TaskInputDataTransformer(
            $this->createMock(Security::class),
            $this->createMock(Validator::class)
        );
        $this->assertTrue($transformer->supportsTransformation(
            new TaskInput(),
            Task::class,
            ['input' => ['class' => 'App\Dto\TaskInput', 'name' => 'TaskInput']]
        ));
        $this->assertFalse($transformer->supportsTransformation(
            $this->createTaskMock(),
            Task::class,
            ['input' => ['class' => 'App\Dto\TaskInput', 'name' => 'TaskInput']]
        ));
        $this->assertFalse($transformer->supportsTransformation(
            new TaskInput(),
            TaskInput::class,
            ['input' => ['class' => 'App\Dto\TaskInput', 'name' => 'TaskInput']]
        ));
    }

    public function testInitializeNull()
    {
        $transformer = new TaskInputDataTransformer(
            $this->createMock(Security::class),
            $this->createMock(Validator::class)
        );
        $this->assertNull($transformer->initialize(TaskInput::class));
    }

    public function testInitialize()
    {
        $currentDate = new \DateTime();
        $weekDays = [0,6];

        $transformer = new TaskInputDataTransformer(
            $this->createMock(Security::class),
            $this->createMock(Validator::class)
        );
        $task = $this->createTaskMock();
        $task->expects(self::any())->method('getName')->willReturn('Test');
        $task->expects(self::any())->method('getDescription')->willReturn('Test desc');
        $task->expects(self::any())->method('isEnabled')->willReturn(true);
        $task->expects(self::any())->method('getPriority')->willReturn(1);
        $task->expects(self::any())->method('getOneDay')->willReturn($currentDate);
        $task->expects(self::any())->method('isAlways')->willReturn(true);
        $task->expects(self::any())->method('getWeekSchedule')->willReturn(WeekScheduler::convertWeekDaysListToBitMask($weekDays));

        //Initialize for PATCH operation
        $taskInput = $transformer->initialize(TaskInput::class, [AbstractItemNormalizer::OBJECT_TO_POPULATE => $task]);
        $this->assertSame('Test', $taskInput->name);
        $this->assertSame('Test desc', $taskInput->description);
        $this->assertSame(true, $taskInput->enabled);
        $this->assertSame(1, $taskInput->priority);
        $this->assertSame($currentDate->format('Ymd'), $taskInput->oneDay->format('Ymd'));
        $this->assertSame(true, $taskInput->always);
        $this->assertSame($weekDays, $taskInput->weekSchedule);

        //Initialize for PUT operation
        $taskInput = $transformer->initialize(TaskInput::class, [
            AbstractItemNormalizer::OBJECT_TO_POPULATE => $task,
            'item_operation_name' => 'put'
        ]);
        $this->assertNull($taskInput->name);
        $this->assertNull($taskInput->description);
        $this->assertTrue($taskInput->enabled);
        $this->assertSame(0, $taskInput->priority);
        $this->assertNull($taskInput->oneDay);
        $this->assertNull($taskInput->always);
        $this->assertNull($taskInput->weekSchedule);
    }

    public function testPostTransform()
    {
        $currentDate = new \DateTime();
        $weekDays = [1,2,3,4,5];

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate');

        $security = $this->createMock(Security::class);
        $security->expects(self::once())->method('getUser')->willReturn(
            $this->createMock(UserInterface::class)
        );

        $transformer = new TaskInputDataTransformer($security, $validator);
        $taskInput = new TaskInput();
        $taskInput->name = 'Test';
        $taskInput->description = 'Test desc';
        $taskInput->priority = 1;
        $taskInput->always = true;
        $taskInput->oneDay = $currentDate;
        $taskInput->weekSchedule = $weekDays;
        $task = $transformer->transform($taskInput, Task::class);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame('Test', $task->getName());
        $this->assertSame('Test desc', $task->getDescription());
        $this->assertSame(1, $task->getPriority());
        $this->assertSame(true, $task->isEnabled());
        $this->assertSame(true, $task->isAlways());
        $this->assertSame($currentDate, $task->getOneDay());
        $this->assertSame(WeekScheduler::convertWeekDaysListToBitMask($weekDays), $task->getWeekSchedule());
        $this->assertInstanceOf(\DateTimeImmutable::class, $task->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $task->getUpdatedAt());
        $this->assertInstanceOf(Uuid::class, $task->getUuid());
    }

    public function testDefaultScheduleTransfer()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $security = $this->createMock(Security::class);
        $security->expects(self::once())->method('getUser')->willReturn(
            $this->createMock(UserInterface::class)
        );

        $transformer = new TaskInputDataTransformer($security, $validator);
        $taskInput = new TaskInput();
        $taskInput->name = 'Test';
        $taskInput->description = 'Test desc';
        $task = $transformer->transform($taskInput, Task::class);

        $this->assertNull($task->isAlways());
        $this->assertNull($task->getWeekSchedule());
        $this->assertInstanceOf(\DateTime::class, $task->getOneDay());
        $this->assertSame((new \DateTime())->format('Ymd'), $task->getOneDay()->format('Ymd'));
    }

    public function testPutOrPatchTransfer()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate');
        $security = $this->createMock(Security::class);
        $task = $this->createMock(Task::class);
        $task->expects(self::once())->method('setName');
        $task->expects(self::once())->method('setDescription');
        $task->expects(self::once())->method('setEnabled');
        $task->expects(self::once())->method('setPriority');
        $task->expects(self::once())->method('setAlways');
        $task->expects(self::once())->method('setOneDay');
        $task->expects(self::once())->method('setWeekSchedule');

        $taskInput = new TaskInput();
        $taskInput->name = 'Test';
        $transformer = new TaskInputDataTransformer($security, $validator);
        $transformer->transform($taskInput, Task::class, [AbstractItemNormalizer::OBJECT_TO_POPULATE => $task]);
    }

    private function createTaskMock(): MockObject
    {
        return $this->getMockBuilder(Task::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
