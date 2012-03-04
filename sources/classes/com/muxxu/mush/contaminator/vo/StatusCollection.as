package com.muxxu.mush.contaminator.vo {
	import com.nurun.structure.environnement.configuration.Config;
	import com.nurun.utils.array.ArrayUtils;
	import com.nurun.structure.mvc.vo.ValueObjectElement;
	import com.nurun.core.collection.Collection;
	import com.nurun.core.lang.vo.XMLValueObject;
	
	/**
	 * 
	 * @author Francois
	 * @date 4 mars 2012
	 */
	public class StatusCollection extends ValueObjectElement implements XMLValueObject, Collection {
		
		private var _collection:Vector.<String>;
		private var _infectedUsers:UserCollection;
		private var _usersToStatus:Array;

		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>StatusCollection</code>.
		 */
		public function StatusCollection() { }

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * @inheritDoc
		 */
		public function get length():uint {
			return _collection!=null? _collection.length : 0;
		}
		
		/**
		 * Sets the number of infected users
		 */
		public function set infectedUsers(infectedUsers:UserCollection):void { _infectedUsers = infectedUsers; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * @inheritDoc
		 */
		public function populate(xml:XML, ...optionnals:Array):void {
			var i:int, len:int, nodes:XMLList, s:String, tot:int;
			nodes = XML(xml.child("status")[0]).child("s");
			len = nodes.length();
			_collection = new Vector.<String>(len, true);
			_usersToStatus = [];
			for(i = 0; i < len; ++i) {
				s = nodes[i];
				_collection[i] = s;
				tot = /<xxx ?\/>/gi.test(s)? 1 : 0;
				tot += /<yyy ?\/>/gi.test(s)? 1 : 0;
				tot += /<zzz ?\/>/gi.test(s)? 1 : 0;
				
				if(_usersToStatus[tot] == undefined) {
					_usersToStatus[tot] = [];
				}
				(_usersToStatus[tot] as Array).push(s);
			}
		}
		
		/**
		 * Gets an item at a specific index.
		 */
		public function getStringAtIndex(index:int):String {
			return _collection[index];
		}
		
		/**
		 * Gets a random status.
		 */
		public function getRandomStatus():String {
			var status:String = ArrayUtils.getRandom(_usersToStatus[_infectedUsers.length]);
			var replacements:Array = ["xxx", "yyy", "zzz"];
			var i:int, len:int, url:String, user:User;
			len = _infectedUsers.length;
			for(i = 0; i < len; ++i) {
				user = _infectedUsers.getUserAtIndex(i);
				url = Config.getPath("userProfile");
				url = url.replace(/\{UID\}/gi, user.uid);
				status = status.replace(new RegExp("<"+replacements[i]+" ?/>", "gi"), "[lien="+url+"']"+user.name+"[/lien]");
			}
			
			return status.replace(/\r\n/gi, "\n").replace(/<link ?\/>/gi, Config.getPath("appURL"));
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}