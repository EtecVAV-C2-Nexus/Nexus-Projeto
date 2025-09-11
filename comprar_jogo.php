<?php


// Inclui os arquivos necessários para o banco de dados e funções auxiliares
require_once 'database.php';
require_once 'funcoes.php';


if (!logado()) {
    redirect("login.php");
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["idjogo"])) {
    $idjogo = filter_var($_POST["idjogo"], FILTER_SANITIZE_NUMBER_INT);
    $usuario = $_SESSION["id"];

    // Inicia uma transação para garantir a atomicidade da operação (compra e diminuição de estoque)
    mysqli_begin_transaction($conexao);

    try {
        // 1. Verifica se o jogo já foi comprado pelo usuário
        $sql_check_purchase = "SELECT idcompra FROM compras WHERE usuario = ? AND idjogo = ?";
        if ($stmt_check_purchase = mysqli_prepare($conexao, $sql_check_purchase)) {
            // A coluna 'usuario' na tabela 'compras' é VARCHAR(50), então bind como 's' (string)
            mysqli_stmt_bind_param($stmt_check_purchase, "si", $usuario, $idjogo);
            mysqli_stmt_execute($stmt_check_purchase);
            mysqli_stmt_store_result($stmt_check_purchase);

            if (mysqli_stmt_num_rows($stmt_check_purchase) > 0) {
                // Jogo já comprado, reverte e redireciona com uma mensagem
                mysqli_rollback($conexao); // Reverte qualquer operação anterior na transação (se houvesse)
                $_SESSION['error_message'] = 'Você já possui este jogo!';
                redirect("index.php");
            }
            mysqli_stmt_close($stmt_check_purchase);
        } else {
            throw new Exception('Erro ao verificar compra existente.');
        }

        // 2. Verifica o estoque atual do jogo antes de comprar
        // FOR UPDATE bloqueia a linha para evitar condições de corrida em ambientes multiusuário
        $sql_check_stock = "SELECT estoque, nome FROM jogos WHERE ID = ? FOR UPDATE";
        if ($stmt_check_stock = mysqli_prepare($conexao, $sql_check_stock)) {
            mysqli_stmt_bind_param($stmt_check_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_check_stock);
            mysqli_stmt_store_result($stmt_check_stock);

            if (mysqli_stmt_num_rows($stmt_check_stock) == 1) {
                mysqli_stmt_bind_result($stmt_check_stock, $current_stock, $game_name);
                mysqli_stmt_fetch($stmt_check_stock);
                mysqli_stmt_close($stmt_check_stock);

                if ($current_stock <= 0) {
                    throw new Exception("Jogo '" . htmlspecialchars($game_name) . "' sem estoque disponível.");
                }
            } else {
                throw new Exception("Jogo não encontrado no catálogo.");
            }
        } else {
            throw new Exception("Erro ao preparar a verificação de estoque.");
        }

        // 3. Insere o registro de compra
        $sql_insert = "INSERT INTO compras (usuario, idjogo) VALUES (?, ?)";
        if ($stmt_insert = mysqli_prepare($conexao, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "si", $usuario, $idjogo); // 's' para $usuario, 'i' para $idjogo
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        } else {
            throw new Exception("Erro ao registrar a compra: " . mysqli_error($conexao));
        }

        // 4. Diminui o estoque do jogo em 1 unidade
        $sql_decrease_stock = "UPDATE jogos SET estoque = estoque - 1 WHERE ID = ?";
        if ($stmt_decrease_stock = mysqli_prepare($conexao, $sql_decrease_stock)) {
            mysqli_stmt_bind_param($stmt_decrease_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_decrease_stock);
            mysqli_stmt_close($stmt_decrease_stock);
        } else {
            throw new Exception("Erro ao diminuir o estoque do jogo: " . mysqli_error($conexao));
        }

        // Se todas as operações foram bem-sucedidas, comita a transação
        mysqli_commit($conexao);
        $_SESSION['success_message'] = 'Jogo "' . htmlspecialchars($game_name) . '" comprado e estoque atualizado com sucesso!';
        redirect("index.php");

    } catch (Exception $e) {
        // Em caso de qualquer erro, reverte a transação e exibe a mensagem de erro
        mysqli_rollback($conexao);
        $_SESSION['error_message'] = 'Erro: ' . $e->getMessage();
        redirect("index.php");
    }

} else {
    // Redireciona se não houver ID do jogo ou método incorreto
    $_SESSION['error_message'] = 'Requisição inválida para compra.';
    redirect("index.php");
}

// Fecha a conexão com o banco de dados
mysqli_close($conexao);
?>