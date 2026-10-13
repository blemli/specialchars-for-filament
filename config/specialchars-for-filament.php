<?php

return [
    /*
     * The characters a field offers when ->specialChars() is called without
     * a list: anything a Swiss keyboard lacks. Each entry is the character
     * itself, a 'U+2300' code point or an int like 0x2300.
     */
    'chars' => ['×', '⌀', '°', 'µ', '–', '…'],

    /*
     * Up to this many characters render as one inline button each (the
     * character as its label); more than that fold into a single dropdown so
     * the input never grows a toolbar.
     */
    'inline_up_to' => 2,

    /*
     * The dropdown trigger: a label (Ω by default) and/or an icon.
     */
    'trigger_label' => 'Ω',
    'trigger_icon' => null,
];
