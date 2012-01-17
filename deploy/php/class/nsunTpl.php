<?php 
/**
 * Simple template
 * @author nsun
 */
class nsunTpl
{
	/**
	 * Liste les URL de chaque script JS à incorporer
	 * @var Array
	 */
	private $_scriptFile = Array();
		
	/**
	 * Scripts JS à incorporer dans <head>
	 * @var Array
	 */
	private $_script = Array();
	
	/**
	 * Liste les URL de chaque feuille de style à incorporer
	 * @var Array
	 */
	private $_styleSheet = Array();
	
	/**
	 * Meta-tags
	 * @var Array
	 */
	private $_meta = Array();
	
	/**
	 * Attributs de body
	 * @var object
	 * @example	<pre>$this->body->id = 'home';</pre>
	 * @example	<pre>$this->body->class = Array('error', 'red');</pre>
	 */
	private $_body;
	
	/**
	 * Titre de la page, attribué en en-tête (HEAD)
	 * @var string
	 */
	public $title = NULL;
	
	/**
	 * Definition du menu
	 * @var array
	 */
	public $menu = Array();
	
	/**
	 * Contenu à insérer dans le corps de la page  
	 * @var string
	 */
	public $c = NULL;
	
	/**
	 * Définis si le template a déjà été appliqué sur la page.
	 * @var bool
	 */
	public $stop = FALSE;
	
	/**
	 * Constructeur
	 */
	public function nsunTpl()
	{
		//init
		$this->body = new stdClass();
		$this->body->id = false;
		$this->body->class = Array();
	}

	/**
	 * Attribuer un id à l'élément BODY
	 * @param string $class	-nom de la classe
	 */
	public function addBodyID($id)
	{
		$this->_body->class[] = strval($id);
	}
	/**
	 * Attribuer une classe à l'élément BODY
	 * @param string $class	-nom de la classe
	 */
	public function addBodyClass($class)
	{
		$this->_body->class[] = strval($class);
	}
	
	/**
	 * Ajouter un script JS
	 * @param string $js	- javascript
	 */
	public function addScript($js)
	{
		$this->_script[] = strval($js);
	}
	/**
	 * Retourne un bloc script formaté pour une intégration dans <head>
	 * @param int $tab	- Nombre d'indentations
	 */
	public function build_scriptHeader()
	{
		if(!count($this->_scriptFile)) return NULL;
		else return "<script type='text/javascript'>\n"
		.implode("\n//---\n", $this->_script)
		."\n</script>\n";
	}
	
	/**
	 * Ajouter un fichier script JS
	 * @param string $jsFile	- URL du script
	 */
	public function addScriptFile($jsFile)
	{
		$this->_scriptFile[] = strval($jsFile);
	}
	/**
	 * Retourne la liste des script formatée pour une intégration dans <head>
	 * @param	int	$tab	- Nombre d'indentations
	 * @return	string
	 */
	public function build_scriptFileHeader($tab=0)
	{
		$t = $this->tab($tab);
		
		if(!count($this->_scriptFile)) return NULL;
		else return "{$t}<script type='text/javascript' src='"
		.implode("'></script>\n{$t}<script type='text/javascript' src='", $this->_scriptFile)
		."'></script>\n";
	}

	/**
	 * Ajouter une feuille de style
	 * @param string $cssFile	- URL du script
	 */
	public function addStyleSheet($cssFile)
	{
		$this->_styleSheet[] = strval($cssFile);
	}
	/**
	 * Retourne la liste des feuilles de style formatée pour une intégration dans <head>
	 * @param	int	$tab	- Nombre d'indentations
	 * @return	string
	 */
	public function build_styleSheetHeader($tab=0)
	{
		$t = $this->tab($tab);
		
		if(!count($this->_styleSheet)) return NULL;
		else return "{$t}<link href='"
		.implode("' rel='stylesheet' type='text/css' />\n{$t}<link href='", $this->_styleSheet)
		."' rel='stylesheet' type='text/css' />\n";
	}
	
	
	/**
	 * Ajouter un Meta-Tag
	 * @param	(string)	$name	-Attribut 'name'
	 * @param	(string)	$content	-Attribut 'content'
	 */
	public function addMetaTag($name,$content=null)
	{
		$this->_meta[strval($name)] = $content;
	}
	/**
	 * Retourne la liste des meta-tags formatée pour une intégration dans <head>
	 * @param	int	$tab
	 * @return	string
	 */
	public function build_metaTags($tab=0)
	{
		$t = $this->tab($tab);
		$str = null;
		foreach($this->_meta as $name => $content)
		{
			$str .= "{$t}<meta name='{$name}' content=\"{$content}\" />\n";
		}
		return $str;
	}
	
	/**
	 * Retourne les attributs formatés de l'élément <body>
	 * @return	string
	 */
	public function build_bodyAttributes()
	{
		//init
		$attributes = NULL;
		$id = isset($this->_body->id) ? $this->_body->id : false;
		$class = isset($this->_body->class) ? $this->_body->class : false;
		
		if($id) $attributes .= " id='{$id}'";
		
		if(is_array($class) && count($class)) $attributes .= " class='".implode(' ',$class)."'";
		elseif(is_string($class)) $attributes .= " class='{$class}'";
		
		return $attributes;
	}
	
	/**
	 * Imprime la page
	 * @todo	intégrer un menu
	 */
	public function build()
	{	
		//La page est déjà produite
		if($this->stop) return false;
		
		echo ""
		."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
		."<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
		//HEAD
		."	<head>\n"
		."		<title>{$this->title}</title>\n"		
		."		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
		//META-TAGS
		.		$this->build_metaTags(2)
		//STYLESHEETS
		.		$this->build_styleSheetHeader(2)
		//SCRIPT-FILES
		.		$this->build_scriptFileHeader(2)
		.		$this->build_scriptHeader()
		."	</head>\n"
		//BODY
		."	<body".$this->build_bodyAttributes().">\n"
		.		$this->c
		."	</body>\n"
		."</html>";
		
		$this->stop = TRUE;
	}
	
	/**
	 * Assemble un certain nombre de tabulations
	 * @param	string	$nb	-Nombre de tabulations
	 * @return	string
	 */
	public function tab($nb)
	{
		if($nb > 0) return str_repeat("\t", $nb);
		else return null;
	}
		
	/**
	 * Destructeur : En fin de script execute la méthode build()
	 */
	public function __destruct()
	{
		$this->build();
	}
}
?>