<?php


class UserModel extends Model {

    private $user_role = [0=>'Гость',5=>'Пользователь',10=>'Администратор'];

    /**
     * @param int $id
     * @return array|mixed
     * Роли пользователей
     */
    public function get_user_role($id = -1){
        if ($id!=-1) {
            return $this->user_role[$id];
        } else {
            return $this->user_role;
        }

    }
}