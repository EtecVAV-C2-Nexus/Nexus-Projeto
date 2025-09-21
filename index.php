<?php
session_start();

// Inclui os arquivos necessários
require_once 'database.php';
require_once 'funcoes.php';

// Redireciona para o login se o usuário não estiver logado
if (!logado()) {
    redirect("login.php");
}

// Corrige a verificação de função usando strtolower()
$gerente = isset($_SESSION["funcao"]) && strtolower($_SESSION["funcao"]) === "gerente";
$repositor = isset($_SESSION["funcao"]) && strtolower($_SESSION["funcao"]) === "repositor";

$sql = "SELECT ID, nome, descr, preço, estoque, imagem FROM jogos ORDER BY nome ASC";
$result = mysqli_query($conexao, $sql);

$jogos = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $jogos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus - Catálogo de Jogos</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
        body {
            background: #1a1a2e; 
            color: #e0e0e0;
            font-family: 'Inter', sans-serif;
            display: block; 
            min-height: 70vh;
            overflow-x: hidden;
        }
        /* Mensagens */
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .warning-message {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        /* Header */
        .header {
            background-color: #0f0f1a;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 255, 144, 0.2);
            border-bottom: 2px solid #35ff90;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header h1 {
            color: #35ff90;
            font-size: 2.5em;
            text-shadow: 0 0 10px #35ff90;
        }
        .header nav {
            display: flex;
            gap: 20px;
        }
        .header nav a, .header .user-info a, .header .user-info span {
            color: #fff;
            text-decoration: none;
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 20px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .header nav a:hover, .header .user-info a:hover {
            background-color: #247BA0;
            color: #e0e0e0;
        }
        .header .user-info {
            display: flex;
            align-items: center;
        }
        .header .user-info span {
            background-color: #2c0597;
            padding: 8px 15px;
            border-radius: 20px;
            margin-right: 10px;
        }
        /* Container */
        .container {
            padding: 40px;
            max-width: 1400px;
            margin: 20px auto;
            background-color: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.3);
            border: 1px solid rgba(53, 255, 144, 0.5);
            min-height: 70vh;
        }
        h2 {
            font-size: 2em;
            color: #69B578;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 8px rgba(105, 181, 120, 0.7);
        }
        /* Game grid */
        .game-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(300px, 1fr)); 
            gap: 30px;
            justify-content: center; 
        }
        .game-card {
            background-color: #0f0f1a;
            border-radius: 8px; 
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            cursor: pointer; 
            border: 1px solid transparent; 
        }
        .game-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 255, 144, 0.6);
            border: 1px solid #35ff90; 
        }
        .game-card img {
            width: 100%;
            height: 180px; 
            object-fit: cover; 
            border-bottom: 2px solid #247BA0;
        }
        .game-content {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .game-content h3 {
            color: #69B578;
            font-size: 1.6em;
            margin-bottom: 8px;
            text-shadow: 0 0 5px rgba(105, 181, 120, 0.5);
        }
        .game-content .description {
            font-size: 0.95em;
            color: #b0b0b0;
            line-height: 1.4;
            margin-bottom: 15px;
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .game-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }
        .preço {
            font-size: 1.4em;
            color: #35ff90;
            font-weight: bold;
            text-align: left;
        }
        .buy-button, .play-button, .edit-button {
            padding: 10px 15px;
            border-radius: 25px;
            max-width: 150px ;
            border: none;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: #fff;
        }
        .buy-button { background: linear-gradient(45deg, #247BA0, #2c0597); }
        .play-button { background: linear-gradient(45deg, #69B578, #35ff90); }
        .edit-button { background: linear-gradient(45deg, #f0ad4e, #e08e0b); }
        .buy-button:hover, .play-button:hover, .edit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            opacity: 0.9;
        }
        .no-games {
            text-align: center;
            font-size: 1.2em;
            color: #b0b0b0;
            padding: 50px 0;
        }
        .add-game-button {
            background: linear-gradient(45deg, #35ff90, #247BA0);
            color: #0f0f1a; 
            padding: 12px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 1.1em;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 255, 144, 0.4);
            text-align: center;
        }
        .add-game-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0, 255, 144, 0.6);
            opacity: 0.9;
        }
        /* Responsividade simplificada */
        @media (max-width: 868px) { .game-grid { grid-template-columns: 1fr; } }
        @media (max-width: 620px) { .game-grid { grid-template-columns: 1fr; } }
        @media (max-width: 480px) { .game-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php
    if (isset($_SESSION['message'])) {
        $message_class = ($_SESSION['message_type'] == 'success') ? 'success-message' : 'error-message';
        echo '<div class="' . $message_class . '">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
?>
<div class="header">
    <h1>Nexus</h1>
    <nav>
        <a href="perfil.php">Perfil</a>
        <a href="meus_jogos.php" class="btn">Meus Jogos</a>
        <?php if ($gerente): ?>
            <a href="adicionar_jogo.php">Adicionar Jogo</a>
            <a href="funcionarios.php">Ver Funcionários</a>
        <?php endif; ?>
        <?php if ($repositor): ?>
            <a href="registrar_entrada_produto.php">        Registrar Entrada de Produto</a>
        <?php endif; ?>
        <a href="Sobre.php" class="btn">Sobre</a>
    </nav>
    <div class="user-info">
        <span>Olá, <?php echo htmlspecialchars($_SESSION["nickname"]); ?>! (<?php echo htmlspecialchars($_SESSION["funcao"]); ?>)</span>
        <a href="logout.php">Sair</a>
    </div>
</div>

<div class="container">
    <h2>Catálogo de Jogos</h2>
    <?php if (!empty($jogos)): ?>
        <div class="game-grid">
            <?php foreach ($jogos as $jogo): ?>
                <div class="game-card">
                    <?php if (!empty($jogo['imagem'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($jogo['imagem']); ?>" alt="<?php echo htmlspecialchars($jogo['nome']); ?>">
                    <?php else: ?>
                        <img src="https://placehold.co/400x180/333/fff?text=Sem+Imagem" alt="Sem Imagem">
                    <?php endif; ?>
                    <div class="game-content">
                        <h3><?php echo htmlspecialchars($jogo['nome']); ?></h3>
                        <p class="description"><?php echo !empty($jogo['descr']) ? htmlspecialchars($jogo['descr']) : 'Nenhuma descrição disponível.'; ?></p>
                        <p class="estoque">Estoque: <?php echo htmlspecialchars($jogo['estoque']); ?></p>
                        <div class="game-details">
                            <div class="preço"><?php echo 'R$', number_format($jogo['preço'], 2, ',', '.'); ?></div>
                            <?php
                            $ja_comprado = false;
                            if (logado()) {
                                $usuario = htmlspecialchars($_SESSION["id"]);
                                $idjogo = $jogo['ID'];
                                $sql_check = "SELECT idcompra FROM compras WHERE usuario = ? AND idjogo = ?";
                                if ($stmt_check = mysqli_prepare($conexao, $sql_check)) {
                                    mysqli_stmt_bind_param($stmt_check, "ii", $usuario, $idjogo);
                                    mysqli_stmt_execute($stmt_check);
                                    mysqli_stmt_store_result($stmt_check);
                                    if (mysqli_stmt_num_rows($stmt_check) > 0) {
                                        $ja_comprado = true;
                                    }
                                    mysqli_stmt_close($stmt_check);
                                }
                            }
                            if ($ja_comprado): ?>
                                <button class="buy-button" disabled style="background-color: #555; cursor: not-allowed;">JÁ POSSUÍDO</button>
                                <button class="play-button" onclick="alert('Iniciando o jogo: <?php echo htmlspecialchars($jogo['nome']); ?>');">JOGAR</button>
                            <?php elseif ($jogo['estoque'] > 0): ?>
                                <form action="comprar_jogo.php" method="post" >
                                    <input type="hidden" name="idjogo" value="<?php echo $jogo['ID']; ?>">
                                    <button type="submit" class="buy-button">COMPRAR</button>
                                </form>
                            <?php else: ?>
                                <button class="buy-button" disabled style="background-color: #555; cursor: not-allowed;">SEM ESTOQUE</button>
                            <?php endif; ?>
                            <?php if ($gerente): ?>
                                <a href="editar_jogo.php?id=<?php echo $jogo['ID']; ?>" class="edit-button">EDITAR</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-games">Nenhum jogo disponível no momento. Volte em breve!</p>
    <?php endif; ?>
</div>

<?php mysqli_close($conexao); ?>
</body>
</html>
