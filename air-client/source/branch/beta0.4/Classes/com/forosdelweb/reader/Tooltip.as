package com.forosdelweb.reader
{
	import flash.text.TextField;
	import flash.display.MovieClip;
	
	public class Tooltip extends MovieClip
	{
		public function Tooltip()
		{
			lbl.autoSize = "left";
		}
		public function setLabel(l:String):void
		{
			lbl.htmlText = l;
			skin_mc.width = Math.round(lbl.textWidth) + 20;
			//skin_mc.height = Math.round(lbl.textHeight) + 25;
		}
	}
}
