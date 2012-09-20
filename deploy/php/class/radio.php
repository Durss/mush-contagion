<?php
class radio{
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
			case 'setRadio':
				//Lister les valeurs
				$keys = array_keys($_POST);
				$list = array();
				foreach($keys as $check){
					if(preg_match('#^check_([1-9][0-9]*)$#', $check, $matche)){
						$list[intval($_POST[$check])] = $_POST["name_{$matche[1]}"];
					}
				}
				$details = "<input type='hidden' name='action' value='{$_POST['action']}'/>"
				."<p>".implode(', ',$list)."</p>"
				."<input type='hidden' name='list' value='".addslashes(serialize($list))."'/>";
				
				$legend = "Gestion des effectifs : Confirmer l'action";
				$do = in_array($_POST['action'],array(1,2)) ? count($list) : false;
				$action = ($_POST['action'] == 1) ? "Attribuer le rang d&#39;opérateur Radio" : "Destituer d&#39;opérateur Radio";
				
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
		
		if($_POST["confirm_{$act}"] != $key){
			return "<div class='adv'>Délai de confirmation dépassé.</div>";
		}
		
		switch($act){
			case 'setRadio':
				return $this->setRadio(func_get_arg(1));
				break;
		}
		return false;
	}
	
	/**
	 * Update du fichier soigneurs.ini
	 * @return string
	 */
	public function setRadio($op){
		$do = 0;
		$list = unserialize(stripslashes($_POST['list']));
		switch($_POST['action']){
			case 2:	//Destituer
				foreach($list as $uid => $name){
					if(!in_array($uid,array(89,3916)) && isset($op[$name]) && $op[$name] == $uid){
						$op[$name] = false;
						unset($op[$name]);
						$do = 1;
						$msg = "retirés";
					}
				}
				break;
			case 1:	//Devenir opérateur radio
				foreach($list as $uid => $name){
					if(!(isset($op[$name]) && $op[$name] == $uid)){
						$op[$name] = $uid;
						$do = 1;
						$msg = "ajoutés";
					}
				}
				break;
		}
		
		if($do){
			$list = array();
			$str = ";Les gens ayant accès au module d'émission radio\n"
			.";\tedit: ".date('Y-m-d H:i:s').' par '.UID."\n";
			foreach($op as $name => $uid) $list[] = "{$name}={$uid};";
			file_put_contents('radio.ini', $str.implode("\n",$list));
			return "<div class='adv'>Les opérateurs radio sélectionnés ont été {$msg}.</div>\n";
		}
		else return "<div class='adv'>Aucun changement d'effectif n'a été enregistré.</div>\n";
	}
}
?>