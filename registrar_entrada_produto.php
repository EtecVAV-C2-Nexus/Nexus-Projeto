<?php
session_start();

require_once 'database.php';
require_once 'funcoes.php';

if (!gerente()){
if (!logado() || !repositor()) {
    redirect("login.php");
}}

$idjogo = $quantidade_entrada = "";
$idjogo_err = $quantidade_entrada_err = "";
$success_message = "";

$jogos_disponiveis = [];
$sql_jogos = "SELECT ID, nome FROM jogos ORDER BY nome ASC";
$result_jogos = mysqli_query($conexao, $sql_jogos);
if ($result_jogos && mysqli_num_rows($result_jogos) > 0) {
    while ($row = mysqli_fetch_assoc($result_jogos)) {
        $jogos_disponiveis[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Valida o ID do jogo selecionado
    if (empty(trim($_POST["idjogo"]))) {
        $idjogo_err = "Por favor, selecione um jogo (produto).";
    } elseif (!is_numeric(trim($_POST["idjogo"]))) {
        $idjogo_err = "Seleção inválida de jogo.";
    } else {
        $idjogo = trim($_POST["idjogo"]);
    }

    // Valida a quantidade de entrada
    if (empty(trim($_POST["quantidade_entrada"]))) {
        $quantidade_entrada_err = "Por favor, insira a quantidade de entrada.";
    } elseif (!filter_var(trim($_POST["quantidade_entrada"]), FILTER_VALIDATE_INT) || trim($_POST["quantidade_entrada"]) <= 0) {
        $quantidade_entrada_err = "Por favor, insira uma quantidade inteira positiva.";
    } else {
        $quantidade_entrada = trim($_POST["quantidade_entrada"]);
    }

    // Se não houver erros de validação
    if (empty($idjogo_err) && empty($quantidade_entrada_err)) {
        // Atualiza a quantidade em estoque no banco de dados, adicionando a nova entrada
        $sql_update_estoque = "UPDATE jogos SET estoque = estoque + ? WHERE ID = ?";
        if ($stmt_update = mysqli_prepare($conexao, $sql_update_estoque)) {
            mysqli_stmt_bind_param($stmt_update, "ii", $quantidade_entrada, $idjogo);

            if (mysqli_stmt_execute($stmt_update)) {
                $success_message = "Entrada de produto registrada e estoque atualizado com sucesso!";
                $idjogo = $quantidade_entrada = "";
            } else {
                echo "Ops! Algo deu errado ao registrar a entrada do produto. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt_update);
        }
    }
}
mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Entrada de Produto - Nexus</title>
    <link rel="stylesheet" href="css/estilo.css">
    <style>
        .wrapper {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: #fff;
            max-width: 500px;
            margin: 50px auto;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.5);
            backdrop-filter: blur(5px);
            border: 2px solid #35ff90;
            position: relative;
            z-index: 1000;
        }
        .wrapper h2 {
            font-size: 2.2em;
            margin-bottom: 20px;
            color: #35ff90;
            text-shadow: 0 0 10px #35ff90;
        }
        .wrapper .inputBx {
            position: relative;
            width: 100%;
            margin-bottom: 25px;
        }
        .wrapper .inputBx input,
        .wrapper .inputBx select {
            width: calc(100% - 20px); /* Ajusta para padding */
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            outline: none;
            color: #fff;
            font-size: 1em;
            transition: 0.3s ease;
        }
        .wrapper .inputBx input:focus,
        .wrapper .inputBx select:focus {
            border-color: #35ff90;
            box-shadow: 0 0 8px rgba(53, 255, 144, 0.5);
        }
        .wrapper .inputBx input[type="submit"] {
            background: linear-gradient(45deg, #247BA0, #2c0597);
            border: none;
            cursor: pointer;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .wrapper .inputBx input[type="submit"]:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .wrapper .error-message {
            color: #FF6347;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
            text-align: left;
        }
        .wrapper .success-message {
            color: #69B578;
            font-size: 1em;
            margin-bottom: 20px;
            display: block;
            text-align: center;
        }
        .wrapper .links a {
            color: #35ff90;
            text-decoration: none;
            font-size: 0.9em;
            margin: 0 10px;
            transition: 0.3s ease;
        }
        .wrapper .links a:hover {
            text-decoration: underline;
        }
        .wrapper label {
            color: #fff;
            font-size: 0.9em;
            margin-bottom: 5px;
            display: block;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="anel">
        <i style="--clr:#69B578;"></i>
        <i style="--clr:#247BA0;"></i>
        <i style="--clr:#2c0597;"></i>
        <div class="login">
            <div class="wrapper">
                <h2>Registrar Entrada de Produto</h2>
                <?php
                if (!empty($success_message)) {
                    echo '<div class="success-message">' . $success_message . '</div>';
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="inputBx">
                        <label for="idjogo">Selecione o Jogo (Produto):</label>
                        <select name="idjogo" id="idjogo">
                            <option value="" style="color: #000">-- Selecione um jogo --</option>
                            <?php foreach ($jogos_disponiveis as $jogo_item): ?>
                                <option value="<?php echo htmlspecialchars($jogo_item['ID']); ?>" <?php echo ($idjogo == $jogo_item['ID']) ? 'selected' : ''; ?> style="color: #000" >
                                    <?php echo htmlspecialchars($jogo_item['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="error-message"><?php echo $idjogo_err; ?></span>
                    </div>

                    <div class="inputBx">
                        <label for="quantidade_entrada">Quantidade de Entrada:</label>
                        <input type="number" placeholder="Ex: 10" name="quantidade_entrada" id="quantidade_entrada" value="<?php echo htmlspecialchars($quantidade_entrada); ?>" min="1">
                        <span class="error-message"><?php echo $quantidade_entrada_err; ?></span>
                    </div>

                    <div class="inputBx">
                        <input type="submit" value="Registrar Entrada">
                    </div>
                    <div class="links">
                        <a href="index.php">Voltar para o Catálogo</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
