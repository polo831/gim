<?php
/**
 * Conexión con la Base de Datos
 */
class DataBase
{
	private static $host;
	private static $dbname;
	private static $dbuser;
	private static $dbpass;

	private $dbh;
	private $stmt;
	private static $error;

	public static $numRows;

	private function __construct() {}

	private static function cargarEnv()
	{
		if (!empty($_ENV['DB_HOST']) && !empty($_ENV['DB_NAME']) && isset($_ENV['DB_USER'])) {
			return;
		}

		$envPath = __DIR__ . '/../../.env';
		if (file_exists($envPath)) {
			$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($lines as $line) {
				$line = trim($line);
				if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
					continue;
				}
				list($name, $value) = explode('=', $line, 2);
				$name = trim($name);
				$value = trim($value, " \t\n\r\0\x0B\"'");
				$_ENV[$name] = $value;
				$_SERVER[$name] = $value;
				putenv("$name=$value");
			}
		}
	}

	public static function connection()
	{
		self::cargarEnv();

		self::$host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
		self::$dbname = $_ENV['DB_NAME'] ?? 'gimnasiodb';
		self::$dbuser = $_ENV['DB_USER'] ?? 'root';
		self::$dbpass = $_ENV['DB_PASS'] ?? '';

		$dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8";
		$opciones = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);

		try {
			$conexion = new PDO($dsn, self::$dbuser, self::$dbpass, $opciones);
			$conexion->exec('SET NAMES utf8');
			$conexion->exec('SET time_zone = "-03:00";');
			$conexion->exec('SET @@session.time_zone = "-03:00";');
		} catch (PDOException $e) {
			self::$error = $e->getMessage();
			return self::$error;
		}
		return $conexion;
	}

	public static function query($sql, $params = [])
	{
		$db = static::connection();
		if (!($db instanceof PDO)) {
			return [];
		}
		$statement = $db->prepare($sql);
		$statement->execute($params);
		return $statement->fetchAll(PDO::FETCH_OBJ);
	}

	public static function execute($sql, $params = [])
	{
		$db = static::connection();
		if (!($db instanceof PDO)) {
			return ['status' => false, 'error' => self::$error];
		}
		try {
			$statement = $db->prepare($sql);
			$ok = $statement->execute($params);
			return ['status' => (bool)$ok];
		} catch (PDOException $e) {
			return ['status' => false, 'error' => $e->getMessage()];
		}
	}

	public static function getRecords($sql, $params = [])
	{
		$result = ['status' => true, 'resultado' => []];
		$db = static::connection();
		if (!($db instanceof PDO)) {
			return ['status' => false, 'resultado' => []];
		}
		try {
			$statement = $db->prepare($sql);
			$statement->execute($params);
			$result['resultado'] = $statement->fetchAll(PDO::FETCH_OBJ);
		} catch (Exception $e) {
			$result['status'] = false;
			$result['resultado'] = [];
		}
		return $result;
	}

	public static function getRecord($sql, $params = [])
	{
		$db = static::connection();
		if (!($db instanceof PDO)) {
			return null;
		}
		try {
			$statement = $db->prepare($sql);
			$statement->execute($params);
			$result = $statement->fetch(PDO::FETCH_OBJ);
			return $result ?: null;
		} catch (Exception $e) {
			return null;
		}
	}

	public static function transaction($sql)
	{
		$statement = static::connection();
		if (!($statement instanceof PDO)) {
			return ['state' => false, 'notification' => self::$error ?: 'Sin conexión'];
		}
		try {
			$statement->beginTransaction();
			for ($i = 0; $i < count($sql); $i++) {
				$statement->exec($sql[$i]);
			}
			$statement->commit();
			$estadoOperacion['state'] = true;
		} catch (PDOException $e) {
			if (stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
				$statement->commit();
			} else {
				$statement->rollBack();
				$estadoOperacion['notification'] = $e->errorInfo[2] ?? $e->getMessage();
			}
			$estadoOperacion['state'] = false;
		}

		if (empty($estadoOperacion['notification'])) {
			$estadoOperacion['notification'] = "Listo!";
		}
		return $estadoOperacion;
	}

	public static function transactionReturn($sql)
	{
		$statement = static::connection();
		if (!($statement instanceof PDO)) {
			return ['state' => false, 'notification' => self::$error ?: 'Sin conexión', 'resultado' => 'Error!'];
		}
		try {
			$statement->beginTransaction();
			$resultado = [];
			for ($i = 0; $i < count($sql); $i++) {
				$eje = $statement->prepare($sql[$i]);
				$eje->execute();
				$resultado = $eje->fetchAll(PDO::FETCH_OBJ);
			}
			$statement->commit();
			$estadoOperacion['state'] = true;
			$estadoOperacion['resultado'] = $resultado;
		} catch (PDOException $e) {
			if (stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
				$statement->commit();
			} else {
				$statement->rollBack();
				$estadoOperacion['notification'] = $e->errorInfo[2] ?? $e->getMessage();
			}
			$estadoOperacion['state'] = false;
			$estadoOperacion['resultado'] = 'Error!';
		}

		if (empty($estadoOperacion['notification'])) {
			$estadoOperacion['notification'] = "Operación exitosa";
		}
		return $estadoOperacion;
	}

	public static function rowCount($sql, $params = [])
	{
		$db = static::connection();
		if (!($db instanceof PDO)) {
			return false;
		}
		try {
			$statement = $db->prepare($sql);
			$statement->execute($params);
			return $statement->rowCount();
		} catch (Exception $e) {
			return false;
		}
	}

	public static function getColumnsNames($table)
	{
		$db = static::connection();
		if (!($db instanceof PDO)) {
			return [];
		}
		$sql = "SELECT column_name
				FROM information_schema.columns
				WHERE table_name = :table";
		$statement = $db->prepare($sql);
		$statement->execute([':table' => $table]);
		return $statement->fetchAll(PDO::FETCH_OBJ);
	}
}