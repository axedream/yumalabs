<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 31.01.2018
 * Time: 9:47
 */
class Model {

    public $db;     //объект базы данных
    public $table;  //наименование наблицы
    public $_where; //условие
    private $sql_true = 0; //наличие таблицы успешно выполненный запрос по получению полей таблицы 1
    public $array_value = [];

    /**
     * Стройка
     */
    function __construct() {
        $this->db = new safemysql(Core::gi()->config['mysqldb']['system']);
        $this->table = lcfirst(explode('Model',get_class($this))[0]);
        $this->setValues();
    }

    /**
     * Реализуем свойства модели (из полей таблицы
     */
    private function setValues(){
        if ($this->table) {
            foreach ($this->db->getAll('SHOW COLUMNS FROM '.$this->table) as $results) {

                //добавляем свойства
                $this->$results['Field'] = '';
                //размещаем полученный свойсва в массив
                $this->array_value[] = $results['Field'];

                //устанавливаем ключ успешного запроса в базу
                if (!$this->sql_true) {
                    $this->sql_true = 1;
                }
            }//end foreach
        }
    }


    public function where($array=[]){
        if (is_array($array) && count($array)>0) {
            foreach ($array as $key => $value) {
                if (empty($this->_where)) {
                    $this->_where = $key." = '".$value."'";
                } else {
                    $this->_where .= " AND ".$key." = '".$value."'";
                }

            }
        }
    }

    /**
     * Получаем все данные из модели либо по ID
     */
    public function getAll($id = FALSE) {
        if ($this->sql_true) {
            //если ID не пустой
            if (!empty($id)) {
                //есди ID цифра
                if (is_numeric($id)) {
                    $result = $this->db->getAll("SELECT * FROM " . $this->table . " WHERE id =" . $id);
                //если ID не цифра
                } else {
                    //если ID массив
                    if (is_array($id)) {
                        //разворачиваем ID массив
                        $this->where($id);
                        //получаем результат
                        $result = $this->db->getAll("SELECT * FROM " . $this->table . " WHERE ".$this->_where);
                    //если ID текст
                    } else {
                        //если свободный запрос
                        $result = $this->db->getAll("SELECT * FROM " . $this->table . " WHERE ".$id);
                    }
                }
                //если не передан ID
            } else {
                if ( !empty($this->_where)) {
                    $result = $this->db->getAll("SELECT * FROM " . $this->table . " WHERE ".$this->_where);
                } else {
                    $result = $this->db->getAll("SELECT * FROM " . $this->table);
                }

            }

            if ($result) {
                if (is_array($result)) {
                    if (count($result)==1) {
                        foreach ($this->array_value as $value) {
                            $this->$value = $result[0][$value];
                        }
                    }
                }
            }

            return $result;
        }
        return FALSE;
    }


    //полумаем свойства полей моедил
    public function __get($name) {
        //return ($this->db->getAll('SHOW COLUMNS FROM '.$this->table));
        return 'Нет такого поля';
    }

}