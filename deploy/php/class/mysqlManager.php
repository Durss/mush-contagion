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
	const LOG_DIR = 'dbLog/';
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
		$this->tbl = $mysql_vars['tables'];
		
		if(!is_dir(self::LOG_DIR)) mkdir(self::LOG_DIR);
	}
	
	/**
	 * Connexion à la DB
	 * @return	bool
	 */
	public function connect()
	{
		if($this->_connected) return true;
		
		//Connexion
		$this->_link = mysql_connect($this->_host, $this->_user, $this->_pass)
			or $this->error('Impossible de se connecter : ' . mysql_error());
		
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
		// Exécution des requêtes SQL
		return $this->result = mysql_query($sql) or $this->error('Echec de la requête : ' . mysql_error() . "<br/> <code>{$sql}</code>");
	}
	
	/**
	 * Rapport d'erreur
	 * @param	string	$msg
	 * @param	bool	$continue	-Passé à <b>false</b> provoque l'arrêt de l'execution du script
	 */
	public function error($msg='undefined', $continue=false)
	{
		if($this->_debugMode) var_dump($msg);
		else
		{
			$flag = '['.date('d').']['.time().']';
			$data = "{$flag}\t".strip_tags($msg)."\n";
			file_put_contents(self::LOG_DIR.date('Ym').'.txt', $data);
			echo "<h1>Error</h1>\n<h3>Please report : [".date('Ym')."]{$flag}</h3>";
		}
		if(!$continue) die();
	}
	
	/**
	 * Destructeur
	 */
	public function __destruct()
	{
		// Libération des résultats
		if(is_resource($this->result)) mysql_free_result($this->result);
		
		// Fermeture de la connexion
		if($this->_link) mysql_close($this->_link);
	}
	
}