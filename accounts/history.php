<?php
session_start();
require('../db.php');

if(isset($_SESSION['id'])){
    $req = $db->prepare("SELECT * FROM partie WHERE id_joueur1 = ? OR id_joueur2 = ?");
    $req->execute(array($_SESSION['id'], $_SESSION['id']));
    $partie_exist = $req->rowCount();
    if($partie_exist){
        $tab = [];
    } else {
        $info = "Aucune partie trouvée.<br/>Lorsque vous lancerez une partie, elle apparaitra ici.";
    }
} else {
    header("Location: /login?redirect=profil");
}


?>
<html lang="fr">
    <head>
        <title>Historique de <?php echo $_SESSION['username']; ?> - Jeu2Dame</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/history.css">
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
                <button onclick="window.location.href = '/login';">Connexion</button>
            </nav>
            <?php endif ?>
        </header>
        <section>
            <div id="fenetre_centre">
                <h2>Historique des parties</h2>
                <div>
                    <?php if($partie_exist): ?>
                    <table id="tableau">
                        <tr>
                            <th>Identifiant</th>
                            <th>Ennemi</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Gagnant</th>
                        </tr>
                        <?php
                        for($i=0;$i<$partie_exist;$i++){
                            echo "<tr>";
                            $ligne = $req->fetch();
                            $req_username = $db->prepare("SELECT username FROM membres WHERE id = ?");
                            if($ligne['id_joueur1'] == $_SESSION['id']){
                                $req_username->execute(array($ligne['id_joueur2']));
                            } else {
                                $req_username->execute(array($ligne['id_joueur1']));
                            }
                            $username = $req_username->fetch();
                            echo "<td>".$ligne['id']."</td>";
                            echo "<td>".$username['username']."</td>";
                            if($ligne['type'] == 1){
                                echo "<td>Solo</td>";
                            } elseif($ligne['type'] == 2) {
                                echo "<td>Multijoueur</td>";
                            } elseif($ligne['type'] == 3) {
                                echo "<td>Personnalisé</td>";
                            }
                            echo "<td>".$ligne['debut_partie']."</td>";
                            if($_SESSION['id'] == $ligne['id_gagnant']){
                                echo "<td>".$_SESSION['username']."</td>";
                            } else {
                                echo "<td>".$username['username']."</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </table>
                    <?php else: ?>
                        <p><?php echo $info; ?></p>
                    <?php endif ?>
                </div>
            </div>
        </section>
        <footer>
            <button onclick="window.location.href = '/us';">Qui sommes-nous ?</button>
            <h4>Développé par des gens sympathiques</h4>
            <button onclick="window.location.href = '/contact';">Contact</button>
        </footer>
   </body>
</html>