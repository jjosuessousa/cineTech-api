<?php
class CategoriaManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Listar todas as categorias
    public function getCategorias() {
        $query = "SELECT * FROM categorias";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Adicionar nova categoria
    public function addCategoria($nome) {
        $query = "INSERT INTO categorias (nome) VALUES (:nome)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        return $stmt->execute();
    }
}
?>
