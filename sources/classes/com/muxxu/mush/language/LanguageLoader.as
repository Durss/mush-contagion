package com.muxxu.mush.language {
	import com.muxxu.mush.avatar.crypto.MushCryptoNew;
	import flash.external.ExternalInterface;
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
	 * @date 16 sept. 2012;
	 */
	 
	[SWF(width="800", height="600", backgroundColor="0xFFFFFF", frameRate="31")]
	public class LanguageLoader extends MovieClip {
		
		[Embed(source="../../../../../assets/language.swf", mimeType="application/octet-stream")]
		private var _embed:Class;
		private var _isHack:Boolean;
		private var _hackRes:String = "Je suis un mauvais h4ck3r qui a essayé de gruger et qui pense avoir réussit alors que non :P.";
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function LanguageLoader() {
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
			if(ExternalInterface.available) {
				//Fake callbacks overriden by the embeded SWF that contains the real algorithme
				ExternalInterface.addCallback("encrypt", encrypt);
				ExternalInterface.addCallback("decrypt", decrypt);
				var pageURL:String = ExternalInterface.call('window.location.href.toString');
				_isHack ||= !/^http:\/\/fevermap.org\/mushcontagion/gi.test(pageURL);
				_isHack ||= !/^http:\/\/fevermap.org\/mushcontagion/gi.test(loaderInfo.url);
			}
			
			
			var loader:Loader = new Loader();
			loader.loadBytes(new _embed(), new LoaderContext(false, ApplicationDomain.currentDomain));
		}
		
		private function encrypt(value:String):String {
			if(_isHack) {
				value = _hackRes;
			}
			return MushCryptoNew.encrypt(value);
		}
		
		private function decrypt(value:String):String {
			if(_isHack) {
				return _hackRes;
			}
			return MushCryptoNew.decrypt(value);;
		}
		
	}
}