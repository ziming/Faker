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
        self::assertContains(Person::firstNameMaleMalay(), ['Ahmad']);
    }

    public function testFirstNameFemaleMalay()
    {
        self::assertContains(Person::firstNameFemaleMalay(), ['Adibah']);
    }

    public function testLastNameMalay()
    {
        self::assertContains(Person::lastNameMalay(), ['Abdullah']);
    }

    public static function testMuhammadName()
    {
        self::assertContains(Person::muhammadName(), ['Muhammad']);
    }

    public static function testNurName()
    {
        self::assertContains(Person::nurName(), ['Nur']);
    }

    public static function testHaji()
    {
        self::assertContains(Person::haji(), ['Haji']);
    }

    public static function testHajjah()
    {
        self::assertContains(Person::hajjah(), ['Hajjah']);
    }

    public static function testTitleMaleMalay()
    {
        self::assertContains(Person::titleMaleMalay(), ['Syed']);
    }

    public static function testLastNameChinese()
    {
        self::assertContains(Person::lastNameChinese(), ['Lim']);
    }

    public static function testFirstNameMaleChinese()
    {
        self::assertContains(Person::firstNameMaleChinese(), ['Goh Tong']);
    }

    public static function testFirstNameFemaleChinese()
    {
        self::assertContains(Person::firstNameFemaleChinese(), ['Mew Choo']);
    }

    public static function testFirstNameMaleChristian()
    {
        self::assertContains(Person::firstNameMaleChristian(), ['Aaron']);
    }

    public static function testFirstNameFemaleChristian()
    {
        self::assertContains(Person::firstNameFemaleChristian(), ['Alice']);
    }

    public static function testInitialIndian()
    {
        self::assertContains(Person::initialIndian(), ['S. ']);
    }

    public static function testFirstNameMaleIndian()
    {
        self::assertContains(Person::firstNameMaleIndian(), ['Arumugam']);
    }

    public static function testFirstNameFemaleIndian()
    {
        self::assertContains(Person::firstNameFemaleIndian(), ['Ambiga']);
    }

    public static function testLastNameIndian()
    {
        self::assertContains(Person::lastNameIndian(), ['Subramaniam']);
    }

    public function testLastName()
    {
        self::assertContains(Person::lastName(), ['Lee']);
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
