package com.muxxu.mush.contaminator.vo {
	import com.nurun.core.collection.Element;
	import com.nurun.core.lang.boolean.parseBoolean;
	import com.nurun.core.lang.vo.XMLValueObject;
	import com.nurun.structure.environnement.configuration.Config;
	import com.nurun.structure.mvc.vo.ValueObjectElement;
	
	/**
	 * 
	 * @author Francois
	 * @date 29 janv. 2012;
	 */
	public class User extends ValueObjectElement implements Element, XMLValueObject {
		
		private var _uid:String;
		private var _isFriend:Boolean;
		private var _name:String;
		private var _avatar:String;
		private var _profileURL:String;
		private var _infectionLevel:Number;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>User</code>.
		 */
		public function User() { }

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */

		public function get uid():String {
			return _uid;
		}

		public function get isFriend():Boolean {
			return _isFriend;
		}

		public function get name():String {
			return _name;
		}

		public function get avatar():String {
			return _avatar;
		}

		public function get profileURL():String {
			return _profileURL;
		}

		public function get infectionLevel():Number {
			return _infectionLevel;
		}
		 



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * @inheritDoc
		 */
		public function populate(xml:XML, ...optionnals:Array):void {
			_uid = xml.@uid;
			_infectionLevel = parseInt(xml.@level);
			_isFriend = parseBoolean(xml.@isFriend);
			_name = xml.child("name")[0];
			_avatar = xml.child("avatar")[0];
			var url:String = Config.getPath("userProfileTwino");
			url = url.replace(/\{UID\}/gi, _uid);
			_profileURL = url;
		}
		
		/**
		 * Gets a string representation of the value object.
		 */
		public function toString():String {
			return "[User :: uid='" + uid + "', name='" + name + "']";
		}

		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}