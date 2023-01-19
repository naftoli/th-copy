<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$categories = [
    'brochures', 'books', 'guides', 'recruitment prizes', 'test prizes', 'sweaters', 'celebration boxes', 'trip items',
    'gifts', 'ID cards', 'awards', 'prizes', 'event'
];

$sizes = [
    ['children xs', 'children s', 'children m', 'children l', 'children xl', 'adult xs', 'adult s', 'adult m',
        'adult l', 'adult xl', 'adult xxl', 'adult xxxl'],
    ['adult s', 'adult m', 'adult l', 'adult xl', 'adult xxl', 'adult xxxl'],
    ['children s', 'children m', 'children l', 'children xl', 'adult s', 'adult m', 'adult l']
];

$items = [
    'brochures' => ['brochure'],
    'books'     => ['books'],
    'guides'    => ['study guide', 'khk guide'],
    'recruitment prizes'    => [
        'book light', 'rechargeable fan', 'neck pillow', 'mini duffle bag',
        'watch' => [
            'colors' => ['blue', 'burgundy']
        ]
    ],
    'test prizes'   => [
        'kop cards game'    => [
            'colors'    => ['blue', 'red', 'purple', 'green', 'yellow']
        ],
        'leather book mark' => [
            'colors'    => ['blue', 'red', 'purple', 'green', 'yellow']
        ],
        'drawstring bag',
        'shape shifting cube'
    ],
    'sweaters'  => [
        'kids'  => [
            'boys'  => $sizes[0],
            'girls  '  => $sizes[0]
        ],
        'hq' => [
            'boys'  => $sizes[0],
            'girls  '  => $sizes[0]
        ],
        'trip staff'    => [
            'boys'  => $sizes[1],
            'girls' => $sizes[1]
        ],
        'bubby'     => $sizes[1],
        'zaidy'     => $sizes[1],
        'mother'    => $sizes[1],
        'father'    => $sizes[1],
    ],
    'celebration boxes'     => ['celebration box'],
    'trip items'            => ['plates', 'napkins', 'tablecloth', 'cups'],
    'gifts'                 => [
        'boys'  => [
            'yarmulka'  => [
                'sizes'     => ['4', '5', '6']
            ],
            'personalized name bottle'  => [
                'color'    => 'blue'
            ]
        ],
        'girls'     => [
            'jewelery',
            'personalized name bottle'  => [
                'color'    => 'pink'
            ]
        ]
    ],
    'ID cards'              => ['ID card'],
    'awards'                => [
        'certificate', 'plaque', 'medal', 'glass trophy', 'khk plaque', 'gold trophy', 'silver trophy', 'bronze trophy'
    ],
    'event'                 => [
        '25 foot bunting', 'podium sign', 'chidon 4 foot flag', 'tzivos hashem 4 foot flag', 'flag pole', 'carpet',
        'foil baloon', 'navy baloon', 'blue baloon', 'stanchion poles', 'stanchion ropes', 'back drop', 'back drop medal frame'
    ],
    'prizes'                => [
        'remote control helicopter',
        'video drone',
        'bracelet',
        'necklace',
        'earrings',
        'chidon T-shirt' => [
            'boys'  => [
                'color' => 'navy',
                'sizes' => $sizes[2]
            ],
            'girls' => [
                'color' => 'burgundy',
                'sizes' => $sizes[2]
            ]
        ],
        'chidon art set',
        'chidon juggling set',
        'chidon soccer ball',
        'chidon basket ball',
        'chidon football',
        'framed rebbe picture 5782',
        'chidon cap'    => [
            'boys'  => [
                'color' => 'navy',
            ],
            'girls' => [
                'color' => 'burgundy'
            ]
        ],
        'der rebbe ret tzu kinder',
        'chidon leather sefer hamitzvos'    => ['boys', 'girls'],
        'chidon leather chitas' => ['boys', 'girls'],
        'chidon leather siddur' => ['boys', 'girls'],
        'chidon leather tehillim'   => ['boys', 'girls'],
        'chidon leather machzor'    => ['boys', 'girls'],
        'chidon baseball',
        'chidon carry-on',
        'personalized name bracelet',
        'chidon pogo ball',
        'comic book'    => [
            'the jewish underground vol 1',
            'the jewish underground vol 2',
            'iron curtain vol 1',
            'iron curtain vol 2',
            'escape from europe',
            'the Rebbe and the mazkir'
        ],
        'chidon towel'  => ['boys', 'girls'],
        'chocolate mold',
        'backpack'  => [
            'boys'  => [
                'color' => 'navy'
            ],
            'girls' => [
                'color' => 'burgundy'
            ]
        ],
        'waffle maker',
        'chidon cookie cutters',
        'reb binyomin kletzker',
        'reb shmuel munkes',
        'the slavita brothers',
        'reb hillel paritcher'
    ]
];