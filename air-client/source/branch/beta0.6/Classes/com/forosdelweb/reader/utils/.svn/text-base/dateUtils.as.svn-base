package com.forosdelweb.reader.utils
{
	public class dateUtils 
	{
		import com.carlcalderon.arthropod.Debug
		
		public function dateUtils()
		{
			
		}
		public static function dateDiff(date:Date):String
		{
			var now:Date = new Date();
			var offsetMilliseconds:Number = now.getTimezoneOffset() * 60 * 1000;
			now.setTime(now.getTime() + offsetMilliseconds);	
			var minutes = Math.round( ( (now.getTime() - date.getTime()) / 1000 ) / 60)
			if ( minutes >= 60 )
			{
				var hours = Math.floor( minutes / 60 );
				if (hours >= 24 )
				{
					var days = Math.floor( hours / 24 ) 
					return String(days) + "d";
				}else
				{
					return String(hours) + "h";
				}
			}else
			{
				return String(minutes) + "m";
			}
		}
	}
}