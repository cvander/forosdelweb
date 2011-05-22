package com.forosdelweb.reader.events
{
	import flash.events.Event;
	public class postViewEvent extends Event
	{
		static public var NEW_POSTS_LOADED:String = "new_posts_loaded"
		public var postCount:Number
		public function postViewEvent(type:String, _postCount:Number, bubbles:Boolean = false, cancelable:Boolean = false) 
		{
			super(type, bubbles, cancelable);
			postCount = _postCount;
		}
	}
}
