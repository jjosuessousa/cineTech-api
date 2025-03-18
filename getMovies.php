<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require "db.php";

try {
    $pdo = Database::getInstance()->getConnection();
    $categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : null;

    if ($categoria) {
        // Adiciona 'ORDER BY id DESC' para garantir que os filmes mais recentes aparecem primeiro
        $query = "SELECT * FROM filmes WHERE categoria = :categoria ORDER BY id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':categoria', $categoria, PDO::PARAM_STR);
    } else {
        // Adiciona 'ORDER BY id DESC' para listar os filmes mais recentes primeiro na busca geral
        $query = "SELECT * FROM filmes ORDER BY id DESC";
        $stmt = $pdo->prepare($query);
    }

    $stmt->execute();
    $filmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ajuste o caminho das imagens para URLs completas
    $filmes = array_map(function ($filme) {
        $filme['imagem'] = "http://localhost/cineTech-api/" . $filme['imagem']; // Ajuste o caminho
        return $filme;
    }, $filmes);

    if (empty($filmes)) {
        echo json_encode(["message" => "Nenhum filme encontrado para a categoria especificada."]);
    } else {
        echo json_encode($filmes);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao buscar filmes: " . $e->getMessage()]);
}
