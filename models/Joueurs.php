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
                // xml_liste_joueur.php retourne la liste basique, il faut enrichir avec xml_joueur.php
                $licencies = $this->_api->getLicencesByClub($club);
                $joueursData = array();

                foreach ($licencies as $licencie) {
                    // Récupérer les données complètes du joueur (classement, points, rangs, etc.)
                    $joueurComplet = $this->_api->getJoueur($licencie['licence']);

                    if ($joueurComplet) {
                        $joueursData[] = array(
                            'licence' => $joueurComplet,
                            'classement' => $joueurComplet
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