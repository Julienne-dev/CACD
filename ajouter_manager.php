<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: connexion.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manager'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($email)) {
        $error = "Tous les champs sont obligatoires.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    }
    else {
        try {
            $conn = new PDO(
                "mysql:host=localhost;dbname=commande_telephone;charset=utf8mb4",
                "root",
                "",
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Vérifier si le manager existe déjà
            $check = $conn->prepare(
               "SELECT COUNT(*) FROM managers WHERE username = ? OR email_manager = ?"
            );
            $check->execute([$username, $email]);

            if ($check->fetchColumn() > 0) {
                $error = "Ce manager existe déjà.";
            } else {

                // Insertion du manager
                $insert = $conn->prepare(
                    "INSERT INTO managers (username, email_manager) VALUES (?, ?)"
                );
                $insert->execute($username, $email);
                $success = "Manager ajouté avec succès. Il devra activer son compte.";
            }

        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
    header("Location: admin.php");
}
?>
