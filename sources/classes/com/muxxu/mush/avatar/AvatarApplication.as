package com.muxxu.mush.avatar {
	import by.blooddy.crypto.Base64;
	import by.blooddy.crypto.MD5;
	import by.blooddy.crypto.image.PNGEncoder;

	import com.muxxu.mush.contaminator.components.MButton;
	import com.muxxu.mush.generator.mushroom.Mushroom;
	import com.muxxu.mush.generator.twinoid.Twinoid;
	import com.muxxu.mush.graphics.ArchiveGraphic;
	import com.muxxu.mush.graphics.AvatarBaseGraphic;
	import com.nurun.core.lang.boolean.parseBoolean;
	import com.nurun.utils.bitmap.BitmapUtils;
	import com.nurun.utils.pos.PosUtils;
	import com.nurun.utils.text.CssManager;

	import flash.display.Bitmap;
	import flash.display.BitmapData;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import flash.events.MouseEvent;
	import flash.external.ExternalInterface;
	import flash.filters.DropShadowFilter;
	import flash.net.FileReference;
	import flash.utils.setTimeout;

	/**
	 * Bootstrap class of the application.
	 * Must be set as the main class for the flex sdk compiler
	 * but actually the real bootstrap class will be the factoryClass
	 * designated in the metadata instruction.
	 * 
	 * @author Francois
	 * @date 4 mars 2012;
	 */
	 
	[SWF(width="150", height="100", backgroundColor="0xFFFFFF", frameRate="31")]
	public class AvatarApplication extends MovieClip {
		private var _holder:Sprite;
		private var _back:AvatarBaseGraphic;
		private var _mushroom:Mushroom;
		private var _twinoid:Twinoid;
		private var _button:MButton;
		private var _scale:Number;
		private var _screenshot:Bitmap;
		private var _screenshotSmall:Bitmap;
		private var _overlay:ArchiveGraphic;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>Application</code>.
		 */
		public function AvatarApplication() {
			if(loaderInfo.parameters["buildDelay"] != undefined) {
				setTimeout(initialize, parseInt(loaderInfo.parameters["buildDelay"]));
			}else{
				initialize();
			}
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
			stage.align = StageAlign.TOP_LEFT;
			stage.showDefaultContextMenu = false;
			stage.scaleMode = StageScaleMode.NO_SCALE;
			
			CssManager.getInstance().setCss(".button {font-family:Trebuchet, Arial; font-size:14px; color:#cc0000; font-weight:bold; flash-bitmap:true; }");

			_scale = 2;
			
			_holder = addChild(new Sprite()) as Sprite;
			_back = _holder.addChild(new AvatarBaseGraphic()) as AvatarBaseGraphic;
			_overlay = new ArchiveGraphic();
			_button = addChild(new MButton("Télécharger")) as MButton;
			
			if(String(loaderInfo.parameters["canDownload"]).toLowerCase() === "false") {
				buttonMode = mouseChildren = tabChildren = tabEnabled = false;
				removeChild(_button);
			}
			
			_back.scaleX = _back.scaleY = _scale;
			_overlay.scaleX = _overlay.scaleY = _scale;
			_button.y = _back.height / _scale + 10;
			PosUtils.hCenterIn(_button, stage);

			var infected:Boolean = parseBoolean(loaderInfo.parameters["infected"]);
			var uid:String = loaderInfo.parameters["uid"] == null ? "89" : loaderInfo.parameters["uid"];
			var pseudo:String = loaderInfo.parameters["pseudo"] == null ? "Durss" : String(loaderInfo.parameters["pseudo"]);
			var key:String = MD5.hash(pseudo+"-_-"+uid);
//			infected = true;
			_mushroom = new Mushroom();
			_mushroom.populate(key, .31 * _scale);
			if(infected) _holder.addChild(_mushroom);
			_mushroom.filters = [new DropShadowFilter(0,135,0,1,7,7,2,2)];
			_twinoid = new Twinoid();
			_twinoid.populate(key, .31 * _scale, 0);
			_twinoid.setAvatarPosition();
			if(!infected) _holder.addChild(_twinoid);
			_twinoid.filters = [new DropShadowFilter(0,135,0,.35,7,7,2,2)];
			_overlay.filters = [new DropShadowFilter(0,135,0,.35,3,3,1,3)];
			if(parseBoolean(loaderInfo.parameters["overlay"])) {
				_holder.addChild(_overlay);
			}
			
			_back.gotoAndStop(infected? 2 : 1);
			
			_mushroom.x = (Math.round((_back.width - _mushroom.getBounds(_mushroom).width) * .5) - _mushroom.getBounds(_mushroom).x);
			_mushroom.y = (Math.round((_back.height - _mushroom.getBounds(_mushroom).height) * .5) - _mushroom.getBounds(_mushroom).y);
			
			_twinoid.x = (Math.round((_back.width - _twinoid.getBounds(_twinoid).width) * .5) - _twinoid.getBounds(_twinoid).x);
			_twinoid.y = (Math.round((_back.height - _twinoid.getBounds(_twinoid).height) * .5) - _twinoid.getBounds(_twinoid).y);
			
			var bmd:BitmapData = new BitmapData(_holder.width, _holder.height, true, 0);
			bmd.draw(_holder);
			while(_holder.numChildren > 0) { _holder.removeChildAt(0); }
			_screenshot = new Bitmap(bmd);
			_screenshotSmall = _holder.addChild(new Bitmap(BitmapUtils.resampleBitmapData(bmd, 1/_scale))) as Bitmap;
			PosUtils.hCenterIn(_holder, stage);
			
			addEventListener(MouseEvent.CLICK, clickHandler);
			if(ExternalInterface.available) {
				ExternalInterface.addCallback("getImage", getImage);
				ExternalInterface.addCallback("update", update);
				ExternalInterface.call("flashReady");
			}
			
			buttonMode = true;
		}

		
		/**
		 * Called when something is clicked.
		 */
		private function clickHandler(event:MouseEvent):void {
			new FileReference().save(PNGEncoder.encode(_screenshot.bitmapData), "avatar.png");
		}
		
		/**
		 * Updates the content
		 */
		private function update(uid:String, pseudo:String, infected:Boolean, hd:Boolean = false, overlay:Boolean = false):void {
			var key:String = MD5.hash(pseudo+"-_-"+uid);
			
			while(_holder.numChildren > 0) { _holder.removeChildAt(0); }
			_holder.addChild(_back);
			if(!infected) _holder.addChild(_twinoid);
			if(infected) _holder.addChild(_mushroom);
			if(overlay) _holder.addChild(_overlay);
			
			_scale = hd? 5 : 1.5;
			_back.scaleX = _back.scaleY = _scale;
			_overlay.scaleX = _overlay.scaleY = _scale;
			_back.gotoAndStop(infected? 2 : 1);
			
			if(infected) {
				_mushroom.populate(key, .31 * _scale);
				_mushroom.x = (Math.round((_back.width - _mushroom.getBounds(_mushroom).width) * .5) - _mushroom.getBounds(_mushroom).x);
				_mushroom.y = (Math.round((_back.height - _mushroom.getBounds(_mushroom).height) * .5) - _mushroom.getBounds(_mushroom).y);
			}else{
				_twinoid.populate(key, .31 * _scale, 0);
				_twinoid.setAvatarPosition();
				_twinoid.x = (Math.round((_back.width - _twinoid.getBounds(_twinoid).width) * .5) - _twinoid.getBounds(_twinoid).x);
				_twinoid.y = (Math.round((_back.height - _twinoid.getBounds(_twinoid).height) * .5) - _twinoid.getBounds(_twinoid).y);
			}
			
			var bmd:BitmapData = new BitmapData(_holder.width, _holder.height, true, 0);
			bmd.draw(_holder);
			while(_holder.numChildren > 0) { _holder.removeChildAt(0); }
			_screenshot.bitmapData = bmd;
			_screenshotSmall.bitmapData = BitmapUtils.resampleBitmapData(bmd, 1/_scale);
			_holder.addChild(_screenshotSmall);
			PosUtils.hCenterIn(_holder, stage);
		}
		
		/**
		 * Gets the image's base64 data.
		 */
		private function getImage(uid:String, pseudo:String, infected:Boolean, hd:Boolean = false, overlay:Boolean = false):String {
			update(uid, pseudo, infected, hd, overlay);
			
			return Base64.encode(PNGEncoder.encode(_screenshot.bitmapData));
		}
		
	}
}