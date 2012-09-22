package com.muxxu.mush.language {
	import com.muxxu.mush.avatar.crypto.MushCrypto;
	import com.muxxu.mush.avatar.crypto.MushCrypto2;

	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.external.ExternalInterface;

	/**
	 * Bootstrap class of the application.
	 * Must be set as the main class for the flex sdk compiler
	 * but actually the real bootstrap class will be the factoryClass
	 * designated in the metadata instruction.
	 * 
	 * @author Francois
	 * @date 4 mars 2012;
	 */
	 
	[SWF(width="300", height="200", backgroundColor="0xFFFFFF", frameRate="31")]
	public class LanguageApplication extends MovieClip {
		
		private var _isHack:Boolean;
		private var _hackRes:String = "Je suis un mauvais h4ck3r qui a essayé de gruger et qui pense avoir réussit alors que non :P.";
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function LanguageApplication() {
			addEventListener(Event.ADDED_TO_STAGE, initialize);
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
		private function initialize(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, initialize);
			if(ExternalInterface.available) {
				ExternalInterface.addCallback("encrypt", encrypt);
				ExternalInterface.addCallback("decrypt", decrypt);
				
//				if(loaderInfo.parameters["setSecureOff"] !== "yes") {
					var pageContent:XML = 
				 	   <script><![CDATA[
				            function(){ return document.getElementsByTagName('html')[0].innerHTML; }
				        ]]></script>;
				    
			        var htmlPage:String = ExternalInterface.call(pageContent.toString());
					_isHack = !/Mush  Contagion /gi.test(htmlPage);
					var pageURL:String = ExternalInterface.call('window.location.href.toString');
					_isHack ||= !/^http:\/\/fevermap.org\/mushcontagion/gi.test(pageURL);
					_isHack ||= !/^http:\/\/fevermap.org\/mushcontagion/gi.test(loaderInfo.url);
//				}
			}else{
				//trace(MushCrypto.decrypt(MushCrypto.encrypt("Salut les amis!")));
			}
		}
		
		private function encrypt(value:String):String {
			if(_isHack) {
				value = _hackRes;
			}
			return MushCrypto2.encrypt(value);
		}
		
		private function decrypt(value:String):String {
			if(_isHack) {
				return _hackRes;
			}
			var res:String = MushCrypto2.decrypt(value);
			if(res == "???" || res.length == 0) {
				res = MushCrypto.decrypt(value);
			}
			return res;
		}
		
	}
}