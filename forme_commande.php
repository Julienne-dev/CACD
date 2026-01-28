<?php
session_start();
$error = "";
$success = "";

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=localhost;dbname=commande_telephone;charset=utf8mb4", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonction de validation
function validate_input($nom, $prenom, $age, $tel, $email, $adresse) {
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $nom)) return "Nom invalide.";
    if (!preg_match("/^[a-zA-ZÀ-ÿ\s'-]+$/u", $prenom)) return "Prénom invalide.";
    if (!is_numeric($age) || $age < 0) return "Age invalide.";
    if (!preg_match("/^01[0-9]{10}$/", $tel)) return "Numéro de téléphone invalide. Format attendu : 01XXXXXXXXXX";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Email invalide.";
    if (empty(trim($adresse))) return "Adresse obligatoire.";
    return true;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_commande'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $sexe = $_POST['sexe'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $tel = trim($_POST['tel'] ?? '');
    $marque = trim($_POST['marque'] ?? '');
    $date_de_livraison = $_POST['date_de_livraison'] ?? '';
    $adresse = trim($_POST['adresse'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');
    $statut = 'En cours';

    if (!$nom || !$prenom || !$sexe || !$email || !$tel || !$marque || !$date_de_livraison || !$adresse) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $valid = validate_input($nom, $prenom, $age, $tel, $email, $adresse);
        if ($valid !== true) {
            $error = $valid;
        } else {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO commandes 
                    (nom, prenom, age, sexe, email_client, tel, marque, date_de_livraison, adresse, commentaire, statut)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nom, $prenom, $age, $sexe, $email, $tel, $marque, $date_de_livraison, $adresse, $commentaire, $statut]);
                $success = "Commande enregistrée avec succès !";
            } catch (PDOException $e) {
                $error = "Erreur lors de l'enregistrement de la commande.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Passer une commande</title>
    <style>
        /* Global */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f8;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Container du formulaire */
        .form-container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        /* Inputs et select */
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 10px 12px;
            margin: 8px 0 16px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        /* Boutons */
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Messages */
        .error-message {
            color: #d8000c;
            background-color: #ffbaba;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success-message {
            color: #4F8A10;
            background-color: #DFF2BF;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        /* Formulaire suivre commande */
        .follow-btn {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .follow-btn button {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Passer une commande</h2>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
            <!-- Bouton pour suivre la commande -->
            <div class="follow-btn">
                <form method="get" action="connexion.php">
                    <button type="submit">Suivre ma commande</button>
                </form>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>Nom*:</label>
            <input type="text" name="nom" required>

            <label>Prénom*:</label>
            <input type="text" name="prenom" required>

            <label>Age*:</label>
            <input type="number" name="age" min="15" required>

            <label>Sexe*:</label>
            <select name="sexe" required>
                <option value="">--Choisir--</option>
                <option value="Masculin">Masculin</option>
                <option value="Féminin">Féminin</option>
                <option value="Féminin">Autre</option>
            </select>

            <label>Email*:</label>
            <input type="email" name="email" required>

            <label>Téléphone*:</label>
            <input type="tel" name="tel" id="telephone" placeholder="01XXXXXXXX" pattern="^01[0-9]{10}$" required>

            <label>Marque du téléphone*:</label>
            <input type="text" name="marque" required>

            <label>Date de livraison*:</label>
            <input type="date" name="date_de_livraison" required>

            <label>Adresse*:</label>
            <textarea name="adresse" required></textarea>

            <label>Commentaire:</label>
            <textarea name="commentaire"></textarea><br>

            <button type="submit" name="submit_commande">Enregistrer la commande</button>
        </form>
    </div>

    <script>
        document.getElementById("telephone").addEventListener("input", function () {
            const regex = /^01[0-9]{10}$/;
            
            if (!regex.test(this.value)) {
                this.setCustomValidity("Le numéro doit commencer par 01 et contenir 12 chiffres.");
            } else {
                this.setCustomValidity("");
            }
        });
    </script>


</body>
</html>
