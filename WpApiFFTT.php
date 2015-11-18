<?php
/*
  Plugin Name: wp-Api-FFTT
  Plugin URI: http://robin-aldasoro.com/docs/wordpress-plugins/wp-Api-FFTT.zip
  Description: Ce plugin affiche les données accessibles via l'API de la FFTT
  Version: 0.2.3
  Author: Robin Aldasoro
  Author URI: robin-aldasoro.com
  License: GPLv2
 */

require_once('Utils.php');
// autoloading des models
autoload_fft_models();

class WpApiFFTT {

    /**
     * Types possibles  de listes de joueurs  à insérer dans les shortcodes
     * @var array
     */
    private $typeListeJoueurs = array(
        'M', 'F', 'MF'
    );

    public function __construct() {
        $this->initializeApi(ParametresApiFFTT::getInstance()->getIdApplication(), ParametresApiFFTT::getInstance()->getMotDePasse());
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('init', array($this, 'api_fftt_style_scripts'));
        add_shortcode('equipe', array($this, 'equipes_front'));
        add_shortcode('joueurs', array($this, 'joueurs_front'));
    }

    /**
     * Intialisation de l'API FFTT
     * @param string $idApplication
     * @param string $motdePasse
     */
    private function initializeApi($idApplication, $motdePasse) {
        if (!is_null($idApplication) && !is_null($motdePasse)) {
            $api = new AccesApi($idApplication, $motdePasse);
            if (empty($_SESSION['serial'])) {
                $_SESSION['serial'] = AccesApi::generateSerial();
            }

            $api->setSerial($_SESSION['serial']);
            $init = $api->initialization();

            if ($init['initialisation']['appli'] === '1') {
                setSessionApi($api);
            }
        }
    }

    public function add_admin_menu() {
        add_menu_page('Donnees FFTT', 'Données FFTT', 'manage_options', 'parametres_wpApiFFTT', array($this, 'admin_module'));
        add_submenu_page('parametres_wpApiFFTT', 'Equipes', 'Equipes', 'manage_options', 'equipes_wpApiFFTT', array($this, 'equipes_admin'));
        add_submenu_page('parametres_wpApiFFTT', 'Joueurs', 'Joueurs', 'manage_options', 'joueurs_wpApiFFTT', array($this, 'joueurs_admin'));
    }

    public function admin_module() {
        $pluginData = $this->getPluginData();
        require_once(__DIR__ . '/views/admin.php');
    }

    public function getPluginData() {
        $datas = get_plugin_data(__FILE__);
        return $datas;
    }

    public function api_fftt_style_scripts() {
        //Styles
        wp_register_style('admin-css', plugins_url('/assets/api-fftt.css', __FILE__), true);
        wp_enqueue_style('admin-css');
        //Javascript
        wp_register_script('api-fftt-js', plugins_url('/assets/api-fftt.js', __FILE__), 'jquery', '1.0', true);
        wp_register_script('table-sorter', plugins_url('/assets/tablesorter/jquery.tablesorter.min.js', __FILE__), 'jquery', '1.0', true);
        wp_register_script('table-sorter-pager', plugins_url('/assets/tablesorter/jquery.tablesorter.pager.js', __FILE__), 'jquery', '1.0', true);
        wp_enqueue_script('api-fftt-js');
        wp_enqueue_script('table-sorter');
        wp_enqueue_script('table-sorter-pager');
    }

    public function register_settings() {
        register_setting('wp_Api_FFTT_settings', ConstantesApiFFTT::WP_API_FFTT_ID_APPLICATION);
        register_setting('wp_Api_FFTT_settings', ConstantesApiFFTT::WP_API_FFTT_MOT_DE_PASSE);
        register_setting('wp_Api_FFTT_settings', ConstantesApiFFTT::WP_API_FFTT_NUM_CLUB);

        add_settings_section('wp_Api_FFTT_section', 'Paramètres du plugin', array($this, 'section_html'), 'wp_Api_FFTT_settings');
        add_settings_field(ConstantesApiFFTT::WP_API_FFTT_ID_APPLICATION, 'Id Application', array($this, 'id_application_html'), 'wp_Api_FFTT_settings', 'wp_Api_FFTT_section');
        add_settings_field(ConstantesApiFFTT::WP_API_FFTT_MOT_DE_PASSE, 'Mot de passe Application', array($this, 'mot_de_passe_html'), 'wp_Api_FFTT_settings', 'wp_Api_FFTT_section');
        add_settings_field(ConstantesApiFFTT::WP_API_FFTT_NUM_CLUB, 'Numéro de club', array($this, 'equipe_num_html'), 'wp_Api_FFTT_settings', 'wp_Api_FFTT_section');
    }

    //  <editor-fold desc="Gestion de la vue de la page parametres">
    public function section_html() {
        echo '<p>Entrez les paramètres de l\'application fournis par la FFTT</p>';
        echo '<p>Si vous n\'en avez pas, vous devrez faire la demande suivante en suivant la procédure décrite ici : <a target="_blank" href="http://www.fftt.com/actus/ouverture_interfaces_smartping_2015_06_30-1362.html">http://www.fftt.com/actus/ouverture_interfaces_smartping_2015_06_30-1362.html</a></p>';
    }

    public function id_application_html() {
        ?>
        <input type="text" name="wp_Api_FFTT_id_application" value="<?php echo get_option(ConstantesApiFFTT::WP_API_FFTT_ID_APPLICATION); ?>" />
        <?php
    }

    public function mot_de_passe_html() {
        ?>
        <input type="text" name="wp_Api_FFTT_mot_de_passe" value="<?php echo get_option(ConstantesApiFFTT::WP_API_FFTT_MOT_DE_PASSE); ?>" />
        <?php
    }

    public function equipe_num_html() {
        ?>
        <input type="text" name="wp_Api_FFTT_num_club" value="<?php echo get_option(ConstantesApiFFTT::WP_API_FFTT_NUM_CLUB); ?>" />
        <?php
    }

    public function getForm() {
        echo '<form action="options.php" method="POST" name="wp_Api_FFTT_settings" class="fftt_plug_settings_form">';
        do_settings_sections('wp_Api_FFTT_settings');
        settings_fields('wp_Api_FFTT_settings');
        echo '<div>' . submit_button('Valider la saisie') . '</div>';
        echo '</form>';
    }

    //  </editor-fold>
    //  <editor-fold desc="Gestion des differents sous menus">
    public function equipes_admin() {
        require_once(__DIR__ . '/views/admin-equipes.php');
    }

    public function joueurs_admin() {
        require_once(__DIR__ . '/views/admin-joueurs.php');
    }

    // </editor-fold>
    // <editor-fold desc="Gestion des shortcodes">
    public function equipes_front($atts, $content) {
        $api = getSessionApi();
        $atts = shortcode_atts(array('iddiv' => 0, 'idpoule' => 0), $atts);
        if ($atts['iddiv'] === 0 || $atts['idpoule'] === 0) {
            return 'Poule ou division incorrecte';
        } else if (is_null($api)) {
            return 'Problème lors de la récupération des résultats';
        } else {
            $listeEquipesM = $api->getEquipesByClub(ParametresApiFFTT::getInstance()->getNumClub(), 'M');
            $listeEquipesF = $api->getEquipesByClub(ParametresApiFFTT::getInstance()->getNumClub(), 'F');
            $listeEquipes = array_merge($listeEquipesM, $listeEquipesF);

            require_once(__DIR__ . '/views/front-equipes.php');
            return ob_get_clean();
        }
    }

    /**
     * Méthode qui gère les liste de joueurs coté front
     * @param type $atts type: M | F | MF
     * @param type $content
     */
    public function joueurs_front($atts, $content) {
        $atts = shortcode_atts(array('type' => 'MF'), $atts);
        if (in_array($atts['type'], $this->getTypeListeJoueurs())) {
            $listeJoueurs = array();
            $joueurs = new Joueurs($atts['type']);
            require_once(__DIR__ . '/views/front-joueurs.php');
            return ob_get_clean();
        } else {
            return 'Erreur de paramètres du shortcode';
        }
    }

    //  </editor-fold>
    // <editor-fold desc="Getters & setters">

    private function getTypeListeJoueurs() {
        return $this->typeListeJoueurs;
    }

// </editor-fold>
}

new WpApiFFTT();

