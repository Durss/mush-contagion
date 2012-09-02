<?php
class mushSQL extends mysqlManager
{
	/**
	 * Constructeur
	 * @param	array	$mysql_vars	-Paramètres de la DB et ses tables
	 * @param	bool	$debugMode	-<code>true</code> : afficher les erreurs sur la page. <code>false</code> : consigner les erreurs dans un fichier privé.
	 */
	public function mushSQL($mysql_vars, $debugMode=false)
	{
		$this->mysqlManager($mysql_vars, $debugMode);
		return $this->connect();
	}
	
	/**
	 * <p>Insert, complète ou met à jour les infos sur l'utilisateur dans la table 'user'</p>
	 * <p>--Si l'utilisateur est déjà enregistré, mon met à jour toutes les données</p>
	 * @param	int			$uid		-N°identifiant utilisateur
	 * @param	string		$pubkey		-Clé publique de l'utilisateur
	 * @param	string		$friendsKey	-Clé du flux friends de l'utilisateur
	 * @param	string		$name		-Nom de l'utilisateur
	 * @param	string		$avatar		-URL de l'avatar de l'utilisateur
	 * @return	bool
	 */
	public function insertUser($uid, $pubkey, $friendsKey, $name, $avatar)
	{
		$sql = <<<EOSQL
-- Nouvel user ou MAJ
INSERT INTO `{$this->db}`.`{$this->tbl['user']}`
(`uid`, `pubkey`, `friends`, `name`, `avatar`) VALUES
('{$uid}', '{$pubkey}', '{$friendsKey}', '{$name}', '{$avatar}')
-- Si l'utilisateur est déjà enregistré, mon met à jour toutes les données
ON DUPLICATE KEY UPDATE
`pubkey` = '{$pubkey}', `friends` = '{$friendsKey}', `name` = '{$name}', `avatar` = '{$avatar}';
EOSQL;
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Insert ou Update de chaque utilisateur ami dans la table 'user'
	 * @param	array	$friendsList	-Liste des amis selon le modèle <code>array( int $id => string $name)</code>
	 * @return	bool
	 */
	public function insertFriends($friendsList)
	{
		//init
		$update = Array();
		foreach($friendsList as $id => $name) $update[] = "('{$id}', '{$name}')";
		$friends = implode(",\n", $update);
		
		$sql = <<<EOSQL
-- Ajout des amis
INSERT INTO `{$this->db}`.`{$this->tbl['user']}`
(`uid`, `name`) VALUES
{$friends}
-- Si l'utilisateur est déjà enregistré, on ne met à jour que son pseudo
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`);
EOSQL;
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Enregistre un lien infectieux dans la table 'link'
	 * @param	array	$list	-Liste d'identifiants des personnes infectées : <code>array( int id, int id, ... )</code> 
	 * @param	int		$parent -N°identifiant du parent de l'infection (0 par défaut, s'il s'agit d'une auto-infection)
	 * @return	bool
	 */
	public function insertLink($list, $parent=0)
	{
		$list = "('{$parent}', '"
		.implode("'),\n('{$parent}', '", $list)."')";
		
		$sql = <<<EOSQL
-- Origine de l'infection
INSERT INTO `{$this->db}`.`{$this->tbl['link']}`
(`parent`, `child`) VALUES
{$list};
EOSQL;
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Enregistre un relevé statistique dans la table 'stat'
	 * @param	int	$realUsers	-Nombre réel de visiteurs 
	 * @param	int	$users		-Nombre de profils enregistrés
	 * @param	int	$infect		-Nombre de personnes infectées
	 * @return	bool
	 */
	public function insertStat($realUsers, $users, $infect)
	{		
		$sql = <<<EOSQL
-- Relevé statistique
INSERT INTO `{$this->db}`.`{$this->tbl['stat']}`
(`realUsers`, `users`, `infect`) VALUES
({$realUsers}, {$users}, {$infect});
EOSQL;
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Infection de l'utilisateur, mis à jour dans la table 'user'
	 * @param	array	$list	-Liste des identifiants des personnes infectées : <code>array( int id, int id, ... )</code>
	 * @return	bool
	 */
	public function updateInfection($list)
	{
		if(count($list) > 1) $target = "IN (".implode(", ", $list).")";
		else $target = "= ".current($list);
		
		$sql = <<<EOSQL
-- Infection de l'utilisateur
UPDATE  `{$this->db}`.`{$this->tbl['user']}`
SET  `infected` = infected+1
WHERE  `{$this->tbl['user']}`.`uid` {$target};
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Recherche un ou plusieurs profils dans la table 'user'
	 * @param	int	$uid				-N°identifiant utilisateur (passer à false pour ignorer)
	 * @param	string	$pseudo			-Nom de l'utilisateur
	 * @return	ressource
	 */
	public function findUsers($uid,$pseudo=false)
	{
		if($pseudo && $_SERVER['REMOTE_ADDR'] == '127.0.0.1') $pseudo = mysql_real_escape_string($pseudo);
		
		if($uid && $pseudo) $where = "WHERE uid={$uid} OR name='{$pseudo}'";
		elseif($uid) $where = "WHERE uid={$uid}";
		elseif($pseudo) $where = "WHERE name='{$pseudo}'";
		else return false;
		
		$sql = <<<EOSQL
-- Sélection un profil
SELECT `id`,`uid`,`pubkey`,`friends`,`name`,`lastvisit`,`avatar`,`infected`
FROM `{$this->tbl['user']}`
{$where}
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Sélectionne des profils dans la table 'user'
	 * @param	bool	$in				-Comment traiter la liste d'utilisateur indiquée : <code>true => IN (list); false => NOT IN (list)</code>
	 * @param	array	$list			-Liste d'utilisateur : <code>array( int id, int id, ... )</code> 
	 * @param	string		$limit		-Limitation du retour : <code>'3', '0,1',...</code>
	 * @param	int		$infection		-Seuil d'infection (inférieur ou égal)
	 * @param	bool	$rand			-Ajouter un paramètre aléatoire dans la requête (inutile quand l'aléa est déjà effectué par PHP en amont)
	 * @param	bool	$lastvisit		-Si la date de la dernière visite est requise
	 * @return	ressource
	 */
	public function selectUsers($in, $list=false, $limit=false, $infection=false, $rand=false, $lastvisit=false)
	{		
		if(count($list) > 1)
		{
			$in = ($in) ? 'IN' : 'NOT IN';
			$target = "`uid` {$in} (".implode(', ', $list).")";
		}
		elseif(is_array($list) && count($list) == 1)
		{
			$equal = ($in) ? '=' : '!=';
			$target = "`uid` {$equal} ".current($list);
		}
		else $target = false;
		
		if($limit === false) $limit = null;
		else $limit = "\n"."LIMIT {$limit}";
		
		if($infection === false) $addcomment = $infection = null;
		else
		{
			$addcomment = "en dessous du seuil d'infection";
			$infection = "`infected` < {$infection}";
		}
		
		if($target && $infection) $and = "\nAND ";
		else $and = null;

		if($target || $infection) $where = "WHERE ";
		else $where = null;
		
		if($rand) $rand = "\n"."ORDER BY RAND()";
		else $rand = null;
		
		if(!$lastvisit) $lastvisit = null;
		else $lastvisit = ', `lastvisit`';
		
		$sql = <<<EOSQL
-- Sélection de profils {$addcomment}
SELECT `uid`, `name`, `avatar`, `infected`{$lastvisit}
FROM `{$this->tbl['user']}`
{$where}{$infection}{$and}{$target}{$rand}{$limit};
EOSQL;
		
		#DEBUG
		#file_put_contents('query.sql', $sql."\n\n", FILE_APPEND);
		
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Sélectionne les profils parents de l'infection d'un utilisateur
	 * @param	int	$uid	-N°identifiant de l'enfant
	 * @return	ressource
	 */
	public function selectParents($uid)
	{
		$sql = <<<EOSQL
-- Look for parents
SELECT L.`parent`, L.`date`, U.`name`, U.`avatar`
FROM `{$this->tbl['link']}` L, `{$this->tbl['user']}` U
WHERE L.`child` = {$uid}
AND L.`parent` = U.`uid`
ORDER BY L.`id` ASC;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Sélectionne les profils enfants, infectés par un utilisateur
	 * @param	int	$uid	-N°identifiant du parent
	 * @return	ressource
	 */
	public function selectChilds($uid)
	{
		$sql = <<<EOSQL
-- Look for childs
SELECT L.`child`, L.`date`, U.`name`, U.`avatar`
FROM `{$this->tbl['link']}` L, `{$this->tbl['user']}` U
WHERE L.`parent` = {$uid}
AND L.`child` = U.`uid`
ORDER BY L.`id` ASC;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Vider la table 'users'
	 * @return	ressource
	 */
	public function truncateUsers()
	{
		$sql = <<<EOSQL
-- Vider la table 'users'
TRUNCATE TABLE `{$this->tbl['user']}`;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Vider la table 'links'
	 * @return	ressource
	 */
	public function truncateLinks()
	{
		$sql = <<<EOSQL
-- Vider la table 'link'
TRUNCATE TABLE `{$this->tbl['link']}`;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Soigner tout le monde
	 * @return	ressource
	 */
	public function healEveryone()
	{
		$sql = <<<EOSQL
-- Soigner tous le monde
UPDATE `{$this->db}`.`{$this->tbl['user']}`
SET `infected` = '0'
WHERE `{$this->tbl['user']}`.`infected` > 0;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Nombre de profils enregistrés
	 * @return	ressource
	 */
	public function countUsers()
	{
		$sql = <<<EOSQL
-- Dernier ID de la table 'user'
SELECT `id` as 'countUsers' FROM `{$this->tbl['user']}` ORDER BY `id` DESC LIMIT 1;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
		
	/**
	 * Nombre de visiteurs
	 * @return	ressource
	 */
	public function countRealUsers()
	{
		$sql = <<<EOSQL
-- Nombre de visiteurs
SELECT count(`id`) as 'countRealUsers' FROM `{$this->tbl['user']}` WHERE `pubkey` != 'NULL';
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Nombre de personnes infectées
	 * @return	ressource
	 */
	public function countInfectedUsers()
	{
		$sql = <<<EOSQL
-- Nombre de personnes infectées
SELECT count(`id`) as 'countInfectedUsers' FROM `{$this->tbl['user']}` WHERE `infected` > 0;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	//Nombre d'users
	//SELECT `id` FROM `mushteasing_user` ORDER BY `mushteasing_user`.`id`  DESC LIMIT 1
		
	/**
	 * Stats générales des tables
	 * @return	ressource
	 */
	public function tableStatus()
	{
		$tables = "('".implode("', '",$this->tbl)."')";
		
		$sql = <<<EOSQL
-- Stats des tables
SHOW TABLE STATUS
FROM `{$this->db}`
WHERE Name IN {$tables};
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
	
	/**
	 * Date de la dernière infection menée par un utilisateur
	 * @param	int	$uid	-N°identifiant de l'utilisateur
	 * @return	ressource
	 */
	public function selectLastInfection($uid)
	{
		$sql = <<<EOSQL
-- Date de la dernière infection menée par un utilisateur
SELECT `date`
FROM `{$this->tbl['link']}`
WHERE `parent` = {$uid}
ORDER BY `{$this->tbl['link']}`.`id` DESC
LIMIT 1
EOSQL;
	
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Récupération des statistiques
	 * @param	int	$start	-(default=0) début du relevé
	 * @param	int	$lenght	-(default=0) longueur du relevé
	 * @return	ressource
	 */
	public function selectStats($start=0, $lenght=0)
	{
		if(!$start && !$lenght) $limit = null;
		else $limit = "\nLIMIT {$start},{$lenght}";
		
		$sql = <<<EOSQL
-- Relevé des stats
SELECT `date`, realUsers`, `users`, `infect`
FROM `{$this->tbl['stat']}`
ORDER BY `{$this->tbl['stat']}`.`id` ASC{$limit};
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}

	/**
	 * Date de la dernière stat enregistrée
	 * @return	ressource
	 */
	public function selectLastStatDate()
	{
		$sql = <<<EOSQL
-- Date de la dernière stat enregistrée
SELECT `date`
FROM `{$this->tbl['stat']}`
ORDER BY `{$this->tbl['stat']}`.`id` DESC
LIMIT 1;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
}
?>