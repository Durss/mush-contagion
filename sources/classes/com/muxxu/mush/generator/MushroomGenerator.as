package com.muxxu.mush.generator {
	import by.blooddy.crypto.MD5;
	import by.blooddy.crypto.image.PNGEncoder;

	import gs.TweenLite;
	import gs.easing.Sine;
	import gs.plugins.TransformAroundCenterPlugin;
	import gs.plugins.TransformAroundPointPlugin;
	import gs.plugins.TweenPlugin;

	import com.innerdrivestudios.visualeffect.WrappingBitmap;
	import com.muxxu.mush.generator.mushroom.Mushroom;
	import com.muxxu.mush.generator.twinoid.Twinoid;
	import com.muxxu.mush.graphics.AvatarBaseGraphic;

	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.MovieClip;
	import flash.display.StageScaleMode;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.filters.DropShadowFilter;
	import flash.net.FileReference;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFieldType;
	import flash.text.TextFormat;
	import flash.text.TextFormatAlign;

	/**
	 * Bootstrap class of the application.
	 * Must be set as the main class for the flex sdk compiler
	 * but actually the real bootstrap class will be the factoryClass
	 * designated in the metadata instruction.
	 * 
	 * @author Francois
	 * @date 21 janv. 2012;
	 */
	 
	[SWF(width="500", height="520", backgroundColor="0xFFFFFF", frameRate="31")]
	public class MushroomGenerator extends MovieClip {
		
		[Embed(source="../../../../../../creas/loopGround.png")]
		private var _groundClass:Class;
		
		private var _avatar:AvatarBaseGraphic;
		private var _mushroomSmall:Mushroom;
		private var _mushroomBig:Mushroom;
		private var _input:TextField;
		private var _ground:WrappingBitmap;
		private var _twinoidBig:Twinoid;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function MushroomGenerator() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			TweenPlugin.activate([TransformAroundPointPlugin, TransformAroundCenterPlugin]);
			
			stage.scaleMode = StageScaleMode.NO_SCALE;
			
			_ground = addChild(new WrappingBitmap(Bitmap(new _groundClass()).bitmapData)) as WrappingBitmap;
			_avatar = addChild(new AvatarBaseGraphic()) as AvatarBaseGraphic;
			_mushroomSmall = _avatar.addChildAt(new Mushroom(), 1) as Mushroom;
			_mushroomBig = addChild(new Mushroom()) as Mushroom;
			_twinoidBig = addChild(new Twinoid()) as Twinoid;
			_input = addChild(new TextField()) as TextField;
			
			_avatar.stop();
			
			_input.defaultTextFormat = new TextFormat("Arial", 20, 0, true, null, null, null, null, TextFormatAlign.CENTER);
			_input.border = true;
			_input.background = true;
			_input.type = TextFieldType.INPUT;
			_input.autoSize = TextFieldAutoSize.LEFT;
			_input.width = 300;
			_input.autoSize = TextFieldAutoSize.NONE;
			_input.text = "Durss-_-89";
//			_input.restrict = "[0-9][a-z][A-Z]\.";
			
			_avatar.buttonMode = true;
			_avatar.x = 200;
			_avatar.y = 100;
			_ground.y = 200;
			_ground.scaleX = _ground.scaleY = 1.5;
			_ground.x = -_ground.width * .5 + 150;
			
			_mushroomSmall.filters = [new DropShadowFilter(0,135,0,1,7,7,2,2)];
			
			stage.focus = _input;
			_input.setSelection(0, _input.length);
			
			stage.addEventListener(MouseEvent.CLICK, clickHandler);
			_input.addEventListener(Event.CHANGE, changeHandler);
			_mushroomBig.addEventListener(MouseEvent.CLICK, clickHandler);
			_avatar.addEventListener(MouseEvent.CLICK, clickHandler);
			changeHandler();
		}

		private function clickHandler(event:MouseEvent):void {
			if (event.currentTarget == _avatar) {
				var bmd:BitmapData = new BitmapData(_avatar.width, _avatar.height, true, 0);
				bmd.draw(_avatar);
				var fr:FileReference = new FileReference();
				fr.save(PNGEncoder.encode(bmd), "avatar.png");
				event.stopPropagation();
			}else if (event.currentTarget == _mushroomBig) {
//				_mushroomBig.hideElements();
//				var bounds:Rectangle = _mushroomBig.getBounds(_mushroomBig);
//				var m:Matrix = new Matrix();
//				m.translate(-bounds.x, -bounds.y);
//				var bmd:BitmapData = new BitmapData(_mushroomBig.width, _mushroomBig.height, true, 0);
//				bmd.draw(_mushroomBig, m);
//				var fr:FileReference = new FileReference();
//				fr.save(PNGEncoder.encode(bmd), "mushroom.png");
//				event.stopPropagation();
			}else{
				var left:Boolean = Math.random() > .5;
				_twinoidBig.jump(left);
				_mushroomBig.jump(left);
//				_mushroomSmall.jump(left);
				var slide:Number = left ? 200 : -200;
				TweenLite.killTweensOf(_twinoidBig);
				TweenLite.killTweensOf(_mushroomBig);
				TweenLite.killTweensOf(_mushroomSmall);
				TweenLite.to(_ground, .65, {scrollX:_ground.scrollX + slide, ease:Sine.easeOut, delay:.2});
			}
		}

		private function changeHandler(event:Event = null):void {
			_twinoidBig.targeted = 20;
//			_twinoidBig.touch();
			
			var key:String = MD5.hash(_input.text);
			_mushroomSmall.populate(key, .36);
//			_mushroomBig.populate(key, 4);
			_mushroomBig.populate(key, 1.5);
			_mushroomBig.x = 25;
			_mushroomBig.y = 30;
			
			_twinoidBig.populate(key, 1.5);
			_twinoidBig.x = 400;
			_twinoidBig.y = 200;
			
			_mushroomSmall.x = Math.round((80 - _mushroomSmall.width) * .5) - _mushroomSmall.getBounds(_mushroomSmall).x;
			_mushroomSmall.y = Math.round((80 - _mushroomSmall.height) * .5) - _mushroomSmall.getBounds(_mushroomSmall).y;
		}
		
	}
}