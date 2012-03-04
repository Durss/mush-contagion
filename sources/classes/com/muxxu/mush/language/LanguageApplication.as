package com.muxxu.mush.language {
	import com.muxxu.mush.avatar.crypto.MushCrypto;

	import flash.display.MovieClip;
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
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function LanguageApplication() {
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
				ExternalInterface.addCallback("encrypt", encrypt);
				ExternalInterface.addCallback("decrypt", decrypt);
				
				if(loaderInfo.parameters["setSecureOff"] !== "yes") {
					var pageContent:XML = 
				 	   <script><![CDATA[
				            function(){ return document.getElementsByTagName('html')[0].innerHTML; }
				        ]]></script>;
				    
			        var htmlPage:String = ExternalInterface.call(pageContent.toString());
					_isHack = !/mush  - contagion/gi.test(htmlPage);
					var pageURL:String = ExternalInterface.call('window.location.href.toString');
					_isHack ||= !/^http:\/\/fevermap.org\/mushcontagion/gi.test(pageURL);
					_isHack ||= !/^http:\/\/fevermap.org\/mushcontagion/gi.test(loaderInfo.url);
				}
			}else{
//				trace(MushCrypto.decrypt("sfd sfsdf sdfsd▌ f▓sd f◄▀►◄▀f ds▄f▐sd▄►sd▒ds▀▄►►■►▼▼▲▄◄▼░▼▀►▐▄◄▼▼▼▄▄►▄▄▒▲▲░▀◄▼▒▀►►█◄▐►▄►▀▒▲▄◄▀■◄◙▀▀▄▐▄▀▲▄░◄░■►●■▐█▼█▐▐▀▌◙◙▄■◙▼◄▌◙◙◘■█▌▲▐◙▓◄◙◄■▀◄►◙■▐▼█◙▌█▓▓◄■▐▼██■▐█◙▌▒▓▄▐▀▓◘▒◘◙◄►▄►▄▐►▒▼►►■►▐▲▓▼◙▼▄▄▄►▀▄▲►■►▌▲►►◙▼▓▼▌▒▀░■▀▀"));
			}
		}
		
		private function encrypt(value:String):String {
			if(_isHack) {
				value = "Je suis un mauvais h4ck3r qui a essayé de gruger et qui pense avoir réussit alors que non.";
			}
			return MushCrypto.encrypt(value);
		}
		
		private function decrypt(value:String):String {
			if(_isHack) {
				value = "▓◘▀▓▓▐●■▐▼▀▌◙ ▀▀▀▀ ▓■◘█▲▓░●░▌ ▄█▓▄▒██▒ ●▌◄▲█◄▲■▲ ▼█◘■▐►▀▀► ▼◘ ░░▓█▐░▐▄▒●▒▓▒░▓►▀▓► ■ ▲▐▲▼◙ ◘▌▄■◙◄◘●◘▒◙●●▄▒ ◙█●●▐◘ ◙▒ ▐ ▼ ▀●◘◙▒◄▌◘ ◄▓█▒ ▼◘▐▀▲◄▌▄▓►● ◄ ► ◄▓▀▄▌█▐▒▄█►▓◙●◄▒►▲■◙◘■■● ► ▒●█■▌●░◘▐◙▼▄●▓ ◄◄●■█■▲ ►▀▓█▀▲ ▼░ ●▒▌▒▓ ▲▲▀►▒ ◘▐▓▀◄▲►▄▓ ●█▒░▓◙█▐■▀◘▓◙◙▲●▲●►◄▼ ▲◙▼▄▒■●■░█▄ ▌▓▀▒◄► ▐■●▓ ▼▄►█▌◙▌▀◘■▲█◙◘ ●▌░▼▄◘▒▄▓◄◘▐●◙◘►►▐▼● █░ ▐▓ ▲█▀◙◘■░■ ■██▓▀▒ ▓►◄█▲►▀▐▓◘■ ◙◘░▌●◄▓▓●▄▼◘◙●▼ ◙◄▲ ▀●▲■■▼░▼●▼◙██░█▼▓►●▀►░ ▓█▄ ●■■▼►►▀▲▐█▼▌ ▌►▲░▌█ ◙▀■▲■░◙◘◙►◘▼█ █▄◄▌◙▼▄░▌▓◘▌▀░▲▲▓ ▒▼▲◘█▐ ◙■ ▲░▀▐█ █▲►■▀▓◄◙●◄▌▲● ◄◙◙░◘◄►▒◄█▲▓ ►▼●■▀ ◄ ▀▌▐ ▓ ■ ►●◄▌▼ ◘▲▒▓▀ ◙░░▐▄ █▒█►►►◘▲◄ ▼◙ ►░░▒▐▼■▼▐ ░◄▀▒►▐▄▼▌ ◘█ ■ ●▼◄▒▐▓ ▼█░◄◘◙◘░●◘▲▓▼◄░▄◄▲▐▲▌▓◘█■◘█◄●◙◘▲ █░◄ ►▒█░◙▒█▼▐●►■▌◙░▌ ▒░▄▒░◘▓░█▀";
			}
			return MushCrypto.decrypt(value);
		}
		
	}
}