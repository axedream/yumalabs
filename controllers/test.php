<?php
//--тестовый файл--//
class Test {

    function index() {
        //тут получаем какие либо данные и отдаем представлению отрисовать их
        //инициализируем все предстартовые отображения
        View::gi()->pageDefault();
        echo "SUSLIK";
        //View::gi()->
    }

    function test1() {
        echo "<br>TEST1<br>";
    }

    function test2() {
        echo "<br>TEST2<br>";
    }
}


//-----------------------Убрать вообще-----------------------------//
/*
echo "<pre>";
var_dump(Core::gi()->config);
echo "<br>";
*/
/* пример работы с базой
$db = new safemysql(Core::gi()->config['mysqldb']['system']);
$sql  = "INSERT INTO test SET name=?s, value = ?s";
$db->query($sql,"test_name","test_value");
*/
//-----------------------Убрать вообще-----------------------------//
