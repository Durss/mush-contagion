package com.muxxu.mush.avatar {
	import by.blooddy.crypto.Base64;
	import by.blooddy.crypto.MD5;
	import by.blooddy.crypto.image.PNGEncoder;

	import com.muxxu.mush.contaminator.components.MButton;
	import com.muxxu.mush.generator.mushroom.Mushroom;
	import com.muxxu.mush.generator.twinoid.Twinoid;
	import com.muxxu.mush.graphics.AvatarBaseGraphic;
	import com.nurun.core.lang.boolean.parseBoolean;
	import com.nurun.utils.pos.PosUtils;
	import com.nurun.utils.text.CssManager;

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
	 
	[SWF(width="90", height="120", backgroundColor="0xFFFFFF", frameRate="31")]
	public class AvatarApplication extends MovieClip {
		private var _holder:Sprite;
		private var _back:AvatarBaseGraphic;
		private var _mushroom:Mushroom;
		private var _twinoid:Twinoid;
		private var _button:MButton;
		
		
		
		
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
			
			CssManager.getInstance().setCss(".button {font-family:Trbuchet, Arial; font-size:14px; color:#cc0000; font-weight:bold; flash-bitmap:true; }");
			
			_holder = addChild(new Sprite()) as Sprite;
			_back = _holder.addChild(new AvatarBaseGraphic()) as AvatarBaseGraphic;
			_button = addChild(new MButton("Télécharger")) as MButton;
			
			if(String(loaderInfo.parameters["canDownload"]).toLowerCase() === "false") {
				buttonMode = mouseChildren = tabChildren = tabEnabled = false;
				removeChild(_button);
			}
			
			PosUtils.hCenterIn(_holder, stage);
			PosUtils.hCenterIn(_button, stage);
			_button.y = _back.height + 10;

			var infected:Boolean = true;//parseBoolean(loaderInfo.parameters["infected"]);
			var uid:String = loaderInfo.parameters["uid"] == null ? "89" : loaderInfo.parameters["uid"];
			var pseudo:String = loaderInfo.parameters["pseudo"] == null ? "durss" : String(loaderInfo.parameters["pseudo"]).toLowerCase();
			var key:String = MD5.hash(pseudo+"."+uid);
			
			_mushroom = new Mushroom();
			_mushroom.populate(key, .36);
			if(infected) _holder.addChild(_mushroom);
			_mushroom.filters = [new DropShadowFilter(0,135,0,1,7,7,2,2)];
				
			_twinoid = new Twinoid();
			_twinoid.populate(key, .31);
			_twinoid.setAvatarPosition();
			if(!infected) _holder.addChild(_twinoid);
			_twinoid.filters = [new DropShadowFilter(0,135,0,.35,7,7,2,2)];
			
			_mushroom.x = Math.round((80 - _mushroom.width) * .5) - _mushroom.getBounds(_mushroom).x;
			_mushroom.y = Math.round((80 - _mushroom.height) * .5) - _mushroom.getBounds(_mushroom).y;
			
			_twinoid.x = Math.round((80 - _twinoid.width) * .5) - _twinoid.getBounds(_twinoid).x;
			_twinoid.y = Math.round((80 - _twinoid.height) * .5) - _twinoid.getBounds(_twinoid).y;
			
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
			var bmd:BitmapData = new BitmapData(_back.width, _back.height, true, 0);
			bmd.draw(_holder);
			new FileReference().save(PNGEncoder.encode(bmd));
		}
		
		/**
		 * Updates the content
		 */
		private function update(uid:String, pseudo:String, infected:Boolean):void {
			pseudo = pseudo.toLowerCase();
			var key:String = MD5.hash(pseudo+"."+uid);
			
			if(_holder.contains(_twinoid)) _holder.removeChild(_twinoid);
			if(_holder.contains(_mushroom)) _holder.removeChild(_mushroom);
			_mushroom.populate(key, .36);
			_twinoid.populate(key, .31);
			
			if(!infected) _holder.addChild(_twinoid);
			if(infected) _holder.addChild(_mushroom);
		}
		
		/**
		 * Gets the image's bas64 data.
		 */
		private function getImage(uid:String, pseudo:String, infected:Boolean):String {
			update(uid, pseudo, infected);
			
			var bmd:BitmapData = new BitmapData(_back.width, _back.height, true, 0);
			bmd.draw(_holder);
			return Base64.encode(PNGEncoder.encode(bmd));
		}
		
	}
}