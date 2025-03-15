<?php
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = "localhost";
        $dbname = "filmes";
        $user = "root";
        $pass = "";

        try {
            // Estabelece a conexão com o banco de dados
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            // Envia erro como JSON e encerra o script
            header('Content-Type: application/json');
            echo json_encode(["error" => "Falha na conexão: " . $e->getMessage()]);
            exit; // Garante que nada mais será executado
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

