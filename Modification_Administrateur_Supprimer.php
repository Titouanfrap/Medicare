<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chemin du fichier XML
$xmlFile = 'BDDmedicare.xml';

// Connexion à la base de données
$host = 'localhost';
$dbname = 'medicare';
$user = 'root'; // À adapter
$pass = '';     // À adapter

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction pour charger le XML en DOM
function loadDOM($xmlFile) {
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;

    if (!$dom->load($xmlFile)) {
        return false;
    }
    return $dom;
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_member'])) {
    $nomToDelete = trim($_POST['nom']);
    $prenomToDelete = trim($_POST['prenom']);
    $emailToDelete = trim($_POST['email']);

    $dom = loadDOM($xmlFile);
    if ($dom !== false) {
        $xpath = new DOMXPath($dom);
        $found = false;
        $idToDelete = null;

        foreach ($xpath->query('//personnels_sante') as $node) {
            $nodeNom = $node->getElementsByTagName('nom')->item(0)?->nodeValue;
            $nodePrenom = $node->getElementsByTagName('prenom')->item(0)?->nodeValue;
            $nodeEmail = $node->getElementsByTagName('email')->item(0)?->nodeValue;

            if (
                strtolower(trim($nodeNom)) === strtolower($nomToDelete) &&
                strtolower(trim($nodePrenom)) === strtolower($prenomToDelete) &&
                strtolower(trim($nodeEmail)) === strtolower($emailToDelete)
            ) {
                // Obtenir l'ID du médecin à supprimer dans la base
                $idNode = $node->getElementsByTagName('id')->item(0);
                if ($idNode !== null) {
                    $idToDelete = (int)$idNode->nodeValue;
                }

                $node->parentNode->removeChild($node);
                $found = true;
                break;
            }
        }

        // Si trouvé et supprimé du DOM, sauvegarder le XML
        if ($found && $dom->save($xmlFile)) {
            if ($idToDelete !== null) {
                $stmt = $pdo->prepare("DELETE FROM personnels_sante WHERE id = ?");
                $stmt->execute([$idToDelete]);
            }

            echo "<p style='color: green;'>Médecin supprimé avec succès du fichier XML et de la base de données.</p>";
            header('Location: Accueil_Administrateur.html');
            exit();
        } else {
            echo $found
                ? "<p style='color: red;'>Erreur lors de l'enregistrement du fichier XML.</p>"
                : "<p style='color: red;'>Médecin non trouvé.</p>";
        }
    } else {
        echo "<p style='color: red;'>Erreur de chargement du fichier XML.</p>";
    }
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
        input[type="number"],
        input[type="text"],
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
        input[type="number"]:focus,
        input[type="text"]:focus,
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
            <h2>Supprimer un Personnel de Santé</h2>
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

                <div class="button-container">
                    <button type="submit" name="delete_member">Supprimer</button>
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
