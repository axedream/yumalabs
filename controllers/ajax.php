<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 17:49
 */

class Ajax extends Basic {

    private $input_data;

    private $out = [
        'error' => true,
        ];
    private $out_test = [
        'error' => true,
        'msg' => [
            /* Продумать и вынести в конфиг сообщений */
            'text' => 'Тестовая проверка на работоспособность метода и контроллера',
        ],
    ];


    /**
     * Меняем отображение для VIEW
     * Ajax constructor.
     */
    public function __construct(){
        parent::__construct();
        View::gi()->content = 'NO';
    }

    /**
     * Выдем результат
     */
    private function echo_finish($type){

        if ($type=='test'){
            /* Продумать и вынести в конфиг сообщений */
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


    /**
     * Получает данные для чтения о пользователе по его ID
     */
    public function get_data_user(){
        $this->get_data();
        if (is_numeric((int)$this->input_data['user_id'])) {
            $user = new UserModel();
            $res = $user->getAll($this->input_data['user_id']);
            if ($res) {
                $res = $res[0];
                $this->out['error'] = false;
                /* Продумать и вынести в отображения */
                $this->out['msg']['table'] = "<table class='table'>
                    <tr><td>ФИО</td><td>".$res['fio']."</td></tr>
                    <tr><td>Логин</td><td>".$res['login']."</td></tr>
                    <tr><td>Email</td><td>".$res['email']."</td></tr>
                    <tr><td>Группа доступа</td><td>".(new UserModel())->get_user_role($res['groupe'])."</td></tr>
                    </table>";
            }
        }
        //sleep(1);
    }

    /**
     * Отдает форму для редактирования параметров пользователя по его ID
     */
    public function edit_data_user_form(){
        $this->get_data();
        if (is_numeric((int)$this->input_data['user_id'])) {
            $user = new UserModel();
            $res = $user->getAll($this->input_data['user_id']);
            if ($res) {
                $res = $res[0];
            }

            if ( (($_SESSION['user_id']==$res['id']) OR ($_SESSION['user_groupe']==10)) && is_array($res) ) {
                $ga = '';
                foreach (((new UserModel())->get_user_role(-1)) as $key => $val) {
                    if ($key!=0) {
                        if ($key==$res['groupe']) {
                            $ga .="<option selected value='".$key."'>".$val."</option>";
                        } else {
                            $ga .="<option value='".$key."'>".$val."</option>";
                        }
                    }
                }
                if ($_SESSION['user_groupe']==10 && $this->input_data['user_id']!=1) {
                    $ga_text = "<tr><td>Группа доступа</td><td><select id='re_groupe'>".$ga."</select></td></tr>";
                } else {
                    $ga_text = "";
                }

                $this->out['error'] = false;
                /* Продумать и вынести в отображения */
                $this->out['msg']['table'] = "<table class='table'>
                    <input type='hidden' name='fio' id='re_user_id' value='".$res['id']."'/>
                    <tr><td>ФИО</td><td><input type='text' name='fio' id='re_fio' value='".$res['fio']."'/></td></tr>
                    <tr><td>Email</td><td><input type='text' name='email' id='re_email' value='".$res['email']."'/></td></tr>
                    <tr><td>Новый пароль</td><td><input type='text' id='re_passwd' name='passswd'/></td></tr>
                    ".$ga_text."
                    </table>";
            }
        }
        //sleep(1);
    }

    /**
     * Редактируем пользователя
     */
    public function edit_user()
    {
        $this->get_data();
        if ((is_numeric((int)$this->input_data['user_id']) OR $_SESSION['user_groupe'] == 10) && ($this->input_data['user_id'] != '2')) {
            $user = new UserModel();
            $res = $user->getAll($this->input_data['user_id']);
            if ($res) {
                $res = $res[0];
                $user_edit = new UserModel();

                $user_edit->fio = (!empty($this->input_data['fio'])) ? $this->input_data['fio'] : $res['fio'];
                $user_edit->email = (!empty($this->input_data['email'])) ? $this->input_data['email'] : $res['email'];
                $user_edit->passwd = (!empty($this->input_data['passwd'])) ? md5($this->input_data['passwd']) : $res['passwd'];

                if ($_SESSION['user_groupe'] == 10 && $this->input_data['user_id'] != 1) {
                    $user_edit->groupe = (!empty($this->input_data['groupe'])) ? $this->input_data['groupe'] : $res['groupe'];
                    $inj = ['id', 'status', 'login'];
                } else {
                    $inj = ['id', 'status', 'login', 'groupe'];
                }

                if ($user_edit->update(['id' => $this->input_data['user_id']], $inj)) {
                    $this->out['error'] = false;
                    $this->out['msg']['text'] = 'Операция по изменению данных произведена успешно!';
                }
            }

        }
        //sleep(10);
    }

    /**
     * Принимает данные из Ajax запроса
     * @return bool
     */
    public function get_data(){
        //принимаем только post запросы
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
            if ($_POST['input_data']) {
                $this->input_data = $_POST['input_data'];
                $this->out['input_data'] = $this->input_data;
            };
        }

        return FALSE;
    }


    /**
     * Выводи данные ответа Ajax запроса
     */
    public function __destruct(){
        echo json_encode($this->out);
    }

}