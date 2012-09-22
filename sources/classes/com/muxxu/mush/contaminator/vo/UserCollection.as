package com.muxxu.mush.contaminator.vo {
	import com.nurun.structure.environnement.configuration.Config;
	import com.nurun.structure.mvc.vo.ValueObjectElement;
	import com.nurun.core.collection.Collection;
	import com.nurun.core.lang.vo.XMLValueObject;
	
	/**
	 * 
	 * @author Francois
	 * @date 29 janv. 2012
	 */
	public class UserCollection extends ValueObjectElement implements XMLValueObject, Collection {
		
		private var _collection:Vector.<User>;
		private var _transformedUser:Vector.<User>;

		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>UserCollection</code>.
		 */
		public function UserCollection() { }

		
		
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
		 * Gets the total of transformed people. The one that actually become mush
		 */
		public function get transformedUsers():Vector.<User> {
			return _transformedUser;
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * @inheritDoc
		 */
		public function populate(xml:XML, ...optionnals:Array):void {
			var i:int, len:int, nodes:XMLList;
			nodes = xml.child("user");
			len = nodes.length();
			_collection = new Vector.<User>(len, true);
			_transformedUser = new Vector.<User>();
			var total:int = Config.getNumVariable("ceil");
			for(i = 0; i < len; ++i) {
				_collection[i] = new User();
				_collection[i].populate(nodes[i]);
				if (_collection[i].infectionLevel == total - 1) {
					_transformedUser.push( _collection[i] );
				}
			}
		}
		
		/**
		 * Gets an item at a specific index.
		 */
		public function getUserAtIndex(index:int):User {
			return _collection[index];
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}