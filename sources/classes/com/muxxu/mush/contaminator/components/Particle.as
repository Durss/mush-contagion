package com.muxxu.mush.contaminator.components {
	import com.nurun.utils.math.MathUtils;
	import flash.display.DisplayObject;
	import flash.display.Shape;
	import flash.geom.Point;
	
	/**
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class Particle extends Shape {
		
		public var next:Particle;
		private var _sy:Number;
		private var _friction:Number;
		private var _incX:Number;
		private var _frequency:Number;
		private var _origin:Point;
		private var _amplitude:Number;
		private var _launched:Boolean;
		private var _radius:Number;
		private var _oy:Number;
		private var _incY:Number;
		private var _distort:Number;
		private var _distortMax:Number;
		private var _lockY:Boolean;
		private var _distance:Number;
		private var _offsetAngle:Number;
		private var _center:Point;
		private var _vibration:Number;
		private var _aVib:Number;
		private var _angle:Number;
		private var _distanceRatio:Number;
		private var _angle2:Number;
		private var _endOffsetAngle:Number;
		private var _amplitudeRand:Number;
		private var _incAngle:Number;
		private var _amplitudeEased:Number;
		private var _slowDown:Boolean;
		private var _target:DisplayObject;
		private var _px:Number;
		private var _maxSy:Number;
		
		


		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Particle</code>.
		 */
		public function Particle() {
			_friction = Math.random() * .3 + .3;
			_radius = Math.random() * 2.5 + 1;
			
			x = y = -1;
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Gets if the particle is launched
		 */
		public function get launched():Boolean { return _launched; }



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
			_amplitude = 20 + Math.random() * 80;
			y = Math.random() * 2 + origin.y;
			_sy = Math.random() * 3 + 3;
			_oy = -1;
			_launched = true;
			
			graphics.clear();
			graphics.beginFill(0xffff88, Math.random()*.2+.2);
			graphics.drawCircle(0, 0, _radius+2);
			graphics.endFill();
			
			graphics.beginFill(0xC7B40E, 1);
			graphics.drawCircle(0, 0, _radius);
			graphics.endFill();
		}
		
		/**
		 * Moves the particle
		 */
		public function move(angle:Number, offsetY:Number):void {
			if(!_launched) return;
			
			//If the particle is locked to a specific target
			if(_target != null) {
				_incX += _frequency;
				var endX:Number = _target.x + _target.width * .5;
				var endY:Number = _target.y + _target.parent.y + _target.height * .5;
				_px += (endX - _px) * (Math.max(0, y)/5000);
				x = _px + Math.sin(_incX) * _amplitude;
				y += _sy;
				if(y < endY) {
					_sy += .1;
				}else{
					_sy -= .1;
				}
				_sy = MathUtils.restrict(_sy, -_maxSy, _maxSy);
				if(Math.abs(x - endX) < 20 && Math.abs(y - endY) < 20) {
					_launched = false;
					parent.removeChild(this);//Diiiiiiiirty tiiiiiiime :D
				}
				return;
			}
			
			
			//Synch with the sky's animation
			if(_lockY) {
				if(_angle == -1) {
					_angle = _offsetAngle + angle;
					_angle2 = angle+Math.PI*.5;
				}else{
					if(!_slowDown) {
						_amplitudeEased += (_amplitude - _amplitudeEased) * .02;//Ease the "wave" effect amplitude
					}else{
						_amplitudeEased *= .98;
					}
					_angle += (_offsetAngle+angle - _angle) * _distanceRatio;//Ease the global direction of the particles depending on the sky's orientation
					_angle += Math.sin(_incX)*_amplitudeEased;//Makes particles moves inside the global angle making them "wave"
					_angle += Math.sin(_incAngle)*_amplitudeRand;//Add randomness to the particles angles to make them "walk" inside the cloud
					if(!_slowDown) {
						_offsetAngle += (_endOffsetAngle - _offsetAngle) * .01;//shapes the cloud in triangle
					}
				}
				if(!_slowDown) {
					_aVib = Math.min(_aVib + .01, 2);//Vibration amplitude
					_vibration += 2.5;//Vibration frequency
					_distort += (_distortMax - _distort) * .01;//makes the particles stretch on a bigger surface
					_angle2 += (angle+Math.PI*.5 - _angle2) * .2;//ease the stretched and the offset position's angles
				}else{
					_aVib *= .98;
					_distort *= .98;
				}
				
				x = Math.cos(_angle) * _distance + _center.x + Math.cos(_angle2) * _distort;
				y = Math.sin(_angle) * _distance + _center.y + Math.sin(_angle2) * _distort;
				x += Math.cos(angle+Math.PI*.5) * Math.cos(_vibration) * _aVib;
				y += Math.sin(angle+Math.PI*.5) * Math.cos(_vibration) * _aVib;
				
				x += Math.abs(Math.cos(_angle2)) * stage.stageWidth*.5;
				y += Math.abs(Math.cos(_angle2)) * stage.stageHeight*.5 + offsetY;
				
				scaleY = 1 + _aVib*.05;
				scaleX = 1 - _aVib*.05;
				rotation = angle * 57.29577951308232;
				
				_incX += .1;
				_incAngle += _frequency;
				if(!_slowDown) {
					_frequency *= 1.01;
				}else{
					_frequency *= .99;
				}
			
			//Simple going up animation
			}else{
				x = _origin.x + Math.sin(_incX) * _amplitude;
				y += Math.max(-_friction*4, _sy);
				
				if(_sy < 0) _incX += _frequency;
				_sy -= _sy<0? _friction*.25 : _friction;
			}
			
			if(!_lockY && y < -5) {
				_launched = false;
				parent.removeChild(this);//Diiiiiiiirty tiiiiiiime :D
			}
		}
		
		/**
		 * Locks the Y position to keep the particle on the screen
		 */
		public function lockY():void {
			if(!_launched) return;
			
			_lockY = true;
			_aVib = 0;
			_incX = 0;
			_incAngle = 0;
			_angle = -1;
			_angle2 = 0;
			_distort = 0;
			_vibration = Math.random() * Math.PI * 2;
			_center = new Point(stage.stageWidth*.5, 0);
			_distance = Math.sqrt( Math.pow(_center.x - x, 2) + Math.pow(_center.y - y, 2));
			_offsetAngle = Math.atan2(y - _center.y, x - _center.x);
			_distortMax = ((y*y*y*y) * .0000000190);
			_distanceRatio = Math.min(1, 1/((_distance*_distance*_distance*_distance*_distance*_distance) * .00000000000001));
			_endOffsetAngle = Math.PI * .5 + (_offsetAngle-Math.PI * .5)*((_distance+_distortMax)/800);
			_amplitude = _distanceRatio*.3;
			_amplitudeEased = 0;
			_amplitudeRand = _distanceRatio*.05;
			_frequency *= 5;
		}
		
		/**
		 * Slow down the particles
		 */
		public function slowDown():void {
			_slowDown = true;
		}
		
		/**
		 * Locks the particle to a target.
		 */
		public function lockTarget(target:DisplayObject):void {
			_target = target;
			_amplitude = Math.random()*15 + 10;
			_incX = Math.random()*Math.PI*2;
			_frequency = Math.random() * Math.PI * .05 + .05;
			if(Math.random() < .5) _frequency = -_frequency;
			_px = _target.x + _target.width + MathUtils.randomNumberFromRange(-300, 300);
			_sy = 0;
			y += 100;
			_maxSy = Math.random()* 2 + 2;
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}