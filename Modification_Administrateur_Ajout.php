<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir le fichier XML
$xmlFile = 'BDDmedicare.xml';

// Fonction pour charger le fichier XML
function loadXMLFile($xmlFile) {
    if (file_exists($xmlFile)) {
        return simplexml_load_file($xmlFile);
    }
    return false;
}
// Connexion à la base de données
$host = 'localhost';
$dbname = 'medicare';
$user = 'root'; // à adapter
$pass = '';     // à adapter selon votre configuration

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $specialite = $_POST['specialite'];
    $telephone = $_POST['telephone'];
    $photo = 'Images/' . $_POST['photo'];
    $cv = 'Images/' . $_POST['cv'];

    // Insertion dans la base de données
    $stmt = $pdo->prepare("INSERT INTO personnels_sante (nom, prenom, email, mot_de_passe, specialite, photo, cv, telephone, est_disponible)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe, $specialite, $photo, $cv, $telephone]);

    // Récupération de l'ID inséré
    $lastInsertedId = $pdo->lastInsertId();

    // Mise à jour facultative du fichier XML
    $xml = simplexml_load_file($xmlFile);
    if ($xml !== false) {
        $newMember = $xml->addChild('personnels_sante');
        $newMember->addChild('id', $lastInsertedId);
        $newMember->addChild('nom', $nom);
        $newMember->addChild('prenom', $prenom);
        $newMember->addChild('email', $email);
        $newMember->addChild('mot_de_passe', $mot_de_passe);
        $newMember->addChild('specialite', $specialite);
        $newMember->addChild('photo', $photo);
        $newMember->addChild('cv', $cv);
        $newMember->addChild('telephone', $telephone);
        $xml->asXML($xmlFile);
    }

    // Redirection ou message
    echo "<p class='success'>Membre ajouté avec succès dans la base de données !</p>";
    header('Location: Accueil_Administrateur.html');
    exit();
}

?>



<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicare - Gérer le Personnel de Santé</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="icon" href="Images/Logo_icone.ico" type="image/x-icon">

    <!-- Bibliothèque jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

    <!-- Dernier JavaScript compilé -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            background-color: #0073b1;
            color: #fff;
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="text"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            transition: all 0.3s ease-in-out;
        }

        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="file"]:focus,
        select:focus {
            border-color: #0073b1;
            box-shadow: 0 0 8px rgba(0, 115, 177, 0.2);
        }

        button[type="submit"] {
            background-color: #0073b1;
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out;
        }

        button[type="submit"]:hover {
            background-color: #005f8c;
        }

        .success {
            color: green;
            margin-top: 10px;
            text-align: center;
        }

        .error {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        .button-container {
            text-align: center;
        }
    </style>
</head>

<body>
<header>
    <div class="header-top">
        <img src="Images/Logo_site.png" alt="Logo Medicare" class="logo">
        <h1>Medicare: Services Médicaux</h1>
    </div>
    <nav>
        <ul>
            <li><a href="Accueil_Administrateur.html">Accueil</a></li>
            <li><a href="Tout_Parcourir_Administrateur.html">Tout Parcourir</a>
                <ul class="dropdown-menu">
                    <li><a href="Medecin_Generaliste_Administrateur.php" onclick="showSpecialty('Médecine générale')">Médecins Généralistes</a></li>
                    <li>
                        <a href="Medecins_specialistes_Administrateur.php">Médecins Spécialistes</a>
                        <ul class="dropdown-submenu">
                            <li><a href="Andrologie_Administrateur.php" onclick="showSpecialty('Addictologie')">Addictologie</a></li>
                            <li><a href="Andrologie_Administrateur.php" onclick="showSpecialty('Andrologie')">Andrologie</a></li>
                            <li><a href="Cardiologie_Administrateur.php" onclick="showSpecialty('Cardiologie')">Cardiologie</a></li>
                            <li><a href="Dermatologie_Administrateur.php" onclick="showSpecialty('Dermatologie')">Dermatologie</a></li>
                            <li><a href="Gastro-Hépato-Entérologie_Administrateur.php" onclick="showSpecialty('Gastro-Hépato-Entérologie')">Gastro-Hépato-Entérologie</a></li>
                            <li><a href="Gynécologie_Administrateur.php " onclick="showSpecialty('Gynécologie')">Gynécologie</a></li>
                            <li><a href="I.S.T._Administrateur.php" onclick="showSpecialty('I.S.T.')">I.S.T.</a></li>
                            <li><a href="Ostéopathie_Administrateur.php" onclick="showSpecialty('Ostéopathie')">Ostéopathie</a></li>
                        </ul>
                    </li>
                    <li><a href="Test_Labo_Administrateur.php">Test en Laboratoire</a></li>
                </ul>
            </li>
            <li><a href="Rechercher_Administrateur.php">Recherche</a></li>
            <li><a href="Votre_Compte_Administrateur.html">Votre Compte</a>
                <ul class="dropdown-menu">
                    <li><a href="Votre_Profil_Administrateur.php">Votre profil</a></li>
                    <li><a href="Modification_Administrateur_Ajout.php">Ajouter un personnel de santé</a></li>
                    <li><a href="Modification_Administrateur_Supprimer.php">Supprimer un personnel de santé</a></li>
                    <li><a href="Accueil.php">Déconnexion</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>

<main>
    <section>
        <div class="container">
            <h2>Ajouter un Personnel de Santé</h2>
            <form action="" method="post">
                <div>
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div>
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
                <div>
                    <label for="email">Adresse Mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="mot_de_passe">Mot de Passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>
                </div>
                <div>
                    <label for="specialite">Spécialité</label>
                    <select id="specialite" name="specialite" required>
                        <option value="">-- Sélectionnez une spécialité --</option>
                        <option value="Médecine générale">Médecine générale</option>
                        <option value="Addictologie">Addictologie</option>
                        <option value="Andrologie">Andrologie</option>
                        <option value="Cardiologie">Cardiologie</option>
                        <option value="Dermatologie">Dermatologie</option>
                        <option value="Gastro-Hépato-Entérologie">Gastro-Hépato-Entérologie</option>
                        <option value="Gynécologie">Gynécologie</option>
                        <option value="I.S.T.">I.S.T.</option>
                        <option value="Ostéopathie">Ostéopathie</option>
                    </select>
                </div>

                <div>
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" required>
                </div>
                <div>
                    <label for="photo">Photo (nom du fichier)</label>
                    <input type="file" id="photo" name="photo" required>
                </div>
                <div>
                    <label for="cv">CV (nom du fichier)</label>
                    <input type="file" id="cv" name="cv" required>
                </div>
                <div class="button-container">
                    <button type="submit" name="add_member">Ajouter</button>
                </div>
            </form>
        </div>
    </section>

</main>

<footer>
    <div class="footer-content text-center">
        <p>Contactez-nous: <a href="mailto:email@medicare.com">email@medicare.com</a> | Tel: +33 1 23 45 67 89 | Adresse: 16 rue Sextius Michel, Paris, France</p>
    </div>
</footer>

<script src="scripts.js"></script>

</body>

</html>
