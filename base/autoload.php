<?php
//--файл автозагрузки классов--//


//базовые классы ядра
function base_autoload($class_name) {
	$file = BCLASS .$class_name.'.php';
	if( file_exists($file) == false ) return false;
	require_once ($file);
}

//базовые классы ядра
function local_autoload($class_name) {
	$file = LCLASS .$class_name.'.php';
	if( file_exists($file) == false ) return false;
	require_once ($file);
}


//базовый класс
spl_autoload_register('base_autoload');
spl_autoload_register('local_autoload');

