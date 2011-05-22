package com.forosdelweb.reader.utils
{
	import com.adobe.xml.syndication.rss.Item20;
	
	public class ArrayUtils
	{
		public function ArrayUtils()
		{
			
		}
		public function orderByDate(source:Array):Array
		{
			source.sort(sortByDate);
			return source;
		}
		private function sortByDate(a:Item20, b:Item20):Number
		{
			var aDate:Number = new Date ( a.pubDate ).getTime();
			var bDate:Number = new Date ( b.pubDate ).getTime();
			if (aDate < bDate)
			{
				return 1;
			} else if (aDate > bDate) 
			{
				return -1;
			} else  
			{
				return 0;
			}
			return 1;
		}

	}
}