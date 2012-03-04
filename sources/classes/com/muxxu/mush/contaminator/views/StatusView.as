package com.muxxu.mush.contaminator.views {
	import flash.display.MovieClip;
	import com.muxxu.mush.graphics.CheckGraphic;
	import flash.desktop.ClipboardFormats;
	import flash.desktop.Clipboard;
	import flash.events.MouseEvent;
	import com.muxxu.mush.contaminator.components.MButton;
	import flash.text.TextFieldType;
	import flash.filters.DropShadowFilter;
	import com.nurun.utils.pos.PosUtils;
	import gs.TweenLite;

	import com.muxxu.mush.contaminator.model.Model;
	import com.muxxu.mush.graphics.ScrollerGraphic;
	import com.muxxu.mush.graphics.ScrolltrackGraphic;
	import com.muxxu.mush.graphics.StatusBackgroundGraphic;
	import com.nurun.components.scroll.ScrollPane;
	import com.nurun.components.scroll.scrollable.ScrollableTextField;
	import com.nurun.components.scroll.scroller.scrollbar.Scrollbar;
	import com.nurun.components.scroll.scroller.scrollbar.ScrollbarClassicSkin;
	import com.nurun.components.text.CssTextField;
	import com.nurun.structure.environnement.label.Label;
	import com.nurun.structure.mvc.model.events.IModelEvent;
	import com.nurun.structure.mvc.views.AbstractView;
	import com.nurun.utils.pos.roundPos;

	import flash.events.Event;

	/**
	 * 
	 * @author Francois
	 * @date 3 mars 2012;
	 */
	public class StatusView extends AbstractView {
		private var _tf:CssTextField;
		private var _scrollable:ScrollableTextField;
		private var _scrollpane:ScrollPane;
		private var _statusBack:StatusBackgroundGraphic;
		private var _copyBt:MButton;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>StatusView</code>.
		 */
		public function StatusView() {
			addEventListener(Event.ADDED_TO_STAGE, initialize);
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		/**
		 * Called on model's update
		 */
		override public function update(event:IModelEvent):void {
			var model:Model = event.model as Model;
			if(model.contaminationComplete) {
				TweenLite.to(this, .5, {y:25});
				_scrollable.text = model.status.getRandomStatus();
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
		private function initialize(event:Event):void {
			removeEventListener(Event.ADDED_TO_STAGE, initialize);
			
			_tf = addChild(new CssTextField("resultTitle")) as CssTextField;
			_statusBack = addChild(new StatusBackgroundGraphic()) as StatusBackgroundGraphic;
			_scrollable = new ScrollableTextField("", "resultStatus");
			var track:ScrolltrackGraphic = new ScrolltrackGraphic();
			var skin:ScrollbarClassicSkin = new ScrollbarClassicSkin(null, null, new ScrollerGraphic(), null, track);
			_scrollpane = addChild(new ScrollPane(_scrollable, new Scrollbar(skin, true))) as ScrollPane;
			_copyBt = addChild(new MButton(Label.getLabel("copyStatus"))) as MButton;
			
			_scrollable.selectable = true;
			_scrollable.type = TextFieldType.INPUT;
			_scrollpane.autoHideScrollers = true;
			
			var w:int, margin:int;
			w = 420;
			margin = 10;
			
			_tf.text = Label.getLabel("contaminationComplete");
			_tf.width = _statusBack.width = w - margin * 2;
			_tf.x = _tf.y = margin;
			
			track.filters = [new DropShadowFilter(2, 128, 0, .3, 2, 2, 1, 2, true)];
			_statusBack.filters = [new DropShadowFilter(2, 128, 0, .3, 2, 2, 1, 2, true)];
			filters = [new DropShadowFilter(5,135,0,.5,5,5,1.5,2)];
			
			_statusBack.y = _tf.y + _tf.height + 10;
			_statusBack.x = margin;
			_statusBack.height = 90;
			_scrollpane.width = _statusBack.width - 5;
			_scrollpane.height = _statusBack.height - 3;
			_scrollpane.x = _statusBack.x + 3;
			_scrollpane.y = _statusBack.y + 3;
			
			_copyBt.y = _scrollpane.y + _scrollpane.height + 10;
			_copyBt.x = (w - _copyBt.width) * .5;
			
			roundPos(_tf, _scrollpane, _statusBack, _copyBt);
			PosUtils.hCenterIn(this, stage);
			
			graphics.beginFill(0xffffff, 1);
			graphics.drawRoundRect(0, 0, w, _copyBt.y + _copyBt.height + margin, 15);
			graphics.endFill();
			
			_copyBt.addEventListener(MouseEvent.CLICK, clickCopyHandler);
			
			y = -height - 20;
//			y = 50;
		}
		
		/**
		 * Called when copy button is clicked
		 */
		private function clickCopyHandler(event:MouseEvent):void {
			if(Clipboard.generalClipboard.setData(ClipboardFormats.TEXT_FORMAT, _scrollable.text)) {
				if(_copyBt.icon == null) {
					_copyBt.icon = new CheckGraphic();
					PosUtils.hCenterIn(_copyBt, _scrollpane);
					_copyBt.x += _scrollpane.x;
				}else{
					MovieClip(_copyBt.icon).gotoAndPlay(1);
				}
			}
		}
		
	}
}