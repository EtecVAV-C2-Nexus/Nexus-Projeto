<?php


require_once 'database.php';
require_once 'funcoes.php';

if (!logado() || !gerente()) {
    redirect("login.php");
}

// Inicializa a variável para armazenar os funcionários
$funcionarios = [];

$sql = "SELECT username, nome_completo, email, funcao FROM funcionarios ORDER BY username ASC";
$result = mysqli_query($conexao, $sql);

if ($result) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $funcionarios[] = $row;
        }
    }
    mysqli_free_result($result); 
} else {
    echo "Erro ao buscar funcionários: " . mysqli_error($conexao);
}

mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Funcionários - Nexus</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body {
            background: #1a1a2e;
            color: #e0e0e0;
            font-family: 'Inter', sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.3);
            backdrop-filter: blur(5px);
            border: 2px solid #35ff90;
        }
        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        header h1 {
            color: #35ff90;
            font-size: 2.8em;
            text-shadow: 0 0 15px #35ff90;
            margin-bottom: 10px;
        }
        .links {
            margin-top: 15px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .links a {
             color: #fff;
            text-decoration: none;
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .links a:hover {
             background-color: #247BA0;
            color: #e0e0e0;
        }
        .func-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .func-table th, .func-table td {
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px;
            text-align: left;
            font-size: 0.95em;
        }
        .func-table th {
            background-color: rgba(53, 255, 144, 0.2);
            color: #35ff90;
            font-weight: bold;
            text-transform: uppercase;
        }
        .func-table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .func-table tr:hover {
            background-color: rgba(53, 255, 144, 0.1);
        }
        .no-employees {
            text-align: center;
            margin-top: 30px;
            font-size: 1.2em;
            color: #bbb;
        }
        @media (max-width: 768px) {
            .func-table, .func-table thead, .func-table tbody, .func-table th, .func-table td, .func-table tr {
                display: block;
            }
            .func-table thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            .func-table tr {
                margin-bottom: 15px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                border-radius: 8px;
            }
            .func-table td {
                border: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            .func-table td:before {
                position: absolute;
                top: 0;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
                color: #35ff90;
            }
            .func-table td:nth-of-type(1):before { content: "Nome de Usuário:"; }
            .func-table td:nth-of-type(2):before { content: "Nome Completo:"; }
            .func-table td:nth-of-type(3):before { content: "Email:"; }
            .func-table td:nth-of-type(4):before { content: "Função:"; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Lista de Funcionários</h1>
            <div class="links">
                <a href="index.php">Voltar para o Catálogo</a>
                <a href="adicionar_func.php">Adicionar Novo Funcionário</a>
                <a href="logout.php">Sair</a>
            </div>
        </header>

        <?php if (!empty($funcionarios)): ?>
            <table class="func-table">
                <thead>
                    <tr>
                        <th>Nome de Usuário</th>
                        <th>Nome Completo</th>
                        <th>Email</th>
                        <th>Função</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funcionarios as $funcionario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($funcionario['username']); ?></td>
                            <td><?php echo htmlspecialchars($funcionario['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($funcionario['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($funcionario['funcao'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-employees">Nenhum funcionário cadastrado no momento.</p>
        <?php endif; ?>
    </div>
</body>
</html>
