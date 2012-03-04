package com.muxxu.mush.avatar {
	import com.nurun.utils.text.CssManager;
	import flash.display.StageAlign;
	import com.nurun.utils.pos.PosUtils;
	import com.nurun.utils.math.MathUtils;
	import com.muxxu.mush.contaminator.components.MButton;
	import by.blooddy.crypto.image.PNGEncoder;
	import flash.net.FileReference;
	import flash.display.BitmapData;
	import flash.events.MouseEvent;
	import flash.display.StageScaleMode;
	import by.blooddy.crypto.MD5;

	import com.muxxu.mush.generator.mushroom.Mushroom;
	import com.muxxu.mush.generator.twinoid.Twinoid;
	import com.muxxu.mush.graphics.AvatarBaseGraphic;
	import com.nurun.core.lang.boolean.parseBoolean;

	import flash.display.DisplayObject;
	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.filters.DropShadowFilter;

	/**
	 * Bootstrap class of the application.
	 * Must be set as the main class for the flex sdk compiler
	 * but actually the real bootstrap class will be the factoryClass
	 * designated in the metadata instruction.
	 * 
	 * @author Francois
	 * @date 4 mars 2012;
	 */
	 
	[SWF(width="90", height="150", backgroundColor="0xFFFFFF", frameRate="31")]
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
			stage.align = StageAlign.TOP_LEFT;
			stage.scaleMode = StageScaleMode.NO_SCALE;
			
			CssManager.getInstance().setCss(".button {font-family:Trbuchet, Arial; font-size:14px; color:#cc0000; font-weight:bold; flash-bitmap:true; }")
			
			_holder = addChild(new Sprite()) as Sprite;
			_back = _holder.addChild(new AvatarBaseGraphic()) as AvatarBaseGraphic;
			_button = addChild(new MButton("Télécharger")) as MButton;
			
			PosUtils.hCenterIn(_holder, stage);
			PosUtils.hCenterIn(_button, stage);
			_button.y = _back.height + 10;

			var infected:Boolean = parseBoolean(loaderInfo.parameters["infected"]);
			var uid:String = loaderInfo.parameters["uid"] == null ? "89" : loaderInfo.parameters["uid"];
			var pseudo:String = loaderInfo.parameters["pseudo"] == null ? "durss" : String(loaderInfo.parameters["pseudo"]).toLowerCase();
			var key:String = MD5.hash(pseudo+"."+uid);
			
			var item:DisplayObject;
			if(infected) {
				item = _mushroom = new Mushroom();
				_mushroom.populate(key, .36);
				_holder.addChild(_holder.addChild(_mushroom));
				item.filters = [new DropShadowFilter(0,135,0,1,7,7,2,2)];
			}else{
				item = _twinoid = new Twinoid();
				_twinoid.populate(key, .31);
				_twinoid.setAvatarPosition();
				_holder.addChild(_holder.addChild(_twinoid));
				item.filters = [new DropShadowFilter(0,135,0,.35,7,7,2,2)];
			}
			
			item.x = Math.round((80 - item.width) * .5) - item.getBounds(item).x;
			item.y = Math.round((80 - item.height) * .5) - item.getBounds(item).y;
			
			addEventListener(MouseEvent.CLICK, clickHandler);
			
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
		
	}
}