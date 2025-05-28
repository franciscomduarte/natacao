<?php
class Inconsistencia {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    public function listar() {
        $stmt = $this->conn->prepare("SELECT * FROM linha_nao_reconhecida");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM linha_nao_reconhecida WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir($id) {
        $stmt = $this->conn->prepare("DELETE FROM linha_nao_reconhecida WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function atualizarSituacao($id) {
        $stmt = $this->conn->prepare("UPDATE linha_nao_reconhecida SET situacao = 'Reconhecido' FROM linha_nao_reconhecida WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
