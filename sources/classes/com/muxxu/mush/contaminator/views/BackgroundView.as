package com.muxxu.mush.contaminator.views {
	import flash.filters.BlurFilter;
	import com.muxxu.mush.graphics.WaterReflectGraphic;
	import gs.TweenLite;
	import gs.easing.Sine;

	import com.innerdrivestudios.visualeffect.WrappingBitmap;
	import com.muxxu.mush.contaminator.components.Fog;
	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.graphics.CountDownGraphic;
	import com.muxxu.mush.graphics.GroundBack;
	import com.muxxu.mush.graphics.MushroomsBmp;
	import com.muxxu.mush.graphics.RocketGraphic;
	import com.muxxu.mush.graphics.SkyBmp;
	import com.nurun.structure.environnement.configuration.Config;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.utils.input.keyboard.KeyboardSequenceDetector;
	import com.nurun.utils.input.keyboard.events.KeyboardSequenceEvent;
	import com.nurun.utils.math.MathUtils;
	import com.nurun.utils.pos.PosUtils;

	import flash.display.Bitmap;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.filters.ColorMatrixFilter;
	import flash.filters.DropShadowFilter;
	import flash.media.Sound;
	import flash.utils.setTimeout;
	
	/**
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class BackgroundView extends AbstractView {
		
		[Embed(source="../../../../../../assets/sounds/fart_short.mp3")]
		private var _fartShort:Class;
		[Embed(source="../../../../../../assets/sounds/fart_long.mp3")]
		private var _fartLong:Class;
		
		private var _ground:Bitmap;
		private var _sky:WrappingBitmap;
		private var _autoRotation:Boolean;
		private var _mushrooms:Bitmap;
		private var _speed:Number;
		private var _offsetX:Number;
		private var _offsetY:Number;
		private var _rotation:Number;
		private var _speedR:Number;
		private var _result:Boolean;
		private var _rocket:RocketGraphic;
		private var _ks:KeyboardSequenceDetector;
		private var _holder:Sprite;
		private var _fog:Fog;
		private var _reflects:WaterReflectGraphic;
		
		
		
		
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
			_holder = addChild(new Sprite()) as Sprite;
			_ground = _holder.addChild(new Bitmap(new GroundBack(NaN, NaN))) as Bitmap;
			_reflects = addChild(new WaterReflectGraphic()) as WaterReflectGraphic;
			_rocket = _holder.addChild(new RocketGraphic()) as RocketGraphic;
			_mushrooms = addChild(new Bitmap(new MushroomsBmp(NaN, NaN))) as Bitmap;
			_fog = _holder.addChild(new Fog()) as Fog;
			_rocket.stop();
			
			_offsetX = _sky.width;
			_offsetY = 0;
			_reflects.alpha = .5;
			_reflects.filters = [new BlurFilter(2,2,2)];
			
			_ground.filters = [new DropShadowFilter(10,-45,0,.5,50,50,1,2)];
			
			if(Config.getBooleanVariable("maintenance")) {
				_sky.filters = [new ColorMatrixFilter([-0.43937185406684875,0.5917701721191406,0.8476016521453857,0,0,0.4568214416503906,0.6498579978942871,-0.10667942464351654,0,0,-0.276028573513031,1.718625545501709,-0.44259706139564514,0,0,0,0,0,1,0])];
			}else{
				TweenLite.to(_sky, 0, {colorMatrixFilter:{contrast:1.25}});
			}
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			ViewLocator.getInstance().addEventListener(LightEvent.THROW_SPORES, throwSporesHandler);
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			stage.addEventListener(Event.RESIZE, computePositions);
			
			_ks = new KeyboardSequenceDetector(stage);
			_ks.addEventListener(KeyboardSequenceEvent.SEQUENCE, keySequenceHandler);
			_ks.addSequence("konami", KeyboardSequenceDetector.KONAMI_CODE);
			
			computePositions();
		}
		
		/**
		 * Called when a keyboard sequence is detected
		 */
		private function keySequenceHandler(event:KeyboardSequenceEvent):void {
			if(_rocket.currentFrame == 1 && !_result) {
				var cd:MovieClip = stage.addChild(new CountDownGraphic()) as MovieClip;
				cd.scaleX = cd.scaleY = 2;
				cd.filters = [new DropShadowFilter(0,0,0,1,10,10,1,3)];
				PosUtils.centerInStage(cd);
				setTimeout(Sound(new _fartShort()).play, 3000);
				setTimeout(Sound(new _fartLong()).play, 3100);
				setTimeout(_rocket.play, 3000);
			}
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
			}
		}
		
		/**
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			PosUtils.alignToBottomOf(_holder, stage);
			PosUtils.alignToBottomOf(_mushrooms, stage);
			_rocket.x = _ground.width - 73;
			_rocket.y = _ground.height - 52;
			_reflects.x = 642;
			_reflects.y = 532;
		}
		
		
		
		
		//__________________________________________________________ SCROLLING
		
		/**
		 * Makes the landskape scrolling vertically.
		 */
		private function scroll():void {
			TweenLite.to(_holder, 3, {y:stage.stageHeight+330, ease:Sine.easeIn});
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
			_fog.stop();
		}
		
		/**
		 * Makes the sky scroll endlessly.
		 */
		private function enterFrameHandler(event:Event):void {
			if(_autoRotation) {
				offsetX += Math.sin(_rotation) * _speed;
				offsetY += -Math.cos(_rotation) * _speed;
				_speed *= (_rotation < Math.PI * .5)? 1.03 : _speed > 20? .99 : .92;
				if(_result && _speed < 1) {
					_speed = 0;
					removeEventListener(Event.ENTER_FRAME, enterFrameHandler);
				}
				_speedR += .0001;
				_rotation += _speedR;
				_rotation = MathUtils.restrict(_rotation, 0, _result? Math.PI : Math.PI * .5); // locks angle to PI/2 while server hasn't answered
				_speed = MathUtils.restrict(_speed, 0, 100);
			}
		}
		
	}
}