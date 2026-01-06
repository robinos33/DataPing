<?php
// Afficher un message de succès après la sauvegarde des paramètres
if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
    echo '<div class="notice notice-success is-dismissible"><p><strong>Paramètres enregistrés avec succès !</strong></p></div>';
}
?>

<div class="DataPing_right_box" style="display: none">
    <p>N'hésitez pas à m'envoyer vos remarques et suggestions à contact@robin-aldasoro.com<br />
        Ceci est un plugin non-officiel et GRATUIT.
    </p>
</div>

<div class="wrap">
    <h2>Synchronisation des données</h2>
    <div class="dataping-sync-section">
        <?php
        $lastSync = DataPing::getLastSyncTimestamp();
        if ($lastSync) {
            $syncDate = date_i18n(get_option('date_format') . ' à ' . get_option('time_format'), $lastSync);
            $timeDiff = human_time_diff($lastSync, current_time('timestamp'));
            echo '<p><strong>Dernière synchronisation :</strong><br>' . esc_html($syncDate) . '<br><small style="color: #666;">(il y a ' . esc_html($timeDiff) . ')</small></p>';
        } else {
            echo '<p><em>Aucune synchronisation manuelle effectuée</em></p>';
        }
        ?>
        <button id="dataping-sync-button" class="button button-primary">
            <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
            Synchroniser les données
        </button>
        <span id="dataping-sync-loading" style="display: none; margin-left: 10px;">
            <span class="spinner is-active" style="float: none; margin: 0;"></span>
            Synchronisation en cours...
        </span>
        <div id="dataping-sync-message" style="margin-top: 10px;"></div>
    </div>

    <h2>Paramètres de l'API FFTT</h2>
    <?php $this->getForm(); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#dataping-sync-button').on('click', function() {
        var $button = $(this);
        var $loading = $('#dataping-sync-loading');
        var $message = $('#dataping-sync-message');

        $button.prop('disabled', true);
        $loading.show();
        $message.html('');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dataping_sync',
                nonce: '<?php echo wp_create_nonce('dataping_sync_nonce'); ?>'
            },
            success: function(response) {
                $loading.hide();
                $button.prop('disabled', false);

                if (response.success) {
                    $message.html('<div class="notice notice-success is-dismissible"><p><strong>Succès !</strong> ' + response.data.message + '</p></div>');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $message.html('<div class="notice notice-error is-dismissible"><p><strong>Erreur :</strong> ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $loading.hide();
                $button.prop('disabled', false);
                $message.html('<div class="notice notice-error is-dismissible"><p><strong>Erreur :</strong> Erreur de communication avec le serveur</p></div>');
            }
        });
    });

    // Gestion de la notification de succès auto-dismissible
    $('.notice.is-dismissible').each(function() {
        var $notice = $(this);
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
    });
});
</script>


