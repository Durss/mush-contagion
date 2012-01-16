package com.muxxu.mush.contaminator.components {
	import flash.events.MouseEvent;
	import flash.display.CapsStyle;
	import flash.display.LineScaleMode;
	import flash.display.Sprite;
	import flash.events.Event;
	
	/**
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class CursorStreak extends Sprite {
		
		private var _historyX:Array;
		private var _historyY:Array;
		private var _prevY:Number;
		private var _prevX:Number;
		private var _enabled:Boolean;
		private var _pressed:Boolean;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>CursorStreak</code>.
		 */
		public function CursorStreak() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		public function get huge():Boolean { return _pressed; }
		
		public function get historyX():Array { return _historyX; }
		
		public function get historyY():Array { return _historyY; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Enables the component
		 */
		public function enable():void {
			if(_enabled) return;
			
			_enabled = true;
			addEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}
		
		/**
		 * Disables the component
		 */
		public function disable():void {
			removeEventListener(Event.ENTER_FRAME, enterFrameHandler);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_historyX = [];
			_historyY = [];
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			stage.addEventListener(MouseEvent.MOUSE_DOWN, mouseEventHandler);
			stage.addEventListener(MouseEvent.MOUSE_UP, mouseEventHandler);
		}

		private function mouseEventHandler(event:MouseEvent):void {
			_pressed = event.type == MouseEvent.MOUSE_DOWN;
		}

		/**
		 * Draw the lines
		 */
		private function enterFrameHandler(event:Event):void {
			if(_prevX != stage.mouseX && _prevY != stage.mouseY) {
				_historyX.push(stage.mouseX);
				_historyY.push(stage.mouseY);
				_prevX = stage.mouseX;
				_prevY = stage.mouseY;
			}else{
				_historyX.shift();
				_historyY.shift();
			}
			
			var i:int, len:int, size:Number, alpha:Number;
			len = _historyX.length;
			i = len-2;
			size = _pressed? 8 : 2;
			alpha = _pressed? 1 : .5;
			graphics.clear();
			graphics.moveTo(stage.mouseX, stage.mouseY);
			for(i; i > -1; --i) {
				graphics.lineStyle(size, 0xffffff, alpha, false, LineScaleMode.NONE, CapsStyle.NONE);
				graphics.lineTo(_historyX[i], _historyY[i]);
				if(i < len-1) {
					_historyX[i] -= (_historyX[i]-_historyX[i+1])*.25;
					_historyY[i] -= (_historyY[i]-_historyY[i+1])*.25;
				}
				size *= .7;
				alpha *= .7;
			}
			
			var max:int = _pressed? 6 : 2;
			if(len > max) {
				_historyX.shift();
				_historyY.shift();
			}
		}
		
	}
}