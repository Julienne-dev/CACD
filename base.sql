CREATE DATABASE IF NOT EXISTS commande_telephone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE commande_telephone;

-- Table des administrateurs
CREATE TABLE admins (
    id_admins INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) DEFAULT NULL
);

-- Table des managers
CREATE TABLE managers (
    id_manager INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email_manager VARCHAR(100) NOT NULL UNIQUE,
    password_manager VARCHAR(255) DEFAULT NULL
);

-- Table des commandes
CREATE TABLE commandes (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    age INT,
    sexe VARCHAR(15) NOT NULL,
    email_client VARCHAR(100) NOT NULL,
    tel INT,
    marque VARCHAR(100) NOT NULL,
    date_de_livraison DATE NOT NULL,
    adresse VARCHAR(255),
    commentaire TEXT(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(50) 
);

-- Table des clients 
CREATE TABLE client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    email_client VARCHAR(100) NOT NULL UNIQUE,
    password_client VARCHAR(100)
);



