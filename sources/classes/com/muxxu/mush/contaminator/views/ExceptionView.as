package com.muxxu.mush.contaminator.views {
	import flash.display.Sprite;
	import com.nurun.utils.draw.createRect;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.contaminator.throwables.ContaminatorError;
	import com.nurun.components.text.CssTextField;
	import com.nurun.structure.environnement.label.Label;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.utils.pos.PosUtils;

	import flash.display.Shape;
	import flash.events.Event;
	import flash.events.UncaughtErrorEvent;
	import flash.filters.DropShadowFilter;

	/**
	 * 
	 * @author Francois
	 * @date 29 janv. 2012;
	 */
	public class ExceptionView extends AbstractView {
		private var _tf:CssTextField;
		private var _disableLayer:Shape;
		private var _popin:Sprite;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>ExceptionView</code>.
		 */
		public function ExceptionView() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Called on model's update
		 */
		override public function update(event:IModelEvent):void {
			var model:Model = event.model as Model;
			model;
		}



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
			visible = false;
			_disableLayer = addChild(createRect(0x66000000)) as Shape;
			_popin = addChild(new Sprite()) as Sprite;
			_tf = _popin.addChild(new CssTextField("speak")) as CssTextField;
			_tf.multiline = true;
			filters = [new DropShadowFilter(5,135,0,.5,5,5,1.5,2)];
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			loaderInfo.uncaughtErrorEvents.addEventListener(UncaughtErrorEvent.UNCAUGHT_ERROR, uncaughtErrorHandler);
			stage.addEventListener(Event.RESIZE, computePositions);
			computePositions();
		}
		
		/**
		 * Called if an uncaught error occurs
		 */
		private function uncaughtErrorHandler(event:UncaughtErrorEvent):void {
			visible = true;
			if (event.error is ContaminatorError) {
				var code:String = ContaminatorError(event.error).code;
				_tf.text = Label.getLabel(code);
			}
			event.stopPropagation();
			event.preventDefault();

			var margin:int = 10;
			_tf.x = _tf.y = margin;
			_popin.graphics.clear();
			_popin.graphics.beginFill(0xffffff, 1);
			_popin.graphics.drawRoundRect(0, 0, _tf.width + margin * 2, _tf.height + margin * 2, 15, 15);
			_popin.graphics.endFill();
			
			computePositions();
		}
		
		/**
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			PosUtils.centerInStage(_popin);
			_disableLayer.width = stage.stageWidth;
			_disableLayer.height = stage.stageHeight;
		}
		
	}
}