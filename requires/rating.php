<?php
	/**
	 * Вспомогательный класс.
	 * Обеспечивает потомкам возможность логировать сообщения об ошибках.
	 */
	class RWGObject {
		protected $errors=array();
		/**
		 * Логирование ошибок, возникающих в объекте
		 * @param $message Сообщение об ошибке
		 * @return Boolean false
		 */
		protected function log_error($message) {
			$this->errors[] = $message;
			return false;
		}
		/**
		 * Получение списка ошибок
		 * @return {Array} Список ошибок
		 */
		public function get_errors() {
			return $this->errors;
		}
	}

	final class RWGDBaseWork extends RWGObject {
		protected $host;
		protected $base;
		protected $user;
		protected $password;
		protected $connection=false;
		protected $requests = array(
			"get_rating"         => "select * from stat_ratings where IDRating={IDRating};",
			"get_rating_options" => "select * from stat_ratings_options where IDRating={IDRating};",
			"get_rating_data"    => "select * from stat_ratings_data  where IDRating={IDRating};",
			"get_rating_players" => "select IDPlayer from stat_ratings_data  group by IDPlayer;",
			"get_player_rating"  => "select * from stat_ratings_data as SRD 
									inner join stat_games as SG on SRD.IDGame = SG.IDGame 
									inner join stat_players as SP on SRD.IDPlayer = SP.IDPlayer 
									where SRD.IDRating={IDRating} and SRD.IDPlayer={IDPlayer} order by SG.Date asc;",
			"get_game_data"      =>	"select SG.Name as GameName, SG.Minutes as Minutes, SG.Seconds as Seconds, 
									SG.Date as GameDate, SG.LoadDate as LoadDate, SP.IDPlayer as IDPlayer, 
									SP.Name as PlayerName, SM.IDMap as IDMap, SM.Name as MapName, 
									SM.MapFile as MapFile, SMD.IDMod as IDMod, SMD.Name as ModName 
									from stat_games as SG 
									inner join stat_players as SP on SG.IDPlayer=SP.IDPlayer 
									inner join stat_maps as SM    on SG.IDMap=SM.IDMap 
									inner join stat_mods as SMD   on SG.IDMod=SMD.IDMod 
									where SG.IDGame={IDGame};",
			"reset_rating_data" => "delete from stat_ratings_data where IDRating={IDRating};",
			"reset_rating_game" => "delete from stat_ratings_data where IDRating={IDRating} and IDGame={IDGame};",
			"get_games_list"    => "select IDGame from stat_games order by Date asc;",
			"get_players_stats" => "select * from stat_teams as SG 
									inner join stat_player_stats as SPS on SG.IDTeam=SPS.IDTeam 
									inner join stat_players as SP on SPS.IDPlayer=SP.IDPlayer 
									where SG.IDGame={IDGame} order by SG.Number asc",
			"write_rating_data" => "insert into stat_ratings_data set IDPlayer={IDPlayer}, IDRating={IDRating}, 
									IDGame={IDGame}, Value='{Value}';",
			"delete_rating_data"=> "delete from stat_ratings_data where IDGame={IDGame} and IDRating={IDRating}"
		);
		
		/**
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
		/**
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
		/**
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
		/**
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
		/**
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
		/**
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
	
	/**
	 * Базовый объект работы с рейтингом
	 */
	abstract class RWGBaseRating extends RWGObject {
		const MIN_GAMES_NUM = 5;
		
		protected $base;	
		protected $id;
		protected $options;
		
		/**
		 * Конструктор объекта
		 * @param {Array} $base Настройки для подключения к БД
		 * @param {Number} $id Идентификатор рейтинга
		 * @return {Boolean} Отсутствие ошибок
		 */
		public function __construct(RWGDBaseWork $base, $id) {
		 	try {
				$this->base = $base;
				$this->id   = $id;
				$this->get_options();
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Получение свойств рейтинга
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function get_options() {
			try {
				$data = $this->base->request('get_rating_options', array('{IDRating}'=>$this->id));
				if (!$data)
					throw new Exception('Не удалось получить настройки рейтинга');
				// переписываем массив для пущего удобствия:
				for ($i=0; $i < count($data); $i++)
					$this->options[$data[$i]['Key']] = $data[$i]['Value'];
				return $this->options;
			} catch (Exception $e) {
				$this->log_error($e->getMessage());
				$this->options = array();
				return false;
			}
		}
		/**
		 * Получение данных по игре
		 * @param {Number} $gameid id игры
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function get_game_data($gameid) {
			try {
				$data = $this->base->request('get_game_data', array('{IDGame}'=>$gameid));
				if (!$data)
					throw new Exception('Не удалось получить данные по игре');
				return $data;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Получить список всех сыгранных игр
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function get_games_list() {
			try {
				// пытаемся получить список:
				$data = $this->base->request('get_games_list', array());
				if (!$data)
					throw new Exception('Не удалось получить список игр');
				return $data;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Получение данных рейтинга
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function get_rating_data() {
			try {
				$data = $this->base->request('get_rating_data', array('{IDRating}'=>$this->id));
				if (!$data)
					throw new Exception('Не удалось получить данные по рейтингу');
				return $data;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Инициализация рейтинга
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function reset_rating() {
			try {
				// удаляем все данные по текущему рейтингу:
				$data = $this->base->request('reset_rating_data', array('{IDRating}'=>$this->id));
				if (!$data)
					throw new Exception('Не удалось сбросить данные по рейтингу');
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Преобразовать стату
		 * @param {Array} $stats Необработанная стата
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function convert_stats($stats) {
			try {
				$result = array();
				for ($i=0; $i < count($stats); $i++) {
					$index = $stats[$i]['Number'];
					if (!$result[$index]) {
						$result[$index] = array(
							'Players'    => array(),
							'IDTeam'     => $stats[$i]['IDTeam'],
							'Win'        => $stats[$i]['Win'],
							'IDGame'     => $stats[$i]['IDGame'],
							'Number'     => $index,
							'ReplayFile' => $stats[$i]['ReplayFile']
						);
					}
					// Является ли игрок наблюдателем:
					// по идее нужно перенести проверку в заливку игры,
					// но неохото туда соваться :(
					$points =	$stats[$i]['InfantryKills'] + $stats[$i]['InfantryLoses'] + $stats[$i]['TanksKills'] +
								$stats[$i]['TanksLoses'] + $stats[$i]['TrucksKills'] + $stats[$i]['TrucksLoses'] + 
								$stats[$i]['AircraftKills'] + $stats[$i]['AircraftLoses'] + $stats[$i]['AntiAircraftKills'] +
								$stats[$i]['AntiAircraftLoses'] + $stats[$i]['ArtilleryKills'] + $stats[$i]['ArtilleryLoses'] +
								$stats[$i]['TrainsShipsKills'] + $stats[$i]['TrainsShipsLoses'] +
								$stats[$i]['UnknownKills'] + $stats[$i]['UnknownLoses'];
					if ($points == 0)	$points = true;
					else				$points = false;
					// заполняем данные по игроку:
					$result[$index]['Players'][] = array(
						'IDPlayer'          => $stats[$i]['IDPlayer'],
						'Name'              => $stats[$i]['Name'],
						'InfantryKills'     => $stats[$i]['InfantryKills'],
						'InfantryLoses'     => $stats[$i]['InfantryLoses'],
						'TanksKills'        => $stats[$i]['TanksKills'],
						'TanksLoses'        => $stats[$i]['TanksLoses'],
						'TrucksKills'       => $stats[$i]['TrucksKills'],
						'TrucksLoses'       => $stats[$i]['TrucksLoses'],
						'AircraftKills'     => $stats[$i]['AircraftKills'],
						'AircraftLoses'     => $stats[$i]['AircraftLoses'],
						'AntiAircraftKills' => $stats[$i]['AntiAircraftKills'],
						'AntiAircraftLoses' => $stats[$i]['AntiAircraftLoses'],
						'ArtilleryKills'    => $stats[$i]['ArtilleryKills'],
						'ArtilleryLoses'    => $stats[$i]['ArtilleryLoses'],
						'TrainsShipsKills'  => $stats[$i]['TrainsShipsKills'],
						'TrainsShipsLoses'  => $stats[$i]['TrainsShipsLoses'],
						'UnknownKills'      => $stats[$i]['UnknownKills'],
						'UnknownLoses'      => $stats[$i]['UnknownLoses'],
						//'Watcher'           => $stats[$i]['Wathcer']
						'Watcher'           => $points
					);
				}
				return $result;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Получить стату игроков для заданной игры
		 * @param {Number} $gameid id игры
		 * @return {Boolean} В случае возникновения ошибок возвращается false, иначе true
		 */
		protected function get_players_stats($gameid) {
			try {
				// пытаемся получить стату:
				$data = $this->base->request('get_players_stats', array('{IDGame}'=>$gameid));
				if (!$data)
					throw new Exception('Не удалось получить стату заданной игры');
				return $this->convert_stats($data);
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Записать результаты рейтингования
		 * @param {Array} $teams
		 */
		protected function write_rating($teams) {
			try {
				foreach ($teams as $tKey => $team)
				foreach ($team['Players'] as $pKey => $player)
				if (!$player['Watcher']) {
					$result = $this->base->request('write_rating_data', array(
						'{IDPlayer}' => $player['IDPlayer'],
						'{IDRating}' => $this->id,
						'{IDGame}'   => $team['IDGame'],
						'{Value}' => $player['Total']
					));
					if (!$result) {
						// удаляем уже записанное:
						$this->base->request('reset_rating_game', array(
							'{IDRating}' => $this->id,
							'{IDGame}'   => $team['IDGame']
						));
						throw new Exception("Не удалось записать стату для игрока {$player['IDPlayer']}");
					}
				}
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Упорядочить список рейтингов
		 * @param {Array} $rating_data Массив рейтингов игроков
		 * @return {Array} Упорядоченный массив рейтингов игроков
		 */
		public function sort_rating($rating_data, $order=SORT_DESC) {
			try {
				// :WARNING: external function array_sort
				return array_sort($rating_data, 'Value', $order);
			} catch (Exception $e) {
				$this->log_error($e->getMessage());
				return array();
			}
		}
		/**
		 * Получить рейтинг игрока
		 * @param {Number} $playerid id игрока
		 * @return {Array}
		 */
		abstract public function get_player_rating($playerid);
		/**
		 * Получить рейтинг всех игроков
		 * @return {Array}
		 */
		public function get_players_rating() {
			try {
				// пытаемся получить список игроков:
				if (!($result = $this->base->request('get_rating_players', array())))
					return false;
				// формируем массив данных по рейтингу:
				$ratings = array();
				foreach ($result as $pKey => $player) {
					$rating = $this->get_player_rating($player['IDPlayer']);
					if (!$player)
						return false;
					// проверка на количество игр:
					if ($rating['Times'] >= $this->MIN_GAMES_NUM && $rating['IDPlayer'] != 376)
						$ratings[] = $rating;
				}
				return $this->sort_rating($ratings, SORT_DESC);
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Учёт игры в текущем рейтинге
		 * @param {Number} $gameid id игры
		 * @return {Boolean} Была ли учтена игра
		 */
		abstract public function calculate_game($gameid);
		/**
		 * Учёт всех сыгранных игр
		 * @return {Boolean} Были ли учтены все игры
		 */
		public function calculate_games() {
			try {
				// сбрасываем рейтинг:
				if (!$this->reset_rating())
					return false;
				// получаем идентификаторы всех сыгранных игр:
				if (!($games_list = $this->get_games_list()))
					return false;
				for ($i=0; $i < count($games_list); $i++)
				if (!$this->calculate_game($games_list[$i]['IDGame'])) {
					$this->reset_rating();
					return false;
				}
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Удаление данных по игре и пересчёт рейтинга
		 * @param {Number} $gameid id игры
		 * @return {Boolean} Была ли учтена игра
		 */
		abstract public function delete_game($gameid);
	}
	/**
	 * 2P рейтинг
	 */
	class DualDeploymentRating extends RWGBaseRating {
		/**
		 * Получить рейтинг игрока
		 * @param {Number} $playerid id игрока
		 * @return {Array} Массив
		 */
		public function get_player_rating($playerid) {
			try {
				// пытаемся получить рейтинг игрока:
				$result = $this->base->request("get_player_rating", array(
					'{IDPlayer}' => $playerid,
					'{IDRating}' => $this->id
				));
				if (!$result)
					return false;
				// генерим ответ:
				$buffer = array(
					'IDPlayer' => $playerid,
					'Name'     => '',
					'Times'    => 0,
					'Value'    => 0,
					'List'     => array()
				);
				// проходимся по исходному массиву:
				foreach ($result as $gKey => $game) {
					$buffer['Name'] = $game['Name'];
					$buffer['Times']++;
					$buffer['Value'] += $game['Value'];
					$buffer['List'][] = array(
						'Date'   => $game['Date'],
						'Value'  => $game['Value']
					);
				}
				$buffer['Value'] = round($buffer['Value'] / $buffer['Times'], 2);
				return $buffer;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Учёт игры в текущем рейтинге
		 * @param {Number} $gameid id игры
		 * @return {Boolean} Была ли учтена игра
		 */
		public function calculate_game($gameid) {
			try {
				// пытаемся получить стату по игре:
				if (!($teams = $this->get_players_stats($gameid)))
					return false;
				// вытаскиваем настройки рейтинга:
				$options = $this->options;
				// проверяем соответствие количества игроков:
				if (isset($options['MinPlayers'])) {
					$players_num = 0;
					foreach ($teams as $k=>$v) {
						$players = $v['Players'];
						foreach ($players as $l=>$b)
						if (!$b['Watcher'])
							$players_num++;
					}
					// если игроков слишком мало - не учитываем игру:
					if ($options['MinPlayers'] > $players_num)
						return true;
				}
				// проходимся по всем игрокам и считаем заработанные ими очки, а также очки команды:
				foreach ($teams as $k=>$v) {
					$team = $v['Players'];
					$team_total = 0;
					// считаем общее кол-во очков для текущей команды:
					for ($j=0; $j < count($team); $j++) {
						$player = $team[$j];
						// пропускаем наблюдателей (в этой версии рейтинга они не получают бонусов за просмотр):
						if ($player['Watcher']) continue;
						// считаем очки для каждого игрока и команды:
						$total = 0;
						// если команда проиграла, считаем вклад в поражение, а иначе в победу:
						if ($v['Win'] == 0) {
							$total += $player['InfantryLoses']     * $options['Infantry'];
							$total += $player['TanksLoses']        * $options['Tank'];
							$total += $player['TrucksLoses']       * $options['Truck'];
							$total += $player['AircraftLoses']     * $options['Aircraft'];
							$total += $player['AntiAircraftLoses'] * $options['AntiAircraft'];
							$total += $player['ArtilleryLoses']    * $options['Artillery'];
							$total += $player['TrainsShipsLoses']  * $options['TrainsShip'];
							$total += $player['UnknownLoses']      * $options['Unknown'];
						} else if ($v['Win'] == 1) {
							$total += $player['InfantryKills']     * $options['Infantry'];
							$total += $player['TanksKills']        * $options['Tank'];
							$total += $player['TrucksKills']       * $options['Truck'];
							$total += $player['AircraftKills']     * $options['Aircraft'];
							$total += $player['AntiAircraftKills'] * $options['AntiAircraft'];
							$total += $player['ArtilleryKills']    * $options['Artillery'];
							$total += $player['TrainsShipsKills']  * $options['TrainsShip'];
							$total += $player['UnknownKills']      * $options['Unknown'];
						} else
							$total = 0;
						$team_total += $total;
						// сохраняем кол-во очков для тек. игрока:
						$teams[$k]['Players'][$j]['Total'] = $total;
					}
					// сохраняем кол-во очков для команды:
					if ($v['Win'] == 0)
						$teams[$k]['Total'] = -$team_total;
					else
						$teams[$k]['Total'] = $team_total;
				}
				// теперь рассчитываем коэффициенты:
				foreach ($teams as $k=>$v) {
					$team_total  = $v['Total'];
					$players_num = count($v['Players']);
					$average     = $v['Total'] / $players_num;
					foreach ($v['Players'] as $l=>$b) {
						if ($team_total) {
							$player_total = $b['Total'];
							$teams[$k]['Players'][$l]['Total'] = $player_total / $average;
						} else {
							$teams[$k]['Players'][$l]['Total'] = 0;
						}
					}
				}
				// собственно, записываем результаты в базу:
				if (!$this->write_rating($teams))
					return false;
				return true;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
		/**
		 * Удаление данных по игре и пересчёт рейтинга
		 * @param {Number} $gameid id игры
		 * @return {Boolean} Была ли учтена игра
		 */
		public function delete_game($gameid) {
			try {
				// пытаемся удать игру:
				$result = $this->base->request("delete_rating_data", array(
					'{IDGame}' => $gameid,
					'{IDRating}' => $this->id
				));
				if ($result)
					return true;
				else
					return false;
			} catch (Exception $e) {return $this->log_error($e->getMessage());}
		}
	}
?>