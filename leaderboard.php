<?php
require('db.php');
session_start();

if(isset($_GET['tri'])){
    if($_GET['tri'] == "name"){
        $req = $db->query("SELECT membres.username AS name,COUNT(partie.id_gagnant) AS win FROM membres INNER JOIN partie ON partie.id_gagnant = membres.id GROUP BY name");
        $nb_ligne = $req->rowCount();
    } elseif($_GET['tri'] == "win"){
        $req = $db->query("SELECT membres.username AS name,COUNT(partie.id_gagnant) AS win FROM membres INNER JOIN partie ON partie.id_gagnant = membres.id GROUP BY name ORDER BY win DESC,name ASC");
        $nb_ligne = $req->rowCount();
    }
} elseif(isset($_GET['search'])){
    $recherche = htmlspecialchars(urldecode($_GET['search']));
    $req = $db->prepare("SELECT membres.username AS name,COUNT(partie.id_gagnant) AS win FROM membres INNER JOIN partie ON partie.id_gagnant = membres.id WHERE membres.username LIKE ? GROUP BY name");
    $req->execute(array("%".$recherche."%"));
    $nb_ligne = $req->rowCount();
} else {
    header("Location: leaderboard?tri=name");
}

if(isset($_POST['form_name'])){
    $name = htmlspecialchars($_POST['name']);
    header("Location: leaderboard?search=".urlencode($name));
}

?>
<html lang="fr">
    
    <head>
        <meta charset="utf-8">
        <title>Jouer - Jeu2Dame</title>
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/leaderboard.css">
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
                <h2>Leaderboard</h2>
                <div id="button_tri">
                    <button onclick="window.location.href = '/leaderboard?tri=name';">Trier par nom</button>
                    <button onclick="window.location.href = '/leaderboard?tri=win';">Trier par victoire</button>
                    <form method="POST" action="">
                        <input type="text" name="name" placeholder="Rechercher un nom" />
                        <input type="submit" name="form_name" value="Rechercher" />
                    </form>
                </div>
                <div>
                    <table id="tableau">
                        <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Victoire</th>
                        </tr>
                        <?php
                        for($i=0;$i<$nb_ligne;$i++){
                            echo "<tr>";
                            $ligne = $req->fetch();
                            echo "<td>".$ligne['name']."</td>";
                            echo "<td>".$ligne['win']."</td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
                <div>
                    <p><em>Les utilisateurs n'ayant pas encore gagnés de partie n'apparaissent pas ici.</em></p>
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