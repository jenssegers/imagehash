<?php

namespace Jenssegers\ImageHash;

use JsonSerializable;
use phpseclib\Math\BigInteger;

class Hash implements JsonSerializable
{
    /**
     * @var BigInteger
     */
    protected $value;

    /**
     * @param BigInteger $value
     */
    private function __construct(BigInteger $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $hex
     * @return self
     */
    public static function fromHex($hex)
    {
        return new self(new BigInteger($hex, 16));
    }

    /**
     * @param string|array $bits
     * @return self
     */
    public static function fromBits($bits)
    {
        if (is_array($bits)) {
            $bits = implode('', $bits);
        }

        return new self(new BigInteger($bits, 2));
    }

    /**
     * @param int $int
     * @return self
     */
    public static function fromInt($int)
    {
        return new self(new BigInteger($int, 10));
    }

    /**
     * @return string
     */
    public function toHex()
    {
        return $this->value->toHex();
    }

    /**
     * @return string
     */
    public function toBytes()
    {
        return $this->value->toBytes();
    }

    /**
     * @return string
     */
    public function toBits()
    {
        return $this->value->toBits();
    }

    /**
     * @return int
     */
    public function toInt()
    {
        return hexdec($this->toHex());
    }

    /**
     * @param Hash $hash
     * @return int
     */
    public function distance(Hash $hash)
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

    /**
     * @param Hash $hash
     * @return bool
     */
    public function equals(Hash $hash)
    {
        return $this->toHex() === $hash->toHex();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toHex();
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return (string) $this;
    }
}
