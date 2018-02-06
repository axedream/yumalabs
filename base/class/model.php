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
     * Удаление записи по
     * либо установленному свойству ID
     * либо переданному в метод ID
     * либо по WHERE
     */
    public function delete($id=0) {
        if ($this->table) {

            $query = "DELETE FROM ".$this->table." WHERE ";


            if(isset($this->id)) {
                if (!empty($this->id) && is_numeric($this->id)) {
                    if (!empty($this->_where)) {
                        $query .= $this->_where;
                    } else {
                        $query .= " id = '".$this->id."'";
                    }
                    return($this->db->query($query));
                }
            } else {
                if (is_numeric($id) && $id) {
                    $query .= " id = '".$id."'";
                    return($this->db->query($query));
                }
            }

        }
        return FALSE;
    }


    /**
     * Добавляем запись
     * @toDo продумать что бы автоматически выбирать на save добавление или обновление записи
     * @return FALSE|resource
     */
    public function  insert() {
        if ($this->sql_true) {
            $query= 'INSERT INTO '.$this->table;

            $key = '';
            $val = '';
            $i=0;

            foreach ($this->array_value as $value) {
                $i ++;
                if (!empty($this->$value)) {

                    if (empty($key)) {
                        $key .= '('.$value;
                    } else {
                        $key .= ' ,'.$value;
                    }


                    if (empty($val)) {
                        $val .=" VALUES ('".$this->$value."'";
                    } else {
                        $val .= ",'".$this->$value."'";
                    }

                    if (count($this->array_value) == $i) {
                        $key .= ') ';
                        $val .= ') ';
                    }

                } //ENDempty
            } //ENDforeach
            $query .= $key . $val;
            return($this->db->query($query));
            //file_put_contents("c:\\OpenServer\\domains\\hosting\\yml.txt","\nВыводимые данные:\n\n".print_r($query,TRUE), FILE_APPEND | LOCK_EX );
        }
    }

    /**
     * Сохранение свойств объекта
     */
    public function update($array=[],$inj=[]){

        if (count($array)>0) {
            $this->where($array);
        }

        if ($this->sql_true) {
            $query = '';
            foreach ($this->array_value as $value) {
                if (!in_array($value,$inj)) {
                    if (empty($query)) {
                        $query = " SET ".$value." = '".$this->$value."'";
                    } else {
                        $query .= " , ".$value." = '".$this->$value."'";
                    }
                }
            }
            if ($this->_where) {
                $query .= " WHERE ".$this->_where;
            }
            $final_query = "UPDATE ".$this->table." ".$query;
            file_put_contents("c:\\OpenServer\\domains\\hosting\\yml.txt","\nВыводимые данные:\n\n".print_r($final_query,TRUE), FILE_APPEND | LOCK_EX );
            return ($this->db->query($final_query));
        }
        return FALSE;
    }


    /**
     * Получаем все данные из модели либо по ID
     */
    public function getAll($id = FALSE) {
        if ($this->sql_true) {
            //если ID не пустой
            if (!empty($id)) {
                //есди ID цифра
                if (is_numeric((int)$id)) {
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
            if ($result) {
                return $result;
            }

        }
        return FALSE;
    }


    //полумаем свойства полей моедил
    public function __get($name) {
        //return ($this->db->getAll('SHOW COLUMNS FROM '.$this->table));
        return 'Нет такого поля';
    }

}