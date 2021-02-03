<?php declare(strict_types=1);

class RodneCislo
{

    public const GENDER_FEMALE = 'F';
    public const GENDER_MALE = 'M';

    private string $rawValue;

    private \DateTimeImmutable $birthDate;

    private string $sequenceNumber;

    private string $gender;

    private bool $isExtraSequence;

    public static function parse(string $rc): self
    {
        $rc = \trim($rc);

        if (\strlen($rc) === 0) {
            throw new InvalidArgumentException('Value cannot be empty');
        }

        // remove everything except for numbers
        $rc = \preg_replace('~[^\d]~', '', $rc);

        $len = \strlen($rc);

        if ($len < 9 || $len > 10) {
            throw new FormatException('Value must contain 9 or 10 digits');
        }

        // year
        $year = (int) \substr($rc, 0, 2);

        if ($len === 9) {
            $year += $year < 54 ? 1900 : 1800;
        } else {
            $year += $year < 54 ? 2000 : 1900;
        }

        // month
        $month = (int) \substr($rc, 2, 2);

        if ($month >= 1 && $month <= 12) {
            $gender = self::GENDER_MALE;
            $extraSequence = false;
        } elseif ($month >= 21 && $month <= 32) {
            $gender = self::GENDER_MALE;
            $extraSequence = true;
            $month -= 20;
        } elseif ($month >= 51 && $month <= 62) {
            $gender = self::GENDER_FEMALE;
            $extraSequence = false;
            $month -= 50;
        } elseif ($month >= 71 && $month <= 82) {
            $gender = self::GENDER_FEMALE;
            $extraSequence = true;
            $month -= 70;
        } else {
            throw new FormatException('Value contains invalid month');
        }

        $day = (int) \substr($rc, 4, 2);

        if ( ! \checkdate($month, $day, $year)) {
            throw new FormatException('Value contains invalid date');
        }

        $sequence = \substr($rc, 6);

        if ($year >= 1954 && (int) $rc % 11 > 0) {
            throw new FormatException('Value contains invalid checkum');
        }

        $birthDate = new \DateTimeImmutable();
        $birthDate->setDate($year, $month, $day);

        $s = new self();
        $s->rawValue = $rc;
        $s->birthDate = $birthDate;
        $s->gender = $gender;
        $s->isExtraSequence = $extraSequence;
        $s->sequenceNumber = $sequence;
    }

    public function toString(bool $withSeparator = true): string
    {
        if ($withSeparator) {
            return \substr($this->rawValue, 0, 6) . '/' . \substr($this->rawValue, 7);
        }

        return $this->rawValue;
    }

}
