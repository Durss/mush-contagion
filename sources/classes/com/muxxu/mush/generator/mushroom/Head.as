package com.muxxu.mush.generator.mushroom {
	import com.cartogrammar.drawing.CubicBezier;

	import flash.display.Shape;
	import flash.filters.DropShadowFilter;
	import flash.geom.Matrix;
	import flash.geom.Point;
	
	/**
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	public class Head extends Shape {
		
		private var _key:String;
		private var _sizeRatio:Number;
		private var _texture:HeadTexture;
		private var _m:Matrix;
		private var _bottomPoint:Point;
		
		
		


		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Head</code>.
		 */
		public function Head() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Gets the bottom's point
		 */
		public function get bottomPoint():Point { return _bottomPoint; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 * 
		 * @param key			generation's key
		 * @param sizeRatio		size ratio
		 */
		public function populate(key:String, sizeRatio:Number = 100):void {
			_sizeRatio = sizeRatio;
			_key = key;
			_texture.populate(key);
			_m = new Matrix();
			_m.scale(_sizeRatio*(1.2/_texture.width), _sizeRatio*(1.2/_texture.width));
			update();
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_texture = new HeadTexture();
		}
		
		/**
		 * Updates the component's rendering
		 */
		private function update():void {
			var points:Array = [];
			var ratio1:Number = parseInt(_key.substr(25, 2),16) / 0xff * .3 - .15;
			var ratio2:Number = parseInt(_key.substr(26, 2),16) / 0xff * .2 - .1;
			var ratio3:Number = parseInt(_key.substr(27, 2),16) / 0xff * .2 - .15;
			var ratio4:Number = parseInt(_key.substr(28, 2),16) / 0xff * .2 - .1;
			var ratio5:Number = parseInt(_key.substr(29, 2),16) / 0xff * .2 - .1;
			var ratio6:Number = parseInt(_key.substr(30, 2),16) / 0xff * .3 - .15;
			var ratio7:Number = parseInt(_key.substr(21, 2),16) / 0xff * .2 - .1;
			var ratio8:Number = parseInt(_key.substr(0, 2),16) / 0xff * .2 - .1;
			var ratio9:Number = parseInt(_key.substr(1, 2),16) / 0xff * .2 - .1;
			var ratio10:Number = parseInt(_key.substr(2, 2),16) / 0xff * .3 - .15;
			var ratio11:Number = parseInt(_key.substr(3, 2),16) / 0xff * .3 - .15;
			var ratio12:Number = parseInt(_key.substr(4, 2),16) / 0xff * .2 - .1;
			
			points.push( new Point(_sizeRatio * (.5 + ratio1) , _sizeRatio * (.1 + ratio2)) );
			points.push( new Point(_sizeRatio * (.25 + ratio3) , _sizeRatio * (.5 + ratio4)) );
			points.push( new Point(_sizeRatio * (.1 + ratio5) , _sizeRatio * (.9 + ratio6)) );
			points.push( new Point(_sizeRatio * (.5 + ratio7) , _sizeRatio * (.9 + ratio8)) );//bottom
			points.push( new Point(_sizeRatio * (.9 + ratio9) , _sizeRatio * (.9 + ratio10)) );
			points.push( new Point(_sizeRatio * (.75 + ratio11) , _sizeRatio * (.5 + ratio12)) );
			
			points.push(points[0]);
			_bottomPoint = points[3];
			
			graphics.clear();
			graphics.beginBitmapFill(_texture, _m, true, true);
			CubicBezier.curveThroughPoints(graphics, points, .5, .75);
//			graphics.beginFill(0xff0000, 1);
//			graphics.drawCircle(_bottomPoint.x, _bottomPoint.y, 4)
			
			filters = [new DropShadowFilter(_sizeRatio*.2, -45, 0, .3, _sizeRatio*.1, _sizeRatio*.1, 1, 3, true),
					   new DropShadowFilter(_sizeRatio*.1, 135, 0xffffff, .3, _sizeRatio*.1, _sizeRatio*.1, 1, 3, true)];
		}
		
	}
}