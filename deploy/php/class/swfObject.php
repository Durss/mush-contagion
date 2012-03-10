<?php
class swfObject
{	
	/**
	 * URL du fichier SWF
	 * <p>spécifie l'URL du votre fichier SWF</p>
	 * @var string
	 */
	public $swfUrl = false;
	/**
	 * spécifie l'id de l’élément div HTML (contenant votre contenu alternatif) que vous aimeriez remplacer par votre contenu Flash
	 * @var string
	 */
	public $id = false;
	/**
	 * spécifie la largeur de votre SWF
	 * @var string
	 */
	public $width = false;
	/**
	 * spécifie la hauteur de votre SWF
	 * @var string
	 */
	public $height = false;
	/**
	 * spécifie la version requise du lecteur Flash pour votre fichier SWF (le format est: "major.minor.release" ou "major")
	 * @var string
	 */
	public $version = false;
	/**
	 * (facultatif) spécifie l'URL de votre fichier SWF "Express Install" et active Adobe Express Install. Notez que "Express Install" s'éxécute une seule fois (la première fois qu’il est appelé), qu’il est uniquement pris en charge par Flash Player 6.0.65 ou supérieur sur Windows ou Mac, et qu'il exige une taille minimale SWF de 310px par 137px.
	 * @var string
	 */
	public $expressInstallSwfurl = null;
	/**
	 * Tableau des flashvars (facultatif) 
	 * <p>spécifie vos valeurs <b>flashvars</b> avec une association <code>nom => valeur</code>.</p>
	 * @var array
	 */
	public $flashvars = array();
	/**
	 * Tableau des paramètres (facultatif) 
	 * <p>spécifie vos éléments <b>params</b> avec une association <code>nom => valeur</code>.</p>
	 * @var array
	 */
	public $params = array();	
	/**
	 * Tableau des attributs (facultatif) 
	 * <p>spécifie les <b>attributs</b> de votre objet avec une association <code>nom => valeur</code>.</p>
	 * @var array
	 */
	public $attributes = array();
	/**
	 * (facultatif) (nom de fonction JavaScript) est utilisé pour définir une fonction de rappel qui est appelée à la fois en cas de succès ou d’échec de l’intégration de votre fichier SWF (voir la documentation de l’API ).
	 * @var	string
	 */
	public $callbackFn = null;
	
	/**
	 * Type d'élément enveloppant le contenu alternatif
	 * @var string
	 */
	public $altTag = "div";
	
	/**
	 * Constructeur
	 */
	public function __construct() {	}
	
	/**
	 * Vérifie la définition des paramètres obligatoires de l'embedSwf
	 * @return	bool
	 */
	private function _checkParams()
	{
		//
		if(! (
				$this->swfUrl
			&&	$this->id
			&&	$this->width
			&&	$this->height
			&&	$this->version
		)	) return false;
		else return true;
	}
	
	/**
	 * Contenu alternatif
	 * @return	string
	 */
	public function alt($contents=null)
	{
		if(! $this->_checkParams()) return false;
		
		$alt = <<<EOALT
<{$this->altTag} id="{$this->id}">{$contents}</{$this->altTag}>
EOALT;
		return $alt;
	}
	
	/**
	 * affichage du SWF avec JavaScript
	 * @link	http://code.google.com/p/swfobject/wiki/api
	 * @return	string
	 */
	public function embedSWF()
	{
		if(! $this->_checkParams()) return false;
		
		//init
		$args = array();
		$options = null;
		$flashvars = $params = $attributes = null;
		
		//Check
		if($this->expressInstallSwfurl != null) $args[] = '"'.$this->expressInstallSwfurl.'"';
		
		foreach(array('flashvars', 'params', 'attributes') as $var)
		{
			if(count($this->{$var}))
			{
				$options .= "\nvar {$var} = {};\n";
				foreach($this->{$var} as $key => $value)
				{
					$options .= "{$var}['{$key}'] = \"{$value}\";\n";
				}
				$args[] = $var;
			}
		}

		if($this->callbackFn != null) $args[] = '"'.$this->callbackFn.'"';
		
		$args = count($args) ? ", ".implode(", ",$args) : null;
		
		$str = <<<EOB
{$options}
swfobject.embedSWF ("{$this->swfUrl}", "{$this->id}", "{$this->width}", "{$this->height}", "{$this->version}"{$args});
EOB;
		return $str;
	}
}