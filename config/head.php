<?php
//--теги заголовка страницы--//

return array(
    "ext"   =>  [
        "js"    =>  [
            "jquery"    =>  [
                "directory" =>  "jquery/jquery-2.1.4.min.js",
            ],
            "buttons"    =>  [
                "directory" =>  "buttons/logout.js",
            ],
            "basic" =>  [
                "directory" =>  "basic/vars.js",
            ],
            "phpgrid"   =>  [
                "directory" =>  [
                    "phpgrid/lib/js/jqgrid/js/i18n/grid.locale-ru.js",
                    "phpgrid/lib/js/jqgrid/js/jquery.jqGrid.min.js",
                ],
            ],
        ],
        "css"   =>  [
            "bootstrap" =>  [
                "directory" =>  "bootstrap/css/bootstrap.min.css",
                ],
                "phpgrid"   =>  [
                    "directory" =>  [
                        "phpgrid/lib/js/themes/smoothness/jquery-ui.custom.css",
                        "phpgrid/lib/js/jqgrid/css/ui.jqgrid.css",
                    ],
                ],
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