<?php
include_once(__DIR__ . '/admin-header.php');
?>
<div class="wrap">
    <h1 class="fftt_plug_title">Les joueurs </h1>
    <p>Insérez le shortcode dans la page ou l'article où vous désirez afficher la liste des joueurs</p>
    <form class="fftt_plug_liste_admin">
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1">Select All</label><input id="cb-select-all-1" type="checkbox"></td>
                    <th>Type</th>
                    <th>Shortcode</th>
                </tr>
            </thead>
            <tbody id="the-list">
                <tr>
                    <th scope="row" class="check-column"></th>
                    <td>Ensemble des joueurs et joueuses</td>
                    <td>[joueurs]</td>
                </tr>
                <tr>
                    <th scope="row" class="check-column"></th>
                    <td>Ensemble des joueuses</td>
                    <td>[joueurs type='F']</td>
                </tr>
                <tr>
                    <th scope="row" class="check-column"></th>
                    <td>Ensemble des joueurs</td>
                    <td>[joueurs type='M']</td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
