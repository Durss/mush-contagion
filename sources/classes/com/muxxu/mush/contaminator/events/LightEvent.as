package com.muxxu.mush.contaminator.events {
	import flash.events.Event;
	
	/**
	 * Event fired by ViewLocator
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class LightEvent extends Event {
		
		public static const THROW_SPORES:String = "throwSpores";
		public static const INFECTION_COMPLETE:String = "infectionComplete";
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>LightEvent</code>.
		 */
		public function LightEvent(type:String, bubbles:Boolean = false, cancelable:Boolean = false) {
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
			return new LightEvent(type, bubbles, cancelable);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}