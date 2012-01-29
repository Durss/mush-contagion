package com.muxxu.mush.contaminator.views {
	import flash.utils.getTimer;
	import com.muxxu.mush.contaminator.controler.FrontControler;
	import com.muxxu.mush.contaminator.components.CursorStreak;
	import graphics_fla.MushroomGraphic_5;
	import graphics_fla.MushroomGraphic_6;

	import gs.TweenLite;
	import gs.TweenMax;
	import gs.easing.Elastic;
	import gs.easing.Sine;

	import com.muxxu.mush.contaminator.components.SpeakToolTip;
	import com.muxxu.mush.contaminator.components.SporesManager;
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
	import flash.geom.Point;
	import flash.media.Sound;
	import flash.utils.setTimeout;

	/**
	 * 
	 * @author Francois
	 * @date 14 janv. 2012;
	 */
	public class MushroomView extends AbstractView {
		
		[Embed(source="../../../../../../assets/sounds/sneeze1.mp3")]
		private var _sneeze1:Class;
		[Embed(source="../../../../../../assets/sounds/sneeze2.mp3")]
		private var _sneeze2:Class;
		[Embed(source="../../../../../../assets/sounds/sneeze3.mp3")]
		private var _sneeze3:Class;
		
		private var _mushroom:MushroomGraphic;
		private var _frontLandscape:Bitmap;
		private var _introductionLayer:Sprite;
		private var _darkness:LightOverlayGraphic;
		private var _light1:Bitmap;
		private var _light2:Bitmap;
		private var _speak:SpeakToolTip;
		private var _inc:Number;
		private var _a1:Number;
		private var _a2:Number;
		private var _a3:Number;
		private var _particles:SporesManager;
		private var _particlesHolder:Sprite;
		private var _streak:CursorStreak;
		private var _lastHitTime:int;
		private var _sneezeHistory:Array;
		private var _dialogDone:Object;
		private var _disabled:Boolean;
		
		
		
		
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
			
			if(model.infectedUsers != null) {
				
				return;
			}
			
			if(model.playIntro) {
				addChild(_introductionLayer);
				addChild(_speak);
				addChild(_streak);
				_light1.alpha = 0;
				_light2.alpha = 0;
				_darkness.alpha = 0;
				TweenLite.to(_light1, 1.5, {autoAlpha:1, delay:.5, ease:Sine.easeInOut});
				TweenLite.to(_darkness, 1.5, {autoAlpha:1, delay:.6, ease:Sine.easeInOut});
				TweenMax.to(_light2, 3, {alpha:1, yoyo:0, delay:2, ease:Sine.easeInOut});
				TweenMax.to(_light1, 3, {alpha:0, yoyo:0, delay:2, ease:Sine.easeInOut});
				TweenMax.to(_darkness, 2, {alpha:1.25, yoyo:0, delay:2, ease:Sine.easeInOut});
				setTimeout(MovieClip(_mushroom.top.getChildByName("left")).play, 2000);
				setTimeout(MovieClip(_mushroom.top.getChildByName("right")).play, 2000);
				setTimeout(MovieClip(_mushroom.bottom.getChildByName("mouth")).play, 2000);
				setTimeout(startTalking, 3200);
			} else {
				MovieClip(_mushroom.top.getChildByName("left")).play();
				MovieClip(_mushroom.top.getChildByName("right")).play();
				MovieClip(_mushroom.bottom.getChildByName("mouth")).play();
				_streak.enable();
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
			_particlesHolder = addChild(new Sprite()) as Sprite;
			_streak = addChild(new CursorStreak()) as CursorStreak;
			
			_introductionLayer = new Sprite();
			_darkness = _introductionLayer.addChild(new LightOverlayGraphic()) as LightOverlayGraphic;
			_light1 = _introductionLayer.addChild(new Bitmap(new LightBmp(NaN, NaN))) as Bitmap;
			_light2 = _introductionLayer.addChild(new Bitmap(new LightBmp(NaN, NaN))) as Bitmap;
			
			_inc = 0;
			_sneezeHistory = [];
			_dialogDone = {};
			_particles = new SporesManager(600, _particlesHolder);
			
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
			_speak.addEventListener(SpeakEvent.SPEAK_COMPLETE, speakEventHandler);
			_speak.addEventListener(SpeakEvent.SNEEZE, speakEventHandler);
			ViewLocator.getInstance().addEventListener(LightEvent.THROW_SPORES, throwSporesHandler);
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			stage.addEventListener(Event.RESIZE, computePositions);
			addEventListener(Event.ENTER_FRAME, enterFrameHandler);
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
			var eyeL:MushroomGraphic_6 = MushroomGraphic_5(_mushroom.top).left as MushroomGraphic_6;
			var eyeR:MushroomGraphic_6 = MushroomGraphic_5(_mushroom.top).right as MushroomGraphic_6;
			
			if(event.type == SpeakEvent.SPEAK) {
				mouth.gotoAndStop(mouth.totalFrames - Math.round(Math.random() * 3));
			
			}else if(event.type == SpeakEvent.STOP_SPEAK) {
				mouth.gotoAndStop(mouth.totalFrames - 3);
				
			}else if(event.type == SpeakEvent.SPEAK_COMPLETE) {
				TweenLite.killTweensOf(_light1);
				TweenLite.killTweensOf(_light2);
				TweenLite.killTweensOf(_darkness);
				TweenLite.killTweensOf(_speak);
				TweenLite.to(_speak, .25, {autoAlpha:0});
				if(_light2.visible) {
					TweenLite.to(_light2, 3, {autoAlpha:0});
					TweenLite.to(_light1, 3, {autoAlpha:0});
					TweenLite.to(_darkness, 2, {autoAlpha:0});
				}
				_streak.enable();
				FrontControler.getInstance().introComplete();
				
			}else if(event.type == SpeakEvent.SNEEZE) {
				eyeL.sneeze();
				eyeR.sneeze();
				var s:Number = Math.random() * 25 + 50;
				sneeze(s, s, true);
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
			stage.removeEventListener(Event.RESIZE, computePositions);
			scroll();
		}
		
		/**
		 * Makes the landskape scrolling vertically.
		 */
		private function scroll():void {
			TweenLite.to(_frontLandscape, 3, {y:stage.stageHeight+400, ease:Sine.easeIn});
			TweenLite.to(_mushroom, 3, {y:stage.stageHeight + _mushroom.height + 450, ease:Sine.easeIn});
			setTimeout(_particles.startAnimation, 4000);
		}
		
		/**
		 * Makes the mushroom sneezing
		 */
		private function sneeze(strength:Number, strengthY:Number, sound:Boolean = false):void {
			_a1 = strength * .3;
			_a2 = strength * .3;
			_a3 = strength * .15;
			_mushroom.scaleY = 1-strengthY/50;
			TweenLite.to(_mushroom, 1, {scaleY:1, ease:Elastic.easeOut});
			_particles.throwParticles(new Point(_mushroom.x, _mushroom.y - 75), strength*2);
			
			if(sound) {
				//Random sneeze sound
				(new this["_sneeze"+(Math.floor(Math.random() * 3) + 1)]() as Sound).play();
			}else{
				_sneezeHistory.push(strength);
				var i:int, len:int, tot:int;
				len = _sneezeHistory.length;
				if(len > 4) {
					for(i = 0; i < len; ++i) {
						tot += _sneezeHistory[i];
					}
					tot /= len;
					var id:String = _dialogDone["harder"] == undefined? "harder" : _dialogDone["stillharder"] == undefined? "stillharder" : "cook";
					if (tot < 30 && _dialogDone[id] == undefined && _dialogDone["better"] == undefined) {
						_sneezeHistory = [];
						_dialogDone[id] = true;
						_speak.populate(id, false);
					}
					_sneezeHistory.shift();
				}
				
				if(strength > 30) {
					if(_dialogDone["harder"] === true && _dialogDone["better"] == undefined) {
						_speak.populate("better", false);
					}else{
						_disabled = true;
						_streak.disable();
						FrontControler.getInstance().throwSpores();
					}
					_dialogDone["better"] = true;
				}
			}
		}
		
		/**
		 * Makes the mushroom moving
		 */
		private function enterFrameHandler(event:Event):void {
			_inc += Math.PI * .3;
			_a1 *= .94;
			_a2 *= .92;
			_a3 *= .95;
			_mushroom.top.rotation = Math.cos(_inc) * _a1;
			_mushroom.middle.rotation = Math.cos(_inc) * _a2;
			_mushroom.rotation = Math.cos(_inc) * _a3;

			if(_speak.speaking) return;
			
			var histX:Array = _streak.historyX;
			var histY:Array = _streak.historyY;
			
			//Detect collision with mushroom.
			//Detects the collision direction to sync the mushroom's animation.
//			graphics.clear();
			if(histX.length >= 2 && getTimer() - _lastHitTime > 500 && !_disabled) {
//				graphics.beginFill(0xff0000, 1);
				var j:int, i:int, len:int, dist:Number, px:Number, py:Number, incX:Number, incY:Number, ratio:Number, distMin:Number;
				j = histX.length-1;
				ratio = .25;
				distMin = 20;
				for(j; j > 0; --j) {
					px = histX[j];
					py = histY[j];
					dist = Math.sqrt(Math.pow(px - histX[j-1], 2) + Math.pow(py - histY[j-1], 2));
					if(dist < distMin) continue;
					
					incX = (px - histX[j-1]) / dist * 1/ratio;
					incY = (py - histY[j-1]) / dist * 1/ratio;
					len = dist * ratio;
					for(i = 0; i < len; ++i) {
						if(_mushroom.hitTestPoint(px, py, true)) {
							dist = Math.sqrt(Math.pow(histX[0] - histX[histX.length-1], 2) + Math.pow(histY[0] - histY[histY.length-1], 2));
							incX = histX[histX.length-1] - histX[0];
							incY = histY[histY.length-1] - histY[0];
							if(incX < -distMin) _inc = Math.PI;
							if(incX > distMin) _inc = 0;
							sneeze(_streak.huge? dist*.2 : dist*.02, _streak.huge? incY * .2 : incY * .05);
							_lastHitTime = getTimer();
							return;
						}
//						graphics.drawCircle(px, py, 2);
						px -= incX;
						py -= incY;
					}
				}
			}
		}
		
	}
}