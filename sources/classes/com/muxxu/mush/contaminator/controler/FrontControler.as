package com.muxxu.mush.contaminator.controler {
	
	import com.muxxu.mush.contaminator.model.Model;
	import flash.errors.IllegalOperationError;
	
	/**
	 * Singleton FrontControler
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class FrontControler {
		private static var _instance:FrontControler;
		private var _model:Model;
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>FrontControler</code>.
		 */
		public function FrontControler(enforcer:SingletonEnforcer) {
			if(enforcer == null) {
				throw new IllegalOperationError("A singleton can't be instanciated. Use static accessor 'getInstance()'!");
			}
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Singleton instance getter.
		 */
		public static function getInstance():FrontControler {
			if(_instance == null)_instance = new  FrontControler(new SingletonEnforcer());
			return _instance;	
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Initialize the class.
		 */
		public function initialize(model:Model):void {
			_model = model;
		}
		
		/**
		 * Starts the application.
		 */
		public function throwSpores():void {
			_model.throwSpores();
		}
		
		/**
		 * Toggles the sound state
		 */
		public function toggleSound():void {
			_model.toggleSound();
		}
		
		/**
		 * Flags introduction as viewed
		 */
		public function introComplete():void {
			_model.introComplete();
		}
		
		/**
		 * Called when contamination completes
		 */
		public function contaminationComplete():void {
			_model.setContaminationComplete();
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}

internal class SingletonEnforcer{}