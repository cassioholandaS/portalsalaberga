<?php
require_once(__DIR__ . '/sessions.php');
$session = new sessions();
$session->autenticar_session();
$session->tempo_session();

require_once(__DIR__ . '/model.liberador.php');
class admin extends liberador
{
    function __construct()
    {
        parent::__construct();
    }
    public function registrar_perda(
        int $id_produto,
        int $quantidade,
        string $tipo_perda,
        string $data_perda
    ): int {

        try {
            $stmt_check = $this->connect->prepare("SELECT * FROM produtos WHERE id = :id");
            $stmt_check->bindParam(':id', $id_produto);
            $stmt_check->execute();

            if ($stmt_check->rowCount() > 0) {

                $dados = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $nova_quantidade = $dados['quantidade'] - $quantidade;
                $stmt_registrar = $this->connect->prepare("UPDATE `produtos` SET quantidade = :quantidade WHERE id = :id");
                $stmt_registrar->bindParam(':id', $id_produto);
                $stmt_registrar->bindParam(':quantidade', $nova_quantidade);
                $stmt_registrar->execute();

                $stmt_registrar = $this->connect->prepare("INSERT INTO perdas_produtos (id_produto, quantidade, tipo, `data`) VALUES (:id_produto, :quantidade, :tipo, :data_perda)");
                $stmt_registrar->bindParam(':id_produto', $id_produto);
                $stmt_registrar->bindParam(':quantidade', $quantidade);
                $stmt_registrar->bindParam(':tipo', $tipo_perda);
                $stmt_registrar->bindParam(':data_perda', $data_perda);
                $stmt_registrar->execute();

                if ($stmt_registrar) {

                    return 1;
                } else {

                    return 2;
                }
            } else {

                return 3;
            }
        } catch (PDOException $e) {
            error_log("Erro ao apagar produto: " . $e->getMessage());

            return 0;
        }
    }
    public function editar_produto_geral(int $id_produto, $barcode, string $nome, int $quantidade, int $id_categoria, string $validade): int
    {
        try {
            $consulta = "UPDATE $this->table4 SET barcode = :barcode, nome_produto = :nome, quantidade = :quantidade, id_categoria = :id_categoria, vencimento = :validade WHERE id = :id";
            $query = $this->connect->prepare($consulta);
            $query->bindValue(":id", $id_produto);
            $query->bindValue(":nome", $nome);
            $query->bindValue(":barcode", $barcode);
            $query->bindValue(":quantidade", $quantidade);
            $query->bindValue(":id_categoria", $id_categoria);
            $query->bindValue(":validade", $validade);

            if ($query->execute()) {
                return 1;
            } else {
                return 2;
            }
        } catch (PDOException $e) {
            error_log("Erro ao apagar produto: " . $e->getMessage());

            return 0;
        }
    }
    public function excluir_produto(int $id): int
    {
        try {
            $consultaDeleteMovimentacoes = "DELETE FROM movimentacao WHERE id_produtos = :id";
            $queryDeleteMovimentacoes = $this->connect->prepare($consultaDeleteMovimentacoes);
            $queryDeleteMovimentacoes->bindValue(":id", $id);


            if ($queryDeleteMovimentacoes->execute()) {
                $consulta = "DELETE FROM perdas_produtos WHERE id_produto = :id";
                $query = $this->connect->prepare($consulta);
                $query->bindValue(":id", $id);


                if ($query->execute()) {
                    $consulta = "DELETE FROM produtos WHERE id = :id";
                    $query = $this->connect->prepare($consulta);
                    $query->bindValue(":id", $id);


                    if ($query->execute()) {

                        return 1;
                    } else {
                        return 2;
                    }
                } else {
                    return 2;
                }
            } else {
                return 2;
            }
        } catch (PDOException $e) {
            error_log("Erro ao apagar produto: " . $e->getMessage());

            return 0;
        }
    }

    public function cadastrar_ambiente($nome_ambiente)
    {
        try {
            $stmt_check = $this->connect->prepare("SELECT * FROM $this->table5 WHERE nome_ambiente = :nome");
            $stmt_check->bindValue(":nome", $nome_ambiente);
            $stmt_check->execute();

            if ($stmt_check->rowCount() <= 0) {

                $stmt_check = $this->connect->prepare("INSERT INTO $this->table5 VALUES(NULL, :nome)");
                $stmt_check->bindValue(":nome", $nome_ambiente);

                if ($stmt_check->execute()) {

                    return 1;
                } else {
                    return 2;
                }
            } else {

                return 3;
            }
        } catch (Exception $e) {
            error_log("Erro ao cadastrar ambiente: " . $e->getMessage());
            return 0;
        }
    }
    public function editar_ambiente($id_ambiente, $nome_ambiente)
    {
        try {
            $stmt_check = $this->connect->prepare("SELECT * FROM $this->table5 WHERE id = :id");
            $stmt_check->bindValue(":id", $id_ambiente);
            $stmt_check->execute();

            if ($stmt_check->rowCount() == 1) {

                $stmt_check = $this->connect->prepare("UPDATE $this->table5 SET nome_ambiente = :nome WHERE id = :id");
                $stmt_check->bindValue(":id", $id_ambiente);
                $stmt_check->bindValue(":nome", $nome_ambiente);

                if ($stmt_check->execute()) {

                    return 1;
                } else {
                    return 2;
                }
            } else {

                return 3;
            }
        } catch (Exception $e) {
            error_log("Erro ao editar ambiente: " . $e->getMessage());
            return 0;
        }
    }
    public function excluir_ambiente($id_ambiente)
    {
        try {
            if ((int) $id_ambiente === 2) {
                return 4;
            }

            $this->connect->beginTransaction();
            $stmt_check = $this->connect->prepare("SELECT * FROM $this->table5 WHERE id = :id");
            $stmt_check->bindValue(":id", $id_ambiente);
            $stmt_check->execute();

            if ($stmt_check->rowCount() == 1) {

                $stmt_check = $this->connect->prepare("UPDATE $this->table4 SET id_ambiente = 2 WHERE id_ambiente = :id");
                $stmt_check->bindValue(":id", $id_ambiente);
                $stmt_check->execute();
                
                $stmt_check = $this->connect->prepare("DELETE FROM $this->table5 WHERE id = :id");
                $stmt_check->bindValue(":id", $id_ambiente);

                if ($stmt_check->execute()) {
                    $this->connect->commit();
                    return 1;
                } else {
                    $this->connect->rollBack();
                    return 2;
                }
            } else {
                $this->connect->rollBack();
                return 3;
            }
        } catch (Exception $e) {
            if ($this->connect->inTransaction()) {
                $this->connect->rollBack();
            }
            error_log("Erro ao excluir ambiente: " . $e->getMessage());
            return 0;
        }
    }
}
