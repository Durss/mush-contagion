package com.muxxu.mush.contaminator.throwables {
	
	/**
	 * 
	 * @author Francois
	 * @date 29 janv. 2012;
	 */
	public class ContaminatorError extends Error {
		
		public var code:String;
		
		
		

		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>ContaminatorError</code>.
		 */
		public function ContaminatorError(code:String, message:* = null, id:* = null) {
			this.code = code;
			if(message == null) message = code;
			if(id == null) id = code;
			super(message, id);
		}

		
		
		/* ***************** *
		 * GETTERS / SETTERS *
		 * ***************** */



		/* ****** *
		 * PUBLIC *
		 * ****** */


		
		
		/* ******* *
		 * PRIVATE *
		 * ******* */
		
	}
}