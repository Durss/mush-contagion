package com.muxxu.mush.contaminator.components {
	import gs.TweenLite;
	import gs.easing.Sine;

	import com.muxxu.mush.contaminator.events.SpeakEvent;
	import com.muxxu.mush.graphics.SpeakArrowGraphic;
	import com.nurun.components.text.CssTextField;
	import com.nurun.structure.environnement.configuration.Config;
	import com.nurun.structure.environnement.label.Label;

	import flash.display.Sprite;
	import flash.media.Sound;
	import flash.net.URLRequest;
	import flash.utils.clearInterval;
	import flash.utils.setInterval;
	
	/**
	 * 
	 * @author Francois
	 * @date 15 janv. 2012;
	 */
	public class SpeakToolTip extends Sprite {
		
		private var _tf:CssTextField;
		private var _sentences:XMLList;
		private var _charIndex:int;
		private var _sentenceIndex:int;
		private var _interval:uint;
		private var _waitFor:Number;
		private var _sound:Sound;
		private var _arrow:SpeakArrowGraphic;
		private var _x:Number;
		private var _y:Number;
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>SpeakToolTip</code>.
		 */
		public function SpeakToolTip() {
			initialize();
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */
		override public function set x(value:Number):void { _x = value; }
		
		override public function set y(value:Number):void { _y = value; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 */
		public function populate(labelId:String):void {
			var xml:XML = new XML(Label.getLabel(labelId));
			_sentences = xml.child("s");
			_charIndex = 0;
			_sentenceIndex = 0;
			
			_interval = setInterval(write, 30);
			TweenLite.to(this, .25, {autoAlpha:1, ease:Sine.easeIn});
			_sound.play(500);
		}


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		/**
		 * Initialize the class.
		 */
		private function initialize():void {
			_arrow = addChild(new SpeakArrowGraphic()) as SpeakArrowGraphic;
			_tf = addChild(new CssTextField("speak")) as CssTextField;
			
			alpha = 0;
			_tf.multiline = true;
			
			_sound = new Sound(new URLRequest(Config.getPath("music")));
		}
		
		/**
		 * Writes the dialogue
		 */
		private function write():void {
			if(--_waitFor > 0) return;
			
			var sentence:String = _sentences[_sentenceIndex];
			
			_tf.text = sentence;
			var w:Number = _tf.width;
			var h:Number = _tf.height;
			var margin:int = 10;
			
			_tf.text = sentence.substr(0, _charIndex);
			//Permet d'éviter les césures changeantes durant l'écriture lettre par lettre.
			//[...]marche pas finalement... :(
//			_tf.text += "<font color='#ffffff'>"+sentence.substr(_charIndex-1)+"</font>";
			if(_charIndex > sentence.length) {
				_waitFor = sentence.length * .6;
				_charIndex = 0;
				_sentenceIndex ++;
				if(_sentenceIndex >= _sentences.length()) {
					clearInterval(_interval);
				}
				dispatchEvent(new SpeakEvent(SpeakEvent.STOP_SPEAK));
			}else{
				dispatchEvent(new SpeakEvent(SpeakEvent.SPEAK));
			}
			
			if(sentence.substr(_charIndex, 7).toLowerCase() == "*atcha*") {
				dispatchEvent(new SpeakEvent(SpeakEvent.SNEEZE));
			}
			
			if(sentence.charAt(_charIndex) == "<") {
				if (sentence.substr(_charIndex, 5) == "<wait") {
					_waitFor = Math.round(1000/30 * parseFloat(sentence.substring(_charIndex+5, sentence.indexOf(" ", _charIndex))));
					dispatchEvent(new SpeakEvent(SpeakEvent.STOP_SPEAK));
				}
				_charIndex = sentence.indexOf(">", _charIndex);
			}
			
			_charIndex ++;
			
			graphics.clear();
			graphics.beginFill(0xffffff, 1);
			graphics.drawRoundRect(0, 0, w + margin * 2, h + margin * 2, 15, 15);
			graphics.endFill();
			_tf.x = _tf.y = margin;
			
			_arrow.x = -17;
			_arrow.y = h - 14 + margin * 2;
			
			super.x = _x + _arrow.x;
			super.y = _y - _arrow.y - _arrow.height;
		}
		
	}
}