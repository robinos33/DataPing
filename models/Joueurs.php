<?php

if (!class_exists('joueurs')) {

    /**
     * Description of joueurs
     *
     * @author robin
     */
    class Joueurs {

        private $_api;
        /**
         * Tableau contenant les joueurs du club
         * @var array Joueur
         */
        private $joueurs = [];

        public function __construct() {
            $this->_api = AccesFFTTApi::getInstance();
            $this->loadJoueurs();
        }

        private function loadJoueurs() {
            $club = ParametresDataPing::getNumClub();
            $cacheKey = $this->_api->buildCacheKeyPublic('joueurs_club', array('numclu' => $club));
            $lifeTime = $this->_api->computeHalfDayTtlPublic();

            $joueursData = $this->_api->getCachedDataPublic($cacheKey, $lifeTime, function() use ($club) {
                $licencies = $this->_api->getLicencesByClub($club);
                $joueursData = array();

                foreach ($licencies as $joueur) {
                    $donneesLicence = $this->_api->getLicence($joueur['licence']);
                    $donneesClassement = $this->_api->getJoueur($joueur['licence']);
                    if (!is_null($donneesLicence) && !is_null($donneesClassement)) {
                        $joueursData[] = array(
                            'licence' => $donneesLicence,
                            'classement' => $donneesClassement
                        );
                    }
                }

                return $joueursData;
            });

            foreach ($joueursData as $joueurData) {
                $this->joueurs[] = new Joueur($joueurData['licence'], $joueurData['classement']);
            }
        }

        public function getJoueurs($sexe) {
            $joueurs = array();
            switch ($sexe) {
                default:
                case 'MF':
                    $joueurs = $this->joueurs;
                    break;
                case 'F':
                case 'M':
                    foreach ($this->joueurs as $joueur) {
                        if ($joueur->getSexe() === $sexe) {
                            $joueurs[] = $joueur;
                        }
                    }
                    break;
            }
            return $joueurs;
        }

    }

}