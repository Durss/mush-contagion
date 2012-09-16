package com.muxxu.mush.generator.twinoid {
	import com.muxxu.mush.generator.mushroom.Eye;
	import com.muxxu.mush.generator.mushroom.Mouth;
	import com.muxxu.mush.graphics.SpotGraphic;
	import com.muxxu.mush.graphics.TwinoidFaceGraphic;

	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.DisplayObject;
	import flash.display.Shape;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.filters.BevelFilter;
	import flash.filters.ColorMatrixFilter;
	import flash.geom.Matrix;
	import flash.utils.setTimeout;
	
	/**
	 * 
	 * @author Francois
	 * @date 12 févr. 2012;
	 */
	public class TwinoidFace extends Sprite {
		
		private var _back:TwinoidFaceGraphic;
		private var _width:Number;
		private var _height:Number;
		private var _front:Boolean;
		private var _mouth:Mouth;
		private var _eyeL:Eye;
		private var _eyeR:Eye;
		private var _contaminationPercent:Number;
		private var _ratio:Number;
		private var _spots:Sprite;
		private var _mask:Shape;
		private var _spotsBmd:BitmapData;
		private var _bmp:Bitmap;
		private var _bevel:BevelFilter;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>TwinoidFace</code>.
		 */
		public function TwinoidFace() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Sets the width of the component without simply scaling it.
		 */
		override public function set width(value:Number):void {
			_width = value;
			computePositions();
		}
		
		/**
		 * Sets the height of the component without simply scaling it.
		 */
		override public function set height(value:Number):void {
			_height = value;
			computePositions();
		}
		
		/**
		 * Gets the contamination percent
		 */
		public function get contaminationPercent():Number {
			return _contaminationPercent;
		}
		
		/**
		 * Sets the contamination percent
		 */
		public function set contaminationPercent(value:Number):void {
			_contaminationPercent = value;
			var m:Array = [];
			m.push(1-value*.15, 0, 0, 0, 0);
			m.push(0, 1-value*.35, 0, 0, 0);
			m.push(0, 0, 1+value*.5, 0, 0);
			m.push(0, 0, 0, 1, 0);
			_back.filters = [new ColorMatrixFilter(m)];
			
			var spot:SpotGraphic = _spots.addChild(new SpotGraphic()) as SpotGraphic;
			spot.scaleX = spot.scaleY = Math.random() * 2 + .5;
			spot.addEventListener(Event.COMPLETE, spotCompleteHandler);
			spot.filters = [_bevel];
			
			spot.x = Math.random() * (_width-4-spot.width) - _width * .5 + 2;
			spot.y = Math.random() * (_height-4-spot.height) - _height * .5 + 2;
		}
		
		/**
		 * Sets the contamination percent
		 */
		public function set contaminationPercentCut(value:Number):void {
			_contaminationPercent = value;
			var m:Array = [];
			m.push(1-value*.15, 0, 0, 0, 0);
			m.push(0, 1-value*.35, 0, 0, 0);
			m.push(0, 0, 1+value*.5, 0, 0);
			m.push(0, 0, 0, 1, 0);
			_back.filters = [new ColorMatrixFilter(m)];
			
			var i:int, len:int;
			len = value * 100;
			for(i = 0; i < len; ++i) {
				setTimeout(addSpot, i*100 + 1200);
			}
		}

		private function addSpot():void {
			var spot:SpotGraphic = new SpotGraphic();
			spot.scaleX = spot.scaleY = Math.random() * 2 + .5;
			spot.x = Math.random() * (_width-4-spot.width) - _width * .5 + 2;
			spot.y = Math.random() * (_height-4-spot.height) - _height * .5 + 2;
			spot.gotoAndStop( 1 );
			spot["randomize"]();
			drawSpot(spot);
		}




		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the front face
		 */
		public function populateFront(key:String, ratio:Number):void {
			_ratio = ratio;
			_front = true;
			if(_mouth == null) {
				_mouth = addChild(new Mouth()) as Mouth;
				_eyeL = addChild(new Eye()) as Eye;
				_eyeR = addChild(new Eye()) as Eye;
			}
			
			_eyeL.populate(key, ((parseInt(key.charAt(16),16)/0xf)*10 + 20)*ratio);
			_eyeR.populate(key, ((parseInt(key.charAt(17),16)/0xf)*10 + 20)*ratio);
			_mouth.populate(key, ((parseInt(key.charAt(18),16)/0xf)*10 + 30)*ratio);
			
			_eyeR.scaleX = -_eyeR.scaleX;
			computePositions();
		}

		public function populateside():void {
			_back.gotoAndStop(2);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_back = addChild(new TwinoidFaceGraphic()) as TwinoidFaceGraphic;
			_spots = addChild(new Sprite()) as Sprite;
			_bmp = _spots.addChild(new Bitmap()) as Bitmap;
			_mask = addChild(new Shape()) as Shape;
			_spots.mask = _mask;
			
			_bmp.filters = [new BevelFilter(1, 135, 0xffffff, 0, 0, 1, 1, 1, 1, 2)];
			_bevel = new BevelFilter(1, 135, 0xffffff, 0, 0, 1, 1, 1, 1, 2);
			
			_back.stop();
		}
		
		/**
		 * Replaces the elements
		 */
		private function computePositions():void {
			if(isNaN(_width) || isNaN(_height)) return;
			_back.width = _width;
			_back.height = _height;
			
			if(_spotsBmd == null || _width != _spotsBmd.width || _height != _spotsBmd.height) {
				if(_spotsBmd != null) _spotsBmd.dispose();
				_spotsBmd = new BitmapData(_width, _height, true, 0);
				_bmp.bitmapData = _spotsBmd;
				_bmp.x = -_width * .5;
				_bmp.y = -_height * .5;
			}
			
			_mask.graphics.clear();
			_mask.graphics.beginFill(0xff0000, 1);
			_mask.graphics.drawRect(-_width * .5, -_height*.5, _width, _height);
			_mask.graphics.endFill();
			
			if (_front) {
				var base:Number = -Math.max(_eyeL.height, _eyeR.height) * .75;
				_eyeL.x = -_eyeL.width;
				_eyeL.y = base - _eyeL.height * .5;
				_eyeR.x = _eyeR.width;
				_eyeR.y = base - _eyeR.height * .5;
				_mouth.x = 0;
				_mouth.y = _mouth.height * .5 + 15 * _ratio;
			}
		}
		
		/**
		 * Called when a spot's anim completes to remove it from stage and
		 * draw it on a bitmap to reduce memory usage.
		 */
		private function spotCompleteHandler(event:Event):void {
			var spot:DisplayObject = event.target as DisplayObject;
			_spots.removeChild(spot);
			drawSpot(spot);
		}

		private function drawSpot(spot:DisplayObject):void {
			spot.filters = [];
			spot.removeEventListener(Event.COMPLETE, spotCompleteHandler);
			var m:Matrix = new Matrix();
			m.scale(spot.scaleX, spot.scaleY);
			m.translate(spot.x + _width * .5, spot.y + _height * .5);
			_spotsBmd.draw(spot, m);
		}
		
	}
}