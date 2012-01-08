<?php 
/**
 * Mapping du flux user
 * @author	nsun
 * @link	nsun at tac-tac dot org
 * @version	0.1
 */
class user extends base_dto
{
	/** id de la personne */
	public $id;
	/** Pseudo de la personne */
	public $name;
	/** Nombre de point total */
	public $points;
	/** Lien vers l'avatar */
	public $avatar;
	/** Langue de l'utilisateur */
	public $lang;
	
	/**
	 * Information générales du profil
	 * @var array
	 * @example	<pre>Array(<br/>	'name' => (string) 'Warp',<br/>	'male' => (boolean) TRUE,<br/>	(...)<br/>)</pre>
	 */
	public $profil = Array();

	/**
	 * Liste des clés attribuées au profil
	 * @var array
	 * @example	<pre>Array(<br/>	'intrusion' => (string) '1a2b3c4d',<br/>	'kingdom' => (string) '9f8e7d6c',<br/>	(...)<br/>)</pre>
	 */
	public $key = Array();

	/**
	 * Ensemble des scores figurant sur la page profil
	 * @var (object) stdClass
	 * @example	<pre>$this->score->fever = Array(<br/>	'Îles libres' => (int) 26,<br/>	'Score' => (string) '313 pts'<br/>);<br/>$this->score->intrusion = Array(<br/>	'Niveau' => (int) 6,<br/>);</pre>
	 */
	public $games;

	/**
	* Liste des amis de l'utilisateur
	* @var array
	* @example	$friends[id] = login_friend;
	*/
	public $friends = NULL;
	
	
	/** Instance de la lib - Permet de manipuler des flux en interne*/
	private $_api = NULL;
	
	/**
	 * Vérifie l'argument et attribue une methode de mapping
	 * @param object	$flow	- Flux XML
	 * @todo éviter de multiplier les instances de mtlib.
	 */
	public function user($flow)
	{
		$this->_api = new mtlib(appName, privKey);
		
		$this->init_dto($flow);
	}

	/**
	 * Mapping du flux XML à partir d'un objet de type SimpleXMLElement
	 */
	public function initSimpleXML()
	{
		//On récupère l'objet SimpleXMLElement correspondant au flux
		if(!$xmlAsObject = $this->getXmlAsObject()) return FALSE;
		
		// On initialise les attributs de cette classe à partir de l'objet SimpleXMl
		// Permet de rendre l'objet "user" plus facilement utilisable. 
		foreach($xmlAsObject->attributes() as $key => $value)
		{
			switch($key)
			{
				//On récupère la clé du flux "friends"
				case 'friends':
					$this->key[ strval($key) ] = strval($value);
					break;
					
				//Les autres info correspondents à des éléments de la fiche de l'utilateur
				default:
					//Raffinage des types
					switch($key)
					{
						//boolean
						case 'male':
							$value = (boolean) ($value == 'true');
							break;
							//int
						case 'points':
							$value = intval($value);
							break;
							//string
						default:
							$value = strval($value);
					}
					
				//Assignation de la donnée traitée à l'objet profil
				$this->profil[ strval($key) ] = $value;
			}
		}

		//Mapping des éléments
		foreach($xmlAsObject as $tag => $contents)
		{
			switch($tag)
			{
				//Compléments du profil
				case 'message':
				case 'title':
					$this->profil[ strval($tag)] = strval($contents);
					break;
					//Données de jeu
				case 'games':
					foreach($contents->g as $g)
					{
						//Référence du jeu
						$game = strval($g['game']);
						//init du score
						$score = Array();

						//Clé
						if(isset($g['key'])) $this->key[$game] = strval($g['key']);
						//Scores
						foreach($g->i as $i)
						{
							//init
							$key = strval($i['key']);
							$value = strval($i);
								
							//raffinage des types
							if(is_numeric($value))
							{
								$value = (floatval($value) > intval($value)) ? floatval($value) : intval($value);
							}
								
							$score[ $key ] = $value;
						}
						//Affectation du score
						if(count($score))
						{
							$this->games->$game = $score;
						}
						else unset($this->games->game);
					}
			}
		}
		
		$this->init_attributs();
		return TRUE;
	}
	
	/**
	 * Initialise différents attribut à partir de l'objet précédement intialisé.
	 */
	private function init_attributs(){
		$this->name = $this->profil['name'];
		$this->points = $this->profil['points'];
		$this->avatar = $this->profil['avatar'];
		$this->lang = $this->profil['lang'];
	}
	
	/**
	 * Récupère les listes des amis de l'utilisateur.
	 * <br />Nécessite au préalable d'avoir initialisé l'id de l'utilisateur
	 */
	public function getFriends() {
		if ($this->friends == NULL) {
			if(isset($this->key['friends']) && isset($this->id))
			{
				$fluxFriends = $this->_api->flow('friends', $this->id, $this->key['friends']);
				if ($fluxFriends != NULL) {
					$friendsAsObject = simplexml_load_string($fluxFriends);
					//print_r($friendsAsObject);
				}
			} else {			
				echo "Veuillez initialiser l'id de l'utilisateur avant de récupérer la liste d'amis";
				echo "<br />user_object->id = id_user";
				return FALSE;
			} 
			
			foreach($friendsAsObject->user  as $user)
			{
				$this->friends[intval($user['id'])] = strval($user['name']);
			}
			return $this->friends;
		}
	}
}
?>