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
	import flash.utils.getTimer;
	
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
		private var _waitFor:Number;
		private var _start:int;
		private var _control:String;
		
		
		
		
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
			var xml:XML = DependencyStorage.getInstance().getDependencyById("infos").xml;
			var delayNode:XML = XML(xml.child("user")[0]).child("delay")[0];
			_waitFor = parseInt(delayNode.@wait);
			_control = delayNode.@ctrl;
			if(_waitFor > 0) _waitFor ++;//Add a security margin
			_start = getTimer();
			update();
		}
		
		/**
		 * Starts the application.
		 */
		public function throwSpores():void {
			if(getTimer() - _start > _waitFor * 1000) {
				var cmd:InfectCmd = new InfectCmd();
				cmd.addEventListener(CommandEvent.COMPLETE, infectCompleteHandler);
				cmd.addEventListener(CommandEvent.ERROR, commandErrorHandler);
				cmd.execute();
				dispatchLight(LightEvent.THROW_SPORES);
			}else{
				dispatchLight(LightEvent.CANT_THROW_SPORES);
			}
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
		 * Sets the sound's state.
		 * Used when app is throttled to mute the sound and set it back to its previous value
		 */
		public function setSoundState(mute:Boolean):void {
			_soundEnabled = mute? false : _so.data["sound"];
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
		
		/**
		 * Get the wait duration
		 */
		public function getWaitDuration():Number {
			var past:Number = getTimer() - _start;
			return Math.max(0, _waitFor * 1000 - past);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_so = SharedObject.getLocal("mushcontagion-v2", "/");
//			_so.clear();//TODO remove!
			if(_so.data["sound"] == undefined) _so.data["sound"] = true;
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
			var data:XML = event.data as XML;
			if(data.child("delay").length() > 0) {
				_waitFor = parseInt(data.child("delay")[0].@wait);
				if(data.child("delay")[0].@ctrl != _control) {
					throw new ContaminatorError("DELAY_CHANGED");
				}else{
					throw new ContaminatorError("DELAY_CHEATED");
				}
//				dispatchLight(LightEvent.CANT_THROW_SPORES);
			}else{
				_infectedUsers = new UserCollection();
				_infectedUsers.populate(XML(event.data).child("infectedUsers")[0]);
				_status.infectedUsers = _infectedUsers;
				update();
			}
		}
		
		
		/**
		 * Called if a command fails
		 */
		private function commandErrorHandler(event:CommandEvent):void {
			throw new ContaminatorError(event.data as String);
		}
	}
}