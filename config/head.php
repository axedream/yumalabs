<?php
//--теги заголовка страницы--//

return array(
    "ext"   =>  [
        "js"    =>  [
            "jquery"    =>  [
                "directory" =>  "jquery/jquery-2.1.4.min.js",
            ],

           "bootstrap"  =>  [
               "directory" => "bootstrap/js/bootstrap.min.js",
           ],
            "bootstrap_select"  =>  [
                "directory" => [
                    "bootstrap/js/bootstrap-select.min.js",
                    "bootstrap/js/defaults-ru_RU.min.js",
                ],
            ],


            "buttons"    =>  [
                "directory" =>  "buttons/logout.js",
            ],
            "basic" =>  [
                "directory" =>  [
                    "basic/vars.js",
                    "basic/ajax.js",
                    ]

            ],
            /*
            "phpgrid"   =>  [
                "directory" =>  [
                    "phpgrid/lib/js/jqgrid/js/i18n/grid.locale-ru.js",
                    "phpgrid/lib/js/jqgrid/js/jquery.jqGrid.min.js",
                ],
            ],
            */
        ],
        "css"   =>  [
            "bootstrap" =>  [
                "directory" =>  "bootstrap/css/bootstrap.min.css",
                ],
            "bootstrap_select" => [
                "directory" =>  "bootstrap/css/bootstrap-select.min.css",
            ],
                /*
                "phpgrid"   =>  [
                    "directory" =>  [
                        "phpgrid/lib/js/themes/smoothness/jquery-ui.custom.css",
                        "phpgrid/lib/js/jqgrid/css/ui.jqgrid.css",
                    ],
                ],
                */
        ],
                        ],

    "description"   =>  [
        'text'      =>      'Тестовая страница Юмалабс',
    ],

    "keywords"  =>  [
        'text'      =>      'Авторизация, пользователи, списки, сортировка, MVC',
    ],

    "title" =>  [
        'text'      =>      'Главная страница',
    ],
);