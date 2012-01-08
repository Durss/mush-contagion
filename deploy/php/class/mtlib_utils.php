<?php
class mtlib_utils extends mtlib
{
    // instance de la classe
    private static $instance;
    
    /** Instance de la classe mtlib */
    private $api = NULL;
    
    /** Liste d'utilisateur */
    private $users = NULL;

    // Un constructeur privé ; empêche la création directe d'objet
    private function __construct() 
    {
    	$this->api = new mtlib(appName, privKey);
    }

    // La méthode singleton
    public static function getInstance() 
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }
    
    /**
     * 
     * Retourne l'objet user de l'utilisateur demandé
     * @param unknown_type $id l'id de l'utilisateur
     * @param unknown_type $pubkey
     * @return NULL
     */
    public function getUser($id, $pubkey) {
    	// Si Non initialisé ou Non existant dans dans la liste
    	if ($this->users == NULL || ($this->users != NULL && $this->users[$id] == NULL)) {
    		$this->users[$id] = $this->getNewUser($id, $pubkey);
    	}
    	
    	return $this->users[$id];
    }
    
    /**
    * Retourne l'objet user de l'utilisateur demandé
    * @param unknown_type $id l'id de l'utilisateur
    */
    private function getNewUser($id, $pubkey) {
    	
    		// Récupération du flux XML de l'utilisateur
    		$fluxUser = $this->api->flow('user', $id, $pubkey);
    		print_r($fluxUser);
    		// Génération d'un objet "user" à partir du flux XML
    		$user = new user($fluxUser);
    		// Permet de récupérer la liste d'amis de l'utilsateur
    		$user->id = $id;
    		return $user;
    }

}

?>