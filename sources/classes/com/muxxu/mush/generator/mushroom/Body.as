package com.muxxu.mush.generator.mushroom {
	import com.cartogrammar.drawing.CubicBezier;
	import com.nurun.utils.color.ColorFunctions;
	import com.nurun.utils.math.MathUtils;

	import flash.display.Shape;
	import flash.filters.DropShadowFilter;
	import flash.geom.Matrix;
	import flash.geom.Point;
	
	/**
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	public class Body extends Shape {
		
		private var _key:String;
		private var _sizeRatio:Number;
		private var _texture:HeadTexture;
		private var _m:Matrix;
		private var _bottomPoint:Point;
		private var _flattenRatio:Number;
		private var _orientation:Number;
		private var _color:uint;
		private var _refPoints:Vector.<Point>;
		
		
		


		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Body</code>.
		 */
		public function Body() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Gets the width of the component.
		 */
		override public function get width():Number { return _sizeRatio * .5; }
		
		/**
		 * Gets the height of the component.
		 */
		override public function get height():Number { return _sizeRatio; }
		
		/**
		 * Gets the bottom's center.
		 */
		public function get bottomPoint():Point { return _bottomPoint; }

		/**
		 * Gets the body's orientation.
		 * Basically it's the angle between the top and the bottom
		 */
		public function get orientation():Number { return _orientation; }

		public function get flattenRatio():Number { return _flattenRatio; }

		public function set flattenRatio(flattenRatio:Number):void { _flattenRatio = flattenRatio; draw(); }
		


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
			_m.scale(_sizeRatio*.01, _sizeRatio*.01);
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
			_flattenRatio = 1;
		}
		
		/**
		 * Updates the component's rendering
		 */
		private function update():void {
			_refPoints = new Vector.<Point>();
			var ratio1:Number = parseInt(_key.substr(15, 2),16) / 0xff * .2;
			var ratio2:Number = parseInt(_key.substr(16, 2),16) / 0xff * .4;
			var ratio3:Number = parseInt(_key.substr(17, 2),16) / 0xff * .1;
			var ratio4:Number = parseInt(_key.substr(18, 2),16) / 0xff * .4;
			var ratio5:Number = parseInt(_key.substr(19, 2),16) / 0xff * .1;
			var ratio6:Number = parseInt(_key.substr(20, 2),16) / 0xff * .2;
			var hRatio:Number = parseInt(_key.substr(14, 2),16) / 0xff * .5 + .5;
			
			_refPoints.push( new Point(_sizeRatio * (.4 - ratio1)*.5 , 0) );
			_refPoints.push( new Point(_sizeRatio * (.4 - ratio2)*.7 , _sizeRatio * (.8 + ratio3)*hRatio) );
			_refPoints.push( new Point(_sizeRatio * (.6 + ratio4)*.7 , _sizeRatio * (.8 + ratio5)*hRatio) );
			_refPoints.push( new Point(_sizeRatio * (.6 + ratio6)*.5 , 0) );
			
			_refPoints.push(_refPoints[0]);

			var cRatio:Number = parseInt(_key.substr(12, 2), 16) / 0xff * 2 - 1;
			var src:int = 0xE1DA95;
			//Workaround of a shitty behavior of the util that do not computes the
			//luminosity of the color if it's the same color than the last.
			//But because of an object's instance modification, without this
			//the color would get brighter and brighter.
			ColorFunctions.getLuminosity(0);
			var luminosity:Number = ColorFunctions.getLuminosity(src) + cRatio * 10;
			_color = ColorFunctions.setRGBBrightness(src, luminosity);
			
			draw();
			
			filters = [new DropShadowFilter(_sizeRatio * .1, -45, 0, .0, _sizeRatio * .05, _sizeRatio * .05, 1, 3, true), new DropShadowFilter(_sizeRatio * .15, 40, 0, .5, _sizeRatio * .1, _sizeRatio * .1, .5, 3, true)];
		}

		private function draw():void {
			var points:Array = [];
			var i:int, len:int;
			len = _refPoints.length;
			for(i = 0; i < len; ++i) {
				points[i] = _refPoints[i].clone();
			}
			var p1:Point = points[1];
			var p2:Point = points[2];
			p1.x -= (_flattenRatio-1)*_sizeRatio*.75;
			p1.y -= (_flattenRatio-1)*_sizeRatio*.75;
			p2.x += (_flattenRatio-1)*_sizeRatio*.75;
			p2.y -= (_flattenRatio-1)*_sizeRatio*.75;
			
			_bottomPoint = new Point();
			_bottomPoint.x = p1.x + (p2.x - p1.x)*.5;
			_bottomPoint.y = p1.y + (p2.y - p1.y)*.5;

			var topPoint:Point = new Point();
			topPoint.x = Point(points[0]).x + (Point(points[3]).x - Point(points[0]).x)*.5;
			topPoint.y = Point(points[0]).y + (Point(points[3]).y - Point(points[0]).y)*.5;
			
			_orientation = Math.atan2(_bottomPoint.y - topPoint.y, _bottomPoint.x - topPoint.x) * MathUtils.RAD2DEG - 90;
			
			graphics.clear();
			graphics.beginFill(_color, 1);
			CubicBezier.curveThroughPoints(graphics, points, .5, .75);
			
//			graphics.beginFill(0xff0000);
//			graphics.drawCircle(topPoint.x, topPoint.y, 2);
//			graphics.drawCircle(_bottomPoint.x, _bottomPoint.y, 2);
//			graphics.lineStyle(0, 0xff0000);
//			graphics.moveTo(topPoint.x, topPoint.y);
//			graphics.lineTo(_bottomPoint.x, _bottomPoint.y);
		}
		
	}
}