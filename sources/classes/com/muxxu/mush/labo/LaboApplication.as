package com.muxxu.mush.labo {
	import flash.utils.ByteArray;
	import flash.events.IOErrorEvent;
	import com.muxxu.mush.graphics.LoaderSpinningGraphic;

	import flash.display.DisplayObject;
	import flash.display.Loader;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.filters.DropShadowFilter;
	import flash.system.ApplicationDomain;
	import flash.system.LoaderContext;
	import flash.utils.setTimeout;

	/**
	 * Bootstrap class of the application.
	 * Must be set as the main class for the flex sdk compiler
	 * but actually the real bootstrap class will be the factoryClass
	 * designated in the metadata instruction.
	 * 
	 * @author Francois
	 * @date 19 sept. 2012;
	 */
	 
	[SWF(width="670", height="377", backgroundColor="0xFFFFFF", frameRate="31")]
	public class LaboApplication extends MovieClip {
		
		[Embed(source="../../../../../fla/labo.swf", mimeType="application/octet-stream")]
		private var _embed:Class;
		
		private var _spin:DisplayObject;
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function LaboApplication() {
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

		private function initialize():void {
			var loader:Loader = new Loader();
			_spin = addChild(new LoaderSpinningGraphic());
			_spin.filters = [new DropShadowFilter(0,0,0,.25,5,5,1,3)];
			addChild(loader);
			var context:LoaderContext = new LoaderContext(false, ApplicationDomain.currentDomain);
			loader.contentLoaderInfo.addEventListener(Event.COMPLETE, loadCompleteHandler);
			loader.contentLoaderInfo.addEventListener(IOErrorEvent.IO_ERROR, loadErrorHandler);
			stage.addEventListener(Event.RESIZE, computePositions);
			computePositions();
			var ba:ByteArray = new _embed();
			setTimeout(loader.loadBytes, 500, ba, context);
		}

		private function loadCompleteHandler(event:Event):void {
		}

		private function loadErrorHandler(event:IOErrorEvent):void {
		}
		
		/**
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			_spin.x = stage.stageWidth * .5;
			_spin.y = stage.stageHeight * .5;
		}
		
	}
}