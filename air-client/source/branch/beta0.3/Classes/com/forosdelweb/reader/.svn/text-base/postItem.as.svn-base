package com.forosdelweb.reader
{
	import com.forosdelweb.reader.events.postItemEvent;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.events.TextEvent;
	import flash.text.TextField
	import com.adobe.xml.syndication.rss.Item20;
	import flash.text.TextFieldAutoSize;
	import com.carlcalderon.arthropod.Debug
	import com.forosdelweb.reader.dateUtils
	import flash.ui.Mouse;
	import flash.net.URLRequest
	import flash.net.navigateToURL;
	
	public class postItem extends MovieClip
	{
		
		private var itemData:Item20;
		
		public function postItem()
		{
			addEventListener(MouseEvent.CLICK, readIt)
			addEventListener(TextEvent.LINK, checkLink);
			title_txt.addEventListener(MouseEvent.MOUSE_OVER, setUnderline);
			title_txt.addEventListener(MouseEvent.MOUSE_OUT, killUnderline);
		}
		public function set data(info:Item20)
		{
			itemData = info
			populateInfo()
		}
		private function setUnderline(e:MouseEvent)
		{
			e.target.htmlText = "<u>"+e.target.htmlText+"</u>"
		}
		private function killUnderline(e:MouseEvent)
		{
			var text:String = e.target.htmlText
			text = text.split("<U>").join("")
			text = text.split("</U>").join("")
			e.target.htmlText = text;
		}
		public function get data():Item20
		{
			return itemData;
		}
		private function populateInfo()
		{
			var datePost:Date = new Date(itemData.pubDate)
			title_txt.text = itemData.title;
			name_txt.text = itemData.creator;
			more_txt.htmlText = "<a href=\"event:"+itemData.categories+"\">Publicado en el foro <font color='#cccccc'>" + itemData.categories + "</font> hace " + dateUtils.dateDiff(new Date ( itemData.pubDate ) ) + "</a>";
			if (title_txt.width > 280)
			{
				title_txt.multiline = true;
				title_txt.width = 280;
				title_txt.autoSize = TextFieldAutoSize.LEFT
				title_txt.htmlText = "<a href=\"event:"+itemData.link+"\">" + title_txt.text + "</a>";
				more_txt.y = ( title_txt.y + title_txt.height ) - 2;
				back.height = ( title_txt.height + name_txt.height + more_txt.height ) - 3; 
			}
		}
		private function readIt(e:MouseEvent)
		{
			read = true
		}
		public function set read( value:Boolean )
		{
			read_mc.visible = (value) ? false : true;
			itemData.read = value
		}
		public function get read ():Boolean
		{
			return read_mc.visible;
		}
		private function checkLink(e:TextEvent)
		{
			switch ( e.target.name)
			{
				case "more_txt":
					Debug.log("Cargando Categoria: " + e.text)
					var cEvent = new postItemEvent(postItemEvent.ON_CATEGORY_CLICK, e.text)
					dispatchEvent(cEvent)
					break
				case "title_txt":
					Debug.log("Llendo al la URL " + e.text)
					navigateToURL( new URLRequest (e.text) )
					break
			}
		}
		public function resize(w:Number, h:Number)
		{
			read_mc.x = w - 35;
			back.width = w - 20;
			title_txt.width = w - 40;
			title_txt.multiline = true;
			more_txt.y = ( title_txt.y + title_txt.height ) - 2;
			back.height = ( title_txt.height + name_txt.height + more_txt.height ) - 3; 
		}
	}
}