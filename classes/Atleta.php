<?php
class Atleta {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    public function buscarPorRegistro($registro) {
        $sql = "SELECT id, nome, nascimento FROM atleta WHERE registro = :registro";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':registro', $registro);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function listar() {
        $stmt = $this->conn->prepare("SELECT * FROM atleta ORDER BY nome");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM atleta WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function inserir($registro, $nome, $nascimento) {
        $stmt = $this->conn->prepare("INSERT INTO atleta (registro, nome, nascimento) VALUES (?, ?, ?)");
        return $stmt->execute([$registro, $nome, $nascimento]);
    }

    public function atualizar($id, $registro, $nome, $nascimento) {
        $stmt = $this->conn->prepare("UPDATE atleta SET registro = ?, nome = ?, nascimento = ? WHERE id = ?");
        return $stmt->execute([$registro, $nome, $nascimento, $id]);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM atleta WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
