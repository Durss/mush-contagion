package com.muxxu.mush.contaminator.views {
	import gs.TweenLite;
	import gs.easing.Sine;

	import com.innerdrivestudios.visualeffect.WrappingBitmap;
	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.graphics.GroundBack;
	import com.muxxu.mush.graphics.MushroomsBmp;
	import com.muxxu.mush.graphics.SkyBmp;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.utils.pos.PosUtils;

	import flash.display.Bitmap;
	import flash.events.Event;

	/**
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class BackgroundView extends AbstractView {
		
		private var _ground:Bitmap;
		private var _sky:WrappingBitmap;
		private var _offsetY:Number;
		private var _autoRotation:Boolean;
		private var _endRotation:Boolean;
		private var _speed:Number;
		private var _mushrooms:Bitmap;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>BackgroundView</code>.
		 */
		public function BackgroundView() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Called on model's update
		 */
		override public function update(event:IModelEvent):void {
			var model:Model = event.model as Model;
			model;
		}
		
		/**
		 * Gets the sky's scroll offset
		 * Internal use.
		 */
		public function get offsetY():Number { return _offsetY; }

		
		/**
		 * Sets the sky's scroll offset.
		 * Internal use.
		 */
		public function set offsetY(offsetY:Number):void { _offsetY = offsetY; scrollSky(); }



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
			_sky = addChild(new WrappingBitmap(new SkyBmp(NaN, NaN))) as WrappingBitmap;
			_ground = addChild(new Bitmap(new GroundBack(NaN, NaN))) as Bitmap;
			_mushrooms = addChild(new Bitmap(new MushroomsBmp(NaN, NaN))) as Bitmap;
			
			offsetY = 0;
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			ViewLocator.getInstance().addEventListener(LightEvent.THROW_SPORES, throwSporesHandler);
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			stage.addEventListener(Event.RESIZE, computePositions);
			computePositions();
		}
		
		/**
		 * Throws the spores
		 */
		private function throwSporesHandler(event:LightEvent):void {
			if(!_autoRotation) {
				scroll();
			}else{
				_endRotation = true;
				_speed = 50;
			}
		}
		
		/**
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			PosUtils.alignToBottomOf(_ground, stage);
			PosUtils.alignToBottomOf(_mushrooms, stage);
		}
		
		
		
		
		//__________________________________________________________ SCROLLING
		
		/**
		 * Makes the landskape scrolling vertically.
		 */
		private function scroll():void {
			TweenLite.to(_ground, .75, {y:stage.stageHeight+130, ease:Sine.easeIn});
			TweenLite.to(_mushrooms, .75, {y:stage.stageHeight, ease:Sine.easeIn});
			TweenLite.to(this, 2, {ease:Sine.easeIn, offsetY:-2000, onComplete:startAutoRotation});
			addEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}
		
		/**
		 * Scrolls the sky depending on the offsetY value.
		 */
		private function scrollSky():void {
			_sky.scrollTo(0, offsetY);
		}
		
		/**
		 * Starts the auto rotation
		 */
		private function startAutoRotation():void {
			_autoRotation = true;
		}
		
		/**
		 * Makes the sky scroll endlessly.
		 */
		private function enterFrameHandler(event:Event):void {
			if(_autoRotation) {
				if(_endRotation) {
					offsetY -= Math.floor(_speed);
					_speed *= .96;
				}else{
					offsetY -= 50;
				}
			}
		}
		
	}
}