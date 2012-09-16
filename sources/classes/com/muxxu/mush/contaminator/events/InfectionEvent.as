package com.muxxu.mush.contaminator.events {
	import flash.events.Event;
	
	/**
	 * Event fired by Twinoid
	 * 
	 * @author Francois
	 * @date 12 févr. 2012;
	 */
	public class InfectionEvent extends Event {
		
		public static const INFECTED:String = "infected";
		public static const NOT_YET_INFECTED:String = "notYetInfected";
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>InfectionEvent</code>.
		 */
		public function InfectionEvent(type:String, bubbles:Boolean = false, cancelable:Boolean = false) {
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
			return new InfectionEvent(type, bubbles, cancelable);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}