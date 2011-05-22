package com.forosdelweb.reader.events
{
	import flash.events.Event;
	public class postItemEvent extends Event
	{
		static public var ON_CATEGORY_CLICK:String = "on_category_click"
		public var category:String
		public function postItemEvent(type:String, cat:String, bubbles:Boolean = false, cancelable:Boolean = false) 
		{
			super(type, bubbles, cancelable);
			category = cat;
		}
	}
}
