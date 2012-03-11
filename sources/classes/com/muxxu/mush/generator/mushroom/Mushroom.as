package com.muxxu.mush.generator.mushroom {
	import gs.TweenLite;
	import gs.easing.Back;
	import gs.easing.Elastic;
	import gs.easing.Sine;

	import flash.display.Sprite;
	import flash.geom.Point;
	
	/**
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	public class Mushroom extends Sprite {
		
		private var _body:Body;
		private var _head:Head;
		private var _eyeL:Eye;
		private var _eyeR:Eye;
		private var _mouth:Mouth;
		private var _key:String;
		private var _ratio:Number;
		private var _holder:Sprite;
		private var _isJumping:Boolean;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Mushroom</code>.
		 */
		public function Mushroom() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Gets if the mushroom is jumping or not
		 */
		public function get isJumping():Boolean { return _isJumping; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component to change it's rendering
		 * 
		 * @param key		MD5 key used to generate the mushroom
		 * @param ratio		mushroom's size ratio
		 */
		public function populate(key:String, ratio:Number):void {
			_ratio = ratio;
			_key = key;
			_head.populate(key, 100 * ratio);
			_body.populate(key, 100 * ratio);
			_eyeL.populate(key, ((parseInt(key.charAt(16),16)/0xf)*10 + 20)*ratio);
			_eyeR.populate(key, ((parseInt(key.charAt(17),16)/0xf)*10 + 20)*ratio);
			_mouth.populate(key, ((parseInt(key.charAt(18),16)/0xf)*10 + 30)*ratio);
			
			_eyeR.scaleX = -_eyeR.scaleX;
			
			placeElements();
		}
		
		/**
		 * Makes the mushroom jump
		 */
		public function jump(left:Boolean = true):void {
			_isJumping = true;
			if(left) {
				TweenLite.to(this, .65, {x:"-"+(150*_ratio), ease:Sine.easeOut, delay:.2});
				TweenLite.to(_holder, .25, {transformAroundPoint:{point:new Point(_head.width*.5, _body.y + _body.height*.8), rotation:-25}, ease:Sine.easeIn, y:45* _ratio, onUpdate:placeElements});
				TweenLite.to(_holder, .25, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:25}, ease:Sine.easeOut, y:-70 * _ratio, delay:.25, onUpdate:placeElements});
				TweenLite.to(_holder, .85, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:0}, ease:Back.easeOut, easeParams:[4], x:0, y:0, delay:.5, onUpdate:placeElements});
				TweenLite.to(_head, .25, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:-15}});
				TweenLite.to(_head, 1.5, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:0}, ease:Elastic.easeOut, easeParams:[3,.6], delay:.5, onComplete:onJumpComplete});
			}else{
				TweenLite.to(this, .65, {x:"+"+(150*_ratio), ease:Sine.easeOut, delay:.2});
				TweenLite.to(_holder, .25, {transformAroundPoint:{point:new Point(_head.width*.5, _body.y + _body.height*.8), rotation:15}, ease:Sine.easeIn, y:40* _ratio, onUpdate:placeElements});
				TweenLite.to(_holder, .25, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:-25}, ease:Sine.easeOut, x:-50*_ratio, y:-70 * _ratio, delay:.25, onUpdate:placeElements});
				TweenLite.to(_holder, .85, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:0}, ease:Back.easeOut, easeParams:[2.5], x:0, y:0, delay:.5, onUpdate:placeElements});
				TweenLite.to(_head, .25, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:15}});
				TweenLite.to(_head, 1.5, {transformAroundPoint:{point:new Point(_head.width*.5, _head.height), rotation:0}, ease:Elastic.easeOut, easeParams:[3,.6], delay:.5, onComplete:onJumpComplete});
			}
		}



		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_holder = addChild(new Sprite()) as Sprite;
			_body = _holder.addChild(new Body()) as Body;
			_head = _holder.addChild(new Head()) as Head;
			_mouth = _holder.addChild(new Mouth()) as Mouth;
			_eyeL = _holder.addChild(new Eye()) as Eye;
			_eyeR = _holder.addChild(new Eye()) as Eye;
		}
		
		/**
		 * Places the elements
		 */
		private function placeElements():void {
			_body.flattenRatio = 1+Math.max(0,_holder.y)/200;
			_mouth.rotation = _body.orientation;
			
			_body.x = _head.bottomPoint.x - _body.width * .5;
			_body.y = _head.bottomPoint.y - _ratio * 5;
			_eyeL.x = _head.bottomPoint.x - _eyeL.width;
			_eyeL.y = _head.bottomPoint.y - _eyeL.height * .15;
			_eyeR.x = _head.bottomPoint.x + _eyeR.width;
			_eyeR.y = _head.bottomPoint.y - _eyeR.height * .15;
			_mouth.scaleY = _mouth.scaleX -(_body.flattenRatio-1)*4;
			_mouth.x = _body.x + _body.bottomPoint.x;
			_mouth.y = _body.y + _body.bottomPoint.y - _mouth.height * .3;
		}
		
		/**
		 * Called when jump animation completes
		 */
		private function onJumpComplete():void {
			_isJumping = false;
		}
		
	}
}