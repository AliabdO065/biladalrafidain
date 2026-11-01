<?php

return [
    // Native GD is suitable for shared hosting; browser mode remains an explicit VPS option.
    'renderer' => env('DOCUMENT_RENDERER', 'gd'),
];
