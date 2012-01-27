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
		$this->connect();
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
		$time = time();
		$sql = <<<EOSQL
-- Nouvel user ou MAJ
INSERT INTO `{$this->db}`.`{$this->tbl['user']}`
(`uid`, `pubkey`, `friends`, `name`, `lastvisit`, `avatar`) VALUES
('{$uid}', '{$pubkey}', '{$friendsKey}', '{$name}', '{$time}', '{$avatar}')
-- Si l'utilisateur est déjà enregistré, mon met à jour toutes les données
ON DUPLICATE KEY UPDATE
`pubkey` = '{$pubkey}', `friends` = '{$friendsKey}', `name` = '{$name}', `lastvisit` = '{$time}', `avatar` = '{$avatar}';
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
		$time = time();
		
		$list = "('{$parent}', '"
		.implode("', '{$time}'),\n('{$parent}', '", $list)
		."', '{$time}')";
		
		$sql = <<<EOSQL
-- Origine de l'infection
INSERT INTO `{$this->db}`.`{$this->tbl['link']}`
(`parent`, `child`, `date`) VALUES
{$list};
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
	 * Sélectionne des profils dans la table 'user'
	 * @param	bool	$in				-Comment traiter la liste d'utilisateur indiquée : <code>true => IN (list); false => NOT IN (list)</code>
	 * @param	array	$list			-Liste d'utilisateur : <code>array( int id, int id, ... )</code> 
	 * @param	string		$limit		-Limitation du retour : <code>'3', '0,1',...</code>
	 * @param	int		$infection		-Seuil d'infection (inférieur ou égal)
	 * @param	bool	$rand			-Ajouter un paramètre aléatoire dans la requête (inutile quand l'aléa est déjà effectué par PHP en amont)
	 * @param	bool	$lastvisit		-Si la date de la dernière visite est requise
	 * @return	ressource
	 */
	public function selectUsers($in, $list, $limit=false, $infection=false, $rand=false, $lastvisit=false)
	{		
		if(count($list) > 1)
		{
			$in = ($in) ? 'IN' : 'NOT IN';
			$target = "{$in} (".implode(', ', $list).")";
		}
		else
		{
			$equal = ($in) ? '=' : '!=';
			$target = "{$equal} ".current($list);
		}
		
		if($limit === false) $limit = null;
		else $limit = "\n"."LIMIT {$limit}";
		
		if($infection === false) $addcomment = $infection = null;
		else
		{
			$addcomment = "en dessous du seuil d'infection";
			$infection = "\n"."AND `infected` <= {$infection}";
		}
		
		if($rand) $rand = "\n"."ORDER BY RAND()";
		else $rand = null;
		
		if(!$lastvisit) $lastvisit = null;
		else $lastvisit = ', `lastvisit`';
		
		$sql = <<<EOSQL
-- Sélection de profils {$addcomment}
SELECT `uid`, `name`, `avatar`, `infected`{$lastvisit}
FROM `{$this->tbl['user']}`
WHERE `uid` {$target}{$infection}{$rand}{$limit};
EOSQL;
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
ORDER BY L.`index` ASC;
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
ORDER BY L.`index` ASC;
EOSQL;
		
		return $this->query($sql) or $this->error(mysql_error());
	}
}