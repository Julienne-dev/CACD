<?php
session_start();

// Vérifier que l'utilisateur est connecté et qu'il est manager
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'manager') {
    header("Location: connexion.php");
    exit;
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=commande_telephone;charset=utf8mb4", "appuser", "july", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Modification du statut de commande si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $commande_id = intval($_POST['commande_id']);
    $new_status = $_POST['statut'];
    $stmt = $conn->prepare("UPDATE commandes SET statut = ? WHERE id_commande = ?");
    $stmt->execute([$new_status, $commande_id]);
}

// Récupération de toutes les commandes
$stmt = $conn->query("SELECT * FROM commandes ORDER BY created_at DESC");
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Manager - Suivi des commandes</title>
    <style>
        body {font-family:'Segoe UI', sans-serif; background:#f4f7f8; padding:20px;}
        h2 {text-align:center;color:#333;}
        table {width:100%;border-collapse:collapse;margin-top:20px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.1);}
        th, td {padding:12px 15px;border-bottom:1px solid #eee;text-align:left;}
        th {background-color:#4CAF50;color:white;}
        tr:nth-child(even) {background-color:#f9f9f9;}
        select {padding:5px;border-radius:4px;}
        button {padding:5px 10px;background:#4CAF50;color:white;border:none;border-radius:4px;cursor:pointer;}
        button:hover {background:#45a049;}
        .status-en-cours {color:#d9822b;font-weight:bold;}
        .status-traite {color:#0c63e4;font-weight:bold;}
        .status-livre {color:#198754;font-weight:bold;}
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
    <h2>Manager - Suivi des commandes</h2>

    <?php if (empty($commandes)): ?>
        <p style="text-align:center;">Aucune commande enregistrée.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Marque</th>
                    <th>Date de livraison</th>
                    <th>Adresse</th>
                    <th>Commentaire</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $c): ?>
                    <tr>
                        <td><?= $c['id_commande'] ?></td>
                        <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
                        <td><?= htmlspecialchars($c['email_client']) ?></td>
                        <td><?= htmlspecialchars($c['tel']) ?></td>
                        <td><?= htmlspecialchars($c['marque']) ?></td>
                        <td><?= htmlspecialchars($c['date_de_livraison']) ?></td>
                        <td><?= htmlspecialchars($c['adresse']) ?></td>
                        <td><?= htmlspecialchars($c['commentaire']) ?></td>
                        <td class="<?php
                            if ($c['statut'] === 'En cours') echo 'status-en-cours';
                            elseif ($c['statut'] === 'Traité') echo 'status-traite';
                            elseif ($c['statut'] === 'Livré') echo 'status-livre';
                        ?>"><?= htmlspecialchars($c['statut']) ?></td>
                        <td>
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="commande_id" value="<?= $c['id_commande'] ?>">
                                <select name="statut">
                                    <option value="En cours" <?= $c['statut']=='En cours'?'selected':'' ?>>En cours</option>
                                    <option value="Traité" <?= $c['statut']=='Traité'?'selected':'' ?>>Traité</option>
                                    <option value="Livré" <?= $c['statut']=='Livré'?'selected':'' ?>>Livré</option>
                                </select>
                                <button type="submit" name="update_status">Modifier</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
      <ul class="bottom-menu">
        <li><a href="connexion.php">Déconnection</a></li>
    </ul>
</body>
</html>
