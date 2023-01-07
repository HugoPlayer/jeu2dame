<?php
require('./2FA_config.php');

if(isset($_SESSION['id'])) {
    $req = $db->prepare('UPDATE membres SET tfa_code = ? WHERE id = ?');
    $req->execute(array(NULL, $_SESSION['id']));
    header("Location: ../accounts/profil");
} else {
    header("Location: ../accounts/profil");
}

?>