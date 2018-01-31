<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 12:10
 * Контроллер пользователей
 */

class User {

    public $auth_true = 0; //по умолчанию авторизации нет = 0, если есть = 1
    public $auth_login = 0; //ключ попытки авторизации 0 не было попыток авторизации 1 была попытка авторизации
    public $db;

    final function __construct()
    {
        //создаем объект для работы с базой данных
        $this->db = new safemysql(Core::gi()->config['mysqldb']['system']);

        //если не установленна user_groupe назначаем по умолчанию гость (0 - гость 5 - пользователь  10 - суперадмин)
        if (!isset($_SESSION['user_groupe']) OR empty($_SESSION['user_groupe'])) {
            $_SESSION['user_groupe'] = 0;
            $_SESSION['user_id']  = 2;
            $this->auth_true = 0;
        }

        //получаем всех пользователей из базы (имена), для того что бы обойти вопросы инжекции
        if ($_SESSION['user_id'] && is_numeric($_SESSION['user_id'])) {
        $sql  = "SELECT * FROM user WHERE status = 1"; //status = 1 акивный пользователь
        $result = $this->db->getAll($sql);
            if ($result) {
                foreach ($result as $users) {
                    if ($users['id']==$_SESSION['user_id']) {

                        $_SESSION['user_login'] = $users['login'];
                        $_SESSION['user_fio'] = $users['fio'];
                        $_SESSION['user_email'] = $users['email'];
                        $_SESSION['user_groupe'] = (int)$users['groupe'];

                        if ($users['groupe']) {
                            $this->auth_true = 1;
                        } else {
                            $this->auth_true = 0;
                        }
                    }
                }
            }
        }
    }

    /**
     * Главное отображение
     * базовая страница входа
     */
    public function index() {

        View::gi()->title = 'ПУ Юмалабс';

        if (!$this->auth_true) {
            View::gi()->show('form_login',['text_message_wrong'=>"Вы вошли как Гость!<br> Для продолжения работы введите логин/пароль.",'size_message_wrong'=>3,'auth_true'=>$this->auth_true,'auth_login'=>$this->auth_login]);
        } else {
            View::gi()->show('form_user',['user'=>['login'=>$_SESSION['user_login'],'fio'=>$_SESSION['user_fio'],'groupe'=>$_SESSION['user_groupe']]]);
        }
        //сбрасываем попытку авторизации
        $this->auth_login = 0;
    }


    /**
     * Логин пользователя
     */
    public function login(){
        //елси пришел пост запрос пробуем авторизоваться
        $this->auth_login = 1;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $login = (!empty($_POST['login']))  ? $_POST['login'] : 0 ;
            $passwd= (!empty($_POST['passwd'])) ? $_POST['passwd']: 0 ;
            //проверяем логин пароль
            if (!empty($login) && !empty($passwd)) {
                //ставим ключ попытки авторизации
                if ($this->getUsers($login,$passwd)){
                    $this->auth_true = 1;
                }
            }
        }
        $this->index();
    }

    /**
     * Выход
     */
    public function logout() {
        //проверям залогинен ли пользователь
        if ($this->auth_true) {
            $this->auth_true = 0;
            unset($_SESSION['user_id'],$_SESSION['user_login'],$_SESSION['user_groupe']);
        }
        $this->index();
    }



    /**
     * Получение всех пользователей
     * получаем логин пароль
     */
    public function  getUsers($login = 0,$passwd = 0){
        if (!$this->auth_true) {
            $sql  = "SELECT * FROM user WHERE status = 1"; //status = 1 акивный пользователь
            $result = $this->db->getAll($sql);
            if ($result) {
                foreach ($result as $users) {
                    if (($users['login'] == $login) && ($login !='guest') && ($users['passwd'] == md5($passwd))) {
                        $_SESSION['user_id'] = $users['id'];
                        $_SESSION['user_login'] = $users['login'];
                        $_SESSION['user_fio'] = $users['fio'];
                        $_SESSION['user_email'] = $users['email'];
                        $_SESSION['user_groupe'] = $users['groupe'];
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;

    }

}