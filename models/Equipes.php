<?php

/**
 * Created by PhpStorm.
 * User: robin
 * Date: 06/09/2017
 * Time: 22:00
 */
class Equipes {
	private $_api;
	private $_equipes = array();

	public function __construct() {
		$this->_api = AccesFFTTApi::getInstance();

		$listeEquipesM = $this->_api->getEquipesByClub( ParametresDataPing::getNumClub(), 'M' );
		$listeEquipesF = $this->_api->getEquipesByClub( ParametresDataPing::getNumClub(), 'F' );
		$this->_setEquipesFromApi( $listeEquipesM, $listeEquipesF );
	}

	/**
	 * Construction de la liste des joueurs
	 *
	 * @param $listeEquipesM
	 * @param $listeEquipesF
	 *
	 * @internal param array $listeEquipes
	 */
	private function _setEquipesFromApi( $listeEquipesM, $listeEquipesF ) {
		foreach ( $listeEquipesM as $equipe ) {
			$this->_equipes[] = new Equipe( $equipe, 'M' );
		}

		foreach ( $listeEquipesF as $equipe ) {
			$this->_equipes[] = new Equipe( $equipe, 'F' );
		}
	}

	public function getEquipes( $sexe ) {
		$equipes = array();
		switch ( $sexe ) {
			default:
			case 'MF':
				$equipes = $this->_equipes;
				break;
			case 'F':
			case 'M':
				foreach ( $this->_equipes as $equipe ) {
					if($equipe->type === $sexe){
						$equipes[] = $equipe;
					}
				}
				break;
		}

		return $equipes;
	}
}