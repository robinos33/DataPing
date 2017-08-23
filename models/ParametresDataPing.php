<?php

if (!class_exists('ParametresDataPing')) {

    /**
     * Description of parametres
     *
     * @author robin
     */
    class ParametresDataPing {

        public static function getInstance() {
            return new self;
        }

        /**
         * @return array $params
         */
        private function getParametresFromDatabase() {
            global $wpdb;
            $params['idApplication'] = get_option(ConstantesDataPing::DATAPING_ID_APPLICATION);
            $params['motDePasse'] = get_option(ConstantesDataPing::DATAPING_MOT_DE_PASSE);
            $params['numClub'] = get_option(ConstantesDataPing::DATAPING_NUM_CLUB);
            return $params;
        }

        public function getIdApplication() {
            $params = $this->getParametresFromDatabase();
            return $params['idApplication'];
        }

        public function getMotDePasse() {
            $params = $this->getParametresFromDatabase();
            return $params['motDePasse'];
        }

        public function getNumClub() {
            $params = $this->getParametresFromDatabase();
            return $params['numClub'];
        }

    }

}
