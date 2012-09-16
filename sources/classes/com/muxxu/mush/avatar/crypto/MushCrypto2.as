package com.muxxu.mush.avatar.crypto {
	import com.nurun.utils.crypto.XOR;
	import flash.utils.ByteArray;
	/**
	 * @author Francois
	 */
	public class MushCrypto2 {
		private static const _CHARS:String = "╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝";
		private static const _CHAR_TO_INDEX:Array = [];
		
		public static function encrypt(value:String):String {
			var ba:ByteArray = new ByteArray();
			ba.writeUTFBytes(value);
			XOR(ba, "5B89BAA2DBD3A7835A6CD8F2DD1FA9AFCB159AF37779255BFF265D236B7B655B75E3B7153433F389888ABE92354EF7F7DE98919D72BD135887F7");
			ba.deflate();
			ba.position = 0;
			var output:String = "";
			var len:int = ba.length;
			var byte:int;
			while(ba.position < len) {
				byte = ba.readUnsignedByte();
				output += _CHARS.charAt(byte&0xf);
				output += _CHARS.charAt(byte>>4);
				if(byte > 0xAf) output += " ";
			}
			
			return output;
		}
		
		public static function decrypt(value:String):String {
			var i:int, len:int, ba:ByteArray, byte:int;
			
			value = value.replace(new RegExp("[^"+_CHARS+"]", "gi"), "");
			
			//Build charmap cache
			if(_CHAR_TO_INDEX.length == 0) {
				len = _CHARS.length;
				for(i = 0; i < len; ++i) {
					_CHAR_TO_INDEX[_CHARS.charAt(i)] = i;
				}
			}
			
			i = 0;
			len = value.length;
			ba = new ByteArray();
			while(i < len) {
				byte =  _CHAR_TO_INDEX[value.charAt(i)] | (_CHAR_TO_INDEX[value.charAt(i+1)]<<4);
				ba.writeByte( byte );
				i += 2;
			}
			
			try {
				ba.inflate();
				XOR(ba, "5B89BAA2DBD3A7835A6CD8F2DD1FA9AFCB159AF37779255BFF265D236B7B655B75E3B7153433F389888ABE92354EF7F7DE98919D72BD135887F7");
			}catch(error:Error) {
				return "???";
			}
			
			return ba.readUTFBytes(ba.length);
		}
		
	}
}
