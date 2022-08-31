<?php

namespace Faker\Test\Provider\en_SG;

use Faker\Provider\en_SG\Person;
use Faker\Test\TestCase;

/**
 * @group legacy
 */
final class PersonTest extends TestCase
{
    public function testFirstNameMaleMalay()
    {
        self::assertNotEmpty(Person::firstNameMaleMalay());
    }

    public function testFirstNameFemaleMalay()
    {
        self::assertNotEmpty(Person::firstNameFemaleMalay());
    }

    public function testLastNameMalay()
    {
        self::assertNotEmpty(Person::lastNameMalay());
    }

    public static function testMuhammadName()
    {
        self::assertNotEmpty(Person::muhammadName());
    }

    public static function testNurName()
    {
        self::assertNotEmpty(Person::nurName());
    }

    public static function testHaji()
    {
        self::assertNotEmpty(Person::haji());
    }

    public static function testHajjah()
    {
        self::assertNotEmpty(Person::hajjah());
    }

    public static function testTitleMaleMalay()
    {
        self::assertNotEmpty(Person::titleMaleMalay());
    }

    public static function testLastNameChinese()
    {
        self::assertNotEmpty(Person::lastNameChinese());
    }

    public static function testFirstNameMaleChinese()
    {
        self::assertNotEmpty(Person::firstNameMaleChinese());
    }

    public static function testFirstNameFemaleChinese()
    {
        self::assertNotEmpty(Person::firstNameFemaleChinese());
    }

    public static function testFirstNameMaleChristian()
    {
        self::assertNotEmpty(Person::firstNameMaleChristian());
    }

    public static function testFirstNameFemaleChristian()
    {
        self::assertNotEmpty(Person::firstNameFemaleChristian());
    }

    public static function testInitialIndian()
    {
        self::assertNotEmpty(Person::initialIndian());
    }

    public static function testFirstNameMaleIndian()
    {
        self::assertNotEmpty(Person::firstNameMaleIndian());
    }

    public static function testFirstNameFemaleIndian()
    {
        self::assertNotEmpty(Person::firstNameFemaleIndian());
    }

    public static function testLastNameIndian()
    {
        self::assertNotEmpty(Person::lastNameIndian());
    }

    public function testLastName()
    {
        self::assertNotEmpty(Person::lastName());
    }
    
    public function testNric(): void
    {
        self::assertValidSingaporeId($this->faker->nric());
    }

    public function testNricAfter2000(): void
    {
        $nric = $this->faker->nric(new \DateTime('2005-03-01'));
        self::assertMatchesRegularExpression('/^T05[0-9]{5}[A-Z]$/', $nric);
        self::assertValidSingaporeId($nric);
    }

    public function testNricBefore2000(): void
    {
        $nric = $this->faker->nric(new \DateTime('1993-03-01'));
        self::assertMatchesRegularExpression('/^S93[0-9]{5}[A-Z]$/', $nric);
        self::assertValidSingaporeId($nric);
    }

    public function testNricBefore1968(): void
    {
        $nric = $this->faker->nric(new \DateTime('1967-12-31'));
        self::assertMatchesRegularExpression('/^S0[0-1][0-9]{5}[A-Z]$/', $nric);
        self::assertValidSingaporeId($nric);
    }

    public function testFin(): void
    {
        self::assertValidSingaporeId($this->faker->fin());
    }

    public function testFinAfter2000(): void
    {
        $fin = $this->faker->fin(new \DateTime('2005-03-01'));
        self::assertMatchesRegularExpression('/^G[0-9]{7}[A-Z]$/', $fin);
        self::assertValidSingaporeId($fin);
    }

    public function testFinBefore2000(): void
    {
        $fin = $this->faker->fin(new \DateTime('1993-03-01'));
        self::assertMatchesRegularExpression('/^F[0-9]{7}[A-Z]$/', $fin);
        self::assertValidSingaporeId($fin);
    }

    public function assertValidSingaporeId(string $id): void
    {
        $prefix = $id[0];
        $weights = [2, 7, 6, 5, 4, 3, 2];
        $checksum = in_array($prefix, ['T', 'G'], true) ? 4 : 0;

        foreach ($weights as $key => $weight) {
            $checksum += (int) $id[$key + 1] * $weight;
        }

        $checksumArr = in_array($prefix, ['F', 'G'], true)
            ? ['X', 'W', 'U', 'T', 'R', 'Q', 'P', 'N', 'M', 'L', 'K']
            : ['J', 'Z', 'I', 'H', 'G', 'F', 'E', 'D', 'C', 'B', 'A'];

        self::assertSame($checksumArr[$checksum % 11], $id[8], sprintf('Invalid checksum for NRIC/FIN: %s', $id));
    }

    protected function getProviders(): iterable
    {
        yield new Person($this->faker);
    }
}
