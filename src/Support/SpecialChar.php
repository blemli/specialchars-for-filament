<?php

namespace Blemli\SpecialChars\Support;

use IntlChar;
use InvalidArgumentException;

/**
 * One character to offer: built from the character itself ('⌀'), a code
 * point notation ('U+2300') or an int (0x2300), named by ICU when ext-intl
 * is around (DIAMETER SIGN), by its code point otherwise.
 */
final class SpecialChar
{
    private function __construct(
        public readonly string $char,
        public readonly int $codePoint,
    ) {}

    public static function from(string | int | self $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_int($value)) {
            return self::fromCodePoint($value);
        }

        $value = trim($value);

        if (preg_match('/^U\+([0-9A-Fa-f]{1,6})$/i', $value, $matches)) {
            return self::fromCodePoint((int) hexdec($matches[1]));
        }

        if (mb_strlen($value) === 1) {
            return new self($value, (int) mb_ord($value));
        }

        throw new InvalidArgumentException("[{$value}] is not a single character, a U+XXXX code point or an int.");
    }

    private static function fromCodePoint(int $codePoint): self
    {
        $char = $codePoint >= 0 && $codePoint <= 0x10FFFF ? mb_chr($codePoint) : false;

        if ($char === false || $char === '') {
            throw new InvalidArgumentException(sprintf('U+%04X is not a valid code point.', $codePoint));
        }

        return new self($char, $codePoint);
    }

    /**
     * @param  iterable<string | int | self>  $values
     * @return list<self>
     */
    public static function collect(iterable $values): array
    {
        $chars = [];

        foreach ($values as $value) {
            $char = self::from($value);
            $chars[$char->codePoint] = $char;
        }

        return array_values($chars);
    }

    /** U+2300 */
    public function notation(): string
    {
        return sprintf('U+%04X', $this->codePoint);
    }

    /** DIAMETER SIGN — the Unicode name, when ext-intl can tell. */
    public function name(): string
    {
        if (class_exists(IntlChar::class)) {
            $name = IntlChar::charName($this->codePoint);

            if (filled($name)) {
                return $name;
            }
        }

        return $this->notation();
    }

    /** Diameter sign */
    public function label(): string
    {
        return ucfirst(mb_strtolower($this->name()));
    }

    /** A name an action can carry: insert_u2300. */
    public function actionName(): string
    {
        return 'insert_u' . strtolower(sprintf('%04x', $this->codePoint));
    }
}
