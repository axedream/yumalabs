<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 17:49
 */

class Ajax extends Basic {

    private $out = [
        'error' => true,
        ];
    private $out_test = [
        'error' => true,
        'msg' => [
            'text' => 'Тестовая проверка на работоспособность метода и контроллера',
        ],
    ];


    public function __construct(){
        parent::__construct();
        View::gi()->content = 'NO';
    }

    /**
     * Выдем результат
     */
    private function echo_finish($type){

        if ($type=='test'){
            $this->out_test['msg']['user'] = 'Текущий пользоватлеь под которым вы авторизованы: '.$_SESSION['user_login'];
            $this->out_test['msg']['role'] = 'Текущая группа пользователя: '.(new UserModel())->get_user_role($_SESSION['user_groupe']);

        } else {

        }
        echo json_encode($this->out_test);

    }

    /**
     * Тестируем работу модуля
     */
    public function test(){
        $this->echo_finish('test');
    }

    public function get_data(){
        //принимаем только post запросы
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        }

        return FALSE;
    }



    public function __call($name, $arguments)
    {
        echo $this->out;
        return false;
    }

}