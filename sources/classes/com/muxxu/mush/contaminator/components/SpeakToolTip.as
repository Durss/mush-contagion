package com.muxxu.mush.contaminator.components {
	import flash.media.SoundChannel;
	import flash.filters.DropShadowFilter;
	import flash.events.MouseEvent;
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
		private var _speaking:Boolean;
		private var _reg:RegExp;
		private var _channel:SoundChannel;
		
		
		
		
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
		
		public function get speaking():Boolean { return _speaking; }



		/* ****** *
		 * PUBLIC *
		 * ****** */
		/**
		 * Populates the component
		 */
		public function populate(labelId:String, music:Boolean =true):void {
			var xml:XML = new XML(Label.getLabel(labelId));
			_sentences = xml.child("s");
			_charIndex = 0;
			_sentenceIndex = 0;
			_speaking = true;
			_tf.text = "";
			_waitFor = 0;
			
			_interval = setInterval(write, 30);
			write();
			TweenLite.to(this, .25, {autoAlpha:1, ease:Sine.easeIn});
			if(music) {
				_channel = _sound.play(500);
			}
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
			
			buttonMode = true;
			mouseChildren = false;
			
			alpha = 0;
			visible = false;
			_reg = new RegExp("</?\w+((\s+\w+(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)/?>", "gi");
			_tf.multiline = true;
			filters = [new DropShadowFilter(5,135,0,.5,5,5,1.5,2)];
			
			_sound = new Sound(new URLRequest(Config.getPath("music")));
			addEventListener(MouseEvent.CLICK, clickHandler);
		}
		
		/**
		 * Called when acomponent is clicked
		 */
		private function clickHandler(event:MouseEvent):void {
			if(_sentenceIndex >= _sentences.length()) return;
			
			var sentence:String = _sentences[_sentenceIndex];
			if(_charIndex >= sentence.length) {
				if(++_sentenceIndex == _sentences.length()) {
					if(_channel != null) {
						_channel.stop();
						_channel = null;
					}
					dispatchEvent(new SpeakEvent(SpeakEvent.STOP_SPEAK));
					dispatchEvent(new SpeakEvent(SpeakEvent.SPEAK_COMPLETE));
					clearInterval(_interval);
					_speaking = false;
					return;
				}else{
					_charIndex = 0;
					sentence = _sentences[_sentenceIndex];
				}
			}
			if(sentence.indexOf("<sneeze", _charIndex) > -1) {
				dispatchEvent(new SpeakEvent(SpeakEvent.SNEEZE));
			}
			_charIndex = sentence.length;
			_waitFor = 0;
			write();
			_waitFor = sentence.replace(_reg, "").length * .55;
			dispatchEvent(new SpeakEvent(SpeakEvent.STOP_SPEAK));
		}
		
		/**
		 * Writes the dialogue
		 */
		private function write():void {
			if(--_waitFor > 0) return;
			
			var sentence:String = _sentences[_sentenceIndex];
			if(sentence == null) {
				clearInterval(_interval);
				return;
			}
			
			_tf.text = sentence;//Provides a way to know the final width and height of the box
			var w:Number = _tf.width;
			var h:Number = _tf.height;
			var margin:int = 10;
			
			_tf.text = sentence.substr(0, _charIndex);
			
			if(_charIndex > sentence.length) {
				_waitFor = sentence.replace(_reg, "").length * .55;
				_charIndex = 0;
				_sentenceIndex ++;
				if(_sentenceIndex >= _sentences.length()) {
					clearInterval(_interval);
					_speaking = false;
					dispatchEvent(new SpeakEvent(SpeakEvent.SPEAK_COMPLETE));
				}
				dispatchEvent(new SpeakEvent(SpeakEvent.STOP_SPEAK));
			}else{
				dispatchEvent(new SpeakEvent(SpeakEvent.SPEAK));
			}
			
			if(sentence.charAt(_charIndex) == "<") {
				if (sentence.substr(_charIndex, 5) == "<wait") {
					_waitFor = Math.round(1000/30 * parseFloat(sentence.substring(_charIndex+5, sentence.indexOf(" ", _charIndex))));
					dispatchEvent(new SpeakEvent(SpeakEvent.STOP_SPEAK));
				}
				if (sentence.substr(_charIndex, 7) == "<sneeze") {
					dispatchEvent(new SpeakEvent(SpeakEvent.SNEEZE));
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