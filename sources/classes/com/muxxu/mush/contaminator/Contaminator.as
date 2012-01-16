package com.muxxu.mush.contaminator {
	import net.hires.debug.Stats;
	import com.muxxu.mush.contaminator.views.SoundView;
	import com.muxxu.mush.contaminator.views.MushroomView;
	import com.muxxu.mush.contaminator.controler.FrontControler;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.contaminator.views.BackgroundView;
	import com.nurun.structure.mvc.views.ViewLocator;

	import flash.display.MovieClip;
	import flash.events.Event;

	/**
	 * Bootstrap class of the application.
	 * Must be set as the main class for the flex sdk compiler
	 * but actually the real bootstrap class will be the factoryClass
	 * designated in the metadata instruction.
	 * 
	 * @author Francois
	 * @date 7 janv. 2012;
	 */
	 
	[SWF(width="870", height="560", backgroundColor="0x000000", frameRate="31")]
	[Frame(factoryClass="com.muxxu.mush.contaminator.ContaminatorLoader")]
	public class Contaminator extends MovieClip {
		private var _model:Model;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Contaminator</code>.
		 */
		public function Contaminator() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_model = new Model();
			ViewLocator.getInstance().initialise(_model);
			FrontControler.getInstance().initialize(_model);
			
			addChild(new BackgroundView());
			addChild(new MushroomView());
			addChild(new SoundView());
			
			addChild(new Stats());
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			
			_model.start();
		}
		
	}
}