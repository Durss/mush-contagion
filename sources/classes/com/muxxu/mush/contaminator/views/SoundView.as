package com.muxxu.mush.contaminator.views {
	import com.muxxu.mush.contaminator.controler.FrontControler;
	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.graphics.SoundIconOffGraphic;
	import com.muxxu.mush.graphics.SoundIconOnGraphic;
	import com.nurun.components.button.visitors.applyDefaultFrameVisitorNoTween;
	import com.nurun.components.form.ToggleButton;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.utils.pos.PosUtils;

	import flash.events.Event;

	/**
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class SoundView extends AbstractView {
		private var _button:ToggleButton;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>SoundView</code>.
		 */
		public function SoundView() {
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
			if(model.soundEnabled) {
				_button.unSelect();
			}else{
				_button.select();
			}
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
			_button = addChild(new ToggleButton("","", "", null, null, new SoundIconOnGraphic(), new SoundIconOffGraphic())) as ToggleButton;
			
			applyDefaultFrameVisitorNoTween(_button, _button.defaultIcon, _button.selectedIcon);
			
			addEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			_button.addEventListener(Event.CHANGE, clickHandler);
		}
		
		/**
		 * Toggles the sound's state
		 */
		private function clickHandler(event:Event):void {
			FrontControler.getInstance().toggleSound();
		}
		
		/**
		 * Called when the stage is available.
		 */
		private function addedToStageHandler(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, addedToStageHandler);
			stage.addEventListener(Event.RESIZE, computePositions);
			computePositions();
		}
		
		/**
		 * Resize and replace the elements.
		 */
		private function computePositions(event:Event = null):void {
			PosUtils.alignToRightOf(_button, stage);
		}
		
	}
}