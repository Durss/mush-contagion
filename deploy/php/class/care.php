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
		switch($act){
			case 'healAllUsers':
				$do = 1;
				$legend = "Soigner tous les 'users'";
				$p = "Attribuer à tous les profil un niveau d'infection = 0";
				$btn = array(
					"cancel" => "Annuler",
					"confirm" => "Soigner",
				);
				break;
		}
		
		if(!$do) return false;
		$str = <<<EOHTML
		<form method="post" action="?{$_SERVER['QUERY_STRING']}">
			<input type="hidden" name="confirm{$act}" value="{$key}"/>
			<fieldset>
				<legend>{$legend}</legend>
				<p>{$p}</p>
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
		if(! isset($_POST['confirm'], $_POST["confirm{$act}"])) return false;
		$tooLate = array(
			'healAllUsers' => "Les 'users' <strong>n'ont pas été</strong> soignés</div>",
		);
		
		if($_POST["confirm{$act}"] != $key){
			return "<div class='adv'>Délai de confirmation dépassé. {$tooLate[$act]}</div>";
		}
		
		switch($act){
			case 'healAllUsers':
				return $this->healAllUsers();
				break;
		}
	}
	
	/**
	 * Soigne tous les users
	 * @return string
	 */
	public function healAllUsers(){
		$this->db->healEveryone();
		if($this->db->result) return "<div class='adv'>Tous les 'users' ont été soignés.</div>\n";
		else return "<div class='adv'>Les 'users' <strong>n'ont pas été</strong> soignés.</div>\n"."<div class='adv'>".mysql_error()."</div>\n";
	}
}
?>