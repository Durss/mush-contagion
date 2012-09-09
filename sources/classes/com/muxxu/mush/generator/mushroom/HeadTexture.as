package com.muxxu.mush.generator.mushroom {
	import flash.display.BitmapData;
	import flash.display.Graphics;
	import flash.display.Shape;
	
	/**
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	public class HeadTexture {
		
		private var _texture:BitmapData;
		private var _key:String;
		private var _shape:Shape;
		private var _width:Number;
		private var _height:Number;
		
		
		

		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>HeadTexture</code>.
		 */
		public function HeadTexture() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */

		public function get texture():BitmapData {
			return _texture;
		}

		public function get width():Number {
			return _width;
		}

		public function get height():Number {
			return _height;
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 */
		public function populate(key:String, sizeRatio:Number):void {
			if(_texture != null) _texture.dispose();
			
			_key = key;
			_width = sizeRatio * 100;
			_height = sizeRatio * 100;
			_texture = new BitmapData(_width, _height);
			
			var gr:Graphics = _shape.graphics;
			gr.clear();

			var bgColor:uint = parseInt(key.substr(16, 6), 16);
			gr.beginFill(bgColor, 1);
			gr.drawRect(0, 0, _width, _height);
			
			var circles:int = parseInt(key.charAt(5), 16) * 2 + 5;
			var i:int, len:int, w:int, h:int, cColor:uint, r:int, g:int, b:int, diff:int, px:int, py:int;
			diff = 0x70;
			len = circles;
			r = (bgColor >> 16) & 0xff;
			g = (bgColor >> 8) & 0xff;
			b = bgColor & 0xff;
//			trace(r,g,b)
			cColor = Math.min(0xFF, r+diff)<<16;
			cColor |= Math.min(0xFF, g+diff)<<8;
			cColor |= Math.min(0xFF, b+diff);
//			trace((cColor>>16)&0xff, (cColor>>8)&0xff, cColor&0xff,"\n")
//			cColor = (r > 0xff-diff? r-diff) : r+diff) << 16;
//			cColor |= (g > 0xff-diff? g-diff : g+diff) << 8;
//			cColor |= b > 0xff-diff? b-diff : b+diff;
			
//			cColor = ColorFunctions.setRGBBrightness(bgColor, ColorFunctions.getLuminosity(bgColor) *2);
//			cColor = parseInt(key.substr(13, 6), 16);// ColorFunctions.setRGBContrast(bgColor, Math.random()*225);
			
			//Draw texture's circle
			for(i = 0; i < len; ++i) {
				gr.beginFill(cColor, 1);
				w = parseInt(key.substr((i+2)%32,1), 16)/0xf * _width*.015 + _width * .2;
				h = w;//parseInt(key.substr((i+3)%32,1), 16)/0xf * height*.1 + width * .1;
				
				px = Math.round(parseInt(key.substr(i%32,1), 16)/0xf * 20)/20 * _width;
				py = Math.round(parseInt(key.substr((i+1)%32,1), 16)/0xf * 20)/20 * _height;
				
				gr.drawEllipse( px, py, w, h );
				gr.endFill();
			}
			
			_texture.draw(_shape);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_shape = new Shape();
		}
		
	}
}