<?php
class care{
	/**
	 * Gestionnaire SQL
	 * @var mushSQL
	 */
	public $db;
	
	/**
	 * Constructeur
	 */
	public function __construct(){	}
	
	/**
	 * Production d'une clé périssable
	 * @param string $act	-Ref de l'action à vérifier
	 * @return string
	 */
	private function _key($act){
		return md5($act.date('YmdH').floor(date('i')/12).'zobiiii la mouche');
	}
	
	/**
	 * Retourne un formulaire de confirmation
	 * @param string $act	-Ref de l'action à vérifier
	 * @return string
	 */
	public function confirm($act){
		$key = $this->_key($act);
		$do = 0;
		$details = null;
		switch($act){
			case 'healAllUsers':
				$do = 1;
				$legend = "Soigner tous les 'users'";
				$details = "<p>Attribuer à tous les profil un niveau d'infection = 0</p>";
				$btn = array(
					"cancel" => "Annuler",
					"confirm" => "Soigner",
				);
				break;
			case 'setHealSomeUsers':
				$do = 1;
				$legend = "Soigner au pifomètre";
				$details = null;
				$valid = 'valid';
				//Examen des critères
				if(isset($_POST['osef'])){
					switch(rand(1,3)){
						case 1: $_POST['setQty'] = 'on';
						case 2:
							$_POST['setFrame'] = 'on';
							break;
						case 3: $_POST['setQty'] = 'on';
					}
				}
				if(isset($_POST['setQty'],$_POST['qty']) && is_numeric($_POST['qty']) && $_POST['qty'] > 0){
					$details .= "<p>{$_POST['qty']} personne(s)"
					."<input type='hidden' name='qty' value='{$_POST['qty']}'/></p>";
					$valid .= "|qty:{$_POST['qty']}";	
				}
				if(isset($_POST['setFrame'], $_POST['compare'], $_POST['ceil'])
				&& in_array($_POST['compare'],array(1,2,3))
				&& is_numeric($_POST['ceil']) && $_POST['ceil'] > 0){
					$compare = array(1 => "égal à", 2 => "supérieur ou égal à", 3 => "inférieur ou égal à");
					$details .= "<p>Dont le seuil d'infection est {$compare[$_POST['compare']]} {$_POST['ceil']}"
					."<input type='hidden' name='compare' value='{$_POST['compare']}'/>"
					."<input type='hidden' name='ceil' value='{$_POST['ceil']}'/></p>";
					$valid = "|compare:{$_POST['compare']}|ceil:{$_POST['ceil']}";
				}
				if(isset($_POST['setActif'], $_POST['actif'])
				&& in_array($_POST['actif'], array('yes','no'))){
					$details .= $_POST['actif'] == 'no' ? "<p>Ceux étant passif dans cette contagion</p>" : "<p>Ceux étant actif dans cette contagion</p>";
					$details .= "<input type='hidden' name='actif' value='{$_POST['actif']}'/></p>";
					$valid = "|actif:{$_POST['actif']}";
				}
				$valid = $this->_key($valid);
				$details .= "<input type='hidden' name='valid' value='{$valid}'/>";
				
				$btn = array(
					"cancel" => "Annuler",
					"confirm" => "Soigner",
				);
				break;
			case 'setToubibs':
			case 'setTargetUsers':
				//Lister les valeurs
				$keys = array_keys($_POST);
				$list = array();
				foreach($keys as $check){
					if(preg_match('#^check_([1-9][0-9]*)$#', $check, $matche)){
						$list[intval($_POST[$check])] = $_POST["name_{$matche[1]}"];
					}
				}
				$r_list = ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') ? addslashes(serialize($list)) : serialize($list);
				$details = "<input type='hidden' name='action' value='{$_POST['action']}'/>"
				."<p>".implode(', ',$list)."</p>"
				."<input type='hidden' name='list' value='{$r_list}'/>";
				
				switch($act){
					case 'setToubibs':
						$legend = "Gestion des effectifs : Confirmer l'action";
						$do = in_array($_POST['action'],array(1,2)) ? count($list) : false;
						$action = ($_POST['action'] == 1) ? "Attribuer le rang de soigneur" : "Destituer du rang de soigneur";
						break;						
					case 'setTargetUsers':
						$legend = "Soins ciblés : Confirmer l'action";
						$do = in_array($_POST['action'],array(1,2,3)) ? count($list) : false;
						$action = array(
							1 => "Soigner les profils sélectionnés",
							2 => "Soigner les profils et leurs enfants",
							3 => "Soigner uniquement  les enfants des profils sélectionnés",
						);
						$action = $action[intval($_POST['action'])];
						break;
				}
				
				$btn = array(
					"cancel" => "Annuler",
					"confirm" => $action,
				);
				
				break;
		}
		
		if(!$do) return false;
		$str = <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<input type="hidden" name="confirm_{$act}" value="{$key}"/>
			<fieldset>
				<legend>{$legend}</legend>
				{$details}
EOHTML;
		foreach($btn as $name => $value){
			$str .= "<input type='submit' name='{$name}' value='{$value}' />";
		}
		return $str."</fieldset></form>";
	}
	
	/**
	 * Contrôle une confirmation
	 * @param string $act	-Ref de l'action à vérifier
	 * @return string
	 */
	public function control($act){
		$key = $this->_key($act);
		if(! isset($_POST['confirm'], $_POST["confirm_{$act}"])) return false;
		$tooLate = array(
			'healAllUsers' => "Les 'users' <u>n'ont pas été</u> soignés</div>",
			'setTargetUsers' => "Les 'users' sélectionnés <u>n'ont pas été</u> soignés</div>",
		);
		
		if($_POST["confirm_{$act}"] != $key){
			$add = isset($tooLate[$act]) ? $tooLate[$act] : null;
			return "<div class='adv'>Délai de confirmation dépassé. {$add}</div>";
		}
		
		switch($act){
			case 'healAllUsers':
				return $this->healAllUsers();
				break;
			case 'setToubibs':
				return $this->setToubibs(func_get_arg(1));
				break;
			case 'setHealSomeUsers':
				return $this->setHealSomeUsers();
				break;
			case 'setTargetUsers':
				$adv = null;
				switch($_POST['action']){
					//Soigner les enfants
					case 3: case 2:
						$adv .= $this->healChildren();
						if($_POST['action'] == 3) break;
					//soigner les profils
					case 1:
						$adv .= $this->healUsers();
						break;
				}
				return $adv;
				break;
		}
		return false;
	}

	/**
	 * Soigne tous les users
	 * @return string
	 */
	public function healAllUsers(){
		$this->db->healEveryone();
		if($this->db->result) return "<div class='adv'>Tous les 'users' ont été soignés.</div>\n";
		else return "<div class='adv'>Les 'users' <u>n'ont pas été</u> soignés.</div>\n"."<div class='adv'>".mysql_error()."</div>\n";
	}
	
	/**
	 * Soigne une sélection users
	 * @return string
	 */
	public function healUsers(){
		$list = unserialize(stripslashes($_POST['list']));
		$listUid = array_keys($list);
		$this->db->healSelectUsers($listUid);
		if($this->db->result) return "<div class='adv'>".implode(", ",$list)." ont été soignés.</div>\n";
		else return "<div class='adv'>".implode(", ",$list)." <u>n'ont pas été</u> soignés.</div>\n"."<div class='adv'>".mysql_error()."</div>\n";
	}
	
	/**
	 * Soigne les enfants d'une sélection users
	 * @return string
	 */
	public function healChildren(){
		$ini = parse_ini_file('params.ini',1);
		$list = unserialize(stripslashes($_POST['list']));
		//Recherche des enfants
		$childsList = array(); 
		foreach ($list as $uid => $name){
			$this->db->selectChilds($uid);
			while($row = mysql_fetch_assoc($this->db->result)){
				$childsList[intval($row['child'])] = intval($row['child']);
			}
		}
		//Découpage des enfants en tranches
		$chunk = array_chunk($childsList, intval($ini['params']['queryLimit']));
		foreach($chunk as $listUid) $this->db->healSelectUsers($listUid);
		if($this->db->result) return "<div class='adv'>Tous les efants de ".implode(", ",$list)." ont été soignés.</div>\n";
		else return "<div class='adv'>Les enfants de ".implode(", ",$list)." <u>n'ont pas été</u> soignés.</div>\n"."<div class='adv'>".mysql_error()."</div>\n";
	}
	
	/**
	 * Soigne au pifomètre
	 */
	public function setHealSomeUsers(){
		//init
		$qty = false;
		$ceil = false;
		$compare = false;
		$pubkey = false;
		$valid = 'valid';
		if(isset($_POST['qty'])){
			$qty = intval($_POST['qty']);
			$valid .= "|qty:{$_POST['qty']}";
		}
		if(isset($_POST['compare'],$_POST['ceil'])){
			$compare = intval($_POST['compare']);
			$ceil = intval($_POST['ceil']);
			$valid = "|compare:{$_POST['compare']}|ceil:{$_POST['ceil']}";
		}
		if(isset($_POST['actif'])){
			$pubkey = $_POST['actif'] == 'no' ? "IS NULL" : "IS NOT NULL";
			$valid = "|actif:{$_POST['actif']}";
		}
		//vérifier l'intégrité des critères
		if(!$valid == $this->_key($_POST['valid'])) return "<div class='adv'>Le délais de confirmation est dépassé</div>";
		
		$this->db->healAs($qty,$ceil,$compare,$pubkey);
		if($this->db->result) return "<div class='adv'>Les soins ont été donnés selon vos ordres.</div>\n";
		else return "<div class='adv'>Les soins n'ont pas pu être prodigués..</div>\n"."<div class='adv'>".mysql_error()."</div>\n";
	}
	
	/**
	 * Update du fichier soigneurs.ini
	 * @return string
	 */
	public function setToubibs($toubibs){
		$do = 0;
		$list = unserialize(stripslashes($_POST['list']));
		switch($_POST['action']){
			case 2:	//Destituer
				foreach($list as $uid => $name){
					if(!in_array($uid,array(89,3916)) && isset($toubibs[$name]) && $toubibs[$name] == $uid){
						$toubibs[$name] = false;
						unset($toubibs[$name]);
						$do = 1;
						$msg = "retirés";
					}
				}
				break;
			case 1:	//Devenir soigneur
				foreach($list as $uid => $name){
					if(!(isset($toubibs[$name]) && $toubibs[$name] == $uid)){
						$toubibs[$name] = $uid;
						$do = 1;
						$msg = "ajoutés";
					}
				}
				break;
		}
		
		if($do){
			$list = array();
			$str = ";Les gens ayant accès au module de soin alpha\n"
			.";\tedit: ".date('Y-m-d H:i:s').' par '.UID."\n";
			foreach($toubibs as $name => $uid) $list[] = "{$name}={$uid};";
			file_put_contents('soigneurs.ini', $str.implode("\n",$list));
			return "<div class='adv'>Les soigneurs sélectionnés ont été {$msg}.</div>\n";
		}
		else return "<div class='adv'>Aucun changement d'effectif n'a été enregistré.</div>\n";
	}
}
?>