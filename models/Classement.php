<?php

/**
 * Description of Classement
 * Classe simplifiée pour ne contenir que les données essentielles
 *
 * @author robin
 */
class Classement {

    private $pointsMensuels;
    private $pointsOfficiels;
    private $progressionAnnuelle;
    private $progressionMensuelle;
    private $classementOfficiel;
    private $rangNational;
    private $rangDepartemental;

    public function __construct($datas) {
        // xml_joueur.php retourne 'point' pour les points mensuels
        $this->setPointsMensuels($datas['point'] ?? 0);
        // 'valcla' pour les points officiels
        $pointsOfficiels = $datas['valcla'] ?? 0;
        $this->setPointsOfficiels($pointsOfficiels);
        // 'progann' et 'progmois' sont calculés par getJoueur()
        $this->setProgressionAnnuelle($datas['progann'] ?? 0);
        $this->setProgressionMensuelle($datas['progmois'] ?? 0);
        // Calcul du classement officiel à partir des points officiels
        $this->setClassementOfficiel($this->calculerClassementFromPoints($pointsOfficiels));
        // 'rangreg' pour le rang national, 'rangdep' pour départemental
        $this->setRangNational($datas['rangreg'] ?? '');
        $this->setRangDepartemental($datas['rangdep'] ?? '');
    }

    public function getPointsMensuels() {
        return $this->pointsMensuels;
    }

    public function setPointsMensuels($pointsMensuels) {
        $this->pointsMensuels = round($pointsMensuels, 2);
    }

    public function getPointsOfficiels() {
        return $this->pointsOfficiels;
    }

    public function setPointsOfficiels($pointsOfficiels) {
        $this->pointsOfficiels = round($pointsOfficiels, 2);
    }

    public function getProgressionAnnuelle() {
        return $this->progressionAnnuelle;
    }

    public function setProgressionAnnuelle($progressionAnnuelle) {
        $this->progressionAnnuelle = round($progressionAnnuelle, 2);
    }

    public function getProgressionMensuelle() {
        return $this->progressionMensuelle;
    }

    public function setProgressionMensuelle($progressionMensuelle) {
        $this->progressionMensuelle = round($progressionMensuelle, 2);
    }

    public function getClassementOfficiel() {
        return $this->classementOfficiel;
    }

    public function setClassementOfficiel($classementOfficiel) {
        $this->classementOfficiel = $classementOfficiel;
    }

    public function getRangNational() {
        return $this->rangNational;
    }

    public function setRangNational($rangNational) {
        $this->rangNational = $rangNational;
    }

    public function getRangDepartemental() {
        return $this->rangDepartemental;
    }

    public function setRangDepartemental($rangDepartemental) {
        $this->rangDepartemental = $rangDepartemental;
    }

    /**
     * Calcule le classement à partir des points officiels
     * - 4 chiffres : prendre les 2 premiers (ex: 1232 => 12)
     * - 3 chiffres : prendre le 1er chiffre (ex: 879 => 8)
     */
    private function calculerClassementFromPoints($points) {
        if (empty($points)) {
            return '';
        }

        $pointsStr = (string) intval($points);
        $nbChiffres = strlen($pointsStr);

        if ($nbChiffres >= 4) {
            // 4 chiffres ou plus : prendre les 2 premiers
            return intval(substr($pointsStr, 0, 2));
        } elseif ($nbChiffres === 3) {
            // 3 chiffres : prendre le premier
            return intval(substr($pointsStr, 0, 1));
        } else {
            // Moins de 3 chiffres : retourner tel quel
            return intval($points);
        }
    }

}
