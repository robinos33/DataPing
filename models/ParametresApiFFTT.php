<?php

if (!class_exists('ParametresApiFFTT')) {

    /**
     * Description of parametres
     *
     * @author robin
     */
    class ParametresApiFFTT {

        public static function getInstance() {
            return new self;
        }

        /**
         * @return array $params
         */
        private function getParametresFromDatabase() {
            global $wpdb;
            $params['idApplication'] = get_option(ConstantesApiFFTT::WP_API_FFTT_ID_APPLICATION);
            $params['motDePasse'] = get_option(ConstantesApiFFTT::WP_API_FFTT_MOT_DE_PASSE);
            $params['numClub'] = get_option(ConstantesApiFFTT::WP_API_FFTT_NUM_CLUB);
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
