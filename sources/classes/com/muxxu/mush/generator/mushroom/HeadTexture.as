package com.muxxu.mush.generator.mushroom {
	import flash.display.BitmapData;
	import flash.display.Graphics;
	import flash.display.Shape;
	
	/**
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	public class HeadTexture extends BitmapData {
		
		private var _key:String;
		private var _shape:Shape;
		
		
		

		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>HeadTexture</code>.
		 */
		public function HeadTexture() {
			super(500,500,false);
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 */
		public function populate(key:String):void {
			_key = key;
			
			var gr:Graphics = _shape.graphics;
			gr.clear();

			var bgColor:uint = parseInt(key.substr(16, 6), 16);
			gr.beginFill(bgColor, 1);
			gr.drawRect(0, 0, width, height);
			
			var circles:int = (width * height) * .002;
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
				w = parseInt(key.substr((i+2)%32,1), 16)/0xf * width*.015 + width * .2;
				h = w;//parseInt(key.substr((i+3)%32,1), 16)/0xf * height*.1 + width * .1;
				
				px = Math.round(parseInt(key.substr(i%32,1), 16)/0xf * 20)/20 * width;
				py = Math.round(parseInt(key.substr((i+1)%32,1), 16)/0xf * 20)/20 * height;
				
				gr.drawEllipse( px, py, w, h );
				gr.endFill();
			}
			
			fillRect(rect, 0);
			draw(_shape);
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