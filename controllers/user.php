<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 12:10
 * Контроллер пользователей
 */

class User extends Basic {

    public $auth_login = 0; //ключ попытки авторизации 0 не было попыток авторизации 1 была попытка авторизации
    public $db;
    public $um_all;     //все пользователи

    /**
     * Главное отображение
     * базовая страница входа
     */
    public function index() {

    View::gi()->title = 'ПУ Юмалабс';

        if (!$this->auth_true) {
            View::gi()->show('form_login',[
                'text_message_wrong'=>"Вы вошли как Гость!<br> Для продолжения работы введите логин/пароль.",
                'size_message_wrong'=>3,
                'auth_true'=>$this->auth_true,
                'auth_login'=>$this->auth_login
            ]);
        } else {
            $list_users = new UserModel();
            if ($_SESSION['sort']) {
                switch ($_SESSION['sort']) {

                    /*соритируем по Логину*/
                    case 'login':
                        $list_users->bsort('login');
                        break;

                    /*соритируем по ФИО*/
                    case 'fio':
                        $list_users->bsort('fio');
                        break;

                    /*соритируем по по доступам*/
                    case 'access':
                        $list_users->bsort('groupe');
                        break;
                }
            }
            View::gi()
                ->show('form_user',[
                    'user'=>[
                        'login'=>$_SESSION['user_login'],
                        'fio'=>$_SESSION['user_fio'],
                        'groupe'=>$_SESSION['user_groupe'],
                        'list'=>$list_users->getAll(),
                    ]
                ]);
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

            $result = $this->um_all->getAll();
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