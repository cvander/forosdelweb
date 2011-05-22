package com.forosdelweb.reader.data
{
    public class Storage
    {
        
		private static var singleton : Storage
		private var _posts:Array = [];
		
		
        public function Storage( caller : Function = null ) 
        {	
            if ( caller != Storage.getInstance )
			{
                throw new Error ("Storage is a singleton class, use getInstance() instead");
			}
            if ( Storage.singleton != null )
			{
                throw new Error( "Only one Singleton instance should be instantiated" );	
			}	
        }
		public static function getInstance() : Storage
        {
            if ( singleton == null )
			{
                singleton = new Storage( arguments.callee );
			}
            return singleton;
        }
	    public function set posts(data:Array)
		{
			_posts = data;
		}
		public function get posts():Array
		{
			return _posts;
		}
    }
}
