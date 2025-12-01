<?php
session_start();
require_once 'funcoes.php';

if (!logado()) {
    redirect("login.php");
}

if (isset($conexao)) {
    mysqli_close($conexao);
}

$nickname = htmlspecialchars($_SESSION["nickname"]);
$funcao = isset($_SESSION["funcao"]) ? htmlspecialchars($_SESSION["funcao"]) : "usuário comum";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Nexus</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #000;
            overflow-x: hidden; /* evita scroll horizontal */
        }

        .anel {
            display: flex;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
            width: 100%;
        }

        .wrapper {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 10px;
            text-align: justify;
            color: #fff;
            width: 100%;
            max-width: 800px; /* largura máxima para grandes telas */
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.5);
            backdrop-filter: blur(5px);
            border: 2px solid #35ff90;
            overflow: visible; /* garante que todo o conteúdo seja exibido */
        }

        .wrapper h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #35ff90;
            text-shadow: 0 0 10px #35ff90;
        }

        .wrapper p {
            font-size: 1.1em;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .wrapper ul {
            margin: 10px 0 20px 20px;
        }

        .back-link {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-top: 30px;
            color: #35ff90;
            text-decoration: none;
            font-size: 1.1em;
            transition: all 0.3s ease;
            width: 220px;
            height: 50px;
            border: 1px solid #35ff90;
            border-radius: 5px;
        }

        .back-link:hover {
            color: #fff;
            background-color: #35ff90;
        }

        /* Responsividade para celulares */
        @media (max-width: 500px) {
            .wrapper {
                padding: 20px;
            }

            .wrapper h2 {
                font-size: 1.5em;
            }

            .back-link {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="anel">
        <div class="wrapper">
            <h2>Sobre o projeto:</h2>
            <p>
                Esta atividade teve como tema Programação em PHP integrada com Banco de Dados, com o objetivo de proporcionar aos alunos uma experiência prática no desenvolvimento de aplicações web dinâmicas, utilizando a linguagem PHP aliada à manipulação de dados em banco de dados, fortalecendo conhecimentos em lógica de programação, estrutura de dados e interação com sistemas de informação.
            </p>
            <p>
                O projeto foi orientado pelos professores Ronildo Aparecido e Luciana Batista, que ofereceram suporte técnico e pedagógico ao longo de todo o processo de desenvolvimento.
            </p>
            <p>Participaram da execução da atividade os alunos:</p>
            <ul>
                <li>Eduardo da Cruz Gonçalves</li>
                <li>Douglas de Souza</li>
                <li>Fernando Braz</li>
                <li>Gustavo Muller</li>
            </ul>
            <p>
                A colaboração entre professores e alunos foi essencial para alcançar os objetivos propostos, resultando em um trabalho de qualidade, com aplicação prática dos conteúdos estudados e fortalecimento das competências na área de desenvolvimento web.
            </p>
            <a href="index.php" class="back-link">Voltar para o Catálogo</a>
        </div>
    </div>
</body>
</html>
