<?php




require_once 'database.php';
require_once 'funcoes.php';


if (!logado()) {
    redirect("login.php");
}


$usuario = $_SESSION["id"];

$sql = "SELECT j.ID, j.nome, j.descr, j.preço, j.imagem
        FROM jogos j
        JOIN compras c ON j.ID = c.idjogo
        WHERE c.usuario = ?
        ORDER BY j.nome ASC";

$jogos_comprados = [];
if ($stmt = mysqli_prepare($conexao, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $usuario);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $jogos_comprados[] = $row;
        }
    } else {
        echo "Erro ao executar a consulta: " . mysqli_error($conexao);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Erro ao preparar a consulta: " . mysqli_error($conexao);
}


mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Jogos - Nebulark</title>
    <link rel="stylesheet" href="estilo.css">
    <style>
   
        .wrapper {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: #fff;
            max-width: 900px; 
            margin: 50px auto;
            box-shadow: 0 0 20px rgba(0, 255, 144, 0.5);
        }

        .wrapper h2 {
            font-size: 2.5em;
            margin-bottom: 30px;
            color: #35ff90;
            text-shadow: 0 0 15px #35ff90;
        }

        .game-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .game-card {
            background-color: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: left;
            display: flex;
            flex-direction: column;
            height: 100%; 
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 255, 144, 0.3);
        }

        .game-card img {
            width: 100%;
            height: 180px;
            object-fit: cover; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .game-content {
            padding: 15px;
            flex-grow: 1; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .game-content h3 {
            font-size: 1.5em;
            color: #69B578; 
            margin-bottom: 10px;
            text-shadow: 0 0 5px rgba(105, 181, 120, 0.5);
        }

        .game-content .description {
            font-size: 0.9em;
            color: #ccc;
            line-height: 1.5;
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
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .preço {
            font-size: 1.3em;
            font-weight: bold;
            color: #247BA0;
        }

        .play-button {
            background: linear-gradient(45deg, #69B578, #247BA0); 
            color: #fff;
            padding: 8px 18px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 1em;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            border: none;
            cursor: pointer;
        }

        .play-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
            opacity: 0.9;
        }

        .no-games {
            font-size: 1.2em;
            color: #aaa;
            margin-top: 30px;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #35ff90;
            text-decoration: none;
            font-size: 1.1em;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #69B578;
        }

        
        @media (max-width: 768px) {
            .game-list {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
            .wrapper {
                margin: 20px auto;
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .game-list {
                grid-template-columns: 1fr;
            }
            .game-card img {
                height: 150px;
            }
            .wrapper h2 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <div class="anel">
        <i style="--clr:#69B578;"></i>
        <i style="--clr:#247BA0;"></i>
        <i style="--clr:#2c0597;"></i>
        <div class="wrapper">
            <h2>Meus Jogos</h2>

            <?php if (!empty($jogos_comprados)): ?>
                <div class="game-list">
                    <?php foreach ($jogos_comprados as $jogo): ?>
                        <div class="game-card">
                            <?php if (!empty($jogo['imagem'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($jogo['imagem']); ?>" alt="<?php echo htmlspecialchars($jogo['nome']); ?>">
                            <?php else: ?>
                                <img src="https://placehold.co/400x180/333/fff?text=Sem+Imagem" alt="Sem Imagem">
                            <?php endif; ?>
                            <div class="game-content">
                                <h3><?php echo htmlspecialchars($jogo['nome']); ?></h3>
                                <p class="description"><?php echo !empty($jogo['descr']) ? htmlspecialchars($jogo['descr']) : 'Nenhuma descrição disponível.'; ?></p>
                                <div class="game-details">
                                    <div class="preço">R$ <?php echo number_format($jogo['preço'], 2, ',', '.'); ?></div>
                                    <button class="play-button">JOGAR</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-games">Você ainda não comprou nenhum jogo. Visite o <a href="index.php" class="back-link" style="margin-top: 0;">Catálogo</a> para encontrar um!</p>
            <?php endif; ?>

            <a href="index.php" class="back-link">Voltar para o Catálogo</a>
        </div>
    </div>
</body>
</html>