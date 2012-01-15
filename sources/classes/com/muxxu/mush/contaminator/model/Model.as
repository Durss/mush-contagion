package com.muxxu.mush.contaminator.model {
	import flash.net.SharedObject;
	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.structure.mvc.model.events.ModelEvent;
	import com.nurun.structure.mvc.model.IModel;
	import flash.events.EventDispatcher;
	
	/**
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class Model extends EventDispatcher implements IModel {
		private var _so:SharedObject;
		private var _playIntro:Boolean;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Model</code>.
		 */
		public function Model() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Gets if the introduction should be played or not.
		 */
		public function get playIntro():Boolean { return _playIntro; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Starts the application.
		 */
		public function start():void {
			_playIntro = _so.data["introPlayed"] == undefined;
			update();
		}
		
		/**
		 * Starts the application.
		 */
		public function throwSpores():void {
			dispatchLight(LightEvent.THROW_SPORES);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_so = SharedObject.getLocal("mushcontagion", "/");	
		}
		
		/**
		 * Fires an update to the views.
		 */
		private function update():void {
			dispatchEvent(new ModelEvent(ModelEvent.UPDATE, this));
		}
		
		/**
		 * Fires an update to the views throught ViewLocator.
		 */
		private function dispatchLight(type:String):void {
			ViewLocator.getInstance().dispatchToViews(new LightEvent(type));
		}
		
	}
}