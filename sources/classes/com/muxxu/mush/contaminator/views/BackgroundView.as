package com.muxxu.mush.contaminator.views {
	import com.nurun.utils.math.MathUtils;
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
		private var _autoRotation:Boolean;
		private var _endRotation:Boolean;
		private var _mushrooms:Bitmap;
		private var _speed:Number;
		private var _offsetX:Number;
		private var _offsetY:Number;
		private var _rotation:Number;
		private var _speedR:Number;
		private var _result:Boolean;
		
		
		
		
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
			
			if(model.infectedUsers != null) {
				_result = true;
				return;
			}
			
		}
		
		/**
		 * Gets the sky's scroll offset Y.
		 * Internal use.
		 */
		public function get offsetY():Number { return _offsetY; }

		
		/**
		 * Sets the sky's scroll offset Y.
		 * Internal use.
		 */
		public function set offsetY(value:Number):void { _offsetY = value; scrollSky(); }
		
		/**
		 * Gets the sky's scroll offset X.
		 * Internal use.
		 */
		public function get offsetX():Number { return _offsetX; }

		
		/**
		 * Sets the sky's scroll offset X.
		 * Internal use.
		 */
		public function set offsetX(value:Number):void { _offsetX = value; scrollSky(); }
		
		/**
		 * Gets the current sky scroll angle.
		 */
		public function get skyAngle():Number { return _rotation; }
		
		/**
		 * Gets the scroll's speed
		 */
		public function get scrollSpeed():Number { return _speed; }



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
			
			_offsetX = _sky.width;
			_offsetY = 0;
			
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
			stage.removeEventListener(Event.RESIZE, computePositions);
			if(!_autoRotation) {
				_speedR = .0005;
				_speed = 5;
				scroll();
			}else{
				_endRotation = true;
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
			TweenLite.to(_ground, 3, {y:stage.stageHeight+330, ease:Sine.easeIn});
			TweenLite.to(_mushrooms, 3, {y:stage.stageHeight+180, ease:Sine.easeIn});
			TweenLite.to(this, 4, {ease:Sine.easeIn, offsetY:-400, onComplete:startAutoRotation});
			addEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}
		
		/**
		 * Scrolls the sky depending on the offsetY value.
		 */
		private function scrollSky():void {
			_sky.scrollTo(offsetX, offsetY);
		}
		
		/**
		 * Starts the auto rotation
		 */
		private function startAutoRotation():void {
			_autoRotation = true;
			_rotation = 0;
		}
		
		/**
		 * Makes the sky scroll endlessly.
		 */
		private function enterFrameHandler(event:Event):void {
			if(_autoRotation) {
				offsetX += Math.sin(_rotation) * _speed;
				offsetY += -Math.cos(_rotation) * _speed;
				_speed *= (_rotation < Math.PI * .5)? 1.03 : _speed > 20? .99 : .92;
				if(_result && _speed < 1) _speed = 0;
				_speedR += .0001;
				_rotation += _speedR;
				_rotation = MathUtils.restrict(_rotation, 0, _result? Math.PI : Math.PI * .5); // locks angle to PI/2 while server hasn't answered
				_speed = MathUtils.restrict(_speed, 0, 100);
			}
		}
		
	}
}