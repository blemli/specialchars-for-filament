<?php

use Blemli\SpecialChars\Support\SpecialChar;

it('reads a character, a code point notation and an int as the same character', function () {
    foreach (['⌀', 'U+2300', 'u+2300', 0x2300] as $input) {
        $char = SpecialChar::from($input);

        expect($char->char)->toBe('⌀')
            ->and($char->codePoint)->toBe(0x2300)
            ->and($char->notation())->toBe('U+2300')
            ->and($char->actionName())->toBe('insert_u2300');
    }
});

it('names a character after unicode', function () {
    expect(SpecialChar::from('⌀')->name())->toBe('DIAMETER SIGN')
        ->and(SpecialChar::from('×')->label())->toBe('Multiplication sign');
})->skip(! class_exists(IntlChar::class), 'ext-intl missing');

it('rejects what is not one character or a valid code point', function (string | int $input) {
    SpecialChar::from($input);
})->throws(InvalidArgumentException::class)->with(['two chars' => 'ab', 'empty' => '', 'beyond unicode' => 0x110000, 'U+ without digits' => 'U+']);

it('dedupes a list by code point and keeps the order', function () {
    expect(array_map(fn (SpecialChar $char): string => $char->char, SpecialChar::collect(['×', 'U+00D7', '⌀', 0x00D7])))->toBe(['×', '⌀']);
});
