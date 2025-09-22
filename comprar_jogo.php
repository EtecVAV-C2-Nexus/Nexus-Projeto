<?php
session_start();

// Inclui os arquivos necessários
require_once 'database.php';
require_once 'funcoes.php';

if (!logado()) {
    redirect("login.php");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["idjogo"])) {
    $idjogo = filter_var($_POST["idjogo"], FILTER_SANITIZE_NUMBER_INT);
    $usuario = $_SESSION["id"];
    $funcao_usuario = $_SESSION["funcao"];

    mysqli_begin_transaction($conexao);

    try {
        // 1. Verifica se o jogo já foi comprado pelo usuário
        $sql_check_purchase = "SELECT idcompra FROM compras WHERE usuario = ? AND idjogo = ?";
        if ($stmt_check_purchase = mysqli_prepare($conexao, $sql_check_purchase)) {
            mysqli_stmt_bind_param($stmt_check_purchase, "si", $usuario, $idjogo);
            mysqli_stmt_execute($stmt_check_purchase);
            mysqli_stmt_store_result($stmt_check_purchase);

            if (mysqli_stmt_num_rows($stmt_check_purchase) > 0) {
                mysqli_rollback($conexao);
                $_SESSION['error_message'] = 'Você já possui este jogo!';
                redirect("index.php");
            }
            mysqli_stmt_close($stmt_check_purchase);
        } else {
            throw new Exception('Erro ao verificar compra existente.');
        }

        // 2. Verifica o estoque e pega o nome e preço do jogo
        $sql_check_stock = "SELECT estoque, nome, preço FROM jogos WHERE ID = ? FOR UPDATE";
        if ($stmt_check_stock = mysqli_prepare($conexao, $sql_check_stock)) {
            mysqli_stmt_bind_param($stmt_check_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_check_stock);
            mysqli_stmt_bind_result($stmt_check_stock, $current_stock, $game_name, $game_price);
            mysqli_stmt_fetch($stmt_check_stock);
            mysqli_stmt_close($stmt_check_stock);

            if ($current_stock <= 0) {
                throw new Exception("Jogo '" . htmlspecialchars($game_name) . "' sem estoque disponível.");
            }
        } else {
            throw new Exception("Erro ao verificar estoque.");
        }

        // 3. Insere o registro de compra
        $sql_insert = "INSERT INTO compras (usuario, idjogo, funcao) VALUES (?, ?, ?)";
        if ($stmt_insert = mysqli_prepare($conexao, $sql_insert)) {
            mysqli_stmt_bind_param($stmt_insert, "sis", $usuario, $idjogo, $funcao_usuario);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        } else {
            throw new Exception("Erro ao registrar a compra: " . mysqli_error($conexao));
        }

        // 4. Diminui o estoque
        $sql_decrease_stock = "UPDATE jogos SET estoque = estoque - 1 WHERE ID = ?";
        if ($stmt_decrease_stock = mysqli_prepare($conexao, $sql_decrease_stock)) {
            mysqli_stmt_bind_param($stmt_decrease_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_decrease_stock);
            mysqli_stmt_close($stmt_decrease_stock);
        } else {
            throw new Exception("Erro ao diminuir o estoque: " . mysqli_error($conexao));
        }

        // Commit da transação
        mysqli_commit($conexao);

        // Redireciona para a página de pagamento passando o preço do jogo
        header("Location: pagina_pagamento.php?amount=" . $game_price);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexao);
        $_SESSION['error_message'] = 'Erro: ' . $e->getMessage();
        redirect("index.php");
    }

} else {
    $_SESSION['error_message'] = 'Requisição inválida para compra.';
    redirect("index.php");
}

mysqli_close($conexao);
?>
