<?php
header("Access-Control-Allow-Origin: http://127.0.0.1:5500");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require "db.php"; // Inclui a classe Database

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Método não permitido
        die(json_encode(["error" => "Método não permitido. Use POST."]));
    }

    // Obtém a conexão com o banco de dados
    $pdo = Database::getInstance()->getConnection();

    // Verifica se os campos obrigatórios estão preenchidos
    $requiredFields = ['title', 'description', 'trailer', 'category'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            die(json_encode(["error" => "O campo '$field' é obrigatório."]));
        }
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        die(json_encode(["error" => "Imagem obrigatória ou erro no upload."]));
    }

    // Configuração do upload (baseado na categoria)
    $category = strtolower(trim($_POST['category'])); // Obtém e formata a categoria
    $uploadDir = "uploads/" . $category . "/"; // Define a subpasta com base na categoria
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Cria a subpasta da categoria se não existir
    }

    $imageFile = $_FILES['image'];
    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    $maxFileSize = 5 * 1024 * 1024;

    if (!in_array($imageFile['type'], $allowedTypes)) {
        die(json_encode(["error" => "Tipo de imagem não permitido."]));
    }

    if ($imageFile['size'] > $maxFileSize) {
        die(json_encode(["error" => "Imagem excede o tamanho de 5MB."]));
    }

    $fileName = uniqid() . "_" . basename($imageFile['name']);
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($imageFile['tmp_name'], $filePath)) {
        die(json_encode(["error" => "Falha ao salvar a imagem."]));
    }

    // Inserir os dados no banco de dados
    $stmt = $pdo->prepare("INSERT INTO filmes (nome, descricao, trailer_link, imagem, categoria) VALUES (:nome, :descricao, :trailer, :imagem, :categoria)");
    $stmt->execute([
        ":nome" => $_POST['title'],
        ":descricao" => $_POST['description'],
        ":trailer" => $_POST['trailer'],
        ":imagem" => $filePath,
        ":categoria" => $_POST['category']
    ]);

    echo json_encode(["success" => true, "message" => "Filme cadastrado com sucesso!"]);
} catch (Exception $e) {
    echo json_encode(["error" => "Erro ao cadastrar filme: " . $e->getMessage()]);
    http_response_code(500); // Código de erro interno do servidor
}
