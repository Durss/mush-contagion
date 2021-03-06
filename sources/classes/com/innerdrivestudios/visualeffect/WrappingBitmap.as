package com.innerdrivestudios.visualeffect {
	import flash.display.BitmapData;
	import flash.display.Sprite;
	import flash.geom.Matrix;
	import flash.geom.Point;

	/**

	 * Implements a sprite that shows the source bitmap wrapped and allows you to scroll through it preserving the

	 * wrapping.

	 *

	 * @author JC Wichman

	 */
	public class WrappingBitmap extends Sprite {
		private var _bitmapdata:BitmapData = null;
		private var _width:Number = 0;
		private var _height:Number = 0;
		private var _matrix:Matrix = null;

		public function WrappingBitmap(pBitmapData:BitmapData, pWidth:Number = -1, pHeight:Number = -1, pOffset:Point = null) {
			_bitmapdata = pBitmapData;

			_width = (pWidth > 0) ? pWidth : pBitmapData.width;

			_height = (pHeight > 0) ? pHeight : pBitmapData.height;

			_matrix = new Matrix();

			if (pOffset != null) {
				_matrix.tx = -pOffset.x;

				_matrix.ty = -pOffset.y;
			}

			_paint();
		}

		private function _paint():void {
			_matrix.tx = _matrix.tx % _width;
			_matrix.ty = _matrix.ty % _height;
			
			graphics.clear();

			graphics.beginBitmapFill(_bitmapdata, _matrix, true, false);

			graphics.drawRect(0, 0, _width, _height);

			graphics.endFill();
		}

		public function scroll(dx:Number, dy:Number):void {
			_matrix.tx += dx;
			_matrix.ty += dy;

			_paint();
		}

		public function scrollTo(x:Number, y:Number):void {
			_matrix.tx = -x;
			_matrix.ty = -y;
			
			_paint();
		}

		public function grab(pDestination:BitmapData = null):BitmapData {
			var lResult:BitmapData = pDestination || new BitmapData(_width, _height, _bitmapdata.transparent, 0);

			lResult.draw(this);

			return lResult;
		}

		public function get scrollX():Number {
			return _matrix.tx;
		}

		public function set scrollX(value:Number):void {
			_matrix.tx = value;
			_paint();
		}

		public function get bitmapdata():BitmapData {
			return _bitmapdata;
		}
	}
}