package com.muxxu.mush.contaminator.views {
	import by.blooddy.crypto.MD5;

	import gs.TweenLite;

	import com.muxxu.mush.contaminator.components.CharacterTooltip;
	import com.muxxu.mush.contaminator.components.Fog;
	import com.muxxu.mush.contaminator.components.Smoke;
	import com.muxxu.mush.contaminator.controler.FrontControler;
	import com.muxxu.mush.contaminator.events.InfectionEvent;
	import com.muxxu.mush.contaminator.events.LightEvent;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.contaminator.vo.User;
	import com.muxxu.mush.contaminator.vo.UserCollection;
	import com.muxxu.mush.generator.mushroom.Mushroom;
	import com.muxxu.mush.generator.twinoid.Twinoid;
	import com.muxxu.mush.graphics.Ground2Bmp;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.utils.vector.VectorUtils;

	import flash.display.Bitmap;
	import flash.display.DisplayObject;
	import flash.events.Event;
	import flash.filters.DropShadowFilter;
	import flash.geom.Rectangle;
	import flash.utils.setInterval;
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
		private var _mushrooms:Vector.<Mushroom>;
		private var _twinoids:Vector.<Twinoid>;
		private var _pseudos:Vector.<CharacterTooltip>;
		private var _contaminated:int;
		private var _fog:Fog;
		
		
		
		
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
			if(u != null && !model.contaminationComplete) {
				var i:int, len:int, user:User, key:String;
				len = u.length;
				for(i = 0; i < len; ++i) {
					user = u.getUserAtIndex(i);
					key = MD5.hash(user.name+"-_-"+user.uid);
					_twinoids[i] = new Twinoid();
					_mushrooms[i] = new Mushroom();
					_pseudos[i] = new CharacterTooltip();
					_pseudos[i].populate(user.name);
					setTimeout(_twinoids[i].populate, i*1 + 5, key, 1);
					setTimeout(_mushrooms[i].populate, i*1.5 + 5, key, 1);
					_twinoids[i].addEventListener(InfectionEvent.INFECTED, infectionCompleteHandler);
				}
				setTimeout(placeMushrooms, 50);
			}
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Gets the contaminations targets
		 */
		public function getTargets():Array {
			return VectorUtils.toArray(_twinoids);
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
			_mushrooms = new Vector.<Mushroom>();
			_twinoids = new Vector.<Twinoid>();
			_pseudos = new Vector.<CharacterTooltip>();
			
			_ground.filters = [new DropShadowFilter(10,-45,0,.5,50,50,1,2)];
			
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
		 * Moves the ground according to the sky's position
		 */
		private function enterFrameHandler(event:Event):void {
			if(_sky.skyAngle >= Math.PI) {
				if (_sky.scrollSpeed < 50 && !visible) {
					visible = true;
					_fog = addChild(new Fog()) as Fog;
					_fog.alpha = 0;
					TweenLite.to(_fog, 1, {autoAlpha:1, delay:2.8});
					TweenLite.to(this, 1.2, {y:stage.stageHeight-_ground.height, delay:2.8});
				}else{
					var i:int, len:int, target:DisplayObject, bounds:Rectangle;
					len = _twinoids.length;
					for(i = 0; i < len; ++i) {
						target = contains(_mushrooms[i])? _mushrooms[i] : _twinoids[i];
						bounds = target.getBounds(target);
						_pseudos[i].x = target.x + bounds.x + (bounds.width - _pseudos[i].width) * .5;
						_pseudos[i].y = target.y + bounds.y - _pseudos[i].height - 10;
					}
				}
			}
		}
		
		/**
		 * Places the mushrooms
		 */
		private function placeMushrooms():void {
			var i:int, len:int, bounds:Rectangle, inc:Number, rnd:Number;
			len = _twinoids.length;
			inc = 1/(_twinoids.length + 1);
			for(i = 0; i < len; ++i) {
				rnd = Math.random() * 65;
				bounds = _twinoids[i].getBounds(_twinoids[i]);
				_twinoids[i].x = stage.stageWidth * inc * (i+1) - bounds.width*.5 - bounds.x;
				_twinoids[i].y = _ground.height - bounds.height - bounds.y - rnd;
				
				bounds = _mushrooms[i].getBounds(_mushrooms[i]);
				_mushrooms[i].x = stage.stageWidth * inc * (i+1) - bounds.width*.5 - bounds.x;
				_mushrooms[i].y = _ground.height - bounds.height - bounds.y - rnd;
			}
			
			_twinoids.sort(sortOnY);
			_mushrooms.sort(sortOnY);
			
			for(i = 0; i < len; ++i) {
				addChild(_twinoids[i]);
				addChild(_pseudos[i]);
				_twinoids[i].validate();
			}
			
			setInterval(jumpMushrooms, 1000);
		}

		private function sortOnY(a:DisplayObject, b:DisplayObject):int {
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
			len = _twinoids.length;
			for(i = 0; i < len; ++i) {
				if(Math.random() > .85 && !_twinoids[i].isJumping) {
					if(_mushrooms[i].x < 150) {
						_mushrooms[i].jump(false);
						_twinoids[i].jump(false);
						if(contains(_mushrooms[i])) _mushrooms[i].jump(false);
					}else if(_mushrooms[i].x > stage.stageWidth - 150 - _mushrooms[i].width) {
						_mushrooms[i].jump(true);
						_twinoids[i].jump(true);
						if(contains(_mushrooms[i])) _mushrooms[i].jump(true);
					} else {
						var left:Boolean = Math.random() > .475;
						_mushrooms[i].jump(left);
						_twinoids[i].jump(left);
						if(contains(_mushrooms[i])) _mushrooms[i].jump(left);
					}
					break;
				}
			}
		}
		
		/**
		 * Called when a twinoid's infection completes
		 */
		private function infectionCompleteHandler(event:InfectionEvent):void {
			var i:int, len:int;
			len = _twinoids.length;
			for(i = 0; i < len; ++i) {
				if(_twinoids[i] == event.target) {
					break;
				}
			}
			
			var j:int, lenJ:int;
			lenJ = 15;
			for(j = 0; j < lenJ; ++j) {
				addChild(new Smoke(_twinoids[i]));
			}
			
			removeChild(event.target as Twinoid);
			addChildAt(_mushrooms[i], i+1);
			_mushrooms[i].x = _twinoids[i].x - _twinoids[i].width * .5;
			_pseudos[i].setMushMode();
			if(++_contaminated == _twinoids.length) {
				FrontControler.getInstance().contaminationComplete();
			}
		}
	}
}