<?php

namespace Jenssegers\ImageHash;

use JsonSerializable;
use phpseclib3\Math\BigInteger;

class Hash implements JsonSerializable
{
    /**
     * @var BigInteger
     */
    protected $value;

    private function __construct(BigInteger $value)
    {
        $this->value = $value;
    }

    public static function fromHex(string $hex): self
    {
        return new self(new BigInteger($hex, 16));
    }

    /**
     * @param string|array $bits
     * @return self
     */
    public static function fromBits($bits): self
    {
        if (is_array($bits)) {
            $bits = implode('', $bits);
        }

        return new self(new BigInteger($bits, 2));
    }

    public static function fromInt(int $int): self
    {
        return new self(new BigInteger($int, 10));
    }

    public function toHex(): string
    {
        return $this->value->toHex();
    }

    public function toBytes(): string
    {
        return $this->value->toBytes();
    }

    public function toBits(): string
    {
        return $this->value->toBits();
    }

    public function toInt(): int
    {
        return hexdec($this->toHex());
    }

    public function distance(Hash $hash): int
    {
        if (extension_loaded('gmp')) {
            return gmp_hamdist('0x' . $this->toHex(), '0x' . $hash->toHex());
        }

        $bits1 = $this->toBits();
        $bits2 = $hash->toBits();
        $length = max(strlen($bits1), strlen($bits2));

        // Add leading zeros so the bit strings are the same length.
        $bits1 = str_pad($bits1, $length, '0', STR_PAD_LEFT);
        $bits2 = str_pad($bits2, $length, '0', STR_PAD_LEFT);

        return count(array_diff_assoc(str_split($bits1), str_split($bits2)));
    }

    public function equals(Hash $hash): bool
    {
        return $this->toHex() === $hash->toHex();
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
