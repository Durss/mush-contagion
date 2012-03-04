package com.muxxu.mush.generator.twinoid {
	import flash.filters.ColorMatrixFilter;
	import com.muxxu.mush.generator.mushroom.Eye;
	import com.muxxu.mush.generator.mushroom.Mouth;
	import com.muxxu.mush.graphics.TwinoidFaceGraphic;

	import flash.display.Sprite;
	
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
		public function set contaminationPercent(contaminationPercent:Number):void {
			_contaminationPercent = contaminationPercent;
			var m:Array = [];
			m.push(1-contaminationPercent*.3, 0, 0, 0, 0);
			m.push(0, 1-contaminationPercent*.7, 0, 0, 0);
			m.push(0, 0, 1+contaminationPercent, 0, 0);
			m.push(0, 0, 0, 1, 0);
			_back.filters = [new ColorMatrixFilter(m)];
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the front face
		 */
		public function populateFront(key:String, ratio:Number):void {
			_front = true;
			if(_mouth == null) {
				_mouth = addChild(new Mouth()) as Mouth;
				_eyeL = addChild(new Eye()) as Eye;
				_eyeR = addChild(new Eye()) as Eye;
			}
			
			_eyeL.populate(key, ((parseInt(key.charAt(16),16)/0xf)*10 + 20)*ratio);
			_eyeR.populate(key, ((parseInt(key.charAt(17),16)/0xf)*10 + 20)*ratio);
			_mouth.populate(key, ((parseInt(key.charAt(18),16)/0xf)*10 + 30)*ratio);
			
			_eyeR.scaleX = -1;
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_back = addChild(new TwinoidFaceGraphic()) as TwinoidFaceGraphic;
		}
		
		/**
		 * Replaces the elements
		 */
		private function computePositions():void {
			_back.width = _width;
			_back.height = _height;
			
			if (_front) {
				var base:Number = -Math.max(_eyeL.height, _eyeR.height) * .75;
				_eyeL.x = -_eyeL.width;
				_eyeL.y = base - _eyeL.height * .5;
				_eyeR.x = _eyeR.width;
				_eyeR.y = base - _eyeR.height * .5;
				_mouth.x = 0;
				_mouth.y = _mouth.height * .5 + 15;
			}
		}
		
	}
}