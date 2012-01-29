package com.muxxu.mush.contaminator.views {
	import com.nurun.utils.vector.VectorUtils;
	import flash.utils.setInterval;
	import by.blooddy.crypto.MD5;

	import gs.TweenLite;

	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.contaminator.vo.UserCollection;
	import com.muxxu.mush.generator.mushroom.Mushroom;
	import com.muxxu.mush.graphics.Ground2Bmp;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.structure.mvc.views.ViewLocator;

	import flash.display.Bitmap;
	import flash.events.Event;
	import flash.geom.Rectangle;
	import flash.utils.setTimeout;

	/**
	 * 
	 * @author Francois
	 * @date 28 janv. 2012;
	 */
	public class ContaminationView extends AbstractView {
		private var _ground:Bitmap;
		private var _displayed:Boolean;
		private var _sky:BackgroundView;
		private var _mush1:Mushroom;
		private var _mush2:Mushroom;
		private var _mush3:Mushroom;
		private var _enabledMushrooms:Vector.<Mushroom>;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>ContaminationView</code>.
		 */
		public function ContaminationView() {
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
			var u:UserCollection = model.infectedUsers;
			if(u != null) {
				if(u.length > 0) {
					setTimeout(_mush1.populate, 1, MD5.hash(u.getUserAtIndex(0).name+"."+u.getUserAtIndex(0).uid), 1);
					addChild(_mush1);
					_enabledMushrooms.push(_mush1);
				}
				if(u.length > 1) {
					setTimeout(_mush2.populate, 2, MD5.hash(u.getUserAtIndex(1).name+"."+u.getUserAtIndex(1).uid), 1);
					addChild(_mush2);
					_enabledMushrooms.push(_mush2);
				}
				if(u.length > 2) {
					setTimeout(_mush3.populate, 3, MD5.hash(u.getUserAtIndex(2).name+"."+u.getUserAtIndex(2).uid), 1);
					addChild(_mush3);
					_enabledMushrooms.push(_mush3);
				}
				setTimeout(placeMushrooms, 4);
			}
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Gets the contaminations targets
		 */
		public function getTargets():Array {
			return VectorUtils.toArray(_enabledMushrooms);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			visible = false;
			_ground = addChild(new Bitmap(new Ground2Bmp(NaN, NaN))) as Bitmap;
			_sky = ViewLocator.getInstance().locateViewByType(BackgroundView) as BackgroundView;
			_mush1 = new Mushroom();
			_mush2 = new Mushroom();
			_mush3 = new Mushroom();
			
			_enabledMushrooms = new Vector.<Mushroom>();
			
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
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			if(_displayed) {
				y = stage.stageHeight - _ground.height;
			}else{
				y = stage.stageHeight + 200;
			}
		}
		
		/**
		 * Called when the spores are thrown
		 */
		private function throwSporesHandler(event:LightEvent):void {
			_displayed = true;
			addEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}
		
		/**
		 * Move sthe ground according to the sky's position
		 */
		private function enterFrameHandler(event:Event):void {
			if(_sky.skyAngle >= Math.PI) {
				if (_sky.scrollSpeed < 50) {
					visible = true;
					TweenLite.to(this, 1.2, {y:stage.stageHeight-_ground.height, delay:2.8});
					removeEventListener(Event.ENTER_FRAME, enterFrameHandler);
				}
			}
		}
		
		/**
		 * Places the mushrooms
		 */
		private function placeMushrooms():void {
			var i:int, len:int, bounds:Rectangle, inc:Number;
			len = _enabledMushrooms.length;
			inc = 1/(_enabledMushrooms.length + 1);
			for(i = 0; i < len; ++i) {
				bounds = _enabledMushrooms[i].getBounds(_enabledMushrooms[i]);
				_enabledMushrooms[i].x = stage.stageWidth * inc * (i+1) - bounds.width*.5 - bounds.x;
				_enabledMushrooms[i].y = _ground.height - bounds.height - bounds.y - Math.random() * 65;
			}
			
			_enabledMushrooms.sort(sortOnY);
			
			for(i = 0; i < len; ++i) {
				addChild(_enabledMushrooms[i]);
			}
			
			setInterval(jumpMushrooms, 1000);
		}

		private function sortOnY(a:Mushroom, b:Mushroom):int {
			var ba:Rectangle = a.getBounds(a);
			var bb:Rectangle = b.getBounds(b);
			var diff:Number = (a.y + ba.bottom) - (b.y + bb.bottom);
			if(diff < 0) return -1;
			if(diff > 0) return 1;
			return 0;
		}

		
		/**
		 * Makes the mushrooms jump
		 */
		private function jumpMushrooms():void {
			var i:int, len:int;
			len = _enabledMushrooms.length;
			for(i = 0; i < len; ++i) {
				if(Math.random() > .8 && !_enabledMushrooms[i].isJumping) {
					if(_enabledMushrooms[i].x < 150) {
						_enabledMushrooms[i].jump(false);
					}else if(_enabledMushrooms[i].x > stage.stageWidth - 150 - _enabledMushrooms[i].width) {
						_enabledMushrooms[i].jump(true);
					}else{
						_enabledMushrooms[i].jump(Math.random() > .475);
					}
					break;
				}
			}
		}
	}
}