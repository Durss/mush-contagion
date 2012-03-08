package com.muxxu.mush.generator.twinoid {
	import gs.TweenLite;
	import gs.easing.Elastic;
	import gs.easing.Sine;

	import com.muxxu.mush.contaminator.events.InfectionEvent;
	import com.nurun.components.volume.Cube;

	import flash.display.Sprite;
	import flash.geom.PerspectiveProjection;
	import flash.geom.Point;
	
	
	[Event(name="infected", type="com.muxxu.mush.contaminator.events.InfectionEvent")]
	
	/**
	 * 
	 * @author Francois
	 * @date 12 févr. 2012;
	 */
	public class Twinoid extends Sprite {
		
		private var _holder:Sprite;
		private var _body:Cube;
		private var _ratio:Number;
		private var _key:String;
		private var _isJumping:Boolean;
		private var _targeted:int;
		private var _touched:int;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Twinoid</code>.
		 */
		public function Twinoid() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Gets if the mushroom is jumping or not
		 */
		public function get isJumping():Boolean { return _isJumping; }

		public function get targeted():int { return _targeted; }

		public function set targeted(targeted:int):void { _targeted = targeted; }



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
			_body.width = ratio * 110;
			_body.height = ratio * 80;
			_body.depth = ratio * 70;
			TwinoidFace(_body.frontFace).populateFront(key, ratio);
//			TwinoidFace(_body.leftFace).populateside();
//			TwinoidFace(_body.rightFace).populateside();
//			TwinoidFace(_body.backFace).populateside();
		}
		
		/**
		 * Makes the avatar jump.
		 * 
		 * @param left	jump left or right
		 */
		public function jump(left:Boolean):void {
			_isJumping = true;
			_body.rotationY = 10;
			
			TweenLite.to(_body, .2, {scaleY:.8, y:90*_ratio*.15});
			TweenLite.to(_body, .5, {scaleY:1, ease:Elastic.easeOut, easeParams:[5, .25], delay:.2});
			TweenLite.to(_body, .1, {overwrite:0, y:-90*_ratio, ease:Sine.easeIn, delay:.15});
			TweenLite.to(_body, .3, {overwrite:0, y:0, ease:Sine.easeIn, delay:.4});
			TweenLite.to(_body, .1, {scaleY:.7, y:90*_ratio*.2, ease:Sine.easeOut, delay:.7});
			TweenLite.to(_body, .2, {scaleY:1, y:0, ease:Sine.easeOut, delay:.8, onComplete:onJumpComplete});
			if(left) {
//				TweenLite.to(this, .65, {x:"-"+(150*_ratio), ease:Sine.easeOut, delay:.2});
				TweenLite.to(_body, .8, {overwrite:0, rotationY:-345, ease:Sine.easeOut, delay:.1, onUpdate:_body.validate});
			}else{
//				TweenLite.to(this, .65, {x:"+"+(150*_ratio), ease:Sine.easeOut, delay:.2});
				TweenLite.to(_body, .8, {overwrite:0, rotationY:345, ease:Sine.easeOut, delay:.1, onUpdate:_body.validate});
			}
		}
		
		/**
		 * Called when a particle touch it.
		 */
		public function touch():void {
			if(++_touched >= _targeted) {
				_isJumping = true;
				TweenLite.to(_body, 2, {scaleX:.5, ease:Elastic.easeIn, easeParams:[1,.1], onComplete:onTransformComplete});
			}else{
				TwinoidFace(_body.topFace).contaminationPercent = _touched/_targeted;
				TwinoidFace(_body.leftFace).contaminationPercent = _touched/_targeted;
				TwinoidFace(_body.rightFace).contaminationPercent = _touched/_targeted;
				TwinoidFace(_body.backFace).contaminationPercent = _touched/_targeted;
				TwinoidFace(_body.bottomFace).contaminationPercent = _touched/_targeted;
				TwinoidFace(_body.frontFace).contaminationPercent = _touched/_targeted;
			}
		}
		
		/**
		 * Forces the component's rendering
		 */
		public function validate():void {
			_body.validate();
		}

		public function setAvatarPosition():void {
			_body.rotationX = 0;
			_body.rotationY = 20;
			_body.validate();
		}




		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_holder = addChild(new Sprite()) as Sprite;
			_body = _holder.addChild(new Cube()) as Cube;
			
			_body.allFaces = TwinoidFace;
			_body.rotationX = 10;

			var pp:PerspectiveProjection = new PerspectiveProjection();
			pp.projectionCenter = new Point(0,0);
			_body.transform.perspectiveProjection = pp;
		}
		
		/**
		 * Called when jump animation completes
		 */
		private function onJumpComplete():void {
			_isJumping = false;
		}
		
		/**
		 * Called when contamination's transofrmation completes.
		 */
		private function onTransformComplete():void {
			_isJumping = false;
			dispatchEvent(new InfectionEvent(InfectionEvent.INFECTED));
		}
		
	}
}