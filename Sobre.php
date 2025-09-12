<?php



require_once 'funcoes.php';


if (!logado()) {
    redirect("login.php");
}



if (isset($conexao)) {
    mysqli_close($conexao);
}


// Obtém o nome de usuário e a função da sessão
$nickname = htmlspecialchars($_SESSION["nickname"]);
$funcao = isset($_SESSION["funcao"]) ? htmlspecialchars($_SESSION["funcao"]) : "usuário comum";
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Nexus</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        .wrapper {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: justify;
            color: #fff;
            max-width: 400px;
            margin: 50px auto;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.5);
            backdrop-filter: blur(5px);
            border: 2px solid #35ff90;
            position: relative;
            z-index: 10;
        }

        .wrapper h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #35ff90;
            text-shadow: 0 0 10px #35ff90;
        }

        .wrapper p {
            font-size: 1.1em;
            margin-bottom: 15px;
            line-height: 1.6;
        }


        .profile-info {
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            padding-top: 20px;
        }

        .profile-info strong {
            color: #69B578;
        }
    </style>
</head>

<body>
    <div class="anel">
        <i style="--clr:#69B578;"></i>
        <i style="--clr:#247BA0;"></i>
        <i style="--clr:#2c0597;"></i>
        <div class="wrapper">
            <h2>Sobre o projeto: </h2>
            <p>
                Esta atividade teve como tema Programação em PHP integrada com Banco de Dados, com o objetivo de proporcionar aos alunos uma experiência prática no desenvolvimento de aplicações web dinâmicas,
                utilizando a linguagem PHP aliada à manipulação de dados em banco de dados, fortalecendo conhecimentos em lógica de programação, estrutura de dados e interação com sistemas de informação.
            </p>
            <p>
                O projeto foi orientado pelos professores Ronildo Aparecido e Luciana Batista, que ofereceram suporte técnico e pedagógico ao longo de todo o processo de desenvolvimento.
            </p>
            <p>
                Participaram da execução da atividade os alunos:
            <ul>

                <li>Eduardo da Cruz Gonçalves</li>
                <li>Douglas de Souza</li>
                <li>Fernando Braz</li>
                <li>Gustavo Muller</li>

            </ul>
            </p>

            <p>
                A colaboração entre professores e alunos foi essencial para alcançar os objetivos propostos, resultando em um trabalho de qualidade,
                com aplicação prática dos conteúdos estudados e fortalecimento das competências na área de desenvolvimento web.
            </p>

        </div>
    </div>
</body>

</html>