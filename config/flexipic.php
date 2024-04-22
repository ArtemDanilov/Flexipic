<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Formats
    |--------------------------------------------------------------------------
    |
    | Define generated formats for the picture element
    | These formats will be used for the srcset attribute
    |
    */

    'formats' => ['webp', 'jpeg'],

    /*
    |--------------------------------------------------------------------------
    | Global variables
    |--------------------------------------------------------------------------
    |
    | Define the global variables for the picture element
    | These parameters can be overwritten in the tag
    |
    */

    'width' => [375, 480, 640, 768, 1024, 1280, 1440, 1920, 2560],

    'sizes' => '100vw',

    'quality' => '75',

    'fit' => 'crop_focal',

    // 'loading' => 'lazy',

    // 'placeholder' => 'blur'
];
