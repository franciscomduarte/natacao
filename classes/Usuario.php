<?php
class Usuario {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    // Autentica usuário por e-mail e senha
    public function autenticar($email, $senha) {
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        var_dump($usuario);

        if ($usuario && md5($senha) == $usuario['senha']) {
            // Remove a senha antes de retornar o usuário
            unset($usuario['senha']);
            return $usuario;
        }
        return false;
    }

    // (Opcional) Cadastrar novo usuário
    public function cadastrar($nome, $email, $senha) {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuario (nome, email, senha) VALUES (?, ?, ?)");
        return $stmt->execute([$nome, $email, $hash]);
    }

    // (Opcional) Buscar por ID
    public function buscarPorId($id) {
        $stmt = $this->conn->prepare("SELECT id, nome, email FROM usuario WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
