package com.muxxu.mush.contaminator.events {
	import flash.events.Event;
	
	/**
	 * Event fired by speak tooltip
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class SpeakEvent extends Event {
		
		public static const SPEAK:String = "speak";
		public static const STOP_SPEAK:String = "stopSpeak";
		public static const SNEEZE:String = "sneeze";
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>SpeakEvent</code>.
		 */
		public function SpeakEvent(type:String, bubbles:Boolean = false, cancelable:Boolean = false) {
			super(type, bubbles, cancelable);
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Makes a clone of the event object.
		 */
		override public function clone():Event {
			return new SpeakEvent(type, bubbles, cancelable);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}