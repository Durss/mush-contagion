package com.muxxu.mush.generator.mushroom {
	import com.muxxu.mush.graphics.MushroomMouthGraphic;
	
	/**
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	public class Mouth extends MushroomMouthGraphic {
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Mouth</code>.
		 */
		public function Mouth() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 * 
		 * @param key			generation's key
		 * @param sizeRatio		size ratio
		 */
		public function populate(key:String, sizeRatio:Number = 50):void {
			scaleX = scaleY = sizeRatio/50;
			gotoAndStop( Math.floor(parseInt(key.substr(5,6), 16) / 0xffffff * totalFrames) + 1 );
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			
		}
		
	}
}