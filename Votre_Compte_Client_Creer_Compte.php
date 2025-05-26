<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connexion à la base de données (WAMP)
    $host = 'localhost';
    $db = 'Medicare';
    $user = 'root';
    $pass = ''; // vide sur WAMP

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion MySQL : " . $e->getMessage());
    }

    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $adresse = $_POST['adresse'];
    $ville = $_POST['ville'];
    $code_postal = $_POST['code_postal'];
    $pays = $_POST['pays'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $carte_vitale = $_POST['carte_vitale'];
    $type_carte_paiement = $_POST['type_carte_paiement'];
    $numero_carte = $_POST['numero_carte'];
    $nom_carte = $_POST['nom_carte'];
    $date_expiration_carte = $_POST['date_expiration_carte'];
    $code_securite_carte = $_POST['code_securite_carte'];

    // Insertion dans la base de données
    try {
        $stmt = $pdo->prepare("INSERT INTO Clients (nom, prenom, adresse, ville, code_postal, pays, telephone, email, mot_de_passe, carte_vitale, type_carte_paiement, numero_carte, nom_carte, date_expiration_carte, code_securite_carte)
        VALUES (:nom, :prenom, :adresse, :ville, :code_postal, :pays, :telephone, :email, :mot_de_passe, :carte_vitale, :type_carte_paiement, :numero_carte, :nom_carte, :date_expiration_carte, :code_securite_carte)");

        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':adresse' => $adresse,
            ':ville' => $ville,
            ':code_postal' => $code_postal,
            ':pays' => $pays,
            ':telephone' => $telephone,
            ':email' => $email,
            ':mot_de_passe' => $mot_de_passe,
            ':carte_vitale' => $carte_vitale,
            ':type_carte_paiement' => $type_carte_paiement,
            ':numero_carte' => $numero_carte,
            ':nom_carte' => $nom_carte,
            ':date_expiration_carte' => $date_expiration_carte,
            ':code_securite_carte' => $code_securite_carte
        ]);
    } catch (PDOException $e) {
        echo "Erreur MySQL : " . $e->getMessage();
        exit;
    }

    // Enregistrement dans le fichier XML
    $xmlFile = 'BDDmedicare.xml';
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
    } else {
        $xml = new SimpleXMLElement('<clients></clients>');
    }

    $client = $xml->addChild('client');
    $client->addChild('nom', $nom);
    $client->addChild('prenom', $prenom);
    $client->addChild('adresse', $adresse);
    $client->addChild('ville', $ville);
    $client->addChild('code_postal', $code_postal);
    $client->addChild('pays', $pays);
    $client->addChild('telephone', $telephone);
    $client->addChild('email', $email);
    $client->addChild('mot_de_passe', $mot_de_passe);
    $client->addChild('carte_vitale', $carte_vitale);
    $client->addChild('type_carte_paiement', $type_carte_paiement);
    $client->addChild('numero_carte', $numero_carte);
    $client->addChild('nom_carte', $nom_carte);
    $client->addChild('date_expiration_carte', $date_expiration_carte);
    $client->addChild('code_securite_carte', $code_securite_carte);

    $xml->asXML($xmlFile);

    echo "<p> Client enregistré avec succès dans MySQL (WAMP) et XML.</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicare - Ajouter un Membre</title>
    <link rel="icon" href="Images/Logo_icone.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 100px auto;
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            background-color: #0073b1;
            color: #fff;
            margin-bottom: 15px;
            padding: 10px 20px;
            border-radius: 3px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            background-color: #0073b1;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            cursor: pointer;
            display: block;
            width: 100%;
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
            <li><a href="Accueil.php">Accueil</a></li>
            <li>
                <a href="Tout_Parcourir.html">Tout Parcourir</a>
                <ul class="dropdown-menu">
                    <li><a href="Medecin_Generaliste.php">Médecins Généralistes</a></li>
                    <li>
                        <a href="Medecins_specialistes.php">Médecins Spécialistes</a>
                        <ul class="dropdown-submenu">
                            <li><a href="Addictologie.php">Addictologie</a></li>
                            <li><a href="Andrologie.php">Andrologie</a></li>
                            <li><a href="Cardiologie.php">Cardiologie</a></li>
                            <li><a href="Dermatologie.php">Dermatologie</a></li>
                            <li><a href="Gastro-Hépato-Entérologie.php">Gastro-Hépato-Entérologie</a></li>
                            <li><a href="Gynécologie.php">Gynécologie</a></li>
                            <li><a href="I.S.T..php">I.S.T.</a></li>
                            <li><a href="Ostéopathie.php">Ostéopathie</a></li>
                        </ul>
                    </li>
                    <li><a href="Test_Labo.php">Test en Laboratoire</a></li>
                </ul>
            </li>
            <li><a href="Rechercher.php">Recherche</a></li>
            <li><a href="Rendez_Vous.php">Rendez-vous</a></li>
            <li><a href="Votre_Compte.html">Votre Compte</a>
                <ul class="dropdown-menu">
                    <li><a href="Votre_Compte_Client_Se_Connecter.php">Client</a></li>
                    <li><a href="Votre_Compte_Medecin_Se_Connecter.php">Médecins</a></li>
                    <li><a href="Votre_Compte_Administrateur_Se_Connecter.php">Administrateur</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</header>
<div class="container">
    <h2>Ajouter un Membre</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div>
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div>
            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>
        <div>
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" required>
        </div>
        <div>
            <label for="ville">Ville</label>
            <input type="text" id="ville" name="ville" required>
        </div>
        <div>
            <label for="code_postal">Code Postal</label>
            <input type="number" id="code_postal" name="code_postal" required>
        </div>
        <div>
            <label for="pays">Pays</label>
            <input type="text" id="pays" name="pays" required>
        </div>
        <div>
            <label for="telephone">Téléphone</label>
            <input type="text" id="telephone" name="telephone" required>
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
            <label for="carte_vitale">Carte Vitale</label>
            <input type="number" id="carte_vitale" name="carte_vitale" required>
        </div>
        <div>
            <label for="type_carte_paiement">Type de carte bancaire</label>
            <select id="type_carte_paiement" name="type_carte_paiement" required>
                <option value="">Sélectionnez un type de carte</option>
                <option value="Visa">Visa</option>
                <option value="Mastercard">MasterCard</option>
                <option value="American Express">American Express</option>
                <option value="PayPal">PayPal</option>
            </select>
        </div>
        <div>
            <label for="numero_carte">Numéro CB</label>
            <input type="text" id="numero_carte" name="numero_carte" required>
        </div>
        <div>
            <label for="nom_carte">Nom sur la carte</label>
            <input type="text" id="nom_carte" name="nom_carte" required>
        </div>
        <div>
            <label for="date_expiration_carte">Date d'Expiration CB</label>
            <input type="date" id="date_expiration_carte" name="date_expiration_carte" required>
        </div>
        <div>
            <label for="code_securite_carte">Code Sécurité CB</label>
            <input type="text" id="code_securite_carte" name="code_securite_carte" required>
        </div>
        <button type="submit" name="add_member">Ajouter Membre</button>
    </form>
</div>
</body>
</html>