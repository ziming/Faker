<?php

namespace Faker;

use Faker\Container\ContainerInterface;
use Faker\Extension\BarcodeExtension;
use Faker\Extension\BloodExtension;
use Faker\Extension\ColorExtension;
use Faker\Extension\DateTimeExtension;
use Faker\Extension\FileExtension;
use Faker\Extension\NumberExtension;
use Faker\Extension\UuidExtension;
use Faker\Extension\VersionExtension;

/**
 * @mixin BarcodeExtension
 * @mixin BloodExtension
 * @mixin ColorExtension
 * @mixin DateTimeExtension
 * @mixin FileExtension
 * @mixin NumberExtension
 * @mixin VersionExtension
 * @mixin UuidExtension
 */
class Generator
{
    protected array $formatters = [];

    private ContainerInterface $container;

    private UniqueGenerator $uniqueGenerator;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container ?: Container\ContainerBuilder::getDefault();
    }

    /**
     * @template T of Extension\Extension
     *
     * @param class-string<T> $id
     *
     * @throws Extension\ExtensionNotFound
     *
     * @return T
     */
    public function ext(string $id)
    {
        if (!$this->container->has($id)) {
            throw new Extension\ExtensionNotFound(sprintf(
                'No Faker extension with id "%s" was loaded.',
                $id
            ));
        }

        $extension = $this->container->get($id);

        if ($extension instanceof Extension\GeneratorAwareExtension) {
            $extension = $extension->withGenerator($this);
        }

        return $extension;
    }

    /**
     * With the unique generator you are guaranteed to never get the same two
     * values.
     *
     * <code>
     * // will never return twice the same value
     * $faker->unique()->randomElement(array(1, 2, 3));
     * </code>
     *
     * @param bool $reset      If set to true, resets the list of existing values
     * @param int  $maxRetries Maximum number of retries to find a unique value,
     *                         After which an OverflowException is thrown.
     *
     * @throws \OverflowException When no unique value can be found by iterating $maxRetries times
     *
     * @return self A proxy class returning only non-existing values
     */
    public function unique($reset = false, $maxRetries = 10000)
    {
        if ($reset || $this->uniqueGenerator === null) {
            $this->uniqueGenerator = new UniqueGenerator($this, $maxRetries);
        }

        return $this->uniqueGenerator;
    }

    /**
     * Get a value only some percentage of the time.
     *
     * @param float $weight A probability between 0 and 1, 0 means that we always get the default value.
     *
     * @return self
     */
    public function optional(float $weight = 0.5, $default = null)
    {
        if ($weight > 1) {
            trigger_deprecation('fakerphp/faker', '1.16', 'First argument ($weight) to method "optional()" must be between 0 and 1. You passed %f, we assume you meant %f.', $weight, $weight / 100);
            $weight = $weight / 100;
        }

        return new ChanceGenerator($this, $weight, $default);
    }

    /**
     * To make sure the value meet some criteria, pass a callable that verifies the
     * output. If the validator fails, the generator will try again.
     *
     * The value validity is determined by a function passed as first argument.
     *
     * <code>
     * $values = array();
     * $evenValidator = function ($digit) {
     *   return $digit % 2 === 0;
     * };
     * for ($i=0; $i < 10; $i++) {
     *   $values []= $faker->valid($evenValidator)->randomDigit;
     * }
     * print_r($values); // [0, 4, 8, 4, 2, 6, 0, 8, 8, 6]
     * </code>
     *
     * @param ?\Closure $validator  A function returning true for valid values
     * @param int       $maxRetries Maximum number of retries to find a valid value,
     *                              After which an OverflowException is thrown.
     *
     * @throws \OverflowException When no valid value can be found by iterating $maxRetries times
     *
     * @return self A proxy class returning only valid values
     */
    public function valid(?\Closure $validator = null, int $maxRetries = 10000)
    {
        return new ValidGenerator($this, $validator, $maxRetries);
    }

    public function seed($seed = null)
    {
        if ($seed === null) {
            mt_srand();
        } else {
            mt_srand((int) $seed, MT_RAND_PHP);
        }
    }

    public function format($format, $arguments = [])
    {
        return call_user_func_array($this->getFormatter($format), $arguments);
    }

    /**
     * @param string $format
     *
     * @return callable
     */
    public function getFormatter($format)
    {
        if (isset($this->formatters[$format])) {
            return $this->formatters[$format];
        }

        if (method_exists($this, $format)) {
            $this->formatters[$format] = [$this, $format];

            return $this->formatters[$format];
        }

        // "Faker\Core\Barcode->ean13"
        if (preg_match('|^([a-zA-Z0-9\\\]+)->([a-zA-Z0-9]+)$|', $format, $matches)) {
            $this->formatters[$format] = [$this->ext($matches[1]), $matches[2]];

            return $this->formatters[$format];
        }

        foreach ($this->providers as $provider) {
            if (method_exists($provider, $format)) {
                $this->formatters[$format] = [$provider, $format];

                return $this->formatters[$format];
            }
        }

        throw new \InvalidArgumentException(sprintf('Unknown format "%s"', $format));
    }

    /**
     * Replaces tokens ('{{ tokenName }}') with the result from the token method call
     *
     * @param string $string String that needs to bet parsed
     *
     * @return string
     */
    public function parse($string)
    {
        $callback = function ($matches) {
            return $this->format($matches[1]);
        };

        return preg_replace_callback('/{{\s?(\w+|[\w\\\]+->\w+?)\s?}}/u', $callback, $string);
    }

    /**
     * Get a random MIME type
     *
     * @example 'video/avi'
     */
    public function mimeType()
    {
        return $this->ext(Extension\FileExtension::class)->mimeType();
    }

    /**
     * Get a random file extension (without a dot)
     *
     * @example avi
     */
    public function fileExtension()
    {
        return $this->ext(Extension\FileExtension::class)->extension();
    }

    /**
     * Get a full path to a new real file on the system.
     */
    public function filePath()
    {
        return $this->ext(Extension\FileExtension::class)->filePath();
    }

    /**
     * Get an actual blood type
     *
     * @example 'AB'
     */
    public function bloodType(): string
    {
        return $this->ext(Extension\BloodExtension::class)->bloodType();
    }

    /**
     * Get a random resis value
     *
     * @example '+'
     */
    public function bloodRh(): string
    {
        return $this->ext(Extension\BloodExtension::class)->bloodRh();
    }

    /**
     * Get a full blood group
     *
     * @example 'AB+'
     */
    public function bloodGroup(): string
    {
        return $this->ext(Extension\BloodExtension::class)->bloodGroup();
    }

    /**
     * Get a random EAN13 barcode.
     *
     * @example '4006381333931'
     */
    public function ean13(): string
    {
        return $this->ext(Extension\BarcodeExtension::class)->ean13();
    }

    /**
     * Get a random EAN8 barcode.
     *
     * @example '73513537'
     */
    public function ean8(): string
    {
        return $this->ext(Extension\BarcodeExtension::class)->ean8();
    }

    /**
     * Get a random ISBN-10 code
     *
     * @see http://en.wikipedia.org/wiki/International_Standard_Book_Number
     *
     * @example '4881416324'
     */
    public function isbn10(): string
    {
        return $this->ext(Extension\BarcodeExtension::class)->isbn10();
    }

    /**
     * Get a random ISBN-13 code
     *
     * @see http://en.wikipedia.org/wiki/International_Standard_Book_Number
     *
     * @example '9790404436093'
     */
    public function isbn13(): string
    {
        return $this->ext(Extension\BarcodeExtension::class)->isbn13();
    }

    /**
     * Returns a random number between $int1 and $int2 (any order)
     *
     * @example 79907610
     */
    public function numberBetween($int1 = 0, $int2 = 2147483647): int
    {
        return $this->ext(Extension\NumberExtension::class)->numberBetween((int) $int1, (int) $int2);
    }

    /**
     * Returns a random number between 0 and 9
     */
    public function randomDigit(): int
    {
        return $this->ext(Extension\NumberExtension::class)->randomDigit();
    }

    /**
     * Generates a random digit, which cannot be $except
     */
    public function randomDigitNot($except): int
    {
        return $this->ext(Extension\NumberExtension::class)->randomDigitNot((int) $except);
    }

    /**
     * Returns a random number between 1 and 9
     */
    public function randomDigitNotZero(): int
    {
        return $this->ext(Extension\NumberExtension::class)->randomDigitNotZero();
    }

    /**
     * Return a random float number
     *
     * @example 48.8932
     */
    public function randomFloat($nbMaxDecimals = null, $min = 0, $max = null): float
    {
        return $this->ext(Extension\NumberExtension::class)->randomFloat(
            $nbMaxDecimals !== null ? (int) $nbMaxDecimals : null,
            (float) $min,
            $max !== null ? (float) $max : null
        );
    }

    /**
     * Returns a random integer with 0 to $nbDigits digits.
     *
     * The maximum value returned is mt_getrandmax()
     *
     * @param int|null $nbDigits Defaults to a random number between 1 and 9
     * @param bool     $strict   Whether the returned number should have exactly $nbDigits
     *
     * @example 79907610
     */
    public function randomNumber($nbDigits = null, $strict = false): int
    {
        return $this->ext(Extension\NumberExtension::class)->randomNumber(
            $nbDigits !== null ? (int) $nbDigits : null,
            (bool) $strict
        );
    }

    /**
     * Get a version number in semantic versioning syntax 2.0.0. (https://semver.org/spec/v2.0.0.html)
     *
     * @param bool $preRelease Pre release parts may be randomly included
     * @param bool $build      Build parts may be randomly included
     *
     * @example 1.0.0
     * @example 1.0.0-alpha.1
     * @example 1.0.0-alpha.1+b71f04d
     */
    public function semver(bool $preRelease = false, bool $build = false): string
    {
        return $this->ext(Extension\VersionExtension::class)->semver($preRelease, $build);
    }

    /**
     * @deprecated
     */
    protected function callFormatWithMatches($matches)
    {
        trigger_deprecation('fakerphp/faker', '1.14', 'Protected method "callFormatWithMatches()" is deprecated and will be removed.');

        return $this->format($matches[1]);
    }

    /**
     * @param string $attribute
     *
     * @deprecated Use a method instead.
     */
    public function __get($attribute)
    {
        trigger_deprecation('fakerphp/faker', '1.14', 'Accessing property "%s" is deprecated, use "%s()" instead.', $attribute, $attribute);

        return $this->format($attribute);
    }

    /**
     * @param string $method
     * @param array  $attributes
     */
    public function __call($method, $attributes)
    {
        return $this->format($method, $attributes);
    }

    public function __destruct()
    {
        $this->seed();
    }

    public function __wakeup()
    {
        $this->formatters = [];
    }
}
