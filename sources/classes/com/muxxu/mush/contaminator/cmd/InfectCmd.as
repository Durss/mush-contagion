package com.muxxu.mush.contaminator.cmd {
	import com.nurun.core.commands.AbstractCommand;
	import com.nurun.core.commands.Command;
	import com.nurun.core.commands.events.CommandEvent;
	import com.nurun.structure.environnement.configuration.Config;

	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	
	/**
	 * The  InfectCmd is a concrete implementation of the ICommand interface.
	 * Its responsability is to infect some friends
	 *
	 * @author Francois
	 * @date 29 janv. 2012;
	 */
	public class InfectCmd extends AbstractCommand implements Command {
		
		private var _loader:URLLoader;
		private var _request:URLRequest;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		public function  InfectCmd() {
			super();
			_loader = new URLLoader();
			_loader.addEventListener(Event.COMPLETE, loadCompleteHandler);
			_loader.addEventListener(IOErrorEvent.IO_ERROR, loadErrorHandler);

			var url:String = Config.getPath("infectService");
			url = url.replace(/\{UID\}/gi, Config.getVariable("id"));
			url = url.replace(/\{KEY\}/gi, Config.getVariable("key"));
			_request = new URLRequest(url);
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Execute the concrete InfectCmd command.
		 * Must dispatch the CommandEvent.COMPLETE event when done.
		 */
		public override function execute():void {
			_loader.load(_request);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */

		private function loadCompleteHandler(event:Event):void {
			try {
				var data:XML = new XML(_loader.data);
			}catch(error:Error) {
				dispatchEvent(new CommandEvent(CommandEvent.ERROR, "XML_FORMAT_ERROR"));
				return;
			}
			
			if (data.child("error").length() > 0) {
				dispatchEvent(new CommandEvent(CommandEvent.ERROR, XML(data.child("error").@code).toString()));
			}else{
				dispatchEvent(new CommandEvent(CommandEvent.COMPLETE, data));
			}
		}

		private function loadErrorHandler(event:IOErrorEvent):void {
			dispatchEvent(new CommandEvent(CommandEvent.ERROR, "INFECT_404"));
		}
	}
}
