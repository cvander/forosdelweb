package com.forosdelweb.reader
{
	import flash.display.MovieClip;
	import com.carlcalderon.arthropod.Debug
	import flash.text.TextField;
	import fl.controls.CheckBox;
	import com.carlcalderon.arthropod.Debug
	
	public class categoryItem extends MovieClip
	{
		private var label_txt:TextField;
		private var forumData:XML
		private var actualWidth:Number = 320;
		private var actualHeight:Number = 500;
		
		public function categoryItem()
		{
			label_txt = label_stage;
		}
		public function set data(info:XML)
		{
			forumData = info
			label_txt.text = info.name;
			//resize(actualWidth, actualHeight)
			
		}
		public function get data():XML
		{
			return forumData;
		}
		public function set check(value:Boolean)
		{
			if (currentFrame == 2)
			{
				var where:Number = (value) ? 2 : 1;
				var checkBox = check_stage;
				checkBox.gotoAndStop(where)
			}
		}
		public function get check():Boolean
		{
			if (currentFrame == 2)
			{
				var checkBox = check_stage;
				return (checkBox.currentFrame == 1) ? false : true
			}
			return false;
		}
		public function resize(w:Number, h:Number)
		{
			actualHeight = h
			actualWidth = w
			var back = back_stage;
			if (currentFrame == 2)
			{
				var checkBox = check_stage;
				var line = line_stage;
				line.x = w - 50
				checkBox.x = w - 40
			}
			back.width = w - 20
			
			
		}
		
		
	}
}