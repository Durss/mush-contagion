package com.muxxu.mush.contaminator.components {
	import com.nurun.components.button.IconAlign;
	import com.nurun.components.button.TextAlign;
	import flash.filters.DropShadowFilter;
	import com.nurun.components.vo.Margin;
	import com.muxxu.mush.graphics.ButtonGraphic;
	import com.nurun.components.button.BaseButton;
	import com.nurun.components.button.visitors.CssVisitor;
	import com.nurun.components.button.visitors.applyDefaultFrameVisitorNoTween;
	
	/**
	 * 
	 * @author Francois
	 * @date 4 mars 2012;
	 */
	public class MButton extends BaseButton {
		
		
		
		
		/* *********** *
		 * CONSTRUCTOR *
		 * *********** */
		/**
		 * Creates an instance of <code>MButton</code>.
		 */
		public function MButton(label:String) {
			super(label, "button", new ButtonGraphic());
			applyDefaultFrameVisitorNoTween(this, background);
			accept(new CssVisitor());
			contentMargin = new Margin(10,5,10,5);
			textAlign = TextAlign.LEFT;
			iconAlign = IconAlign.RIGHT;
			iconSpacing = 10;
			height = 25;
			
			filters = [new DropShadowFilter(3, 318, 0, .5, 3, 3, .3, 2, true)];
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