<?php
session_start();

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
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

// --- CRUD MANAGER ---

// Ajouter un manager
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manager'])) {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($username) || empty($email)) {
        $error = "Tous les champs sont obligatoires.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    }
    else {
        try {
            $stmt = $conn->prepare(
                "INSERT INTO managers (username, email_manager)
                 VALUES (?, ?)"
            );
            $stmt->execute([$username, $email]);

            $success = "Manager ajouté avec succès. Il devra activer son compte.";

        } catch (PDOException $e) {

            // Gestion propre des doublons
            if ($e->getCode() == 23000) {
                $error = "Ce nom d'utilisateur ou cet email existe déjà.";
            } else {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
// Supprimer un manager
if (isset($_GET['delete_manager'])) {
    $stmt = $conn->prepare("DELETE FROM managers WHERE id_manager=?");
    $stmt->execute([intval($_GET['delete_manager'])]);
}

// --- Commandes ---
// Modifier le statut d'une commande
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_commande'])) {
    $id = intval($_POST['id_commande']);
    $statut = $_POST['statut'];
    $stmt = $conn->prepare("UPDATE commandes SET statut=? WHERE id_commande=?");
    $stmt->execute([$statut, $id]);
}

// Supprimer une commande
if (isset($_GET['delete_commande'])) {
    $stmt = $conn->prepare("DELETE FROM commandes WHERE id_commande=?");
    $stmt->execute([intval($_GET['delete_commande'])]);
}

// Récupérer managers
$managers = $conn->query("SELECT * FROM managers ORDER BY id_manager DESC")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer commandes
$commandes = $conn->query("SELECT * FROM commandes ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion</title>
    <style>
        body {font-family:'Segoe UI', sans-serif; background:#f4f7f8; padding:20px;}
        h2 {text-align:center;color:#333;}
        table {width:100%;border-collapse:collapse;margin-top:20px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.1);}
        th, td {padding:12px 15px;border-bottom:1px solid #eee;text-align:left;}
        th {background-color:#4CAF50;color:white;}
        tr:nth-child(even){background-color:#f9f9f9;}
        select, input[type=email], input[type=text] {padding:5px;border-radius:4px;}
        button {padding:5px 10px;background:#4CAF50;color:white;border:none;border-radius:4px;cursor:pointer;margin:2px;}
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
    <h2>Admin - Gestion des managers</h2>

    <form method="post">
        <input type="text" name="username" required>
        <input type="email" name="email" placeholder="Email manager" required>
        <button type="submit" name="add_manager">Ajouter Manager</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($managers as $m): ?>
            <tr>
                <td><?= $m['id_manager'] ?></td>
                <td><?= htmlspecialchars($m['email_manager']) ?></td>
                <td>
                    <a href="?delete_manager=<?= $m['id_manager'] ?>" onclick="return confirm('Supprimer ce manager ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Admin - Gestion des commandes</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Marque</th>
                <th>Date livraison</th>
                <th>Adresse</th>
                <th>Commentaire</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($commandes as $c): ?>
            <tr>
                <td><?= $c['id_commande'] ?></td>
                <td><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></td>
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
                        <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">
                        <select name="statut">
                            <option value="En cours" <?= $c['statut']=='En cours'?'selected':'' ?>>En cours</option>
                            <option value="Traité" <?= $c['statut']=='Traité'?'selected':'' ?>>Traité</option>
                            <option value="Livré" <?= $c['statut']=='Livré'?'selected':'' ?>>Livré</option>
                        </select>
                        <button type="submit" name="update_commande">Modifier</button>
                    </form>
                    <a href="?delete_commande=<?= $c['id_commande'] ?>" onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
      <ul class="bottom-menu">
        <li><a href="connexion.php">Déconnection</a></li>
    </ul>
</body>
</html>
