<?php

namespace App\Tests\Utils;

use App\Utils\WeekScheduler;
use PHPUnit\Framework\TestCase;

class WeekSchedulerTest extends TestCase
{
    public function testConvertBitMaskToWeekDaysList(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $this->assertSame([$i], WeekScheduler::convertBitMaskToWeekDaysList(1<<$i));
        }
        $this->assertSame([1,2,3,4,5], WeekScheduler::convertBitMaskToWeekDaysList($this->getWorkingDaysBitMask()));
        $this->assertSame([0,6], WeekScheduler::convertBitMaskToWeekDaysList($this->getWeekendDaysBitMask()));
    }

    public function testConvertWeekDaysListToBitMask(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $this->assertSame(1<<$i, WeekScheduler::convertWeekDaysListToBitMask([$i]));
        }
        $this->assertSame($this->getWorkingDaysBitMask(), WeekScheduler::convertWeekDaysListToBitMask([1,2,3,4,5]));
        $this->assertSame($this->getWorkingDaysBitMask(), WeekScheduler::convertWeekDaysListToBitMask([5,4,3,2,1]));
        $this->assertSame($this->getWeekendDaysBitMask(), WeekScheduler::convertWeekDaysListToBitMask([0,6]));
        $this->assertSame($this->getWeekendDaysBitMask(), WeekScheduler::convertWeekDaysListToBitMask([6,0]));
    }

    public function testGetWeekDayBitMask(): void
    {
        $this->assertSame(WeekScheduler::BITMASK_SUNDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-09')));
        $this->assertSame(WeekScheduler::BITMASK_MONDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-10')));
        $this->assertSame(WeekScheduler::BITMASK_TUESDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-11')));
        $this->assertSame(WeekScheduler::BITMASK_WEDNESDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-12')));
        $this->assertSame(WeekScheduler::BITMASK_THURSDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-13')));
        $this->assertSame(WeekScheduler::BITMASK_FRIDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-14')));
        $this->assertSame(WeekScheduler::BITMASK_SATURDAY, WeekScheduler::getWeekDayBitMask(new \DateTime('2022-01-15')));
    }

    public function testIsActiveOnDate(): void
    {
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_SUNDAY, new \DateTime('2022-01-09')));
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_MONDAY, new \DateTime('2022-01-10')));
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_TUESDAY, new \DateTime('2022-01-11')));
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_WEDNESDAY, new \DateTime('2022-01-12')));
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_THURSDAY, new \DateTime('2022-01-13')));
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_FRIDAY, new \DateTime('2022-01-14')));
        $this->assertTrue(WeekScheduler::isActiveOnDate(WeekScheduler::BITMASK_SATURDAY, new \DateTime('2022-01-15')));

        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWeekendDaysBitMask(), new \DateTime('2022-01-09')));
        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWorkingDaysBitMask(), new \DateTime('2022-01-10')));
        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWorkingDaysBitMask(), new \DateTime('2022-01-11')));
        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWorkingDaysBitMask(), new \DateTime('2022-01-12')));
        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWorkingDaysBitMask(), new \DateTime('2022-01-13')));
        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWorkingDaysBitMask(), new \DateTime('2022-01-14')));
        $this->assertTrue(WeekScheduler::isActiveOnDate($this->getWeekendDaysBitMask(), new \DateTime('2022-01-15')));
    }

    private function getWorkingDaysBitMask(): int
    {
        return WeekScheduler::BITMASK_MONDAY | WeekScheduler::BITMASK_TUESDAY | WeekScheduler::BITMASK_WEDNESDAY |
            WeekScheduler::BITMASK_THURSDAY | WeekScheduler::BITMASK_FRIDAY;
    }

    private function getWeekendDaysBitMask(): int
    {
        return WeekScheduler::BITMASK_SUNDAY | WeekScheduler::BITMASK_SATURDAY;
    }
}
