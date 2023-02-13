<?php
$categories = [
    'brochures', 'books', 'guides', 'recruitment prizes', 'test prizes', 'children sweaters', 'extra purchases',
    'gifts', 'ID cards', 'awards', 'prizes'
];

$items = [
    'brochures'             => ['brochures'],
    'books'                 => ['books'],
    'guides'                => ['study guides', 'khk guides'],
    'recruitment prizes'    => ['book light', 'rechargeable fan', 'neck pillow', 'mini duffle bag', 'watch'],
    'test prizes'           => ['kop cards game', 'leather book mark', 'drawstring bag', 'shape shifting cube'],
    'children sweaters'     => ['children sweaters'],
    'extra purchases'       => ['celebration boxes', 'sweaters'],
    'gifts'                 => ['yarmulka', 'personalized bottle', 'bracelet'],
    'ID cards'              => ['ID card'],
    'awards'                => ['certificate', 'plaque', 'medal', 'glass trophy', 'khk plaque'],
    'prizes'                => ['remote control helicopter', 'video drone', 'bracelet', 'necklace', 'earrings',
        'chidon T-shirt', 'chidon art set', 'chidon juggling set', 'chidon soccer ball', 'chidon basket ball',
        'chidon football', 'framed rebbe picture 5782', 'chidon cap', 'der rebbe ret tzu kinder',
        'chidon leather sefer hamitzvos', 'chidon leather chitas', 'chidon leather siddur', 'chidon leather tehillim',
        'chidon leather machzor', 'chidon baseball', 'chidon carry-on', 'personalized name bracelet', 'chidon pogo ball',
        'the jewish underground vol 1', 'the jewish underground vol 2', 'iron curtain vol 1', 'iron curtain vol 2',
        'escape from european', 'the Rebbe and the mazkir', 'chidon towel', 'chocolate mold', 'backpack', 'waffle maker',
        'chidon cookie cutters', 'reb binyomin kletzker', 'reb shmuel munkes', 'the slavita brothers', 'reb hillel paritcher'],
//    'event'                 => ['25 foot bunting', 'podium sign', 'chidon 4 foot flag', 'tzivos hashem 4 foot flag',
//        'flag pole', 'carpet', 'foil baloon', 'navy baloon', 'blue baloon', 'stanchion poles', 'stanchion ropes',
//        'back drop', 'back drop medal frame'],
];

// create array of fields for the admin to choose from
// key refers to input name and value refers to what the user will see
// key contains the table/field that we need to fetch
// if key does not refer to table, then it has just a description or variable name
$fields = [
    's.school_name' => 'School',
    'c.class_grade' => 'Class Grade',
    'c.class_sub'   => 'Class Sub',
    'c.class_teacher'   => 'Teacher',
    'u.user_serial' => 'Serial Number',
    'u.first'       => 'First Name',
    'u.last'        => 'Last Name',
    'size'          => 'Size',
    'color'         => 'Color',
    'name'          => 'Personalization Name',
    's.shipping_first'      => 'Shipping First Name',
    's.shipping_last'       => 'Shipping Last Name',
    's.shipping_phone'      => 'Shipping Contact Number',
    's.shipping_address1'   => 'Shipping Address 1',
    's.shipping_address2'   => 'Shipping Address 2',
    's.shipping_city'       => 'Shipping City',
    's.shipping_state'      => 'Shipping State',
    's.shipping_postal'     => 'Shipping Zip',
    's.shipping_country'    => 'Shipping Country',
    's.shipping_requests'   => 'Shipping Requests'
];

//$sizes = [
//    ['children xs', 'children s', 'children m', 'children l', 'children xl', 'adult xs', 'adult s', 'adult m',
//        'adult l', 'adult xl', 'adult xxl', 'adult xxxl'],
//    ['adult s', 'adult m', 'adult l', 'adult xl', 'adult xxl', 'adult xxxl'],
//    ['children s', 'children m', 'children l', 'children xl', 'adult s', 'adult m', 'adult l']
//];
//
//$items2 = [
//    'brochures' => ['brochure'],
//    'books'     => ['books'],
//    'guides'    => ['study guide', 'khk guide'],
//    'recruitment prizes'    => [
//        'book light', 'rechargeable fan', 'neck pillow', 'mini duffle bag',
//        'watch' => [
//            'colors' => ['blue', 'burgundy']
//        ]
//    ],
//    'test prizes'   => [
//        'kop cards game'    => [
//            'colors'    => ['blue', 'red', 'purple', 'green', 'yellow']
//        ],
//        'leather book mark' => [
//            'colors'    => ['blue', 'red', 'purple', 'green', 'yellow']
//        ],
//        'drawstring bag',
//        'shape shifting cube'
//    ],
//    'sweaters'  => [
//        'kids'  => [
//            'boys'  => $sizes[0],
//            'girls  '  => $sizes[0]
//        ],
//        'hq' => [
//            'boys'  => $sizes[0],
//            'girls  '  => $sizes[0]
//        ],
//        'trip staff'    => [
//            'boys'  => $sizes[1],
//            'girls' => $sizes[1]
//        ],
//        'bubby'     => $sizes[1],
//        'zaidy'     => $sizes[1],
//        'mother'    => $sizes[1],
//        'father'    => $sizes[1],
//    ],
//    'celebration boxes'     => ['celebration box'],
//    'trip items'            => ['plates', 'napkins', 'tablecloth', 'cups'],
//    'gifts'                 => [
//        'boys'  => [
//            'yarmulka'  => [
//                'sizes'     => ['4', '5', '6']
//            ],
//            'personalized name bottle'  => [
//                'color'    => 'blue'
//            ]
//        ],
//        'girls'     => [
//            'jewelery',
//            'personalized name bottle'  => [
//                'color'    => 'pink'
//            ]
//        ]
//    ],
//    'ID cards'              => ['ID card'],
//    'awards'                => [
//        'certificate', 'plaque', 'medal', 'glass trophy', 'khk plaque', 'gold trophy', 'silver trophy', 'bronze trophy'
//    ],
//    'event'                 => [
//        '25 foot bunting', 'podium sign', 'chidon 4 foot flag', 'tzivos hashem 4 foot flag', 'flag pole', 'carpet',
//        'foil baloon', 'navy baloon', 'blue baloon', 'stanchion poles', 'stanchion ropes', 'back drop', 'back drop medal frame'
//    ],
//    'prizes'                => [
//        'remote control helicopter',
//        'video drone',
//        'bracelet',
//        'necklace',
//        'earrings',
//        'chidon T-shirt' => [
//            'boys'  => [
//                'color' => 'navy',
//                'sizes' => $sizes[2]
//            ],
//            'girls' => [
//                'color' => 'burgundy',
//                'sizes' => $sizes[2]
//            ]
//        ],
//        'chidon art set',
//        'chidon juggling set',
//        'chidon soccer ball',
//        'chidon basket ball',
//        'chidon football',
//        'framed rebbe picture 5782',
//        'chidon cap'    => [
//            'boys'  => [
//                'color' => 'navy',
//            ],
//            'girls' => [
//                'color' => 'burgundy'
//            ]
//        ],
//        'der rebbe ret tzu kinder',
//        'chidon leather sefer hamitzvos'    => ['boys', 'girls'],
//        'chidon leather chitas' => ['boys', 'girls'],
//        'chidon leather siddur' => ['boys', 'girls'],
//        'chidon leather tehillim'   => ['boys', 'girls'],
//        'chidon leather machzor'    => ['boys', 'girls'],
//        'chidon baseball',
//        'chidon carry-on',
//        'personalized name bracelet',
//        'chidon pogo ball',
//        'comic book'    => [
//            'the jewish underground vol 1',
//            'the jewish underground vol 2',
//            'iron curtain vol 1',
//            'iron curtain vol 2',
//            'escape from europe',
//            'the Rebbe and the mazkir'
//        ],
//        'chidon towel'  => ['boys', 'girls'],
//        'chocolate mold',
//        'backpack'  => [
//            'boys'  => [
//                'color' => 'navy'
//            ],
//            'girls' => [
//                'color' => 'burgundy'
//            ]
//        ],
//        'waffle maker',
//        'chidon cookie cutters',
//        'reb binyomin kletzker',
//        'reb shmuel munkes',
//        'the slavita brothers',
//        'reb hillel paritcher'
//    ]
//];