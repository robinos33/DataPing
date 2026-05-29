<?php
/*
  Plugin Name: DataPing
  Plugin URI: http://robin-aldasoro.com/docs/wordpress-plugins/DataPing.zip
  Description: Ce plugin affiche les données accessibles via l'API de la FFTT
  Version: 0.3.0
  Author: Robin Aldasoro
  Author URI: robin-aldasoro.com
  License: GPLv2
 */

require_once('Utils.php');

class DataPing
{

    /**
     * Types possibles  de listes de joueurs  à insérer dans les shortcodes
     * @var array
     */
    private $typeListeJoueurs = array(
        'M', 'F', 'MF'
    );

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'dataping_style_scripts'));
        add_shortcode('equipe', array($this, 'equipes_front'));
        add_shortcode('joueurs', array($this, 'joueurs_front'));

        // Hooks pour exposer les données en cache aux autres plugins
        add_filter('dataping_get_joueurs', array($this, 'get_joueurs_data'), 10, 1);
        add_filter('dataping_get_equipes', array($this, 'get_equipes_data'), 10, 1);
        add_filter('dataping_get_classement_poule', array($this, 'get_classement_poule_data'), 10, 2);
        add_filter('dataping_get_rencontres_poule', array($this, 'get_rencontres_poule_data'), 10, 2);

        // AJAX handlers
        add_action('wp_ajax_dataping_sync', array($this, 'handle_ajax_sync'));
        add_action('wp_ajax_dataping_generate_pages', array($this, 'handle_ajax_generate_pages'));

        // Widget dashboard
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }

    public function add_admin_menu()
    {
        add_menu_page('DataPing', 'DataPing', 'manage_options', 'parametres_DataPing', array($this, 'admin_module'));
        add_submenu_page('parametres_DataPing', 'Equipes', 'Equipes', 'manage_options', 'equipes_DataPing', array($this, 'equipes_admin'));
        add_submenu_page('parametres_DataPing', 'Joueurs', 'Joueurs', 'manage_options', 'joueurs_DataPing', array($this, 'joueurs_admin'));
    }

    public function admin_module()
    {
        $this->_getLayout('admin');
    }

    public static function getPluginData()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $datas = get_plugin_data(__FILE__);
        return $datas;
    }

    public function dataping_style_scripts()
    {
        // Styles
        wp_register_style('admin-css', plugins_url('/assets/DataPing.css', __FILE__), array(), '1.0');
        wp_enqueue_style('admin-css');
        // Javascript
        wp_register_script('dataping-js', plugins_url('/assets/DataPing.js', __FILE__), array('jquery'), '1.0', true);
        wp_register_script('table-sorter', plugins_url('/assets/tablesorter/jquery.tablesorter.min.js', __FILE__), array('jquery'), '1.0', true);
        wp_register_script('table-sorter-pager', plugins_url('/assets/tablesorter/jquery.tablesorter.pager.js', __FILE__), array('jquery', 'table-sorter'), '1.0', true);
        wp_enqueue_script('dataping-js');
        wp_enqueue_script('table-sorter');
        wp_enqueue_script('table-sorter-pager');
    }

    public function register_settings()
    {
        register_setting('DataPing_settings', ConstantesDataPing::DATAPING_ID_APPLICATION);
        register_setting('DataPing_settings', ConstantesDataPing::DATAPING_MOT_DE_PASSE);
        register_setting('DataPing_settings', ConstantesDataPing::DATAPING_NUM_CLUB);

        add_settings_section('DataPing_section', 'Paramètres du plugin', array($this, 'section_html'), 'DataPing_settings');
        add_settings_field(ConstantesDataPing::DATAPING_ID_APPLICATION, 'Id Application', array($this, 'id_application_html'), 'DataPing_settings', 'DataPing_section');
        add_settings_field(ConstantesDataPing::DATAPING_MOT_DE_PASSE, 'Mot de passe Application', array($this, 'mot_de_passe_html'), 'DataPing_settings', 'DataPing_section');
        add_settings_field(ConstantesDataPing::DATAPING_NUM_CLUB, 'Numéro de club', array($this, 'equipe_num_html'), 'DataPing_settings', 'DataPing_section');
    }

    public function section_html()
    {
        echo '<p>Entrez les paramètres de l\'application fournis par la FFTT</p>';
        echo '<p>Si vous n\'en avez pas, vous devrez faire la demande suivante en suivant la procédure décrite ici : <a target="_blank" href="http://www.fftt.com/actus/ouverture_interfaces_smartping_2015_06_30-1362.html">http://www.fftt.com/actus/ouverture_interfaces_smartping_2015_06_30-1362.html</a></p>';
    }

    public function id_application_html()
    {
        ?>
        <input type="text" name="DataPing_id_application"
               value="<?php echo esc_attr(get_option(ConstantesDataPing::DATAPING_ID_APPLICATION)); ?>"/>
        <?php
    }

    public function mot_de_passe_html()
    {
        ?>
        <input type="text" name="DataPing_mot_de_passe"
               value="<?php echo esc_attr(get_option(ConstantesDataPing::DATAPING_MOT_DE_PASSE)); ?>"/>
        <?php
    }

    public function equipe_num_html()
    {
        ?>
        <input type="text" name="DataPing_num_club"
               value="<?php echo esc_attr(get_option(ConstantesDataPing::DATAPING_NUM_CLUB)); ?>"/>
        <?php
    }

    public function getForm()
    {
        echo '<form action="options.php" method="POST" name="DataPing_settings" class="DataPing_settings_form">';
        do_settings_sections('DataPing_settings');
        settings_fields('DataPing_settings');
        echo '<div>' . submit_button('Valider la saisie') . '</div>';
        echo '</form>';
    }

    public function equipes_admin()
    {
        $this->_getLayout('equipes');
    }

    public function joueurs_admin()
    {
        $this->_getLayout('joueurs');
    }

    private function _getLayout($view){
        include_once(__DIR__ . '/views/admin/layout.php');
    }

    public function equipes_front($atts, $content)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api)) {
            return __('Problème lors de la récupération des résultats', 'dataping');
        }

        // Normalise et valide les attributs du shortcode
        $atts = shortcode_atts(array('iddiv' => '', 'idpoule' => ''), (array) $atts, 'equipe');
        $atts['iddiv'] = (string) $atts['iddiv'];
        $atts['idpoule'] = (string) $atts['idpoule'];
        if ($atts['iddiv'] === '' || $atts['idpoule'] === '') {
            return __('Poule ou division incorrecte', 'dataping');
        }

        $listeEquipesM = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'M');
        $listeEquipesF = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'F');
        $listeEquipes = array_merge((array) $listeEquipesM, (array) $listeEquipesF);

        ob_start();
        require __DIR__ . '/views/front/equipes.php';
        return ob_get_clean();
    }

    /**
     * Méthode qui gère les liste de joueurs coté front
     * @param type $atts type: M | F | MF
     * @param type $content
     * @return string
     */
    public function joueurs_front($atts, $content)
    {
        $atts = shortcode_atts(array('type' => 'MF'), (array) $atts, 'joueurs');
        if (in_array($atts['type'], $this->getTypeListeJoueurs(), true)) {
            $listeJoueurs = array();
            $joueurs = new Joueurs();
            $api = AccesFFTTApi::getInstance();
            $numClub = ParametresDataPing::getNumClub();
            $updatedAt = $api->getCacheUpdatedAt('joueurs_club', array('numclu' => $numClub));
            ob_start();
            require __DIR__ . '/views/front/joueurs.php';
            return ob_get_clean();
        }
        return __('Erreur de paramètres du shortcode', 'dataping');
    }

    private function getTypeListeJoueurs()
    {
        return $this->typeListeJoueurs;
    }

    /**
     * Hook pour récupérer les données des joueurs en cache
     * Usage: $joueurs = apply_filters('dataping_get_joueurs', 'MF');
     * @param string $type Type de joueurs ('M', 'F', ou 'MF')
     * @return array Tableau d'objets Joueur
     */
    public function get_joueurs_data($type = 'MF')
    {
        if (!in_array($type, $this->typeListeJoueurs, true)) {
            $type = 'MF';
        }
        $joueurs = new Joueurs($type);
        return $joueurs->getJoueurs($type);
    }

    /**
     * Hook pour récupérer les données des équipes en cache
     * Usage: $equipes = apply_filters('dataping_get_equipes', 'M');
     * @param string $type Type d'équipes ('M' ou 'F', null pour toutes)
     * @return array Tableau d'équipes
     */
    public function get_equipes_data($type = null)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api)) {
            return array();
        }

        if ($type === 'M' || $type === 'F') {
            return $api->getEquipesByClub(ParametresDataPing::getNumClub(), $type);
        }

        // Retourne toutes les équipes (M et F)
        $equipesM = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'M');
        $equipesF = $api->getEquipesByClub(ParametresDataPing::getNumClub(), 'F');
        return array_merge((array) $equipesM, (array) $equipesF);
    }

    /**
     * Hook pour récupérer le classement d'une poule en cache
     * Usage: $classement = apply_filters('dataping_get_classement_poule', null, array('division' => 'D1', 'poule' => 'A'));
     * @param mixed $value Valeur par défaut (ignorée)
     * @param array $params Paramètres avec 'division' et 'poule'
     * @return array Classement de la poule
     */
    public function get_classement_poule_data($value, $params)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api) || !isset($params['division']) || !isset($params['poule'])) {
            return array();
        }
        return $api->getPouleClassement($params['division'], $params['poule']);
    }

    /**
     * Hook pour récupérer les rencontres d'une poule en cache
     * Usage: $rencontres = apply_filters('dataping_get_rencontres_poule', null, array('division' => 'D1', 'poule' => 'A'));
     * @param mixed $value Valeur par défaut (ignorée)
     * @param array $params Paramètres avec 'division' et 'poule'
     * @return array Rencontres de la poule
     */
    public function get_rencontres_poule_data($value, $params)
    {
        $api = AccesFFTTApi::getInstance();
        if (!is_object($api) || !isset($params['division']) || !isset($params['poule'])) {
            return array();
        }
        return $api->getPouleRencontres($params['division'], $params['poule']);
    }

    /**
     * Handler AJAX pour la synchronisation manuelle des données
     */
    public function handle_ajax_sync()
    {
        check_ajax_referer('dataping_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permissions insuffisantes'));
            return;
        }

        $api = AccesFFTTApi::getInstance();
        if (!is_object($api)) {
            wp_send_json_error(array('message' => 'Erreur de connexion à l\'API FFTT'));
            return;
        }

        try {
            $numClub = ParametresDataPing::getNumClub();
            $syncResults = array();
            $debugLog = array();

            // Vérifier les paramètres de connexion
            if (empty($numClub)) {
                throw new Exception('Numéro de club non configuré');
            }
            $debugLog[] = "Numéro de club: " . $numClub;

            $idApp = ParametresDataPing::getIdApplication();
            $motDePasse = ParametresDataPing::getMotDePasse();
            if (empty($idApp) || empty($motDePasse)) {
                throw new Exception('Identifiants API FFTT non configurés');
            }
            $debugLog[] = "ID Application: configuré";

            // Synchronisation des joueurs
            $api->clearJoueursCache($numClub);
            $debugLog[] = "Cache joueurs effacé";

            $joueurs = new Joueurs();
            $joueursListe = $joueurs->getJoueurs('MF');
            $syncResults['joueurs'] = count($joueursListe);
            $debugLog[] = "Joueurs récupérés: " . $syncResults['joueurs'];

            // Synchronisation des équipes
            $api->clearEquipesCache($numClub);
            $debugLog[] = "Cache équipes effacé";

            $equipesM = $api->getEquipesByClub($numClub, 'M');
            $equipesF = $api->getEquipesByClub($numClub, 'F');
            $syncResults['equipes'] = count($equipesM) + count($equipesF);
            $debugLog[] = "Équipes M récupérées: " . count($equipesM);
            $debugLog[] = "Équipes F récupérées: " . count($equipesF);

            // Vérifier si aucune donnée n'a été récupérée
            if ($syncResults['joueurs'] === 0 && $syncResults['equipes'] === 0) {
                throw new Exception('Aucune donnée récupérée. Vérifiez vos identifiants API et le numéro de club.');
            }

            // Pour chaque équipe, synchroniser classements et rencontres
            $allEquipes = array_merge($equipesM, $equipesF);
            foreach ($allEquipes as $equipe) {
                if (isset($equipe['iddiv']) && isset($equipe['idpoule'])) {
                    $api->clearPouleCache($equipe['iddiv'], $equipe['idpoule']);
                    $api->getPouleClassement($equipe['iddiv'], $equipe['idpoule']);
                    $api->getPouleRencontres($equipe['iddiv'], $equipe['idpoule']);
                }
            }

            // Enregistrer l'horodatage de la dernière synchronisation
            update_option('dataping_last_sync', time());

            wp_send_json_success(array(
                'message' => 'Synchronisation réussie',
                'timestamp' => current_time('mysql'),
                'results' => $syncResults,
                'debug' => $debugLog
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Erreur lors de la synchronisation: ' . $e->getMessage(),
                'debug' => isset($debugLog) ? $debugLog : array()
            ));
        }
    }

    /**
     * Handler AJAX : génération automatique de pages WordPress pour les équipes sélectionnées.
     * Crée (ou met à jour) une page WP par équipe sous une page parent "Équipes".
     */
    public function handle_ajax_generate_pages()
    {
        check_ajax_referer('dataping_generate_pages_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permissions insuffisantes'));
            return;
        }

        $teams = isset($_POST['teams']) ? (array) $_POST['teams'] : array();
        if (empty($teams)) {
            wp_send_json_error(array('message' => 'Aucune équipe sélectionnée'));
            return;
        }

        $parentId = $this->getOrCreateEquipesParentPage();
        $created  = 0;
        $updated  = 0;

        foreach ($teams as $team) {
            $iddiv   = sanitize_text_field($team['iddiv']    ?? '');
            $idpoule = sanitize_text_field($team['idpoule']  ?? '');
            $title   = sanitize_text_field($team['libequipe'] ?? '');

            if (empty($iddiv) || empty($title)) {
                continue;
            }

            $content  = '[equipe iddiv="' . $iddiv . '" idpoule="' . $idpoule . '"]';
            $existing = get_page_by_title($title, OBJECT, 'page');

            if ($existing) {
                wp_update_post(array(
                    'ID'           => $existing->ID,
                    'post_content' => $content,
                    'post_parent'  => $parentId,
                    'post_status'  => 'publish',
                ));
                $updated++;
            } else {
                wp_insert_post(array(
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_parent'  => $parentId,
                ));
                $created++;
            }
        }

        $parentUrl = get_permalink($parentId);
        wp_send_json_success(array(
            'message'    => $created . ' page(s) créée(s), ' . $updated . ' mise(s) à jour.',
            'created'    => $created,
            'updated'    => $updated,
            'parent_url' => $parentUrl,
        ));
    }

    /**
     * Trouve ou crée la page parent "Équipes" pour les pages d'équipe.
     * @return int ID de la page parent
     */
    private function getOrCreateEquipesParentPage()
    {
        $existing = get_page_by_path('equipes', OBJECT, 'page');
        if ($existing) {
            return $existing->ID;
        }

        return (int) wp_insert_post(array(
            'post_title'   => 'Équipes',
            'post_name'    => 'equipes',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ));
    }

    /**
     * Récupère l'horodatage de la dernière synchronisation
     * @return int|false Timestamp de la dernière sync ou false
     */
    public static function getLastSyncTimestamp()
    {
        return get_option('dataping_last_sync', false);
    }

    /**
     * Ajoute le widget au dashboard WordPress
     */
    public function add_dashboard_widget()
    {
        wp_add_dashboard_widget(
            'dataping_sync_widget',
            'DataPing - Synchronisation',
            array($this, 'render_dashboard_widget')
        );
    }

    /**
     * Affiche le contenu du widget dashboard
     */
    public function render_dashboard_widget()
    {
        $lastSync = self::getLastSyncTimestamp();
        ?>
        <div class="dataping-dashboard-widget">
            <?php if ($lastSync): ?>
                <?php
                $syncDate = date_i18n(get_option('date_format') . ' à ' . get_option('time_format'), $lastSync);
                $timeDiff = human_time_diff($lastSync, current_time('timestamp'));
                ?>
                <p>
                    <strong>Dernière synchronisation :</strong><br>
                    <?php echo esc_html($syncDate); ?><br>
                    <small style="color: #666;">(il y a <?php echo esc_html($timeDiff); ?>)</small>
                </p>
            <?php else: ?>
                <p><em>Aucune synchronisation effectuée</em></p>
            <?php endif; ?>

            <p>
                <button id="dataping-dashboard-sync-button" class="button button-primary button-large" style="width: 100%;">
                    <span class="dashicons dashicons-update" style="vertical-align: middle;"></span>
                    Synchroniser les données
                </button>
            </p>

            <div id="dataping-dashboard-sync-loading" style="display: none; text-align: center; margin: 10px 0;">
                <span class="spinner is-active" style="float: none; margin: 0;"></span>
                <span style="vertical-align: middle; margin-left: 5px;">Synchronisation en cours...</span>
            </div>

            <div id="dataping-dashboard-sync-message" style="margin-top: 10px;"></div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#dataping-dashboard-sync-button').on('click', function(e) {
                e.preventDefault();
                var $button = $(this);
                var $loading = $('#dataping-dashboard-sync-loading');
                var $message = $('#dataping-dashboard-sync-message');

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
                            $message.html('<div class="notice notice-success inline"><p><strong>Succès !</strong> ' + response.data.message + '</p></div>');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $message.html('<div class="notice notice-error inline"><p><strong>Erreur :</strong> ' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $loading.hide();
                        $button.prop('disabled', false);
                        $message.html('<div class="notice notice-error inline"><p><strong>Erreur :</strong> Erreur de communication avec le serveur</p></div>');
                    }
                });
            });
        });
        </script>

        <style>
        .dataping-dashboard-widget p {
            margin: 10px 0;
        }
        .dataping-dashboard-widget .notice.inline {
            margin: 10px 0 0 0;
            padding: 8px 12px;
        }
        .dataping-dashboard-widget .spinner {
            visibility: visible;
        }
        </style>
        <?php
    }
}

new DataPing();

