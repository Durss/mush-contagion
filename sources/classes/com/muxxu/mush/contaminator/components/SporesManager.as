package com.muxxu.mush.contaminator.components {
	import com.muxxu.mush.generator.twinoid.Twinoid;
	import com.muxxu.mush.contaminator.views.BackgroundView;
	import com.muxxu.mush.contaminator.views.ContaminationView;
	import com.nurun.structure.mvc.views.ViewLocator;
	import com.nurun.utils.array.ArrayUtils;

	import flash.display.DisplayObjectContainer;
	import flash.display.Shape;
	import flash.events.Event;
	import flash.events.EventDispatcher;
	import flash.geom.Point;
	import flash.utils.setTimeout;
	
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
		private var _backgroundView:BackgroundView;
		private var _offsetY:int;
		private var _slowingDown:Boolean;
		private var _mushroomsView:ContaminationView;
		private var _timeFlag:Boolean;
		
		
		

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
			_offsetY = 0;
			_currentParticle = particle;
		}
		
		/**
		 * Loks the particles into the screen.
		 */
		public function startAnimation():void {
			var particle:Particle, i:int;
			particle = _firstParticle;
			while(particle != null && i++ < _len) {
				particle.lockY();
				particle = particle.next;
			}
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
			particle.next = _firstParticle;//makes the spool loop
			
			_efTarget = new Shape();
			_efTarget.addEventListener(Event.ENTER_FRAME, enterFrameHandler);
			_backgroundView = ViewLocator.getInstance().locateViewByType(BackgroundView) as BackgroundView;
			_mushroomsView = ViewLocator.getInstance().locateViewByType(ContaminationView) as ContaminationView;
		}
		
		/**
		 * Moves all the particles
		 */
		private function enterFrameHandler(event:Event):void {
			if(_startPoint == null) return;
			var particle:Particle, i:int;
			
			if(_backgroundView.skyAngle > Math.PI * .5){
				//slow down
				if (!_slowingDown && _backgroundView.scrollSpeed < 40) {
					_slowingDown = true;
					particle = _firstParticle;
					while(particle != null && i++ < _len) {
						particle.slowDown();
						particle = particle.next;
					}
				}
				if(_slowingDown) {
					_offsetY = Math.max(0, _offsetY-5);
				}else{
					_offsetY = Math.min(500, _offsetY+7);
				}
			}
			
			if(_backgroundView.scrollSpeed == 0 && !_timeFlag) {
				_timeFlag = true;
				setTimeout(contaminate, 2000);
			}
			
			particle = _firstParticle;
			while(particle != null && i++ < _len) {
				particle.move(_backgroundView.skyAngle, _offsetY);
				particle = particle.next;
			}
		}
		
		/**
		 * Contaminates the mushrooms
		 */
		private function contaminate():void {
			var particle:Particle, i:int, target:Twinoid;
			var targets:Array = _mushroomsView.getTargets();
			
			particle = _firstParticle;
			while(particle != null && i++ < _len) {
				if(particle.launched) {
					target = ArrayUtils.getRandom(targets) as Twinoid;
					target.targeted ++;
					particle.lockTarget( target );
				}
				particle = particle.next;
			}
			
		}
		
	}
}