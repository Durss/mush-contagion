package com.muxxu.mush.contaminator.views {
	import gs.TweenLite;
	import gs.TweenMax;
	import gs.easing.Sine;

	import com.muxxu.mush.contaminator.components.SpeakToolTip;
	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.muxxu.mush.contaminator.events.SpeakEvent;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.graphics.GroundFront;
	import com.muxxu.mush.graphics.LightBmp;
	import com.muxxu.mush.graphics.LightOverlayGraphic;
	import com.muxxu.mush.graphics.MushroomGraphic;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.utils.pos.PosUtils;

	import flash.display.Bitmap;
	import flash.display.BlendMode;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.geom.Matrix;
	import flash.utils.setTimeout;

	/**
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class MushroomView extends AbstractView {
		private var _mushroom:MushroomGraphic;
		private var _frontLandscape:Bitmap;
		private var _introductionLayer:Sprite;
		private var _darkness:LightOverlayGraphic;
		private var _light1:Bitmap;
		private var _light2:Bitmap;
		private var _speak:SpeakToolTip;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>MushroomView</code>.
		 */
		public function MushroomView() {
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
			if(model.playIntro) {
				addChild(_introductionLayer);
				addChild(_speak);
				_light1.alpha = 0;
				_light2.alpha = 0;
				_darkness.alpha = 0;
				TweenLite.to(_light1, 1.5, {autoAlpha:1, delay:.5, ease:Sine.easeInOut});
				TweenLite.to(_darkness, 1.5, {autoAlpha:1, delay:.6, ease:Sine.easeInOut, onComplete:startTalking});
				TweenMax.to(_light2, 3, {alpha:1, yoyo:0, delay:2, ease:Sine.easeInOut});
				TweenMax.to(_light1, 3, {alpha:0, yoyo:0, delay:2, ease:Sine.easeInOut});
				TweenMax.to(_darkness, 2, {alpha:1.25, yoyo:0, delay:2, ease:Sine.easeInOut});
				setTimeout(MovieClip(_mushroom.top.getChildByName("left")).play, 2000);
				setTimeout(MovieClip(_mushroom.top.getChildByName("right")).play, 2000);
				setTimeout(MovieClip(_mushroom.bottom.getChildByName("mouth")).play, 2000);
			}
		}



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
			_mushroom = addChild(new MushroomGraphic()) as MushroomGraphic;
			_frontLandscape = addChild(new Bitmap(new GroundFront(NaN, NaN))) as Bitmap;
			_speak = addChild(new SpeakToolTip()) as SpeakToolTip;
			
			_introductionLayer = new Sprite();
			_darkness = _introductionLayer.addChild(new LightOverlayGraphic()) as LightOverlayGraphic;
			_light1 = _introductionLayer.addChild(new Bitmap(new LightBmp(NaN, NaN))) as Bitmap;
			_light2 = _introductionLayer.addChild(new Bitmap(new LightBmp(NaN, NaN))) as Bitmap;

			var m:Matrix = new Matrix();
			m.scale(-1, 1);
			m.translate(_light2.width, 0);
			_light2.bitmapData.fillRect(_light2.bitmapData.rect, 0);
			_light2.bitmapData.draw(_light1, m);
			_light1.x = 331;
			_light2.x = 350;
			_darkness.blendMode = BlendMode.SUBTRACT;
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			_speak.addEventListener(SpeakEvent.SPEAK, speakEventHandler);
			_speak.addEventListener(SpeakEvent.STOP_SPEAK, speakEventHandler);
			_speak.addEventListener(SpeakEvent.SNEEZE, speakEventHandler);
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
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			PosUtils.alignToBottomOf(_frontLandscape, stage);
			_mushroom.x = 410;
			_mushroom.y = stage.stageHeight - 60;
			
			_speak.x = _mushroom.x + 110;
			_speak.y = _mushroom.y - 140;
		}
		
		/**
		 * Called when speak tooltip fires an event
		 */
		private function speakEventHandler(event:SpeakEvent):void {
			var mouth:MovieClip = _mushroom.bottom.getChildByName("mouth") as MovieClip;
			
			if(event.type == SpeakEvent.SPEAK) {
				mouth.gotoAndStop(mouth.totalFrames - Math.round(Math.random() * 3));
			
			}else if(event.type == SpeakEvent.STOP_SPEAK) {
				mouth.gotoAndStop(mouth.totalFrames - 3);
				
			}else if(event.type == SpeakEvent.SNEEZE) {
				
			}
		}
		
		/**
		 * Makes the mushroom talk the introduction
		 */
		private function startTalking():void {
			_speak.populate("intro");
		}

		
		/**
		 * Called when the spores are thrown
		 */
		private function throwSporesHandler(event:LightEvent):void {
			scroll();
		}
		
		/**
		 * Makes the landskape scrolling vertically.
		 */
		private function scroll():void {
			TweenLite.to(_frontLandscape, .75, {y:stage.stageHeight+200, ease:Sine.easeIn});
			TweenLite.to(_mushroom, .75, {y:stage.stageHeight + _mushroom.height + 100, ease:Sine.easeIn});
		}
		
	}
}