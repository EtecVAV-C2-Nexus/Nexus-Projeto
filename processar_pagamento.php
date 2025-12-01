<?php
session_start();

// Inclui os arquivos necessários
require_once 'database.php';
require_once 'funcoes.php';

// Proteção: o usuário deve estar logado
if (!logado()) {
    redirect("login.php");
}

// Proteção: deve haver uma compra pendente na sessão
if (!isset($_SESSION['compra_pendente_idjogo'])) {
    $_SESSION['message'] = 'Nenhuma transação pendente encontrada.';
    $_SESSION['message_type'] = 'error';
    redirect("index.php");
}

// Pega o status do pagamento (da URL)
$status = isset($_GET['status']) ? $_GET['status'] : 'cancelado';
$idjogo = $_SESSION['compra_pendente_idjogo'];
$usuario = $_SESSION["id"];
$funcao_usuario = $_SESSION["funcao"];

// Limpa a variável de sessão de qualquer forma,
// pois a transação está sendo processada (seja para aprovar ou cancelar)
unset($_SESSION['compra_pendente_idjogo']);


if ($status == 'aprovado') {
    // Se aprovado, executa a lógica de banco de dados que estava em 'comprar_jogo.php'
    
    mysqli_begin_transaction($conexao);

    try {
        // 1. Re-verifica o estoque para garantir (transação segura)
        $sql_check_stock = "SELECT estoque, nome FROM jogos WHERE ID = ? FOR UPDATE"; // FOR UPDATE bloqueia a linha
        if ($stmt_check_stock = mysqli_prepare($conexao, $sql_check_stock)) {
            mysqli_stmt_bind_param($stmt_check_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_check_stock);
            mysqli_stmt_bind_result($stmt_check_stock, $current_stock, $game_name);
            
            // MODIFICAÇÃO: Checamos se o fetch (busca) funcionou
            if (!mysqli_stmt_fetch($stmt_check_stock)) {
                // Se não encontrou o jogo, fecha o statement e joga um erro
                mysqli_stmt_close($stmt_check_stock);
                throw new Exception("Jogo com ID $idjogo não foi encontrado. A compra não pode ser processada.");
            }
            
            // Se chegou aqui, o fetch funcionou e as variáveis $current_stock e $game_name existem.
            // Agora podemos fechar o statement.
            mysqli_stmt_close($stmt_check_stock);

            // Agora que sabemos que o jogo existe, checamos o estoque.
            if ($current_stock <= 0) {
                throw new Exception("Jogo '" . htmlspecialchars($game_name) . "' ficou sem estoque durante a transação.");
            }
        } else {
            throw new Exception("Erro ao re-verificar estoque.");
        }

        // 2. Insere o registro de compra
        $sql_insert = "INSERT INTO compras (usuario, idjogo, funcao) VALUES (?, ?, ?)";
        if ($stmt_insert = mysqli_prepare($conexao, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "sis", $usuario, $idjogo, $funcao_usuario);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        } else {
            throw new Exception("Erro ao registrar a compra: " . mysqli_error($conexao));
        }

        // 3. Diminui o estoque
        $sql_decrease_stock = "UPDATE jogos SET estoque = estoque - 1 WHERE ID = ?";
        if ($stmt_decrease_stock = mysqli_prepare($conexao, $sql_decrease_stock)) {
            mysqli_stmt_bind_param($stmt_decrease_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_decrease_stock);
            mysqli_stmt_close($stmt_decrease_stock);
        } else {
            throw new Exception("Erro ao diminuir o estoque: " . mysqli_error($conexao));
        }

        // 4. Commit da transação
        mysqli_commit($conexao);

        // Define mensagem de sucesso e redireciona
        $_SESSION['message'] = 'Compra de "' . htmlspecialchars($game_name) . '" realizada com sucesso!';
        $_SESSION['message_type'] = 'success';
        redirect("index.php"); // ou 'meus_jogos.php'

    } catch (Exception $e) {
        // 5. Rollback em caso de erro
        mysqli_rollback($conexao);
        $_SESSION['message'] = 'Erro: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        redirect("index.php");
    }

} else {
    // Se o status for 'cancelado' ou qualquer outra coisa
    $_SESSION['message'] = 'Compra cancelada.';
    $_SESSION['message_type'] = 'error'; // ou 'info'
    redirect("index.php");
}

mysqli_close($conexao);
?>