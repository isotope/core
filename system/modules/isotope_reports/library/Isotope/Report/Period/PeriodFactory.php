<?php

namespace Isotope\Report\Period;

class PeriodFactory
{

    public static function create($type)
    {
        switch ($type) {
            case 'day':
                return new Day();

            case 'week':
                return new Week();

            case 'month':
                return new Month();

            case 'year':
                return new Year();

            default:
                throw new \InvalidArgumentException('Invalid period "' . $type . '". Reset your session to continue.');
        }
    }
}
