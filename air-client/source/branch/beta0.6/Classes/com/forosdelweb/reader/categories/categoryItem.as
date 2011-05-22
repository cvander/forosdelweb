package com.forosdelweb.reader.categories
{
	import flash.display.MovieClip;
	import com.carlcalderon.arthropod.Debug
	import flash.text.TextField;
	import fl.controls.CheckBox;
	import com.carlcalderon.arthropod.Debug
	
	public class categoryItem extends MovieClip
	{
		public static const CATEGORY:String = "category";
		public static const FORUM:String = "forum"
		
		public var label_txt:TextField;
		public var arrow:MovieClip
		private var forumData:XML
		private var actualWidth:Number = 320;
		private var actualHeight:Number = 500;
		private var _type:String
		private var _cat:String
		
		public function categoryItem()
		{
			label_txt = label_stage;
			arrow = arrow_stage;
		}
		public function set type(str:String)
		{
			_type = str
		}
		public function get type():String
		{
			return _type
		}
		public function set category(str:String)
		{
			_cat = str
		}
		public function get category():String
		{
			return _cat
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
			}else
			{
				arrow_stage.x = w - 34
			}
			back.width = w - 20
			
			
		}
		
		
	}
}