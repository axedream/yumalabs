<?php
//--тестовый файл--//

    echo View::gi()->view['html']['head'];
    echo View::gi()->view['headerBuild'];
    echo View::gi()->view['body']['up'];

    echo "<pre>";
    var_dump(Core::gi()->config);
    echo "</pre>";

    echo View::gi()->view['body']['down'];
