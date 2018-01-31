<?php
//--файл загрузки конфигурационных файлов--//

return array (
    'mysqldb'       =>      include CONF.'mysqldb.php',      //  настройки mysql
    'regexp'        =>      include CONF.'regexp.php',       //  регулярные выражения
    'message'       =>      include CONF.'message.php',      //  сообщения
    'default'       =>      include CONF.'default.php',      //  дефолтные настройки для контроллеров, отображений...
    'head'          =>      include CONF.'head.php',         //  теги заголовка страницы
);