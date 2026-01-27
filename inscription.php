<?php
session_start();

$error = '';
$success = '';
$_SESSION['user_role'] = 'client';

/* ================= FONCTION MOT DE PASSE FORT ================= */
function motDePasseFort($password) {
    // Minimum 8 caractères, au moins une majuscule, une minuscule, un chiffre et un caractère spécial
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    return preg_match($pattern,$password);
}

/* ================= FONCTION ACTIVER COMPTE ================= */
function activerCompte($conn, $table, $idField, $passwordField, $id, $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "UPDATE $table SET $passwordField = ? WHERE $idField = ?"
    );
    $stmt->execute([$hash, $id]);
}


/* ================= TRAITEMENT DU FORMULAIRE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    $email = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$email || !$password || !$confirm) {
        $error = "Tous les champs sont obligatoires.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    }
    elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    }
    elseif (!motDePasseFort($password)) {
        $error = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
    }
    else {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=commande_telephone;charset=utf8mb4",
                "root", "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            /* ================= ADMIN ================= */
            $stmt = $conn->prepare("SELECT id_admins, password_hash FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                if ($admin['password_hash']) {
                    $error = "Compte administrateur déjà activé.";
                } else {
                    activerCompte($conn, 'admins', 'id_admins', 'password_hash', $admin['id_admins'], $password);
                    $_SESSION['user_role'] = 'admin';
                    header("Location: admin.php");
                    exit;
                }
            }

            /* ================= MANAGER ================= */
            $stmt = $conn->prepare("SELECT id_manager, password_manager FROM managers WHERE email_manager = ?");
            $stmt->execute([$email]);
            $manager = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($manager) {
                if ($manager['password_manager']) {
                    $error = "Compte manager déjà activé.";
                } else {
                    activerCompte($conn, 'managers', 'id_manager', 'password_manager', $manager['id_manager'], $password);
                    $_SESSION['user_role'] = 'manager';
                    header("Location: manager.php");
                    exit;
                }
            }

            /* ================= CLIENT ================= */
            // Vérifier si le client a déjà une commande
            $stmt = $conn->prepare("SELECT COUNT(*) FROM commandes WHERE email_client = ?");
            $stmt->execute([$email]);

            if ($stmt->fetchColumn() > 0) {

                // Vérifier si le client existe déjà
                $stmt = $conn->prepare("SELECT id_client FROM client WHERE email_client = ?");
                $stmt->execute([$email]);
                $client = $stmt->fetch();

                if (!$client) {
                    // Créer le client
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare(
                        "INSERT INTO client (email_client, password_client) VALUES (?, ?)"
                    );
                    $stmt->execute([$email, $hash]);
                }else{
                    echo"Compte déjà existant";
                }

                $_SESSION['user_role'] = 'client';
                header("Location: client.php");
                exit;
            }

            /* ================= AUCUN CAS ================= */
            $error = "Aucun compte correspondant trouvé. Veuillez contacter l’administrateur.";

        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .register-container {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        label { display: block; margin-top: 15px; margin-bottom: 6px; font-weight: 600; color: #555; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 12px 14px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px; transition: all 0.3s ease;
        }
        input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }
        .showpass { margin: 10px 0 15px; display: flex; align-items: center; gap: 8px; font-size: 14px; color: #555; }
        .center-btns { display: flex; flex-direction: column; gap: 12px; margin-top: 10px; }
        button { padding: 12px; border-radius: 6px; border: none; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s ease, transform 0.1s ease; }
        button[name="register"] { background: #667eea; color: #fff; }
        button[name="register"]:hover { background: #5a67d8; }
        button[type="button"] { background: #999; color: #fff; }
        button[type="button"]:hover { background: #777; }
        button:active { transform: scale(0.97); }
        .error-message { background: #ffe0e0; color: #c00; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .success-message { background: #e0f8e9; color: #087f23; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        small { display: block; color:#555; font-size:13px; margin-top:4px; }
    </style>
</head>
<body>
<div class="register-container">
    <h2>Inscription</h2>
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label for="identity">Email</label>
        <input type="text" name="identity" id="identity" required>

        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>
        <small>Mot de passe : min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.</small>

        <label for="confirm_password">Confirmer le mot de passe</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <div class="showpass">
            <input type="checkbox" id="show_pass" onclick="togglePassword()">
            <label for="show_pass" style="font-weight:normal;">Afficher le mot de passe</label>
        </div>

        <div class="center-btns">
            <button type="submit" name="register">S'inscrire</button>
            <button type="button" onclick="window.location.href='connexion.php'" style="background:#888;">Retour connexion</button>
        </div>
    </form>
</div>

<script>
function togglePassword() {
    var p1 = document.getElementById('password');
    var p2 = document.getElementById('confirm_password');
    if (document.getElementById('show_pass').checked) {
        p1.type = 'text'; p2.type = 'text';
    } else {
        p1.type = 'password'; p2.type = 'password';
    }
}
</script>
</body>
</html>
