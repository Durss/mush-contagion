<?php
/**
 * MySql Manager
 * @author nsun
 *
 */
class mysqlManager
{
	/**
	 * Répertoire des logs
	 * @var string
	 */
	public $logDir = 'dbLog/';
	/**
	 * Afficher ou consigner les messages d'erreur
	 * @var bool
	 */
	private $_debugMode = false;
	/**
	 * HOST
	 * @var string
	 */
	private $_host;
	/**
	 * USER
	 * @var string
	 */
	private $_user;
	/**
	 * PASS
	 * @var string
	 */
	private $_pass;
	/**
	 * Lien de connexion
	 * @var ressource
	 */
	private $_link = false;
	/**
	 * Si la connexion est établie
	 * @var bool
	 */
	private $_connected = false;
	/**
	 * Nom de la base
	 * @var string
	 */
	public $db;
	/**
	 * Noms des tables
	 * @var array
	 */
	public $tbl = Array();
	/**
	 * Ressource retournée à la suite d'une requ�te
	 * @var ressource
	 */
	public $result = false;
	/**
	 * Rapport d'erreurs
	 * @var array
	 */
	public $errorLog = false;
	
	/**
	 * Constructeur
	 * @param	array	$mysql_vars	-Paramètres de connexion à la DB
	 * @example	<pre>$mysql_vars = array(<br/>	'host' => 'localhost',<br/>	'user' => 'root',<br/>	'pass' => '',<br/>	'db' => 'kube_photo',<br/>	'tables' => array(<br/>		'photos' => 'photos',<br/>	),<br/>);</pre>
	 * @param	bool	$debugMode	-Afficher ou consigner les messages d'erreur
	 */
	public function mysqlManager($mysql_vars, $debugMode=false)
	{
		//init
		$this->_debugMode = (bool) $debugMode;
		$this->_host = $mysql_vars['host'];
		$this->_user = $mysql_vars['user'];
		$this->_pass = $mysql_vars['pass'];
		$this->db = $mysql_vars['db'];
		$this->tbl = $mysql_vars['tbl'];
		
		if(!is_dir($this->logDir)) mkdir($this->logDir);
	}
	
	/**
	 * Connexion à la DB
	 * @return	bool
	 */
	public function connect()
	{
		if($this->_connected) return true;
		
		//Connexion
		$this->_link = @mysql_connect($this->_host, $this->_user, $this->_pass);
		if(!$this->_link)
		{
			$this->error('Impossible de se connecter : ' . mysql_error());
			return $this->_connected = false;
		}
		//Sélection de la base
		mysql_select_db($this->db) or $this->error('Impossible de sélectionner la base de données');
		
		return $this->_connected = true;
	}
	
	/**
	 * Execution d'une requête SQL
	 * @param	string	$sql	-Requête SQL
	 * @return	ressource
	 */
	public function query($sql)
	{
		//Libération des résultats
		#$this->freeResult();
		// Exécution des requêtes SQL
		return $this->result = mysql_query($sql) or $this->error('Echec de la requête : ' . mysql_error() . "<br/> <code>{$sql}</code>");
	}
	
	/**
	 * Rapport d'erreur
	 * @param	string	$msg
	 */
	public function error($msg=false)
	{
		if(!$msg) $msg = mysql_error();
		
		$this->errorLog = array();
		$this->errorLog['no'] = mysql_errno();
		$this->errorLog['error'] = $msg;
		$this->errorLog['report'] = null;
		
		if($this->_debugMode) var_dump(mysql_errno(), $msg);
		else //Consigne l'erreur dans un fichier
		{
			$flag = '['.date('d').']['.time().']';
			$data = "{$flag}\t".strip_tags($msg)."\n";
			file_put_contents($this->logDir.date('Ym').'.txt', $data, FILE_APPEND);
			$this->errorLog['report'] = "[".date('Ym')."]{$flag}";
		}
	}
	
	/**
	 * RAZ du rapport d'erreurs
	 */
	public function errorLogReset()
	{
		$this->errorLog = false;
	}
	
	/**
	 * Libération des résultats
	 */
	public function freeResult()
	{
		if(is_resource($this->result)
		&& get_resource_type($this->result) == 'mysql result')
		{
			mysql_free_result($this->result);
		}
	}
	
	/**
	 * Destructeur
	 */
	public function __destruct()
	{
		//Libération des résultats
		$this->freeResult();
		
		// Fermeture de la connexion
		if($this->_connected
		&& is_resource($this->_link)
		&& get_resource_type($this->_link) == 'mysql link')
		{
			mysql_close($this->_link);
		}
	}
}