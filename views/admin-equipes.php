<?php
include_once(__DIR__ . '/admin-header.php');
$api = getSessionApi();
$listeEquipesM = $api->getEquipesByClub(ParametresApiFFTT::getInstance()->getNumClub(), 'M');
$listeEquipesF = $api->getEquipesByClub(ParametresApiFFTT::getInstance()->getNumClub(), 'F');
$listeEquipes = array_merge($listeEquipesM, $listeEquipesF);
?>
<div class="wrap">
    <h1 class="fftt_plug_title">Les équipes </h1>
    <p>Insérez le shortcode dans la page ou l'article où vous désirez afficher la poule de chaque équipe</p>
    <form class="fftt_plug_liste_admin">
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                    <th>Equipe</th>
                    <th>Division</th>
                    <th>Shortcode</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <?php
                foreach ($listeEquipes as $equipe) {
                    ?>
                    <tr>
                        <th scope="row" class="check-column"><input type="checkbox" /></th>
                        <td><?php echo $equipe['libequipe']; ?></td>
                        <td><?php echo $equipe['libdivision']; ?></td>
                        <td><?php echo '[equipe iddiv="' . $equipe['iddiv'] . '" idpoule="' . $equipe['idpoule'] . '"]'; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </form>
</div>
