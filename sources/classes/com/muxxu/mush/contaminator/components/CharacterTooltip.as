package com.muxxu.mush.contaminator.components {
	import flash.display.DisplayObject;
	import flash.utils.clearTimeout;
	import gs.TweenLite;
	import gs.easing.Sine;

	import com.nurun.components.text.CssTextField;
	import com.nurun.structure.environnement.label.Label;

	import flash.display.Sprite;
	import flash.filters.DropShadowFilter;
	import flash.text.TextFieldAutoSize;
	import flash.utils.setTimeout;
	
	/**
	 * 
	 * @author Francois
	 * @date 3 mars 2012;
	 */
	public class CharacterTooltip extends Sprite {
		
		private var _tf:CssTextField;
		private var _labels:XMLList;
		private var _timeout:uint;
		private var _pseudo:String;
		private var _style:String;
		private var _target1:DisplayObject;
		private var _target2:DisplayObject;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>CharacterTooltip</code>.
		 */
		public function CharacterTooltip() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */

		public function get target1():DisplayObject {
			return _target1;
		}

		public function get target2():DisplayObject {
			return _target2;
		}



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 */
		public function populate(pseudo:String, target1:DisplayObject, target2:DisplayObject):void {
			_target2 = target2;
			_target1 = target1;
			_pseudo = pseudo;
			_tf.autoSize = TextFieldAutoSize.LEFT;
			_tf.wordWrap = false;
			var label:String, labels:XMLList;
			var hasCustom:Boolean = _labels.(attribute("p") == pseudo).length() > 0;
			if (hasCustom && Math.random() > .8) {
				labels = _labels.(attribute("p") == pseudo);
				label = labels[Math.floor(Math.random() * labels.length())];
			}else{
				labels = _labels.(attribute("p") == undefined);
				label = labels[Math.floor(Math.random() * labels.length())];
			}
			_tf.text = "<span class='pseudo'>"+pseudo + " :</span><br /><span class='"+_style+"'>" + label + "</span>";
			_tf.x = _tf.y = 5;
			if(_tf.width > 150) _tf.width = 150;
			
			graphics.clear();
			graphics.beginFill(0xffffff, 1);
			graphics.drawRoundRect(0, 0, _tf.width + 10, _tf.height + 10, 5);
			graphics.endFill();
			alpha = 1;
			TweenLite.from(this, .5, {autoAlpha:0, ease:Sine.easeIn});
			
			clearTimeout(_timeout);
			_timeout = setTimeout(populate, Math.random()*15000 + 10000, pseudo, _target1, _target2);
		}
		
		/**
		 * Sets the mush dialogs
		 */
		public function setMushMode():void {
			_labels = new XML(Label.getLabel("mush")).child("s");
			_style = "chatMush";
			populate(_pseudo, _target1, _target2);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_tf = addChild(new CssTextField()) as CssTextField;
			_labels = new XML(Label.getLabel("twinoids")).child("s");
			_style = "chatTwin";
			
			filters = [new DropShadowFilter(5, 135, 0, .5, 5, 5, 1.5, 2)];
		}
		
	}
}