<?php
require('../db.php');
require('function_game.php');
session_start();

if(!isset($_SESSION['id'])) {
    header("Location: /play");
}

if(isset($_GET['action']) AND !empty($_GET['action'])) {
    if($_GET['action'] == "creer") {
        
        // Définission de l'id de partie
        do{
            $longueurKey = 8;
            $key = "";
            for($i=1;$i<$longueurKey;$i++) {
                $key .= mt_rand(0,9);
            }
        } while(partie_exist($key));
        
        // Définission du plateau de jeu
        $plateau = array_fill(0,10,array_fill(0,10,0));
        for($i=0;$i<4;$i++){
            for($j=0;$j<10;$j++){
                if($i%2 == 0 AND $j%2 == 0){
                    $plateau[$i][$j] = 1;
                } elseif($i%2 == 1 AND $j%2 == 1) {
                    $plateau[$i][$j] = 1;
                }
            }
        }
        for($i=6;$i<10;$i++){
            for($j=0;$j<10;$j++){
                if($i%2 == 0 AND $j%2 == 0){
                    $plateau[$i][$j] = 2;
                } elseif($i%2 == 1 AND $j%2 == 1) {
                    $plateau[$i][$j] = 2;
                }
            }
        }
        
        // Insertion des informations de la partie dans la db
        $req_insert = $db->prepare('INSERT INTO partie(id, type, id_joueur1, plateau) VALUES (?,?,?,?)');
        $req_insert->execute(array($key, 3, $_SESSION['id'], convert_str($plateau)));
        
        // Insertion des informations de la partie dans la variable de session du créateur
        $_SESSION['partie']['id'] = $key;
        
        // Redirection vers la partie
        header("Location: /play/game?id=".urlencode($key));
    }
}

if(isset($_POST['formidpartie'])){
    $id_partie = htmlspecialchars($_POST['idpartie']);
    if(partie_exist($id_partie)){
        $req = $db->prepare("SELECT * FROM partie WHERE id = ?");
        $req->execute(array($id_partie));
        $partie = $req->fetch();
        if($partie['id_joueur1'] == $_SESSION['id']){
            // Possibilité de se reconnecter
            header("Location: /play/game?id=".urlencode($id_partie));
        } else {
            if($partie['id_joueur2'] == NULL){
                $tirage_premier_joueur = rand(1,2);
                $req_insert = $db->prepare("UPDATE partie SET id_joueur2 = ?, id_joueur_tour = ? WHERE id = ?");
                if($tirage_premier_joueur == 1){
                    $req_insert->execute(array($_SESSION['id'], $partie['id_joueur1'], $id_partie));
                } elseif($tirage_premier_joueur == 2) {
                    $req_insert->execute(array($_SESSION['id'], $_SESSION['id'], $id_partie));
                }
                $_SESSION['partie']['id'] = $id_partie;
                $_SESSION['partie']['id_ennemi'] = $partie['id_joueur1'];
                header("Location: /play/game?id=".urlencode($id_partie));
            } else {
                $erreur = "La partie est pleine";
                // Faire un mode spectateur ici par exemple
            }
        }
    } else {
        $erreur = "La partie n'existe pas";
    }
}


?>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Partie personnalisée - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/perso.css">
        <link rel="stylesheet" href="../style/stylebutton1.css">
    </head>
    <body>
        <header>
            <a class="header_titre" href="/">Jeu2Dame</a>
            <?php if(isset($_SESSION['id'])): ?>
            <nav class="navbar_connected">
                <?php if(isset($_SESSION['admin'])): ?>
                <button class="header_button" onclick="window.location.href = '/admin';">Panel d'administration</button>
                <?php endif ?>
                <button class="header_button" onclick="window.location.href = '/accounts/profil';">Profil</button>
                <button class="header_button" onclick="window.location.href = '/logout';">Déconnexion</button>
                <h4 id="username_hello"><?php echo('Bonjour ' . $_SESSION['username']); ?></h4>
            </nav>
            <?php else: ?>
            <nav>
                <button onclick="window.location.href = '/register';">Inscription</button>
                <button onclick="window.location.href = '/login?redirect=play';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_centre">
                <div>
                    <button onclick="window.location.href = '/play/perso?action=creer'">Créer une partie personnalisée</button>
                </div>
                <div id="texte_ou">
                    <p>----------------- OU -----------------</p>
                </div>
                <div>
                    <form method="POST" action="">
                        <input type="text" placeholder="identifiant de partie" name="idpartie"/>
                        <input type="submit" value="Rejoindre la partie" name="formidpartie"/>
                    </form>
                    <p><?php if(isset($erreur)){ echo $erreur; } ?></p>
                </div>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathique</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
    </body>
</html>
