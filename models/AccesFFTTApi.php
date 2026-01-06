<?php

if (!class_exists('AccesFFTTApi')) {

    /**
     * @author Robin Aldasoro -
     * Source originale : classe AccesFFTTApi VincentBab vincentbab@gmail.com
     */
    class AccesFFTTApi
    {

        private static $_instance = null;

        private $cache;

        /**
         * @var string $appId ID de l'application fourni par la FFTT (ex: AM001)
         */
        protected $appId;

        /**
         * @var string $appKey Mot de passe fourni par la FFTT
         */
        protected $appKey;

        /**
         * @var string $serial Serial de l'utilisateur
         */
        protected $serial;

        /**
         * @var string $ipSource
         */
        protected $ipSource;

        public function __construct()
        {
            if (!is_null(ParametresDataPing::getIdApplication()) && !is_null(ParametresDataPing::getMotDePasse())) {

                $this->appId = ParametresDataPing::getIdApplication();
                $this->appKey = ParametresDataPing::getMotDePasse();

                // Démarre une session si nécessaire (certaines installations WP n'utilisent pas les sessions par défaut)
                if (function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
                    @session_start();
                }

                if (empty($_SESSION['serial'])) {
                    $_SESSION['serial'] = AccesFFTTApi::generateSerial();
                }

                $this->setSerial($_SESSION['serial']);
                // Initialise l'application si possible (les éventuelles erreurs XML sont gérées en interne)
                $this->initialization();
            }

            // Assure que les erreurs libxml n'interrompent pas l'exécution
            libxml_use_internal_errors(true);
        }

        public static function getInstance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new AccesFFTTApi();
            }

            return self::$_instance;
        }

        public function getAppId()
        {
            return $this->appId;
        }

        public function getAppKey()
        {
            return $this->appKey;
        }

        public function setSerial($serial)
        {
            $this->serial = $serial;

            return $this;
        }

        public function getSerial()
        {
            return $this->serial;
        }

        public function setIpSource($ipSource)
        {
            $this->ipSource = $ipSource;

            return $this;
        }

        public function getIpSource()
        {
            return $this->ipSource;
        }

        public function initialization()
        {
            return AccesFFTTApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_initialisation.php', array()));
        }

        public function getClubsByDepartement($departement)
        {
            //return $this->getCachedData("clubs{$departement}", 3600 * 24 * 30, function ($this) use ($departement) {
                return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_club_dep2.php', array('dep' => $departement)), 'club');
            //});
        }

        public function getClub($numero)
        {
            return AccesFFTTApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_club_detail.php', array('club' => $numero)), 'club');
        }

        public function getJoueur($licence)
        {
            $joueur = AccesFFTTApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_joueur.php', array('licence' => $licence, 'auto' => 1)), 'joueur');

            if (!isset($joueur['licence'])) {
                return null;
            }

            if (empty($joueur['natio'])) {
                $joueur['natio'] = 'F';
            }

            $joueur['photo'] = "http://www.fftt.com/espacelicencie/photolicencie/{$joueur['licence']}_.jpg";
            $joueur['progmois'] = round($joueur['point'] - $joueur['apoint'], 2); // Progression mensuelle
            $joueur['progann'] = round($joueur['point'] - $joueur['valinit'], 2); // Progression annuelle

            return $joueur;
        }


        public function getJoueursByName($nom, $prenom = '')
        {
            return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur.php', array('nom' => $nom, 'prenom' => $prenom)), 'joueur');
        }

        public function getJoueursByClub($club)
        {
            return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur.php', array('club' => $club)), 'joueur');
        }

        public function getEquipesByClub($club, $type = null)
        {
            if ($type && !in_array($type, array('M', 'F'))) {
                $type = 'M';
            }

            $key = $this->buildCacheKey('equipes_club', array('numclu' => $club, 'type' => $type));
            $lifeTime = $this->computeHalfDayTtl();
            $teams = $this->getCachedData($key, $lifeTime, function () use ($club, $type) {
                return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_equipe.php', array('numclu' => $club, 'type' => $type)), 'equipe');
            });

            foreach ($teams as &$team) {
                $params = array();
                parse_str($team['liendivision'], $params);

                $team['idpoule'] = $params['cx_poule'];
                $team['iddiv'] = $params['D1'];
            }

            return $teams;
        }

        public function getPoules($division)
        {
            $poules = AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_equ.php', array('action' => 'poule', 'D1' => $division)), 'poule');

            foreach ($poules as &$poule) {
                $params = array();
                parse_str($poule['lien'], $params);

                $poule['idpoule'] = $params['cx_poule'];
                $poule['iddiv'] = $params['D1'];
            }

            return $poules;
        }

        public function getPouleClassement($division, $poule = null)
        {
            $key = $this->buildCacheKey('poule_classement', array('D1' => $division, 'cx_poule' => $poule));
            $lifeTime = $this->computeHalfDayTtl();
            return $this->getCachedData($key, $lifeTime, function () use ($division, $poule) {
                return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_equ.php', array('auto' => 1, 'action' => 'classement', 'D1' => $division, 'cx_poule' => $poule)), 'classement');
            });
        }

        public function getPouleRencontres($division, $poule = null)
        {
            $key = $this->buildCacheKey('poule_rencontres', array('D1' => $division, 'cx_poule' => $poule));
            $lifeTime = $this->computeHalfDayTtl();
            return $this->getCachedData($key, $lifeTime, function () use ($division, $poule) {
                return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_result_equ.php', array('auto' => 1, 'D1' => $division, 'cx_poule' => $poule)), 'tour');
            });
        }


        public function getLicencesByName($nom, $prenom = '')
        {
            return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur_o.php', array('nom' => strtoupper($nom), 'prenom' => ucfirst($prenom))), 'joueur');
        }

        public function getLicencesByClub($club)
        {
            return AccesFFTTApi::getCollection($this->getData('http://www.fftt.com/mobile/pxml/xml_liste_joueur.php', array('club' => $club)), 'joueur');
        }

        public function getLicence($licence)
        {
            return AccesFFTTApi::getObject($this->getData('http://www.fftt.com/mobile/pxml/xml_licence.php', array('licence' => $licence)), 'licence');
        }

        private function getCachedData($key, $lifeTime, $callback)
        {
            // Use WordPress transients when available; otherwise, bypass cache
            if (!function_exists('get_transient') || !function_exists('set_transient')) {
                return $callback($this);
            }

            $data = get_transient($key);
            if ($data === false) {
                $data = $callback($this);
                // Store data and last update timestamp
                set_transient($key, $data, (int) $lifeTime);
                set_transient($key . '__updated_at', time(), (int) $lifeTime);
            }

            return $data;
        }

        private function buildCacheKey($prefix, array $params)
        {
            ksort($params);
            $base = $prefix . '|' . http_build_query($params);
            return 'dataping_' . md5($base);
        }

        private function computeHalfDayTtl()
        {
            // Use WP local time if available
            $now = function_exists('current_time') ? (int) current_time('timestamp') : time();

            // Define target times today at 08:00 and 13:00
            $today = getdate($now);
            $mk = function ($hour) use ($today) {
                return mktime($hour, 0, 0, $today['mon'], $today['mday'], $today['year']);
            };
            $t8 = $mk(8);
            $t13 = $mk(13);

            if ($now < $t8) {
                $next = $t8;
            } elseif ($now < $t13) {
                $next = $t13;
            } else {
                // Next day 08:00
                $next = $t8 + 86400;
            }
            $ttl = max(60, $next - $now);
            return $ttl;
        }

        public function getCacheUpdatedAt($prefix, array $params)
        {
            if (!function_exists('get_transient')) {
                return false;
            }
            $key = $this->buildCacheKey($prefix, $params) . '__updated_at';
            $ts = get_transient($key);
            return $ts === false ? false : (int) $ts;
        }

        /**
         * Supprime le cache des équipes d'un club
         * @param string $club Numéro du club
         */
        public function clearEquipesCache($club)
        {
            if (!function_exists('delete_transient')) {
                return;
            }

            $types = array('M', 'F');
            foreach ($types as $type) {
                $key = $this->buildCacheKey('equipes_club', array('numclu' => $club, 'type' => $type));
                delete_transient($key);
                delete_transient($key . '__updated_at');
            }
        }

        /**
         * Supprime le cache d'une poule (classement et rencontres)
         * @param string $division Division
         * @param string $poule Poule
         */
        public function clearPouleCache($division, $poule)
        {
            if (!function_exists('delete_transient')) {
                return;
            }

            $keyClassement = $this->buildCacheKey('poule_classement', array('D1' => $division, 'cx_poule' => $poule));
            delete_transient($keyClassement);
            delete_transient($keyClassement . '__updated_at');

            $keyRencontres = $this->buildCacheKey('poule_rencontres', array('D1' => $division, 'cx_poule' => $poule));
            delete_transient($keyRencontres);
            delete_transient($keyRencontres . '__updated_at');
        }

        public function getData($url, $params = array(), $generateHash = true)
        {
            if ($generateHash) {
                $params['serie'] = $this->getSerial();
                $params['id'] = $this->getAppId();
                $params['tm'] = date('YmdHis') . substr(microtime(), 2, 3);
                $params['tmc'] = hash_hmac('sha1', $params['tm'], hash('md5', $this->getAppKey(), false));
            }

            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            if ($this->getIpSource()) {
                curl_setopt($curl, CURLOPT_INTERFACE, $this->getIpSource());
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Accept:", // Suprime le header par default de cUrl (Accept: */*)
                "User-agent: Mozilla/4.0 (compatible; MSIE 6.0; Win32)",
                "Content-Type: application/x-www-form-urlencoded",
                "Accept-Encoding: gzip",
                "Connection: Keep-Alive",
            ));
            $data = curl_exec($curl);
            curl_close($curl);

            $xml = simplexml_load_string($data);

            if (!$xml) {
                return false;
            }

            // Petite astuce pour transformer simplement le XML en tableau
            return json_decode(json_encode($xml), true);
        }

        public static function getCollection($data, $key = null)
        {
            if (empty($data)) {
                return array();
            }

            if ($key) {
                if (!array_key_exists($key, $data)) {
                    return array();
                }
                $data = $data[$key];
            }

            return isset($data[0]) ? $data : array($data);
        }

        public static function getObject($data, $key = null)
        {
            if ($key && $data !== false) {
                return array_key_exists($key, $data) ? $data[$key] : null;
            } else {
                return empty($data) ? null : $data;
            }
        }

        public static function generateSerial()
        {
            $serial = '';
            for ($i = 0; $i < 15; $i++) {
                $serial .= chr(mt_rand(65, 90)); //(A-Z)
            }

            return $serial;
        }

    }

}