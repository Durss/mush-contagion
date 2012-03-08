package com.muxxu.mush.contaminator.model {
	import com.muxxu.mush.contaminator.cmd.InfectCmd;
	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.muxxu.mush.contaminator.throwables.ContaminatorError;
	import com.muxxu.mush.contaminator.vo.StatusCollection;
	import com.muxxu.mush.contaminator.vo.UserCollection;
	import com.nurun.core.commands.events.CommandEvent;
	import com.nurun.structure.environnement.dependency.DependencyStorage;
	import com.nurun.structure.mvc.model.IModel;
	import com.nurun.structure.mvc.model.events.ModelEvent;
	import com.nurun.structure.mvc.views.ViewLocator;

	import flash.events.EventDispatcher;
	import flash.media.SoundMixer;
	import flash.media.SoundTransform;
	import flash.net.SharedObject;
	
	/**
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class Model extends EventDispatcher implements IModel {
		
		private var _so:SharedObject;
		private var _playIntro:Boolean;
		private var _soundEnabled:Boolean;
		private var _infectedUsers:UserCollection;
		private var _contaminationComplete:Boolean;
		private var _status:StatusCollection;
		
		
		
		
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
		
		/**
		 * Gets the sound's state
		 */
		public function get soundEnabled():Boolean { return _soundEnabled; }
		
		/**
		 * Gets if the contamination is complete
		 */
		public function get contaminationComplete():Boolean { return _contaminationComplete; }
		
		/**
		 * Gets the infected users
		 */
		public function get infectedUsers():UserCollection { return _infectedUsers; }
		
		/**
		 * Gets the status data.
		 */
		public function get status():StatusCollection { return _status; }
		



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Starts the application.
		 */
		public function start():void {
			_playIntro = _so.data["introPlayed"] == undefined;
			trace(DependencyStorage.getInstance().getDependencyById("infos").xml)
			update();
		}
		
		/**
		 * Starts the application.
		 */
		public function throwSpores():void {
			var cmd:InfectCmd = new InfectCmd();
			cmd.addEventListener(CommandEvent.COMPLETE, infectCompleteHandler);
			cmd.addEventListener(CommandEvent.ERROR, commandErrorHandler);
			cmd.execute();
			dispatchLight(LightEvent.THROW_SPORES);
		}
		
		/**
		 * Toggles the sound state
		 */
		public function toggleSound():void {
			_soundEnabled = !_soundEnabled;
			_so.data["sound"] = _soundEnabled;
			SoundMixer.soundTransform = new SoundTransform(_soundEnabled? 1 : 0);
		}
		
		/**
		 * Flags introduction as viewed
		 */
		public function introComplete():void {
			_so.data["introPlayed"] = true;
		}
		
		/**
		 * Called when contamination completes
		 */
		public function setContaminationComplete():void {
			_contaminationComplete = true;
			update();
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_so = SharedObject.getLocal("mushcontagion", "/");
			_soundEnabled = _so.data["sound"] !== false;
			_status = new StatusCollection();
			_status.populate(DependencyStorage.getInstance().getDependencyById("status").xml);
			SoundMixer.soundTransform = new SoundTransform(_soundEnabled? 1 : 0);
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
		
		
		
		
		//__________________________________________________________ COMMANDS
		
		/**
		 * Called when infection completes
		 */
		private function infectCompleteHandler(event:CommandEvent):void {
			_infectedUsers = new UserCollection();
			_infectedUsers.populate(event.data as XML);
			_status.infectedUsers = _infectedUsers;
			update();
		}
		
		
		/**
		 * Called if a command fails
		 */
		private function commandErrorHandler(event:CommandEvent):void {
			throw new ContaminatorError(event.data as String);
		}
	}
}