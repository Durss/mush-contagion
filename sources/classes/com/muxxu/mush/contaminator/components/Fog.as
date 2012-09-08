package com.muxxu.mush.contaminator.components {
	import com.nurun.core.lang.Disposable;
	import com.nurun.utils.math.MathUtils;

	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.BitmapDataChannel;
	import flash.display.InterpolationMethod;
	import flash.display.Shape;
	import flash.display.SpreadMethod;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.filters.BlurFilter;
	import flash.geom.Matrix;
	import flash.geom.Point;
	
	/**
	 * 
	 * @author Francois
	 * @date 5 sept. 2012;
	 */
	public class Fog extends Sprite {
		private var _p1:Point;
		private var _p2:Point;
		private var _bmd:BitmapData;
		private var _holder:Sprite;
		private var _maskMc:Shape;
		private var _seed:Number;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Fog</code>.
		 */
		public function Fog() {
			addEventListener(Event.ADDED_TO_STAGE, initialize);
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		override public function get height():Number {
			return 300;
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */

		public function stop():void {
			removeEventListener(Event.ENTER_FRAME, enterFrameHandler);
			while(numChildren > 0) {
				if(getChildAt(0) is Disposable) Disposable(getChildAt(0)).dispose();
				removeChildAt(0);
			}
			_bmd.dispose();
			_holder.mask = null;
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, initialize);
			_p1 = new Point();
			_p2 = new Point();
			_bmd = new BitmapData(150, 400, true, 0);
			_maskMc = addChild(new Shape()) as Shape;
			_seed = MathUtils.randomNumberFromRange(0, 99999999999999);
			
			var m:Matrix = new Matrix();
			m.createGradientBox(_bmd.width, _bmd.height, Math.PI*.5);
			_maskMc.graphics.beginGradientFill("linear", [0xff0000,0xff0000], [0,.5], [0,0x5f], m, SpreadMethod.PAD, InterpolationMethod.RGB);
			_maskMc.graphics.drawRect(0, 0, _bmd.width, _bmd.height);
			_maskMc.graphics.endFill();
			
			_holder = addChild(new Sprite()) as Sprite;
			_holder.addChild(new Bitmap(_bmd));
			
			_holder.cacheAsBitmap = true;
			_maskMc.cacheAsBitmap = true;
			_holder.mask = _maskMc;
			
			//*
			_holder.width = stage.stageWidth;
			_holder.height = height;
//			_holder.y = stage.stageHeight - _holder.height;
			//*/
			_maskMc.x = _holder.x;
			_maskMc.y = _holder.y;
			_maskMc.width = _holder.width;
			_maskMc.height = _holder.height;
			
//			_holder.blendMode = BlendMode.SUBTRACT;
			
			addEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}
		
		private function enterFrameHandler(event:Event):void {
			_p1.x += .5;
			_p2.x -= .5;
			_bmd.fillRect(_bmd.rect, 0);
			_bmd.perlinNoise(200, 200, 2, _seed, true, true, BitmapDataChannel.ALPHA, true, [_p1, _p2]);
			_bmd.threshold(_bmd, _bmd.rect, new Point(), "<", 0x777777, 0, 0xffffff, true);
			_bmd.applyFilter(_bmd, _bmd.rect, new Point(), new BlurFilter(30,30,2));
			//bmd.copyChannel(msk, msk.rect, new Point(), BitmapDataChannel.ALPHA, BitmapDataChannel.ALPHA);
			//bmd.draw(msk, null, ct, BlendMode.ALPHA);
			//bmd.merge(msk, msk.rect, new Point(), 0,0,0,0);
		}
		
	}
}