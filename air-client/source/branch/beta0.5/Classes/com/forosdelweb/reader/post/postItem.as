package com.forosdelweb.reader.post
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
	import com.forosdelweb.reader.utils.dateUtils
	import flash.ui.Mouse;
	import flash.net.URLRequest
	import flash.net.navigateToURL;
	import fl.controls.Button;
	import flash.display.SimpleButton
	import flash.html.HTMLLoader
	import flash.events.TimerEvent
	import flash.utils.Timer
	import com.forosdelweb.reader.utils.Tooltip
	
	
	public class postItem extends MovieClip
	{
		
		public static const CHANGE_SIZE:String = "change_size";
		
		private var itemData:Item20;
		private var tTimer
		private var tooltip
		private var main
		private var isShowingPreview:Boolean;
		
		public function postItem(_main)
		{
			main = _main
			read_mc.addEventListener(MouseEvent.CLICK, readIt)
			addEventListener(TextEvent.LINK, checkLink);
			//title_txt.addEventListener(MouseEvent.MOUSE_OVER, setUnderline);
			//title_txt.addEventListener(MouseEvent.MOUSE_OUT, killUnderline);
			postHome..addEventListener(MouseEvent.CLICK, goPost)
			reply..addEventListener(MouseEvent.CLICK, goReply)
			
			postHome.buttonMode = true
			postHome.useHandCursor = true
			reply.buttonMode = true
			reply.useHandCursor = true
			read_mc.buttonMode = true
			read_mc.useHandCursor = true
			
			postHome.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			postHome.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			reply.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			reply.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			read_mc.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			read_mc.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			tTimer = new Timer(500);
			tTimer.addEventListener(TimerEvent.TIMER, showTip);
			
			//getUserAvatar("tmeister")
		}
		private function showToolTip(e:MouseEvent)
		{
			var target = e.currentTarget;
			tooltip = new Tooltip();
			Debug.log("Tool :: "+ target.name)
			switch (target.name)
			{
				case "postHome":
					tooltip.setLabel("Ir al Tema")
					break;
				case "reply":
					tooltip.setLabel("Responder al Tema")
					break;
				case "config_mc":
					tooltip.setLabel("Configuración")
					break;
				case "read_mc":
					//Debug.log("Esta " + read)
					if (read)
					{
						tooltip.setLabel("Marcar como leido")
					}else
					{
						tooltip.setLabel("Marcar como no leido")
					}
					break;
			}
			tooltip.x = ( main.mouseX - tooltip.width ) 
			tooltip.y = ( main.mouseY - tooltip.height ) - 10
			tooltip.visible = false
			main.addChild(tooltip);
			tTimer.start();
			
		}
		private function showTip(e:TimerEvent)
		{
			tTimer.stop();
			tooltip.visible = true
		}
		private function killToolTip(e:MouseEvent = null)
		{
			Debug.log("Matando el ToolTip")
			tTimer.stop();
			if (tooltip != null)
			{
				main.removeChild(tooltip)
				tooltip = null
			}
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
			name_txt.htmlText = "<a href=\"event:"+itemData.creator+"\">"+itemData.creator+"</a>";
			avatar.source = (itemData.avatar.length) ? itemData.avatar : "http://static.forosdelweb.com/images/misc/unknown.gif";
			avatar.buttonMode = true
			avatar.useHandCursor = true
			avatar.addEventListener(MouseEvent.CLICK, goUserPage);
			more_txt.htmlText = "Publicado en el foro <font color='#EEBB66'><a href=\"event:"+itemData.categories+"\">" + itemData.categories + "</a></font> hace " + dateUtils.dateDiff(itemData.pubDate );
			if (title_txt.width > 230)
			{
				title_txt.multiline = true;
				title_txt.width = 230;
				title_txt.autoSize = TextFieldAutoSize.LEFT
				
				desc_txt.multiline = true;
				desc_txt.width = 230;
				desc_txt.autoSize = TextFieldAutoSize.LEFT
				
				more_txt.multiline = true;
				more_txt.width = 230;
				more_txt.autoSize = TextFieldAutoSize.LEFT
				
				
				
				
				title_txt.htmlText = "<a href=\"event:"+itemData.link+"\">" + title_txt.text + "</a>";
				more_txt.y = ( title_txt.y + title_txt.height ) - 2;
				back.height = ( title_txt.height + name_txt.height + more_txt.height ) - 3; 
			}
		}
		private function goUserPage(e:MouseEvent)
		{
			navigateToURL ( new URLRequest ( "http://www.forosdelweb.com/miembros/" + itemData.creator ) );
		}
		private function readIt(e:MouseEvent)
		{
			Debug.log("El post esta marcado como "+read)
			read = (read) ? false : true
		}
		public function set read( value:Boolean )
		{
			//Debug.log("Seteando el post como "+ value)
			var where = ( value ) ? 1 : 2
			read_mc.gotoAndStop(where);
			//read_mc.visible = value;
			itemData.read = value
		}
		public function get read ():Boolean
		{
			var what = ( read_mc.currentFrame == 1 ) ? true : false
			return what
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
					Debug.log("Muestra el Preview....")
					showPreview();
					
					break
				case "name_txt":
					navigateToURL ( new URLRequest ( "http://www.forosdelweb.com/miembros/" + itemData.creator ) );
					break
				case "desc_txt":
					navigateToURL( new URLRequest (itemData.link) )
					break
			}
		}
		private function showPreview()
		{
			if ( ! isShowingPreview )
			{
				var text:String = itemData.description.split("\n").join("");
				desc_txt.htmlText = text.substr(0, 200) + "....<br><br><a href=\"event:gotopost\"><font color='#EEBB66'>Leer Completo</font></a>"; ;
				desc_txt.y = ( title_txt.y + title_txt.height ) + 5;
				more_txt.y = ( desc_txt.y + desc_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5;
				isShowingPreview = true
			}else
			{
				desc_txt.text = "";
				more_txt.y = ( title_txt.y + title_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5; 
				isShowingPreview = false
			}
			var cEvent:Event = new Event(CHANGE_SIZE);
			dispatchEvent(cEvent);
			
		}
		
		private function goPost(e:MouseEvent)
		{
			navigateToURL( new URLRequest (itemData.link) )
		}
		private function goReply(e:MouseEvent)
		{
			Debug.log("Guid " + itemData.link)
			var link:String = itemData.link;
			var parts:Array = link.split("-")
			var id:String = String(parts[parts.length - 1]).substr(0, String ( parts[parts.length - 1] ).length - 1);
			Debug.log("ID " + id)
			navigateToURL( new URLRequest ( "http://www.forosdelweb.com/newreply.php?do=postreply&t=" + id ) )
		}
		public function resize(w:Number, h:Number)
		{
			
			reply.x = Math.round( w - 45 )
			postHome.x = reply.x - 22
			read_mc.x =  postHome.x - 20 ;
			//bloq_read.x = read_mc.x
			back.width = w - 25;
			title_txt.width = w - 90;
			more_txt.width = w - 90;
			desc_txt.width = w - 90;
			title_txt.multiline = true;
			more_txt.multiline = true;
			desc_txt.multiline = true;
			if ( ! isShowingPreview )
			{	
				more_txt.y = ( title_txt.y + title_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5; 
			}else
			{
				desc_txt.y = ( title_txt.y + title_txt.height ) + 5;
				more_txt.y = ( desc_txt.y + desc_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5; 
			}
		}
	}
}