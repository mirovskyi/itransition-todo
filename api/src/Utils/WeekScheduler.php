<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Week scheduler
 *
 * This class is responsible to convert an array of week-day indexes to bitmask and vice versa.
 * For example the bitmask for scheduler with active monday and wednesday will be MONDAY | WEDNESDAY,
 * it equals to bit operation: 0000010 | 0001000 = 0001010
 */
class WeekScheduler
{
    /**
     * Week days bitmask
     */
    public const BITMASK_SUNDAY    = 1<<0;
    public const BITMASK_MONDAY    = 1<<1;
    public const BITMASK_TUESDAY   = 1<<2;
    public const BITMASK_WEDNESDAY = 1<<3;
    public const BITMASK_THURSDAY  = 1<<4;
    public const BITMASK_FRIDAY    = 1<<5;
    public const BITMASK_SATURDAY  = 1<<6;

    /**
     * Convert bitmask to array of week-day indexes
     * For example 3 (0000011) will be converted to [0,1]
     *
     * @param int $bitMask Integer representation of the bitmask
     *
     * @return array Returns list of week-day indexes
     */
    public static function convertBitMaskToWeekDaysList(int $bitMask): array
    {
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            if (($bitMask&(1<<$i)) > 0) {
                $weekDays[] = $i;
            }
        }

        return $weekDays;
    }

    /**
     * Convert week-day indexes list to bitmask
     *
     * @param array $weekDays Lis of week-day indexes
     *
     * @return int Returns integer representation of week-day indexes bitmask
     */
    public static function convertWeekDaysListToBitMask(array $weekDays): int
    {
        $bitMask = 0;
        foreach ($weekDays as $weekDay) {
            $bitMask = $bitMask|(1<<(int)$weekDay);
        }

        return $bitMask;
    }

    /**
     * Gets week-day bitmask by particular date.
     * Week-day index will be calculated from the given date object.
     *
     * @param \DateTimeInterface $date
     *
     * @return int
     */
    public static function getWeekDayBitMask(\DateTimeInterface $date): int
    {
        return 1<<(int)$date->format('w');
    }

    /**
     * Check is weekly scheduler bitmask includes given date
     *
     * @param int       $scheduleBitMask Weekly scheduler bitmask
     * @param \DateTime $date            Date that will be checked
     *
     * @return bool
     */
    public static function isActiveOnDate(int $scheduleBitMask, \DateTime $date): bool
    {
        return (self::getWeekDayBitMask($date)&$scheduleBitMask) > 0;
    }
}
