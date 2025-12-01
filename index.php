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
    <link rel="stylesheet" href="css/estilo2.css">

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
            <a href="registrar_entrada_produto.php">Registrar Entrada de Produto</a>
        <?php endif; ?>
        <a href="Sobre.php" class="btn">Sobre</a>
    </nav>
    <div class="user-info">
        <span>Olá, <?php echo htmlspecialchars($_SESSION["nickname"]); ?>!</span>
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
                        $funcao = htmlspecialchars($_SESSION["funcao"]);
                        $idjogo = $jogo['ID'];

                        // A consulta agora verifica o ID, o jogo E a função do usuário logado
                        $sql_check = "SELECT funcao FROM compras WHERE usuario = ? AND idjogo = ? AND funcao = ?";
                        if ($stmt_check = mysqli_prepare($conexao, $sql_check)) {
                            mysqli_stmt_bind_param($stmt_check, "iis", $usuario, $idjogo, $funcao);
                            mysqli_stmt_execute($stmt_check);
                            
                            // Se encontrar uma linha, significa que o usuário logado com aquela função já comprou
                            if (mysqli_stmt_fetch($stmt_check)) {
                                $ja_comprado = true;
                            }
                            mysqli_stmt_close($stmt_check);
                        }
                    }

                    // Lógica para exibir os botões com base na verificação
                    if ($ja_comprado): ?>
                        <button class="buy-button" disabled style="background-color: #555; cursor: not-allowed;">JÁ POSSUÍDO </button>
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
