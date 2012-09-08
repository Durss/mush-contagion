package com.muxxu.mush.contaminator.views {
	import com.muxxu.mush.contaminator.controler.FrontControler;
	import net.hires.debug.Stats;

	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.utils.input.keyboard.KeyboardSequenceDetector;
	import com.nurun.utils.input.keyboard.events.KeyboardSequenceEvent;

	import flash.events.Event;
	import flash.utils.getTimer;
	
	/**
	 * 
	 * @author Francois
	 * @date 7 sept. 2012;
	 */
	public class StatsView extends Stats {
		private var _ks:KeyboardSequenceDetector;
		private var _prevFPS:uint;
		private var _startDelay:int;
		private var _paused:Boolean;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>StatsView</code>.
		 */
		public function StatsView() {
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
			
			_startDelay = getTimer();
			
			visible = false;
			_ks = new KeyboardSequenceDetector(stage);
			_ks.addSequence("debug", KeyboardSequenceDetector.DEBUG_CODE);
			_ks.addEventListener(KeyboardSequenceEvent.SEQUENCE, sequenceHandler);
			stage.addEventListener("throttle", throttleHandler);
		}

		private function throttleHandler(event:Event):void {
			_paused = event["state"] == "throttle";
			FrontControler.getInstance().setSoundState(_paused);
		}

		private function sequenceHandler(event:KeyboardSequenceEvent):void {
			visible = !visible;
		}
		
		override protected function update(e:Event):void {
			timer = getTimer();
			if (!_paused && timer - 1000 > ms_prev ) {
				if (Math.abs(_prevFPS - fps) < 5 && fps < 20 && fps > 5 && timer-_startDelay > 2000) {
					var i:int, len:int;
					var views:Array = ViewLocator.getInstance().getViews();
					len = views.length;
					for(i = 0; i < len; ++i) {
						if(views[i] is ILightableView) {
							ILightableView(views[i]).lowerQuality();
						}
					}
				}
				_prevFPS = fps;
			}
			super.update(e);
		}
		
	}
}