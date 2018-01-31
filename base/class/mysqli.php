<?php
define('OBJECT', 'OBJECT');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');
define('ARRAY_K', 'ARRAY_K');
define('DB_CHARSET', 'utf8');

if(!defined('DB_SAVEQUERIES')) define('DB_SAVEQUERIES', false);

if(!defined('DB_ADDSLASHES')) define('DB_ADDSLASHES', TRUE);

class db {
	var $show_errors = TRUE;
	var $num_queries = 0;
	var $num_error = 0;
	var $last_query;
	var $queries;
	var $started;

	/**
	 * @var bool
	 */
	public $insert_id = false;

	/**
	 * @var
	 */
	private $config;

	/**
	 * @var bool
	 */
	private $multi_slave = false;

	# конект к базе
	function __construct($dbuser, $dbpassword, $dbname, $dbhost) {
		register_shutdown_function(array(&$this, "__destruct"));

		if(defined('DB_CHARSET')) $this->charset = DB_CHARSET;

		$this->dbh = mysqli_connect($dbhost, $dbuser, $dbpassword);

		if(!$this->dbh) {
			$this->dbh = mysqli_connect($dbhost, $dbuser, $dbpassword);
			if(!$this->dbh) {
				$this->dbh = mysqli_connect($dbhost, $dbuser, $dbpassword);
			}
		}

		if(!empty($this->charset)) $this->query("SET NAMES '$this->charset'");

        $this->query("SET sql_mode = 'IGNORE_SPACE';");

		$this->select($dbname);

		$mtime = microtime();

		$mtime = explode(' ', $mtime);
		$this->started = $mtime[1] + $mtime[0];
		
	}

	/**
	 * Выявляем иньекции, если находим, ломаем (просто банально ломаем скрипт что бы он пришел в отстойник с IP адресом виновника)
	 */
	function spec_jnjection($query){
			$tb_inj = array(
					'version(' => '',
					'SLEEP' => '',
					'BENCHMARK' => '',
					'SYSTEM_USER' => '',
					'HEX(' => '',
					'information_schema' => '',
					'0x27' => '',
					'0x7e' => '',
					'time-based' => '',
					'blind' => '',
					'ORD(' => '',
					'MID(' => ''				
			);
			$query_new = str_replace(array_keys($tb_inj), array_values($tb_inj), $query);
			if(strlen($query) != strlen($query_new)){		
				$this->bail("ВЗЛОМ: До - ".$query." | После - ".$query_new);
				
				return $query_new;
			}
			return $query;
	}

	/**
	 * @param $dbuser
	 * @param $dbpassword
	 * @param $dbname
	 * @param $dbhost
	 */
	function add_slave($dbuser, $dbpassword, $dbname, $dbhost){
		$this->set_slave_status(true);
		$this->dbh_slave = mysqli_connect($dbhost, $dbuser, $dbpassword);
		if(!$this->dbh_slave || !mysqli_select_db($this->dbh_slave, $dbname)) {
			$this->set_slave_status(false);
		}else {
			if (!empty($this->charset)) @mysqli_query($this->dbh_slave, "SET NAMES '$this->charset'");
		}

	}

	/**
	 * @return bool
	 */
	function get_slave_status(){
		return $this->multi_slave;
	}

	/**
	 * @param bool $status
	 */
	function set_slave_status($status = false){
		$status = $status ? true: false;
		// если статус пытаемся определить для несуществующего подключения
		if($status && !$this->dbh_slave) $status = false;

		$this->multi_slave = $status;
	}

	/**
	 * @return mixed
	 */
	function get_load_time(){
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$time_end = $mtime[1] + $mtime[0];
		$time_total = $time_end - $this->started;
		return $time_total;
	}


	function __destruct() {
        $this->_sql_close();
		return TRUE;	
	}

	function ping() {
		if(mysqli_ping($this->dbh))
			return true;
		else return false;
		}

	# выбор базы
	function select($db) {
		if(!mysqli_select_db($this->dbh, $db)) {};
	}



	# ошибки SQL/DB error.
	function print_error($str = '') {
		if(!$str) $str = mysqli_error($this->dbh);
		if(trim($str) != ''){
			$this->bail("Ошибка запроса: ".$this->last_query.". Описание ошибки: ".$str);
		}
	}



	# вкл/выкл показа ошибок
	function show_errors() {
		$this->show_errors = TRUE;
	}

	function hide_errors() {
		$this->show_errors = FALSE;
	}



    # очистка кэша запросов
	function flush() {
		$this->last_result = array();
		$this->last_query = NULL;
	}



	# ескейп кавычек в массиве
	function add_magic_quotes($array) {
		if(DB_ADDSLASHES == FALSE) return $array;

		foreach($array as $k => $v) {
			if(is_array($v)) $array[$k] = $this->add_magic_quotes($v);
			else $array[$k] = $this->escape($v);
		}
		return $array;
	}



	# ескейп кавычек
	function escape($string) {
		if(DB_ADDSLASHES == FALSE) return $string;

		return mysqli_real_escape_string($this->dbh, $string);
	}


	function sql_add_num_queries(){
		++$this->num_queries;
	}

	/**
	 * запрос к базе
	 * @param $query
	 * @return bool|int
	 */
	function query($query) {
		
		//$query = $this->spec_jnjection($query);
		
		$return_val = 0;

		# подготовка
		$this->flush();

		# записываем какая функция была вызвана
		$this->func_call = "\$db->query(\"$query\")";

		# запоминаем последний запрос
		$this->last_query = $query;

		# выполняем запрос
		if(DB_SAVEQUERIES) $this->timer_start();

		if($this->get_slave_status() == true){
			if(preg_match('/^(.?)select /i', $query) > 0){
				$dbh =& $this->dbh_slave;
			}else {
				$dbh =& $this->dbh;
			}
		}else {
			$dbh =& $this->dbh;
		}
		
		$this->result = @mysqli_query($dbh, $query);
		
		++$this->num_queries;

        if(DB_SAVEQUERIES) {
            $backtrace = debug_backtrace();
            $debTrace = [];
            if($backtrace){
                $debTrace = [$backtrace[1]['file'], $backtrace[1]['line']];
            }
            $this->queries[] = [$dbh->host_info ." :: " . $query, $this->timer_stop(), $debTrace];
        }

		# вывод ошибки запроса
		if(@mysqli_error($dbh)) {
			$this->print_error();
			return FALSE;
		}
		

		
		if(preg_match("/^\\s*(insert|delete|update|replace) /i", $query)) {			
			$this->rows_affected = mysqli_affected_rows($dbh);
			# запоминаем insert_id
			if(preg_match("/^\\s*(insert|replace) /i", $query)) $this->insert_id = mysqli_insert_id($dbh);
			# возвращаем кол-во затронутых рядов
			$return_val = $this->rows_affected;
		}
		else {
			$num_rows = 0;
			while($row = @mysqli_fetch_object($this->result)) {
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}

			@mysqli_free_result($this->result);

			# запоминаем число выбраных рядов
			$this->num_rows = $num_rows;

			# возвращаем число выбраных рядов
			$return_val = $this->num_rows;
			
		}
		$this->num_error += mysqli_warning_count($this->dbh);//Возвращяем ошибку, если есть		
		return $return_val;
	}



	# get IN/NOT/=/!= set
	function get_in_set($field, $array, $negate = FALSE, $allow_empty_set = FALSE) {
		$array = (array)$array;

		if(empty($array)) {
			if(!$allow_empty_set) $this->print_error('No values specified for SQL IN comparison');
			else return ($negate) ? '1 = 1' : '1 = 0';
		}
		if(count($array) == 1) return $field . ($negate ? ' <> ' : ' = ') . $this->escape(current($array));
		else return $field . ($negate ? ' NOT IN ' : ' IN ') . '(' . implode(', ', $this->add_magic_quotes($array)) . ')';
	}

	/**
	 * Возвращает массив для списка
	 * @param string $table_name
	 * @param string $key_field_name
	 * @param string $title_field_name
	 * @return array 2 мерный массив, каждый элемент которого - массив с полями $key,$name
	 */
	function get_select_array($table_name,$key_field_name,$title_field_name,$order_field=false){
		$table_name=$this->escape($table_name);
		$key_field_name=$this->escape($key_field_name);
		$title_field_name=$this->escape($title_field_name);
		$order_field=$this->escape($order_field);
		if($order_field){$order=' ORDER BY `'.$order_field.'` ';}else{$order='';}
		$query='SELECT `'.$key_field_name.'` as `key`,`'.$title_field_name.'` as `name` FROM `'.$table_name.'` '.$order;
		return $this->get_results($query);
	}

	# вставка массива в таблицу
	function insert($table, $data) {

		$data = $this->add_magic_quotes($data);
		foreach ($data AS $k => $v){
			if(is_array($v)){
				$data[$k] = implode(",", $v);
			}		
		}
		$fields = array_keys($data);
		$query = "INSERT INTO $table (`" . implode('`, `', $fields) . "`) VALUES ('" . implode("','", $data) . "')";

		return $this->query($query);
	}
/**
	* Build sql statement from array for insert/update/select statements
	*
	* Idea for this from Ikonboard
	* Possible query values: INSERT, INSERT_SELECT, UPDATE, SELECT
	*
	*/
	function sql_build_array($query, $assoc_ary = false)
	{
		if (!is_array($assoc_ary))
		{
			return false;
		}

		$fields = $values = array();

		if ($query == 'INSERT' || $query == 'INSERT_SELECT')
		{
			foreach ($assoc_ary as $key => $var)
			{
				$fields[] = $key;

				if (is_array($var) && is_string($var[0]))
				{
					// This is used for INSERT_SELECT(s)
					$values[] = $var[0];
				}
				else
				{
					$values[] = $this->_sql_validate_value($var);
				}
			}

			$query = ($query == 'INSERT') ? ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')' : ' (' . implode(', ', $fields) . ') SELECT ' . implode(', ', $values) . ' ';
		}
		else if ($query == 'MULTI_INSERT')
		{
			trigger_error('The MULTI_INSERT query value is no longer supported. Please use sql_multi_insert() instead.', E_USER_ERROR);
		}
		else if ($query == 'UPDATE' || $query == 'SELECT')
		{
			$values = array();
			foreach ($assoc_ary as $key => $var)
			{
				$values[] = "$key = " . $this->_sql_validate_value($var);
			}
			$query = implode(($query == 'UPDATE') ? ', ' : ' AND ', $values);
		}

		return $query;
	}
	
	/**
	* Function for validating values
	* @access private
	*/
	function _sql_validate_value($var)
	{
		if (is_null($var))
		{
			return 'NULL';
		}
		else if (is_string($var))
		{
			return "'" . $this->sql_escape($var) . "'";
		}
		else
		{
			return (is_bool($var)) ? intval($var) : $var;
		}
	}
	function sql_return_on_error($fail = false)
	{
		$this->sql_error_triggered = false;
		$this->sql_error_sql = '';

		$this->return_on_error = $fail;
	}
/**
	* Build sql statement from array for select and select distinct statements
	*
	* Possible query values: SELECT, SELECT_DISTINCT
	*/
	function sql_build_query($query, $array)
	{
		$sql = '';
		switch ($query)
		{
			case 'SELECT':
			case 'SELECT_DISTINCT';

				$sql = str_replace('_', ' ', $query) . ' ' . $array['SELECT'] . ' FROM ';

				// Build table array. We also build an alias array for later checks.
				$table_array = $aliases = array();
				$used_multi_alias = false;

				foreach ($array['FROM'] as $table_name => $alias)
				{
					if (is_array($alias))
					{
						$used_multi_alias = true;

						foreach ($alias as $multi_alias)
						{
							$table_array[] = $table_name . ' ' . $multi_alias;
							$aliases[] = $multi_alias;
						}
					}
					else
					{
						$table_array[] = $table_name . ' ' . $alias;
						$aliases[] = $alias;
					}
				}

				// We run the following code to determine if we need to re-order the table array. ;)
				// The reason for this is that for multi-aliased tables (two equal tables) in the FROM statement the last table need to match the first comparison.
				// DBMS who rely on this: Oracle, PostgreSQL and MSSQL. For all other DBMS it makes absolutely no difference in which order the table is.
				if (!empty($array['LEFT_JOIN']) && sizeof($array['FROM']) > 1 && $used_multi_alias !== false)
				{
					// Take first LEFT JOIN
					$join = current($array['LEFT_JOIN']);

					// Determine the table used there (even if there are more than one used, we only want to have one
					preg_match('/(' . implode('|', $aliases) . ')\.[^\s]+/U', str_replace(array('(', ')', 'AND', 'OR', ' '), '', $join['ON']), $matches);

					// If there is a first join match, we need to make sure the table order is correct
					if (!empty($matches[1]))
					{
						$first_join_match = trim($matches[1]);
						$table_array = $last = array();

						foreach ($array['FROM'] as $table_name => $alias)
						{
							if (is_array($alias))
							{
								foreach ($alias as $multi_alias)
								{
									($multi_alias === $first_join_match) ? $last[] = $table_name . ' ' . $multi_alias : $table_array[] = $table_name . ' ' . $multi_alias;
								}
							}
							else
							{
								($alias === $first_join_match) ? $last[] = $table_name . ' ' . $alias : $table_array[] = $table_name . ' ' . $alias;
							}
						}

						$table_array = array_merge($table_array, $last);
					}
				}

				$sql .= $this->_sql_custom_build('FROM', implode(', ', $table_array));

				if (!empty($array['LEFT_JOIN']))
				{
					foreach ($array['LEFT_JOIN'] as $join)
					{
						$sql .= ' LEFT JOIN ' . key($join['FROM']) . ' ' . current($join['FROM']) . ' ON (' . $join['ON'] . ')';
					}
				}

				if (!empty($array['WHERE']))
				{
					$sql .= ' WHERE ' . $this->_sql_custom_build('WHERE', $array['WHERE']);
				}

				if (!empty($array['GROUP_BY']))
				{
					$sql .= ' GROUP BY ' . $array['GROUP_BY'];
				}

				if (!empty($array['ORDER_BY']))
				{
					$sql .= ' ORDER BY ' . $array['ORDER_BY'];
				}

			break;
		}

		return $sql;
	}

	# обновление записи в таблице
	function update($table, $data, $where) {
		$data = $this->add_magic_quotes($data);
		$bits = $wheres = array();
		//new dBug($data);
		foreach($data as $k => $v) {
			if(is_array($v)){
				$bits[] = "`$k` = '". implode(",",$v)."'";
			}else{
				$bits[] = "`$k` = '$v'";	
			}
		}
		if(is_array($where)) {
			foreach($where as $k => $v) {
				if(is_array($v))
				{
					$or = array();
					foreach($v as $or_k => $or_v)
						$or[] = "$or_k = '" . $this->escape($or_v) . "'";
					$wheres[] = '('.implode(' OR ', $or).')';
				}
				else
					$wheres[] = "$k = '" . $this->escape($v) . "'";
			}
		}
		else return FALSE;

		return $this->query("UPDATE $table SET " . implode(', ', $bits) . ' WHERE ' . implode(' AND ', $wheres));
	}



	function delete($table, $where) {
		$bits = $wheres = array();
		if(is_array($where)) {
			foreach($where as $k => $v) {
				$wheres[] = $k . " = '" . $this->escape($v) . "'";
			}
		}
		else return FALSE;

		return $this->query("DELETE FROM " . $table . " WHERE " . implode(' AND ', $wheres));
	}


	
	# получить переменную из базы
	function get_var($query = NULL, $x = 0, $y = 0, $cache = false) {
		$this->func_call = "\$db->get_var(\"$query\", $x, $y)";
		

		if($query) $this->query($query);

		# выбрать переменную из последнего запроса по x, y
		if(!empty($this->last_result[$y])) $values = array_values(get_object_vars($this->last_result[$y]));

		# возвращаем значение иначе NULL
		return (isset($values[$x]) && $values[$x] !== '') ? $values[$x] : NULL;
	}



    # получить строку из таблицы
	function get_row($query = NULL, $output = ARRAY_A, $y = 0,$cache = false) {
		$this->func_call = "\$db->get_row(\"$query\", $output, $y)";

		if($query) $this->query($query);
	
		if(!isset($this->last_result[$y])) return NULL;

		if($output == OBJECT) return $this->last_result[$y] ? $this->last_result[$y] : NULL;
		elseif($output == ARRAY_A) return $this->last_result[$y] ? get_object_vars($this->last_result[$y]) : NULL;
		elseif($output == ARRAY_N) return $this->last_result[$y] ? array_values(get_object_vars($this->last_result[$y])) : NULL;
	}



	# получить столбец из таблицы
	function get_col($query = NULL, $x = 0,$cache = false) {
		$this->func_call = "\$db->get_col(\"$query\", $x)";

		if($query) $this->query($query);
		# выбираем значения столбца
		if(!empty($this->last_result)) {
			for($i = 0; $i < count($this->last_result); $i++) {
				$new_array[$i] = $this->get_var(NULL, $x, $i);
			}
			return $new_array;
		}
        else return NULL;
	}
	# получить названия столбцов с колонки
	function get_fields($table){
		$output = array();
		$fields = $this->get_results("SHOW FIELDS FROM $table");
		foreach($fields AS $field){
			$output[] = $field["Field"];
		}
		return $output;
	}

	# получить результаты из базы
	function get_results($query = NULL, $output = ARRAY_A, $cache = false) {
		$this->func_call = "\$db->get_results(\"$query\", $output)";

		if($query) $this->query($query);

		if($output == OBJECT) return $this->last_result;
		elseif($output == ARRAY_A || $output == ARRAY_N || $output == ARRAY_K) {
			if($this->last_result) {
				$i = 0;
				foreach($this->last_result as $row) {
					$new_arr[$i] = (array)$row;
					$n_ar = array_values($new_arr[$i]);
					
					if($output == ARRAY_A) $new_array[$i] = $new_arr[$i];
					
					if($output == ARRAY_N) $new_array[$i] = $n_ar;
					
					if($output == ARRAY_K){ 
						$new_array[$n_ar[0]] = $new_arr[$i];
						unset($new_arr[$i]);
					}
						
					$i++;
				}
				return $new_array;
			}
			else return NULL;
		}
	}



    # транзакции
	function start_transaction() {
		return @mysqli_autocommit($this->dbh, FALSE);
	}

	function stop_transaction() {
		$result = @mysqli_commit($this->dbh);
		@mysqli_autocommit($this->dbh, TRUE);
		return $result;
	}

	function rollback_transaction() {
		$result = @mysqli_rollback($this->dbh);
		@mysqli_autocommit($this->dbh, true);
		return $result;
	}



	# таймер
	function timer_start() {
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$this->time_start = $mtime[1] + $mtime[0];
		return TRUE;
	}

	function timer_stop() {
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$time_end = $mtime[1] + $mtime[0];
		$time_total = $time_end - $this->time_start;
		return $time_total;
	}

	function sql_error($sql = '')
	{
		$this->bail($sql);
	}


    /*
	 * PHP BB huck
	 */
	/**
	* Version information about used database
	* @param bool $use_cache If true, it is safe to retrieve the value from the cache
	* @return string sql server version
	*/
	function sql_server_info($raw = false, $use_cache = true)
	{
		global $cache;

		if (!$use_cache || empty($cache) || ($this->sql_server_version = $cache->get('mysqli_version')) === false)
		{
			$result = @mysqli_query($this->dbh, 'SELECT VERSION() AS version');
			$row = @mysqli_fetch_assoc($result);
			@mysqli_free_result($result);

			$this->sql_server_version = $row['version'];

			if (!empty($cache) && $use_cache)
			{
				$cache->put('mysqli_version', $this->sql_server_version);
			}
		}

		return ($raw) ? $this->sql_server_version : 'MySQL(i) ' . $this->sql_server_version;
	}

	/**
	* SQL Transaction
	* @access private
	*/
	function _sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				return @mysqli_autocommit($this->dbh, false);
			break;

			case 'commit':
				$result = @mysqli_commit($this->dbh);
				@mysqli_autocommit($this->dbh, true);
				return $result;
			break;

			case 'rollback':
				$result = @mysqli_rollback($this->dbh);
				@mysqli_autocommit($this->dbh, true);
				return $result;
			break;
		}

		return true;
	}

	/**
	* Base query method
	*
	* @param	string	$query		Contains the SQL query which shall be executed
	* @param	int		$cache_ttl	Either 0 to avoid caching or the time in seconds which the result shall be kept in cache
	* @return	mixed				When casted to bool the returned value returns true on success and false on failure
	*
	* @access	public
	*/
	function sql_query($query = '', $cache_ttl = 0)
	{
		if ($query != '')
		{
			global $cache;

			// EXPLAIN only in extra debug mode
			if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('start', $query);
			}

			$this->query_result = ($cache_ttl && method_exists($cache, 'sql_load')) ? $cache->sql_load($query) : false;
			$this->sql_add_num_queries($this->query_result);

			if ($this->query_result === false)
			{
				if (($this->query_result = @mysqli_query($this->dbh, $query)) === false)
				{
			
					$this->sql_error(mysql_error()." -". mysql_errno(). " - ".$query);
				}

				if (defined('DEBUG_EXTRA'))
				{
					$this->sql_report('stop', $query);
				}

				if ($cache_ttl && method_exists($cache, 'sql_save'))
				{
					$cache->sql_save($query, $this->query_result, $cache_ttl);
				}
			}
			else if (defined('DEBUG_EXTRA'))
			{
				$this->sql_report('fromcache', $query);
			}
		}
		else
		{
			return false;
		}

		return $this->query_result;
	}

	/**
	* Build LIMIT query
	*/
	function _sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		$this->query_result = false;

		// if $total is set to 0 we do not want to limit the number of rows
		if ($total == 0)
		{
			// MySQL 4.1+ no longer supports -1 in limit queries
			$total = '18446744073709551615';
		}

		$query .= "\n LIMIT " . ((!empty($offset)) ? $offset . ', ' . $total : $total);

		return $this->sql_query($query, $cache_ttl);
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows()
	{
		return ($this->dbh) ? @mysqli_affected_rows($this->dbh) : false;
	}

	/**
	* Fetch current row
	*/
	function sql_fetchrow($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (!is_object($query_id) && isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_fetchrow($query_id);
		}

		return ($query_id !== false) ? @mysqli_fetch_assoc($query_id) : false;
	}

	/**
	* Seek to given row number
	* rownum is zero-based
	*/
	function sql_rowseek($rownum, &$query_id)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (!is_object($query_id) && isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_rowseek($rownum, $query_id);
		}

		return ($query_id !== false) ? @mysqli_data_seek($query_id, $rownum) : false;
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid()
	{
		return ($this->dbh) ? @mysqli_insert_id($this->dbh) : false;
	}

	/**
	* Free sql result
	*/
	function sql_freeresult($query_id = false)
	{
		global $cache;

		if ($query_id === false)
		{
			$query_id = $this->query_result;
		}

		if (!is_object($query_id) && isset($cache->sql_rowset[$query_id]))
		{
			return $cache->sql_freeresult($query_id);
		}

		return @mysqli_free_result($query_id);
	}

	/**
	* Escape string used in sql query
	*/
	function sql_escape($msg)
	{
		return @mysqli_real_escape_string($this->dbh, $msg);
	}

	/**
	* Build LIKE expression
	* @access private
	*/
	function _sql_like_expression($expression)
	{
		return $expression;
	}

	/**
	* Build db-specific query data
	* @access private
	*/
	function _sql_custom_build($stage, $data)
	{
		switch ($stage)
		{
			case 'FROM':
				$data = '(' . $data . ')';
			break;
		}

		return $data;
	}

	/**
	* return sql error array
	* @access private
	*/
	function _sql_error()
	{
		if (!$this->dbh)
		{
			return array(
				'message'	=> @mysqli_connect_error(),
				'code'		=> @mysqli_connect_errno()
			);
		}

		return array(
			'message'	=> @mysqli_error($this->dbh),
			'code'		=> @mysqli_errno($this->dbh)
		);
	}

	/**
	* Close sql connection
	* @access private
	*/
	function _sql_close()
	{
		return @mysqli_close($this->dbh);
	}

	/**
	* Build db-specific report
	* @access private
	*/
	function _sql_report($mode, $query = '')
	{
		static $test_prof;

		// current detection method, might just switch to see the existance of INFORMATION_SCHEMA.PROFILING
		if ($test_prof === null)
		{
			$test_prof = false;
			if (strpos(@mysqli_get_server_info($this->dbh), 'community') !== false)
			{
				$ver = mysqli_get_server_version($this->dbh);
				if ($ver >= 50037 && $ver < 50100)
				{
					$test_prof = true;
				}
			}
		}

		switch ($mode)
		{
			case 'start':

				$explain_query = $query;
				if (preg_match('/UPDATE ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}
				else if (preg_match('/DELETE FROM ([a-z0-9_]+).*?WHERE(.*)/s', $query, $m))
				{
					$explain_query = 'SELECT * FROM ' . $m[1] . ' WHERE ' . $m[2];
				}

				if (preg_match('/^SELECT/', $explain_query))
				{
					$html_table = false;

					// begin profiling
					if ($test_prof)
					{
						@mysqli_query($this->dbh, 'SET profiling = 1;');
					}

					if ($result = @mysqli_query($this->dbh, "EXPLAIN $explain_query"))
					{
						while ($row = @mysqli_fetch_assoc($result))
						{
							$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
						}
					}
					@mysqli_free_result($result);

					if ($html_table)
					{
						$this->html_hold .= '</table>';
					}

					if ($test_prof)
					{
						$html_table = false;

						// get the last profile
						if ($result = @mysqli_query($this->dbh, 'SHOW PROFILE ALL;'))
						{
							$this->html_hold .= '<br />';
							while ($row = @mysqli_fetch_assoc($result))
							{
								// make <unknown> HTML safe
								if (!empty($row['Source_function']))
								{
									$row['Source_function'] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $row['Source_function']);
								}

								// remove unsupported features
								foreach ($row as $key => $val)
								{
									if ($val === null)
									{
										unset($row[$key]);
									}
								}
								$html_table = $this->sql_report('add_select_row', $query, $html_table, $row);
							}
						}
						@mysqli_free_result($result);

						if ($html_table)
						{
							$this->html_hold .= '</table>';
						}

						@mysqli_query($this->dbh, 'SET profiling = 0;');
					}
				}

			break;

			case 'fromcache':
				$endtime = explode(' ', microtime());
				$endtime = $endtime[0] + $endtime[1];

				$result = @mysqli_query($this->dbh, $query);
				while ($void = @mysqli_fetch_assoc($result))
				{
					// Take the time spent on parsing rows into account
				}
				@mysqli_free_result($result);

				$splittime = explode(' ', microtime());
				$splittime = $splittime[0] + $splittime[1];

				$this->sql_report('record_fromcache', $query, $endtime, $splittime);

			break;
		}
	}

	/**
	 * @method получает инфо столбцы таблицы db
	 * храниит ввиде массива в классе если уже раз собирали
	 * @author Krox
	 */
	function get_table_columns($tableName, $fields=array(), $exclude=array()){
		if(!isset($this->dbTableInfo[$tableName])){
			$sql = "SHOW COLUMNS FROM ".$tableName;
			$columns = $this->get_col($sql);
				
			$this->dbTableInfo[$tableName] = $columns;
		};
		
		if($fields){
			//$fields = array();
			foreach($this->dbTableInfo[$tableName] as $value){
				if(isset($fields[$value])){
					$fields[] = $value;
				}
			}
			return $fields;
		}elseif($exclude){
			$exclude = array_flip($exclude);
			foreach($this->dbTableInfo[$tableName] as $value){
				if(!isset($exclude[$value])){
					$fields[] = $value;
				}
			}
			return $fields;
		}
	
		return $this->dbTableInfo[$tableName];
	}
	
	/**
	 * конструктор запросов
	 * собирает запроc 
	 * @author	Krox
	 * @param array $tables array'tableName'=>array('relation'=>array('tablename.col','somename.col'))
	 * @param array $compares array(array('tablename.col',0), array('tablename.col','NOW()','expression'))
	 * @param array $sort array('tableName.field','ASC')
	 * @param array $group array('tableName.field')
	*/
	public function buildSql($tables=array(), $compares=array(), $sort=null, $group=null){
		$sqlFields = array();
		$sqlTables = array();
		$counter = 0;
		$tAliases = array();
		$where = '';
	
		foreach($tables as $tableName => $tAttrs){
	
			$columns = isset($tAttrs['fields']) ? $tAttrs['fields'] : null;
			$exclude = isset($tAttrs['excludeFilds']) ? $tAttrs['excludeFilds'] : null;
			$fields = $this->get_table_columns($tableName, $columns, $exclude);
				
			if($fields){
				$counter++;
				$tAliases[$tableName] = $tAlias = isset($tAttrs['alias']) ? $tAttrs['alias'] : 't'.$counter;
				
				//fields
				foreach($fields as $field){
					$sqlFields[] = $this->replaceTableAlias($tableName.'.'.$field,$tAliases).' AS '.$tableName.'_'.$field;
				}
	
				//tables compares
				if($tAttrs && isset($tAttrs['relation']) && count($tAttrs['relation'])){
					$sqlCompare = $this->replaceCompareStr($tAttrs['relation'], $tAliases);
					$sqlTables[] = "\n LEFT JOIN ".$tableName.' AS '.$tAlias.' ON '.$sqlCompare;
				}else{
					$sqlTables[] = $tableName.' AS '.$tAlias;
				}
	
			}
		}
		
		//where
		foreach($compares as $compare){
			$each[] = $this->replaceCompareStr($compare, $tAliases);
		}
		if($each){
			$where = "\n WHERE ".join("\n AND ", $each);
		}
		
		//group
		if($group){
			$strGroup = "\n GROUP BY ".$this->replaceTableAlias($group[0], $tAliases);
		}
		
		//sort
		if($sort){
			$dest = isset($sort[1]) ? $sort[1] : 'ASC';
			$strSort = "\n ORDER BY ".$this->replaceTableAlias($sort[0], $tAliases).' '.$dest."\n";
		}
		
		$sql = 'SELECT '.join(', ',$sqlFields)."\n FROM ".join(' ', $sqlTables).' '.$where.' '.$strGroup.' '.$strSort;
		
		return $sql;
	}
	
	/**
	 * заменяет имена в строке на алиас
	 * 
	 * @param	string $str строка сравнения
	 * @param	string $tAliases алиасы таблиц
	 */
	protected function replaceCompareStr($str, $tAliases, $replace=true){
		$compare_a1 = explode('.',$str[0]);
		if(isset($str[2]) && $str[2]=='expression'){
			$compare_a1[0] = isset($tAliases[$compare_a1[0]]) ? $tAliases[$compare_a1[0]] : $compare_a1[0];
			return join('.',$compare_a1).' '.$str[1];
		}
		
		$compare_a2 = explode('.',$str[1]);
		if($replace){
			$compare_a1[0] = $this->replaceTableAlias($compare_a1[0], $tAliases);
			$compare_a2[0] = $this->replaceTableAlias($compare_a2[0], $tAliases);
		}
		$typeCompare = isset($str[2]) ? $str[2] : '=';
		if(isset($str[3]) && $str[3]=='string')
			return join('.',$compare_a1).' '.$typeCompare.' "'.join('.',$compare_a2).'"';
		
		return join('.',$compare_a1).' '.$typeCompare.' '.join('.',$compare_a2);
	}

	/**
	 * заменяет имена таблиц на алиас
	 * 
	 * @param	string $str
	 * @param	array $tAliases 
	 */
	protected function replaceTableAlias($str, $tAliases){
		$_a = explode('.',$str);
		$_a[0] = isset($tAliases[$_a[0]]) ? $tAliases[$_a[0]] : $_a[0];
		return join('.',$_a);
	}
	
}

	//
	//	Функция для получения id записи справочников по значению других полей

	function _id_by_value($tn, $fn, $v)
	{
		$ret = "NOT_EXIST";
		$sql = sprintf("SELECT id FROM %s WHERE %s = '%s'", $tn, $fn, sql_escape($v));
		if (($res = sql_query($sql)) && ($row = sql_fetch($res)))
		  $ret = $row["id"];
		return $ret;
	}
	
	
	//
	//  Проверка существования записи с заданным id
	
	function _id_exists($tn, $id)
	{
		$id = (int) $id;
		if ($res = sql_query("SELECT id FROM $tn WHERE id = $id"))
			if (sql_rows_count($res) == 1)
				return 1;
		return 0;
	}
	
?>
