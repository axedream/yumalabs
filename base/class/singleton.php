<?php
/**
 * Одиночка (прототип)
 */
class Singleton
{

    /**
     * @var self
     */
    private static $_aInstances = array();

    /**
     * Возвращает экземпляр себя
     *
     * @return self
     */
    public static function getInstance( $className=false ) {
		$sClassName = ($className===false) ? get_called_class() : $className;
		if( class_exists($sClassName) ){
			if( !isset( self::$_aInstances[ $sClassName ] ) ) self::$_aInstances[ $sClassName ] = new $sClassName();
			$oInstance = self::$_aInstances[ $sClassName ];
			return $oInstance;
		    }
            else    throw new Except('Class '.get_called_class().'  no exist!');
        }

    /**
    * Удобный вызов
    *
    * @return
    */
	public static function gi( $className=false ) {
		return self::getInstance($className);
	    }

    /**
     * Конструктор закрыт
     */
    final function __construct() {
        }

    /**
     * Клонирование запрещено
     */
    private function __clone() {
        }

    /**
     * Сериализация запрещена
     */
    private function __sleep() {
        }

    /**
     * Десериализация запрещена
     */
    private function __wakeup() {
        }
}
