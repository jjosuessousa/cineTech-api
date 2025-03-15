<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json"); // Define o formato como JSON

require "db.php"; // Inclui a classe Database

try {
    // Verifica requisições OPTIONS para evitar erros CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Obtém a conexão com o banco de dados
    $pdo = Database::getInstance()->getConnection();

    // Verifica se há um termo de busca
    $search = isset($_GET['search']) ? trim($_GET['search']) : "";

    // Construir a consulta SQL
    $sql = "SELECT * FROM filmes";
    if ($search) {
        $sql .= " WHERE nome LIKE :search OR categoria LIKE :search";
    }

    // Preparar e executar a consulta
    $stmt = $pdo->prepare($sql);
    if ($search) {
        $stmt->bindValue(":search", "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();

    // Montar a resposta com o caminho completo das imagens
    $movies = array_map(function ($movie) {
        $movie['imagem'] = "http://localhost/cineTech-api/" . $movie['imagem'];
        return $movie;
    }, $stmt->fetchAll());

    // Retornar resultado como JSON
    if (empty($movies)) {
        echo json_encode(["message" => "Nenhum filme encontrado."]);
    } else {
        echo json_encode($movies);
    }
} catch (Exception $e) {
    // Capturar e retornar erros como JSON
    echo json_encode(["error" => "Erro ao buscar filmes: " . $e->getMessage()]);
    http_response_code(500); // Código de erro apropriado
}
