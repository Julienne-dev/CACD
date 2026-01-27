<?php
session_start();

// Vérifier que l'utilisateur est connecté et qu'il est client
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'client') {
    header("Location: connexion.php");
    exit;
}

$email_client = $_SESSION['email'] ?? '';

try {
    $conn = new PDO("mysql:host=localhost;dbname=commande_telephone;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupération des commandes du client
$stmt = $conn->prepare("SELECT * FROM commandes WHERE email_client = ? ORDER BY created_at DESC");
$stmt->execute([$email_client]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi de mes commandes</title>
    <style>
        body {font-family:'Segoe UI', sans-serif; background:#f4f7f8; padding:20px;}
        h2 {text-align:center;color:#333;}
        table {width:100%;border-collapse:collapse;margin-top:20px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.1);}
        th, td {padding:12px 15px;border-bottom:1px solid #eee;text-align:left;}
        th {background-color:#4CAF50;color:white;}
        tr:nth-child(even) {background-color:#f9f9f9;}
        .status-en-cours {color:#d9822b;font-weight:bold;}
        .status-traite {color:#0c63e4;font-weight:bold;}
        .status-livre {color:#198754;font-weight:bold;}
        .no-commandes {text-align:center;margin-top:50px;font-size:18px;color:#555;}
        .bottom-menu {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #4CAF50;
    display: flex;
    justify-content: center;
    gap: 40px;
    padding: 15px 0;
    margin: 0;
    list-style: none;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.15);
}

.bottom-menu li a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 16px;
}

.bottom-menu li a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <h2>Suivi de mes commandes</h2>

    <?php if (empty($commandes)): ?>
        <p class="no-commandes">Vous n'avez encore passé aucune commande.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Age</th>
                    <th>Sexe</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Marque</th>
                    <th>Date de livraison</th>
                    <th>Adresse</th>
                    <th>Commentaire</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $commande): ?>
                    <tr>
                        <td><?= htmlspecialchars($commande['nom']) ?></td>
                        <td><?= htmlspecialchars($commande['prenom']) ?></td>
                        <td><?= htmlspecialchars($commande['age']) ?></td>
                        <td><?= htmlspecialchars($commande['sexe']) ?></td>
                        <td><?= htmlspecialchars($commande['email_client']) ?></td>
                        <td><?= htmlspecialchars($commande['tel']) ?></td>
                        <td><?= htmlspecialchars($commande['marque']) ?></td>
                        <td><?= htmlspecialchars($commande['date_de_livraison']) ?></td>
                        <td><?= htmlspecialchars($commande['adresse']) ?></td>
                        <td><?= htmlspecialchars($commande['commentaire']) ?></td>
                        <td class="<?php
                            if ($commande['statut'] === 'En cours') echo 'status-en-cours';
                            elseif ($commande['statut'] === 'Traité') echo 'status-traite';
                            elseif ($commande['statut'] === 'Livré') echo 'status-livre';
                        ?>">
                            <?= htmlspecialchars($commande['statut']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <ul class="bottom-menu">
        <li><a href="forme_commande.php">Ajouter+</a></li>
        <li><a href="connexion.php">Déconnection</a></li>
    </ul>
</body>
</html>
