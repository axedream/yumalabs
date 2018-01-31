<?php
session_start();
define(BASIC_URL, 'http://'.$_SERVER['HTTP_HOST']);
define(BASIC_URL_FULL, BASIC_URL."/");

require_once 'directory.php';               //константы переменных
require_once BASE.'autoload.php';           //автозагрузка классов

//инициализируем загрузку
Core::gi()->run();

//отрисовываем контент
View::gi()->getPage();
?>