package com.muxxu.mush.contaminator.components {
	import gs.TweenLite;
	import gs.easing.Strong;

	import com.muxxu.mush.graphics.SmokeGraphic;
	import com.nurun.utils.math.MathUtils;

	import flash.display.DisplayObject;
	
	/**
	 * 
	 * @author Francois
	 * @date 3 mars 2012;
	 */
	public class Smoke extends SmokeGraphic {
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Smoke</code>.
		 */
		public function Smoke(target:DisplayObject) {
			x = target.x + MathUtils.randomNumberFromRange(-50, 50);
			y = target.y + MathUtils.randomNumberFromRange(-50, 50);
			alpha = Math.random()*.5 + .5;
			var endX:int = x + (x - target.x);
			var endY:int = y + (y - target.y);
			TweenLite.to(this, .5, {rotation:MathUtils.randomNumberFromRange(-45, 45), ease:Strong.easeOut, x:endX, y:endY, onComplete:onComplete});
			TweenLite.to(this, .25, {overwrite:0, autoAlpha:0, delay:.15});
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
		private function onComplete():void {
			parent.removeChild(this);
		}
		
	}
}