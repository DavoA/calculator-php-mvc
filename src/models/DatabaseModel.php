<?php
namespace Models;

use PDO;
use PDOException;

class DatabaseModel {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $name = DB_NAME;
    private $db_handler;
    private $stmt;
    private $error;

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8mb4";
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];

        try {
            $this->db_handler = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log($this->error);
            throw new PDOException("Database connection failed: {$this->error}");
        }
    }

    public function query($sql) {
        $this->stmt = $this->db_handler->prepare($sql);
    }

    public function bind($params, $values, $types = null) {
        if (!is_array($params)) {
            $params = [$params];
            $values = [$values];
            $types = $types ? [$types] : null;
        }

        foreach ($params as $key => $param) {
            $value = $values[$key];
            $type = $types ? $types[$key] : match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };

            $this->stmt->bindValue($param, $value, $type);
        }
    }

    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            error_log("Database execute failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function createCalculation($data) {
        $this->query("
            INSERT INTO calculations (
                first_number,
                operation,
                second_number,
                result
            ) VALUES (
                :fnumber,
                :operator,
                :snumber,
                :result
            )
        ");
        $this->bind(
            [':fnumber', ':operator', ':snumber', ':result'],
            [
                $data['first_number'],
                $data['operation'],
                $data['second_number'],
                $data['result']
            ]
        );
        try{
            return $this->execute();
        } catch (PDOException $e){
            error_log("Database insert failed: " . $e->getMessage());
            return false;
        }
    }

    public function selectAll(): array {
        $this->query("SELECT * FROM calculations ORDER BY id DESC");
        return $this->resultSet();
    }
}