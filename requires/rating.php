<?php
	/*
	 * Вспомогательный класс.
	 * Обеспечивает потомкам возможность логировать сообщения об ошибках.
	 */
	class RWGObject {
		protected $errors=array();
		/*
		 * Логирование ошибок, возникающих в объекте
		 * @param $message Сообщение об ошибке
		 * @return Boolean false
		 */
		protected function log_error($message) {
			$this->errors[] = $message;
			return false;
		}
		/*
		 * Получение списка ошибок
		 * @return {Array} Список ошибок
		 */
		protected function get_errors() {
			return $this->errors;
		}
	}

	class RWGDBaseWork extends RWGObject {
		protected $host;
		protected $base;
		protected $user;
		protected $password;
		protected $connection=false;
		protected $requests = array(
			"get_rating"         => "select * from stat_ratings where IDRating={IDRating};",
			"get_rating_options" => "select * from stat_ratings_options where IDRating={IDRating};",
			"get_rating_data"    => "select * from stat_ratings_data  where IDRating={IDRating};",
			"get_game_data"      =>	"select SG.Name as GameName, SG.Minutes as Minutes, SG.Seconds as Seconds, 
									SG.Date as GameDate, SG.LoadDate as LoadDate, SP.IDPlayer as IDPlayer, 
									SP.Name as PlayerName, SM.IDMap as IDMap, SM.Name as MapName, 
									SM.MapFile as MapFile, SMD.IDMod as IDMod, SMD.Name as ModName 
									from stat_games as SG 
									inner join stat_players as SP on SG.IDPlayer=SP.IDPlayer 
									inner join stat_maps as SM    on SG.IDMap=SM.IDMap 
									inner join stat_mods as SMD   on SG.IDMod=SMD.IDMod 
									where SG.IDGame={IDGame}"
		);
		
		/*
		 * Подключение к БД
		 * @return Integer Идентификатор соединения
		 */
		private function connect() {
			try {
				$this->connection = mysql_connect($this->host, $this->user, $this->password);
				if ($this->connection === false)
					return $this->log_error('Не удалось открыть соединение с БД');
				if (!mysql_select_db($this->base, $this->connection))
					return $this->log_error('Не удалось подключиться к БД');
				return $this->connection;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/*
		 * Конструктор объекта
		 * @param $host Хост, на котором крутится БД
		 * @param $base Имя БД
		 * @param $user Пользователь БД
		 * @param $password Пароль пользователя для доступа к БД
		 * @return Boolean Отсутствие ошибок
		 */
		public function __construct($host, $base, $user, $password) {
		 	try {
				$this->host     = $host;
				$this->base     = $base;
				$this->user     = $user;
				$this->password = $password;
				$this->connect();
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/*
		 * Сформировать строку запроса к БД
		 * @param $request Шаблон запроса
		 * @param $options Массив опций, используемый при формировании строки запроса
		 * @return String Строка запроса к БД
		 */
		protected function get_query($request, $options) {
			try {
				return strtr($request, $options);
			} catch (Exception $e) {
				$this->log_error($e->getMessage());
				return '';
			}
		}
		/*
		 * Получить двумерный массив из результата запроса к БД
		 * @param $result Шаблон запроса
		 * @return Array Результат запроса в виде двумерного массива
		 */
		 protected function result_to_arr($result) {
		 	try {
				for ($data=array(); $row=mysql_fetch_assoc($result); $data[]=$row);
				return $data;
			} catch (Exception $e) {
				$this->log_error($e->getMessage());
				return array();
			}
		 }
		/*
		 * Осуществить простой запрос к БД
		 * @param $query Строка запроса
		 * @return Array Список с результатами запроса к БД
		 */
		public function simple_request($query) {
			try {
				if ($this->connection === false) return false;
				$result = mysql_query($query, $this->connection);
				if (is_bool($result))
					return $result;
				else
					return $this->result_to_arr($result);
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/*
		 * Осуществить заданный запрос к БД
		 * @param $name Имя запроса
		 * @param $options Массив опций, используемый при формировании строки запроса
		 * @return Array Список с результатами запроса к БД
		 */
		public function request($name, $options) {
			try {
				if (!isset($this->requests[$name])) return false;
				// получаем строку запроса:
				$query = $this->get_query($this->requests[$name], $options);
				return $this->simple_request($query);
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
	}
	
	/*
	 * Базовый объект работы с рейтингом
	 */
	abstract class RWGBaseRating extends RWGObject {
		protected $base;	
		protected $id;
		protected $options;
		
		/*
		 * Конструктор объекта
		 * @param {Array} $base Настройки для подключения к БД
		 * @param {Number} $id Идентификатор рейтинга
		 * @return {Boolean} Отсутствие ошибок
		 */
		public function __construct(RWGDBaseWork $base, $id) {
		 	try {
				$this->$base = $base;
				$this->id    = $id;
				$this->get_options();
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		
		/*
		 * Получение свойств рейтинга
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function get_options() {
			try {
				$data = $this->base->request('get_rating_options', array('{IDRating}'=>$this->id));
				if (!$data)
					throw new Exception('Не удалось получить настройки рейтинга');
				$this->options = $data;
				return $data;
			} catch (Exception $e) {
				$this->log_error($e->getMessage());
				$this->options = array();
				return false;
			}
		}
		
		protected function get_game_data($gameid) {
			try {
				$data = $this->base->request('get_game_data', array('{IDGame}'=>$gameid));
				if (!$data)
					throw new Exception('Не удалось получить данные по игре');
				return $data;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		
		protected function get_rating_data() {
			try {
				$data = $this->base->request('get_game_data', array('{IDGame}'=>$gameid));
				if (!$data)
					throw new Exception('Не удалось получить данные по игре');
				return $data;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
	}
?>