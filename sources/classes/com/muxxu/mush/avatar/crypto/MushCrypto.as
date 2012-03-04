package com.muxxu.mush.avatar.crypto {
	import com.nurun.utils.crypto.XOR;
	import flash.utils.ByteArray;
	/**
	 * @author Francois
	 */
	public class MushCrypto {
		
		private static const _CHARS:String = "▀▄█▌▐■▲►▼◄●◘◙▓▒░";// ╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╜╛╚╙╘╗╖╕╔╓╒║═┼┴┬┤├┘└┐┌│─#‼•‡‖†˟";
		private static const _CHAR_TO_INDEX:Array = [];
		
		public static function encrypt(value:String):String {
			var ba:ByteArray = new ByteArray();
			ba.writeUTFBytes(value);
			XOR(ba, "f28bbade554294b95d4c370d0955b4495bce59b461113cd3999cb2dedb274a28b862187fbb061ad53f3343ba31fc89a89fbe6faa560f3bc0efd79019760602bc");
			ba.deflate();
			ba.position = 0;
			//57 chars. 1 byte = 256;
			var output:String = "";
			var len:int = ba.length;
			var byte:int;
			while(ba.position < len) {
				byte = ba.readUnsignedByte();
				output += _CHARS.charAt(byte&0xf);
				if(Math.random() > .85) output += " ";
				output += _CHARS.charAt(byte>>4);
				if(Math.random() > .9) output += " ";
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
				XOR(ba, "f28bbade554294b95d4c370d0955b4495bce59b461113cd3999cb2dedb274a28b862187fbb061ad53f3343ba31fc89a89fbe6faa560f3bc0efd79019760602bc");
			}catch(error:Error) {
				return "???";
			}
			
			return ba.readUTFBytes(ba.length);
		}
		
	}
}
