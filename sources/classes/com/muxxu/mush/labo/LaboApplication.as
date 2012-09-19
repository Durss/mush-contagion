package com.muxxu.mush.labo {
	import flash.system.ApplicationDomain;
	import flash.system.LoaderContext;
	import flash.display.Loader;
	import flash.display.MovieClip;

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
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function LaboApplication() {
			var loader:Loader = new Loader();
			addChild(loader);
			loader.loadBytes(new _embed(), new LoaderContext(false, ApplicationDomain.currentDomain));
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
		
	}
}