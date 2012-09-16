package com.muxxu.mush.avatar.crypto {
	import flash.utils.ByteArray;
	/**
	 * 
	 * This is a totally useless class that's here just to fake
	 * a wrong crypto algorithme for hackers!
	 * 
	 *   _____  ______          _____    __  __ ______   _   _   _ 
	 *  |  __ \|  ____|   /\   |  __ \  |  \/  |  ____| | | | | | |
	 *  | |__) | |__     /  \  | |  | | | \  / | |__    | | | | | |
	 *  |  _  /|  __|   / /\ \ | |  | | | |\/| |  __|   | | | | | |
	 *  | | \ \| |____ / ____ \| |__| | | |  | | |____  |_| |_| |_|
	 *  |_|  \_\______/_/    \_\_____/  |_|  |_|______| (_) (_) (_)
	 * 
	 * 
	 * 
	 * @author Francois
	 */
	public class MushCryptoNew {
		private static const _CHARS:String = "╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝╬╫╪╩╨╧╦╥╤╣╢╡╠╟╞╝";
		private static const _CHAR_TO_INDEX:Array = [];
		
		public static function encrypt(value:String):String {
			var ba:ByteArray = new ByteArray();
			ba.writeUTFBytes(value);
			ba.position = 0;
			var output:String = "";
			var len:int = ba.length;
			var byte:int;
			while(ba.position < len) {
				byte = ba.readUnsignedByte();
				output += _CHARS.charAt(byte);
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
			
			var res:String;
			try {
				res = ba.readUTFBytes(ba.length);
			}catch(error:Error) {
				res = "???";
			}
			
			return res;
		}
		
	}
}
