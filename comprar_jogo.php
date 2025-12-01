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

    try {
        //Verifica se o jogo já foi comprado pelo usuário
        $sql_check_purchase = "SELECT idcompra FROM compras WHERE usuario = ? AND idjogo = ?";
        if ($stmt_check_purchase = mysqli_prepare($conexao, $sql_check_purchase)) {
            mysqli_stmt_bind_param($stmt_check_purchase, "si", $usuario, $idjogo);
            mysqli_stmt_execute($stmt_check_purchase);
            mysqli_stmt_store_result($stmt_check_purchase);

            if (mysqli_stmt_num_rows($stmt_check_purchase) > 0) {
                mysqli_stmt_close($stmt_check_purchase);
                $_SESSION['message'] = 'Você já possui este jogo!'; // Mensagem de erro amigável
                $_SESSION['message_type'] = 'error';
                redirect("index.php");
            }
            mysqli_stmt_close($stmt_check_purchase);
        } else {
            throw new Exception('Erro ao verificar compra existente.');
        }

        //Verifica o estoque e pega o nome e preço do jogo
        $game_name = '';
        $game_price = 0;
        $current_stock = 0;
        $sql_check_stock = "SELECT estoque, nome, preço FROM jogos WHERE ID = ?";
        if ($stmt_check_stock = mysqli_prepare($conexao, $sql_check_stock)) {
            mysqli_stmt_bind_param($stmt_check_stock, "i", $idjogo);
            mysqli_stmt_execute($stmt_check_stock);
            mysqli_stmt_bind_result($stmt_check_stock, $current_stock, $game_name, $game_price);
            
            if (!mysqli_stmt_fetch($stmt_check_stock)) {
                 throw new Exception("Jogo não encontrado.");
            }
            mysqli_stmt_close($stmt_check_stock);

            if ($current_stock <= 0) {
                throw new Exception("Jogo '" . htmlspecialchars($game_name) . "' sem estoque disponível.");
            }
        } else {
            throw new Exception("Erro ao verificar estoque.");
        }

        //Apenas salva na sessão e redireciona para pagamento.
        $_SESSION['compra_pendente_idjogo'] = $idjogo;
        


        // Redireciona para a página de pagamento passando o preço do jogo
        header("Location: pagina_pagamento.php?amount=" . $game_price);
        exit();

    } catch (Exception $e) {
        $_SESSION['message'] = 'Erro: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        redirect("index.php");
    }

} else {
    $_SESSION['message'] = 'Requisição inválida para compra.';
    $_SESSION['message_type'] = 'error';
    redirect("index.php");
}

mysqli_close($conexao);
?>