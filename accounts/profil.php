<?php
session_start();
require('../db.php');
 
if(isset($_GET['id']) AND $_GET['id'] > 0) {
    $getid = intval($_GET['id']);
    $req = $db->prepare('SELECT * FROM membres WHERE id = ?');
    $req->execute(array($getid));
    $userinfo = $req->fetch();
    
    $req = $db->prepare('SELECT * FROM partie WHERE id_joueur1 = ? OR id_joueur2 = ?');
    $req->execute(array($getid, $getid));
    $nb_partie_jouee = $req->rowCount();

    $req = $db->prepare('SELECT * FROM partie WHERE id_gagnant = ?');
    $req->execute(array($getid));
    $nb_partie_gagnee = $req->rowCount();
    
    if(!isset($_SESSION['id']) || $userinfo['id'] != $_SESSION['id']) {
        header("Location: ../login");
    }
} else {
    header("Location: ../login");
}

if(isset($_POST['form_ami'])){
    $username_ami = htmlspecialchars($_POST['nom_ami']);
    $req = $db->prepare('SELECT id FROM membres WHERE username= ?');
    $req->execute(array($username_ami));
    $user_exist = $req->rowCount();
    if($user_exist) {
        $id_username_ami = $req->fetch();
        $id_username_ami = $id_username_ami['id'];
        $req = $db->prepare('SELECT * FROM liste_ami WHERE (id_joueur1 = ? AND id_joueur2 = ?) OR (id_joueur1 = ? AND id_joueur2 = ?)');
        $req->execute(array($_SESSION['id'], $id_username_ami, $id_username_ami, $_SESSION['id']));
        $ami_exist = $req->rowCount();
        if($ami_exist == 0) {
            $req = $db->prepare('SELECT * FROM requete_attente WHERE type = ? AND ((id_joueur1 = ? AND id_joueur2 = ?) OR (id_joueur1 = ? AND id_joueur2 = ?))');
            $req->execute(array(1, $id_username_ami, $_SESSION['id'], $_SESSION['id'], $id_username_ami));
            $demande_ami_exist = $req->rowCount();
            if($demande_ami_exist == 0) {
                $req = $db->prepare("INSERT INTO requete_attente (type, id_joueur1, id_joueur2, valider_user1) VALUES (?,?,?,?)");
                $req->execute(array(1, $_SESSION['id'], $id_username_ami, 1));
                $erreur = "La demande d'ami a bien été envoyée.";
            } else {
                $compteur = 0;
                for($i=0;$i<$demande_ami_exist;$i++) {
                    $ligne = $req->fetch();
                    if($ligne['id_joueur1'] == $id_username_ami AND $ligne['valider_user1'] == 1) {
                        $req_update = $db->prepare("UPDATE requete_attente SET valider_user2 = ? WHERE type = ? AND id_joueur1 = ? AND id_joueur2 = ?");
                        $req_update->execute(array(1, 1, $id_username_ami, $_SESSION['id']));
                        $req_ajout_ami = $db->prepare("INSERT INTO liste_ami (id_joueur1,id_joueur2) VALUES (?,?)");
                        $req_ajout_ami->execute(array($id_username_ami, $_SESSION['id']));
                        $erreur = "Vous avez désormais un nouveau contact.";
                        break;
                    }
                    $compteur += 1;
                }
                if($compteur == $demande_ami_exist){
                    $erreur = "Une demande d'ami a déjà été envoyée.";
                }
            }
        } else {
            $erreur = "Cet utilisateur est déjà votre ami.";
        }
    } else {
        $erreur = "Cet utilisateur n'existe pas.";
    }
}

?>
<html lang="fr">
    <head>
        <title>Profil de <?php echo $_SESSION['username']; ?> - Jeu2Dame</title>
        <meta charset="utf-8">
        <link rel="stylesheet" href="../style/border.css">
        <link rel="stylesheet" href="../style/profil.css">
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
                <button disabled class="header_button" onclick="window.location.href = '/accounts/profil';">Profil</button>
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
            <div id="fenetre_info_profil">
                <div id="photo_profil">
                    <?php if(!empty($userinfo['avatar'])): ?>
                    <img src="avatar/<?php echo $userinfo['avatar']; ?>" alt="Photo de profil" />
                    <?php else: ?>
                    <img src="avatar/avatar_defaut.png" alt="Photo de profil" />
                    <?php endif ?>
                    <h2>Profil de <?php echo $userinfo['username']; ?></h2>
                </div>
                <p>Nom d'utilisateur = <?php echo $userinfo['username']; ?></p>
                <p>Mail = <?php echo $userinfo['mail']; ?></p>
                <div id="stats_partie">
                    <div>
                        <p>Partie gagnée</p>
                        <?php echo "<p>".$nb_partie_gagnee."</p>"; ?>
                    </div>
                    <div>
                        <p>Partie jouée</p>
                        <?php echo "<p>".$nb_partie_jouee."</p>"; ?>
                    </div>
                    <div>
                        <button onclick="window.location.href='/accounts/history'">Accéder à l'historique de partie</button>
                    </div>
                </div>
                <a href="profil_edit">Editer mon profil</a>
                <a href="../2FA/2FA_activation">Activer la double authentification</a>
            </div>
            <div id="fenetre_liste_ami">
                <h3>Liste d'ami</h3>
                <form method="POST" action="">
                    <input type="text" name="nom_ami" placeholder="Ajouter un utilisateur"/>
                    <input type="submit" name="form_ami" value="Ajouter"/>
                </form>
                <p><?php if(isset($erreur)) { echo $erreur; } ?></p>
                <div id="liste_ami">
                    <?php
                    $tab = array();
                    $tab_id = array();
                    $load_ami = $db->prepare("SELECT * FROM liste_ami WHERE id_joueur1 = ? OR id_joueur2 = ?");
                    $load_ami->execute(array($_SESSION['id'], $_SESSION['id']));
                    // select une fois tous les id1 et une fois les id2 puis mettre dans un tab et trier par alpha
                    $nb_ligne = $load_ami->rowCount();
                    for($i=0;$i<$nb_ligne;$i++) {
                        $ligne_traitement = $load_ami->fetch();
                        array_push($tab_id,$ligne_traitement['id']);
                        if($ligne_traitement['id_joueur1'] == $_SESSION['id']) {
                            array_push($tab, $ligne_traitement['id_joueur2']);
                        } else {
                            array_push($tab, $ligne_traitement['id_joueur1']);
                        }
                    }
                    for($j=0;$j<$i;$j++) {
                        //remplacer tous les éléments du tableau par les usernames
                        $username_ligne = $db->prepare("SELECT username FROM membres WHERE id = ?");
                        $username_ligne->execute(array($tab[$j]));
                        $res_ligne = $username_ligne->fetch();
                        $tab[$j] = $res_ligne['username'];
                    }
                    sort($tab);
                    for($i=0;$i<$j;$i++) {
                        echo "<div class=\"ligne_ami\"><p>".$tab[$i]."</p>";
                        echo "<div class=\"croix_delete\" onclick=\"window.location.href = 'delete_friend.php?id=".$tab_id[$i]."';\"></div></div>";
                    }
                    ?>
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