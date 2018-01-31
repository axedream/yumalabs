<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 17:49
 */

class basic {

    public $auth_true = 0; //по умолчанию авторизации нет = 0, если есть = 1


    /**
     * Инициализация пользователя
     * basic constructor.
     */
    public function __construct()
    {
        //создаем объект для работы с базой данных
        //$this->db = new safemysql(Core::gi()->config['mysqldb']['system']);

        //создаем модель для работы с пользователями
        $this->um_all = new UserModel();
        $this->um_all->where(['status'=>'1']);
        $result = $this->um_all->getAll();

        //если не установленна user_groupe назначаем по умолчанию гость (0 - гость 5 - пользователь  10 - суперадмин)
        if (!isset($_SESSION['user_groupe']) OR empty($_SESSION['user_groupe'])) {
            $_SESSION['user_groupe'] = 0;
            $_SESSION['user_id']  = 2;
            $this->auth_true = 0;
        }

        //получаем всех пользователей из базы (имена), для того что бы обойти вопросы инжекции
        if ($_SESSION['user_id'] && is_numeric($_SESSION['user_id'])) {

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

}