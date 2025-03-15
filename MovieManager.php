<?php
class MovieManager {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function addMovie($data, $imageFile) {
        // Validação dos dados
        $requiredFields = ['title', 'description', 'trailer', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                throw new Exception("O campo '$field' é obrigatório.");
            }
        }

        // Validação da imagem
        $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
        $maxFileSize = 5 * 1024 * 1024;
        if (!in_array($imageFile['type'], $allowedTypes)) {
            throw new Exception("Tipo de imagem não permitido.");
        }
        if ($imageFile['size'] > $maxFileSize) {
            throw new Exception("Imagem excede o tamanho de 5MB.");
        }

        // Configuração do upload
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . "_" . basename($imageFile['name']);
        $filePath = $uploadDir . $fileName;

        if (!move_uploaded_file($imageFile['tmp_name'], $filePath)) {
            throw new Exception("Falha ao salvar a imagem.");
        }

        // Inserção no banco
        $stmt = $this->pdo->prepare("INSERT INTO filmes (nome, descricao, trailer_link, imagem, categoria) VALUES (:nome, :descricao, :trailer, :imagem, :categoria)");
        $stmt->execute([
            ":nome" => $data['title'],
            ":descricao" => $data['description'],
            ":trailer" => $data['trailer'],
            ":imagem" => $filePath,
            ":categoria" => $data['category']
        ]);
    }

    public function getMovies($search = "") {
        $sql = "SELECT * FROM filmes";
        if ($search) {
            $sql .= " WHERE nome LIKE :search OR categoria LIKE :search";
        }
        $stmt = $this->pdo->prepare($sql);
        if ($search) {
            $stmt->bindValue(":search", "%$search%", PDO::PARAM_STR);
        }
        $stmt->execute();

        $movies = array_map(function ($movie) {
            $movie['imagem'] = "http://localhost/cineTech-api/" . $movie['imagem'];
            return $movie;
        }, $stmt->fetchAll());

        return $movies;
    }
}