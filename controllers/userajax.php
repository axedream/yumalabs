<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 17:49
 */

class UserAjax extends Basic {

    private $input_data;

    private $out = [
        'error' => true,
        'page_reload' => false,
        ];
    private $out_test = [
        'error' => true,
        'msg' => [
            //toDo вынести в конфиг сообщений
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
            //toDo вынести в конфиг сообщений
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
                //toDo вынести в отображение
                //---------------------генерируем форму------------------------//
                $this->out['msg']['table'] = "
                    <table class='table'>
                        <tr>
                            <td>ФИО</td>
                            <td>".$res['fio']."</td>
                        </tr>
                        <tr>
                            <td>Логин</td>
                            <td>".$res['login']."</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>".$res['email']."</td>
                        </tr>
                        <tr>
                            <td>Группа доступа</td>
                            <td>".(new UserModel())->get_user_role($res['groupe'])."</td>
                        </tr>
                    </table>
                    ";
                //---------------------END генерируем форму------------------------//
            }
        }
        //sleep(1);
    }

    /**
     * Отдает форму для создания пользователя
     */
    public function create_user_form(){
        $this->get_data();
        //создавать пользователей может только админ
        if ($_SESSION['user_groupe']==10) {

            //---------------------генерируем группы------------------------//
            $ga = '';
            foreach (((new UserModel())->get_user_role(-1)) as $key => $val) {
                if ($key!=0) {
                    $ga .="<option value='".$key."'>".$val."</option>";
                }
            }
            //если основной options сгенерирован, генерируем селекты
            if (!empty($ga)) {
                $ga_text = "<tr><td>Группа доступа</td><td><select id='re_groupe' class='selectpicker'>".$ga."</select></td></tr>";
            }
            //---------------------END генерируем группы------------------------//

            //---------------------генерируем форму------------------------//
            //toDo вынести в отображение
            $this->out['msg']['table'] = "
                    <table class='table'>
                        <tr>
                            <td>*Логин</td>
                            <td>
                                <input type='text' class='form-control' name='login' id='re_login' value='' placeholder='Идентификатор пользователя' autocomplete='off'/>
                            </td>
                        </tr>                    
                        <tr>
                            <td>ФИО</td>
                            <td>
                                <input type='text' class='form-control' name='fio' id='re_fio' value='' placeholder='Фамилия Имя Отчество' autocomplete='off'/>
                            </td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>
                                <input type='text' class='form-control' name='email' id='re_email' value='' placeholder='Электронный адрес' autocomplete='off'/>
                            </td>
                        </tr>
                        <tr>
                            <td>*Пароль</td>
                            <td>
                                <input type='password' class='form-control' id='re_passwd' name='passswd' value='' placeholder='Пароль пользователя' autocomplete='off'/>
                            </td></tr>
                    ".$ga_text."
                    <div>* - поля обязательные для заполнения</div>
                    </table>
            ";
            //---------------------END генерируем форму------------------------//

            $this->out['error'] = false;
        }
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

            //---------------------генерируем группы------------------------//
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
                    $ga_text = "<tr><td>Группа доступа</td><td><select id='re_groupe' class='selectpicker'>".$ga."</select></td></tr>";
                } else {
                    $ga_text = "";
                }
                //---------------------END генерируем группы------------------------//

                $this->out['error'] = false;
                //toDo вынести в отображение
                //---------------------генерируем форму------------------------//
                $this->out['msg']['table'] = "
                    <table class='table'>
                        <input type='hidden' name='fio' id='re_user_id' value='".$res['id']."'/>
                        <tr>
                            <td>ФИО</td>
                            <td>
                                <input type='text' class='form-control' name='fio' id='re_fio' value='".$res['fio']."'/>
                            </td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>
                                <input type='text' class='form-control' name='email' id='re_email' value='".$res['email']."'/>
                            </td>
                        </tr>
                        <tr>
                            <td>Новый пароль</td>
                            <td>
                                <input type='password' class='form-control' id='re_passwd' name='passswd'/>
                            </td>
                        </tr>
                    ".$ga_text."
                    </table>
                ";
                //---------------------END генерируем форму------------------------//
            }
        }
        //sleep(1);
    }

    /**
     * Создаем пользователя
     */
    public function create_user(){
        $this->get_data();
        //пользователя может создать только пользователь с правами администратора
        $this->out['input_data']['passswd'] = '***';
        if ($_SESSION['user_groupe']==10) {
            if (!empty($this->input_data['login']) && !empty($this->input_data['passwd']) ){
                $ut = new UserModel();
                $ut->where(['login'=>$this->input_data['login']]);
                $res = $ut->getAll();

                /*------ Проверяем есть ли такой логин в системе -----*/
                if (!$res) {
                    $user_create = new UserModel();
                    $user_create->login = $this->input_data['login'];
                    $user_create->passwd = md5($this->input_data['passwd']);
                } else {
                    $this->out['error'] = false;
                    $this->out['page_reload'] = false;
                    $this->out['msg']['table'] = 'Ошибка! Такой логин уже есть в системе!';
                    return FALSE;
                }
                /*------ END Проверяем есть ли такой логин в системе -----*/

                /*------ Проверяем корректность email -----*/
                if (!empty($this->input_data['email'])) {
                    if(!preg_match("/^[-a-z0-9!#$%&'*+\=?^_`{|}~]+(?:\.[-a-z0-9!#$%&'*+\=?^_`{|}~]+)*@(?:[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?\.)*(?:aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$/i", $this->input_data['email'])) {
                        $this->out['error'] = false;
                        $this->out['page_reload'] = false;
                        $this->out['msg']['table'] = 'Ошибка! Не верно задан email!';
                        return FALSE;
                    } else {
                        $user_create->email = $this->input_data['email'];
                    }
                }
                /*------END Проверяем корректность email -----*/

                if (!empty($this->input_data['fio'])) {
                    $user_create->fio = $this->input_data['fio'];
                }

                /*------ Проверяем группу доступа ------*/
                if (!empty($this->input_data['groupe'])) {

                    /*-- массив значений групп ---*/
                    $n = (new UserModel())->get_user_role(-1);
                    $nn = [];
                    foreach ($n as $key => $value) {
                        array_push($nn,$key);
                    }
                    /*-- массив значений групп ---*/

                    if(is_numeric((int)$this->input_data['groupe']) && in_array($this->input_data['groupe'],$nn)) {
                        $user_create->groupe = $this->input_data['groupe'];
                    } else {
                        $this->out['error'] = false;
                        $this->out['page_reload'] = false;
                        $this->out['msg']['table'] = 'Ошибка! Не верно задана группа!';
                    }
                }
                /*------END Проверяем группу доступа ------*/

                if($user_create->insert()) {
                    $this->out['error'] = false;
                    $this->out['page_reload'] = true;
                    $this->out['msg']['table'] = 'Запись успешно добавлена!';
                }

            } else {
                $this->out['error'] = false;
                $this->out['page_reload'] = false;
                $this->out['msg']['table'] = 'Ошибка! Не верно задан/не задан логин/пароль!';
            }
        }
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
                    //toDo вынести в конфиг сообщений
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