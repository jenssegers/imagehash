<?php

namespace Jenssegers\ImageHash;

use JsonSerializable;

class Hash implements JsonSerializable
{
    /**
     * A string containing zeros and ones
     *
     * @var string
     */
    protected string $binaryValue;

    /**
     * Hash will be split in several integers if longer than PHP_INT_SIZE
     *
     * @var int[]|null
     */
    protected ?array $integers = null;

    /**
     * @param string $binaryValue
     */
    private function __construct(string $binaryValue)
    {
        $this->binaryValue = $binaryValue;
    }

    /**
     * Create a hash from an array of bits or a string containing a binary representation of the hash
     *
     * @param string|array $bits
     *
     * @return self
     */
    public static function fromBits(array|string $bits): self
    {
        if (\is_array($bits)) {
            $bits = implode('', $bits);
        }

        return new self($bits);
    }

    /**
     * Use integers representation and concatenate their hexadecimal representation
     *
     * @return string
     */
    public function toHex(): string
    {
        if (\extension_loaded('gmp')) {
            $gmp = gmp_init('0b'.$this->binaryValue);

            return bin2hex(gmp_export($gmp));
        }

        return implode(
            '',
            array_map(
                static function (int $int) {
                    return dechex($int);
                },
                $this->getIntegers()
            )
        );
    }

    public function toBits(): string
    {
        return $this->binaryValue;
    }

    /**
     * Used to compute hexadecimal value and can be used to store the hash in database as an integer
     *
     * @return int[]
     */
    public function getIntegers(): array
    {
        if (null !== $this->integers) {
            return $this->integers;
        }

        $maxIntSize = PHP_INT_SIZE * 8; // 8 bytes (a byte is 8 bits)

        // Fixing binary if it doesn't fit an exact multiple of max int size
        $fixedSizeBinary = str_pad(
            $this->binaryValue,
            ((int) ceil(\strlen($this->binaryValue) / $maxIntSize)) * $maxIntSize, // Is there a better way?
            '0',
            STR_PAD_LEFT
        );

        $this->integers = [];
        foreach (str_split($fixedSizeBinary, $maxIntSize) as $split) {
            $sign = $split[0]; // Extract sign
            $int = bindec(substr($split, 1)); // Convert to decimal without first bit
            $int |= ((bool) $sign) << ($maxIntSize - 1); // Reapply last bit with bitwise operation
            $this->integers[] = $int;
        }

        return $this->integers;
    }

    /**
     * Super simple distance computation algorithm, we don't need anything else
     *
     * @param Hash $hash
     *
     * @return int
     */
    public function distance(Hash $hash): int
    {
        if (\extension_loaded('gmp')) {
            return gmp_hamdist('0b'.$this->toBits(), '0b'.$hash->toBits());
        }

        $bits1 = $this->toBits();
        $bits2 = $hash->toBits();
        $length = max(\strlen($bits1), \strlen($bits2));

        // Add leading zeros so the bit strings are the same length.
        $bits1 = str_pad($bits1, $length, '0', STR_PAD_LEFT);
        $bits2 = str_pad($bits2, $length, '0', STR_PAD_LEFT);

        return \count(array_diff_assoc(str_split($bits1), str_split($bits2)));
    }

    /**
     * @param Hash $hash
     *
     * @return bool
     */
    public function equals(Hash $hash): bool
    {
        return ltrim($this->binaryValue, '0') === ltrim($hash->binaryValue, '0');
    }

    public function __toString(): string
    {
        return $this->toHex();
    }

    public function jsonSerialize(): string
    {
        return (string) $this;
    }
}
