<?php
session_start();
$error = "";

// Vérifie si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } else {
        try {
            // Connexion à la base de données
            $conn = new PDO("mysql:host=localhost;dbname=commande_telephone;charset=utf8mb4", "root", "", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Définition des rôles et des infos
            $roles = [
                'admin' => [
                    'table' => 'admins',
                    'id_col' => 'id_admins',
                    'email_col' => 'email',
                    'password_col' => 'password_hash',
                    'redirect' => 'admin.php'
                ],
                'manager' => [
                    'table' => 'managers',
                    'id_col' => 'id_manager',
                    'email_col' => 'email_manager',
                    'password_col' => 'password_manager',
                    'redirect' => 'manager.php'
                ],
                'client' => [
                    'table' => 'client',
                    'id_col' => 'id_client',
                    'email_col' => 'email_client',
                    'password_col' => 'password_client',
                    'redirect' => 'client.php'
                ]
            ];

            $logged_in = false;

            foreach ($roles as $role => $info) {
                $stmt = $conn->prepare("SELECT * FROM {$info['table']} WHERE {$info['email_col']} = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Vérifie le mot de passe
                    if (!empty($user[$info['password_col']]) && password_verify($password, $user[$info['password_col']])) {
                        // Stocke les infos dans la session
                        $_SESSION['user_role'] = $role;
                        $_SESSION['email'] = $user[$info['email_col']];
                        $_SESSION['user_id'] = $user[$info['id_col']];

                        // Redirection vers la page dédiée
                        header("Location: {$info['redirect']}");
                        exit;
                    }
                }
            }

            // Si aucun compte trouvé
            $error = "Identifiants invalides ou compte non activé.";

        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données.";
            // Pour debug : echo "Erreur SQL : " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #ede9ee3f;
            color:white;}
        .login-container { width: 400px; margin: 60px auto; padding: 30px 25px; background: #3b55e7c0; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);}
        h2 { margin-bottom: 20px; text-align: center;}
        label { display: block; margin-top: 12px; margin-bottom: 5px; font-weight: bold;}
        input { width: 100%; padding: 10px; margin-bottom: 8px; border: 1px solid #ccc; border-radius: 4px;}
        .center-btns { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-top: 10px;}
        button { width: 150px; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;}
        button:hover { background: #0056b3;}
        .error-message { color: #c00; margin-bottom: 10px; text-align: center;}
        .showpass { margin-top: 5px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;}
    </style>
</head>
<body>
<div class="login-container">
    <h2>Connexion</h2>
    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <label for="identity">Email</label>
        <input type="text" name="identity" id="identity" required>
        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>
        <div class="center-btns">
            <button type="submit" name="login">Se connecter</button>
            <button type="button" onclick="window.location.href='inscription.php'" style="background:#888;">S'inscrire</button>
        </div>
    </form>
</div>
</body>
</html>