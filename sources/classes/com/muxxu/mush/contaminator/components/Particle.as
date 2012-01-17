package com.muxxu.mush.contaminator.components {
	import com.nurun.utils.math.MathUtils;
	import flash.geom.Point;
	import flash.display.Shape;
	
	/**
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class Particle extends Shape {
		
		public var next:Particle;
		public var sx:Number;
		public var sy:Number;
		public var friction:Number;
		private var _incX:Number;
		private var _frequency:Number;
		private var _origin:Point;
		private var _amplitude:Number;
		private var _launched:Boolean;
		private var _radius:Number;
		private var _applitudeLock:Number;
		private var _oy:Number;
		private var _incY:Number;
		private var _distortY:Number;
		private var _distortMax:Number;
		
		


		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Particle</code>.
		 */
		public function Particle() {
			friction = Math.random() * .3 + .3;
			_radius = Math.random() * 2.5 + 1;
			
			x = y = -1;
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Initializes the particle
		 */
		public function init(origin:Point) : void {
			_origin = origin;
			_incX = Math.random() * Math.PI * 2;
			_incY = Math.random() * Math.PI * 2;
			_frequency = (Math.random()-Math.random()) * Math.PI * .01;
			do {
				_amplitude = Math.random() * 100;
			}while(_amplitude < 20);
			y = Math.random() * 2 + origin.y;
			sx = -(origin.x - x)*.25;
			sy = Math.random() * 3 + 3;
			_oy = -1;
			_applitudeLock = 0;
			_distortY = 1;
			_distortMax = Math.random()*200;
			_launched = true;
		}
		
		/**
		 * Moves the particle
		 */
		public function move(lockY:Boolean = false):void {
			if(!_launched) return;
			
			if(sy < 0) {
				_incX += _frequency * MathUtils.sign(_frequency);
			}
			x = _origin.x + Math.sin(_incX) * _amplitude;
			if(lockY) {
				if(_oy == -1) _oy = y;
				_distortY = Math.min(_distortMax, _distortY + 1.5);
				_applitudeLock = Math.min(3, _applitudeLock + .05);
				_incY += 3.1;
				y = _oy + Math.sin(_incY)*_applitudeLock + _distortY;
				scaleY = 1+_applitudeLock/5;
				scaleX = 1-_applitudeLock/10;
			}else{
				y += Math.max(-friction*4, sy);
			}
			
			sy -= sy<0? friction*.25 : friction;
//			_amplitude -= .1;
			if(y < -5) {
				_launched = false;
				parent.removeChild(this);//Diiiiiiiirty tiiiiiiime :D
			}
			graphics.clear();
			graphics.beginFill(0xffff88, Math.random()*.2+.2);
			graphics.drawCircle(0, 0, _radius+2);
			graphics.endFill();
			
			graphics.beginFill(0xC7B40E, 1);
			graphics.drawCircle(0, 0, _radius);
			graphics.endFill();
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}