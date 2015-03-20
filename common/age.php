<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * A common class between the admin and public facing sides.
 * Constructor takes a date in the format of YYYY-MM-DD.
 * It will list the date into Year, Month, Day and full date.
 */
class McKenzieTownAge {
    private $date;
    private $year;
    private $month;
    private $day;
    private $age;
    private $grade;
    private $group;


    public function __construct($date) {
        $this->date = $date;

        list($this->year, $this->month, $this->day) = explode('-', $date);

    }

    /**
     * Given a year from instantiating the class the age of
     * the user is determined.
     * @return int number of years old the user is.
     */
    public function determineAge() {
        $timeInOneYear = 365.256 * 24 * 60 * 60;
        $yearsofAge = floor((strtotime(date('Y')) - strtotime($this->year.'-12-31'))/ $timeInOneYear);

        $this->age = $yearsofAge;

        return (int) $yearsofAge;
    }

    /**
     * Based upon age from instantiating the class the
     * grade is determined. The month also instantiated 
     * from the class is used to determine the grade the
     * child should be in.
     * @return int return a grade number
     */
    public function determineGradeByAge() {
        $grade = false;
        switch ($this->age) {
            case 10:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 4;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 5;
                }
                break;
            case 11:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 5;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 6;
                }
                break;
            case 12:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 6;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 7;
                }
                break;
            case 13:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 7;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 8;
                }
                break;
            case 14:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 8;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 9;
                }
                break;
            case 15:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 9;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 10;
                }
                break;
            case 16:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 10;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 11;
                }
                break;
            case 17:
                if ($this->month >= 1 && $this->month <= 6) {
                    $grade = 11;
                } elseif ($this->month >= 7 && $this->month <= 12) {
                    $grade = 12;
                }
                break;
            default:
                $grade = 0;
                break;
        }
        $this->grade = $grade;
        return (int) $grade;
    }

    /**
     * Uaing the grade determined by determineGradeByAge() a
     * youth group is selected. These strings represent the 
     * groups availabe in mailchimp.
     * @return string A string of what the youth group is
     */
    public function findGradeGroup() {
        $group = false;

        if ($this->grade === 5 || $this->grade === 6) {
            $group = '5-6';
        } elseif ($this->grade >= 7 && $this->grade <= 9) {
            $group = 'Jr High';
        } elseif ($this->grade >= 10 && $this->grade <= 12) {
            $group = 'Sr High';
        }
        $this->group = $group;
        return (string) $group;

    }
}