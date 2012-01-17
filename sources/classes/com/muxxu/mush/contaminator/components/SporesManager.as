package com.muxxu.mush.contaminator.components {
	import flash.display.DisplayObjectContainer;
	import flash.display.Shape;
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.geom.Point;
	
	/**
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class SporesManager extends EventDispatcher {
		
		private var _len:int;
		private var _firstParticle:Particle;
		private var _efTarget:Shape;
		private var _currentParticle:Particle;
		private var _parent:DisplayObjectContainer;
		private var _startPoint:Point;
		private var _lockY:Boolean;
		
		
		

		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>SporesManager</code>.
		 */
		public function SporesManager(len:int, parent:DisplayObjectContainer) {
			_parent = parent;
			_len = len;
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Throws particles
		 */
		public function throwParticles(startPoint:Point, tot:int):void {
			_startPoint=startPoint;var particle:Particle, i:int;
			particle = _currentParticle;
			while(particle != null && i++ < tot) {
				particle.init(startPoint);
				_parent.addChild(particle);
				particle = particle.next;
			}
			_currentParticle = particle;
		}
		
		public function goingUp():void {
			_lockY = true;
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			var i:int, len:int, particle:Particle, tmp:Particle;
			len = _len;
			_firstParticle = _currentParticle = tmp = new Particle();
			for(i = 1; i < len; ++i) {
				particle = new Particle();
				particle.x = -1;
				particle.y = -1;
				tmp.next = particle;
				tmp = particle;
				
			}
			particle.next = _firstParticle;//makes the spool loops
			
			_efTarget = new Shape();
			_efTarget.addEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}
		
		/**
		 * Moves all the particles
		 */
		private function enterFrameHandler(event:Event):void {
			if(_startPoint == null) return;
			
			var particle:Particle, i:int;
			particle = _firstParticle;
			while(particle != null && i++ < _len) {
				if(_lockY) {
//					_parent.filters = [new BlurFilter(0, 5)];
				}
				particle.move(_lockY);
				particle = particle.next;
			}
		}
		
	}
}