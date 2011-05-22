package com.forosdelweb.reader.post
{
	import flash.display.Loader;
	import flash.display.MovieClip;
	import com.carlcalderon.arthropod.Debug
	import fl.containers.ScrollPane
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.events.SecurityErrorEvent;
	import flash.events.TimerEvent;
	import flash.net.SharedObject;
	import flash.net.URLRequest;
	import flash.net.URLLoader;
	import flash.utils.Timer
	import com.adobe.utils.XMLUtil;
	import com.adobe.xml.syndication.rss.RSS20;
	import com.adobe.xml.syndication.rss.Item20;
	import com.adobe.utils.StringUtil;
	import com.forosdelweb.reader.utils.ArrayUtils
	import flash.utils.getTimer;
	import com.forosdelweb.reader.events.postItemEvent
	import com.forosdelweb.reader.Main
	import com.forosdelweb.reader.utils.Tooltip
	import com.forosdelweb.reader.data.Storage
	import caurina.transitions.Tweener;
	import com.forosdelweb.reader.events.postViewEvent
	
	
	public class postView extends MovieClip
	{
		private var scrollPane:ScrollPane
		private var actualHeight:Number;
		private var actualWidth:Number;
		//private var posts:Array;
		private var totalForums:Number
		private var countForums:Number
		private var mainContent	
		private var postInStage:Array
		private var loader_mc;
		private var timer:Timer
		private var isOnlyOneCategory:Boolean
		private var main:Main;
		private var tooltip:Tooltip;
		private var tTimer:Timer;
		public var refreshTimer:Timer
		private var newPostCount:Number;
		
		private const RSS_URL:String = "http://www.forosdelweb.com/external.php?type=RSS2&forumids="
		
		public function postView()
		{
			Debug.log("Vamos con la vista de posts....");
			showAll.visible = false;
			//loader_mc.label.text = "Actualizando informacion";
			scrollPane = scrollPane_stage;
			//posts = [];
			postInStage = []
			
			refreshTimer = new Timer(300000);
			refreshTimer.addEventListener(TimerEvent.TIMER, refreshAll);
			refreshTimer.start();
			
			timer = new Timer(1000);
			timer.addEventListener(TimerEvent.TIMER, updateScroll)
			
			tTimer = new Timer(500);
			tTimer.addEventListener(TimerEvent.TIMER, showTip);
			
			refresh.addEventListener(MouseEvent.CLICK, loadForums)
			showAll.addEventListener(MouseEvent.CLICK, showAllPost)
			config_mc.addEventListener(MouseEvent.CLICK, loadConfig)
			readAll.addEventListener(MouseEvent.CLICK, markAllRead)
			
			showAll.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			showAll.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			refresh.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			refresh.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			config_mc.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			config_mc.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			readAll.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			readAll.addEventListener(MouseEvent.MOUSE_OUT,killToolTip)
			
			//resize(actualWidth, actualHeight)
			loadForums();
		}
		private function refreshAll(e:TimerEvent)
		{
			Debug.log("Refrescando automaticamente")
			loadForums();
		}
		private function showToolTip(e:MouseEvent)
		{
			var target:MovieClip = e.currentTarget as MovieClip;
			tooltip = new Tooltip();
			switch (target.name)
			{
				case "refresh":
					Debug.log("Mostarndo el tooltip de refresh")
					tooltip.setLabel("Actualizar")
					break;
				case "showAll":
					Debug.log("Mostarndo el tooltip de showAll")
					tooltip.setLabel("Mostrar todos")
					break;
				case "config_mc":
					Debug.log("Mostarndo el tooltip de config_mc")
					tooltip.setLabel("Configuración")
					break;
				case "readAll":
					tooltip.setLabel("Marcar todos como leidos")
					break;
			}
			
			tooltip.x = ( mouseX - tooltip.width ) 
			tooltip.y = ( mouseY - tooltip.height ) - 10
			tooltip.visible = false
			addChild(tooltip);
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
				removeChild(tooltip)
				tooltip = null
			}
		}
		private function loadForums(e:MouseEvent = null)
		{
			//var soFwd = SharedObject.getLocal("fwdlist");\
			//loadSingleRss(RSS_URL)
			newPostCount = 0
			refresh.removeEventListener(MouseEvent.CLICK, loadForums)
			var soFwd:SharedObject = SharedObject.getLocal("fwdlist");
			var forums:Array = soFwd.data.forums.toString().split(",");
			totalForums = Number ( forums.length - 1 ) ;
			countForums = 0;
			loader_mc = new preload();
			loader_mc.x = 10;
			loader_mc.y = actualHeight - 45
			addChild(loader_mc)
			for each ( var forum in forums)
			{
				if ( StringUtil.trim(forum).length )
				{
					Debug.log("Cargando ::: " + forum + "&anticache=" + getTimer() )
					loadSingleRss(RSS_URL + forum + "&anticache=" + getTimer())
				}
			}
		}
		private function loadSingleRss(url:String)
		{
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest(url);
			loader.addEventListener(Event.COMPLETE, onDataLoad);
			loader.addEventListener(IOErrorEvent.IO_ERROR, onIOError);
			loader.addEventListener(SecurityErrorEvent.SECURITY_ERROR, onSecurityError);
			loader.load(request);
		}
		private function onDataLoad(e:Event)
		{
			//Debug.log("Cargo el XML");
			parseRSS( XML( e.target.data )  ) ;
		} 
		private function onIOError(e:IOErrorEvent)
		{
			Debug.log("IOError");
		}
		private function onSecurityError(e:SecurityErrorEvent)
		{
			Debug.log("SecurityErrorEvent");
		}
		private function parseRSS(data:XML)
		{
			if( ! XMLUtil.isValidXML(data) ) 
			{
				Debug.log("No es un XML valido..")
				return;
			}
			var rss:RSS20 = new RSS20();
			rss.parse(data);
			var items:Array = rss.items;
			Debug.log("Termino de Cargar " + rss.channel.title)
			var forumName:String = rss.channel.title.toString().split("Foros del Web -").join("")
			loader_mc.label.text = forumName + " completado.";
			for each(var item:Item20 in items)
			{
				var found:Boolean = false
				for each (var post:Item20 in Storage.getInstance().posts)
				{
					if ( post.guid.id  == item.guid.id )
					{
						found = true
						break;
					}
				}
				if (!found)
				{
					newPostCount++
					Storage.getInstance().posts.push ( item );
				}
			}
			if ( ! ( ++countForums < totalForums) )
			{
				removeChild(loader_mc);
				refresh.addEventListener(MouseEvent.CLICK, loadForums)
				var aUtil:ArrayUtils = new ArrayUtils;
				Storage.getInstance().posts = aUtil.orderByDate ( Storage.getInstance().posts );
				drawItems();
				var cEvent = new postViewEvent(postViewEvent.NEW_POSTS_LOADED, newPostCount);
				dispatchEvent(cEvent);
			}
		}
		private function drawItems()
		{
			var basey:Number = 0;
			var basex:Number = 5
			//var source:MovieClip = new empty()
			postInStage = []
			scrollPane.source = new MovieClip();
			
			Debug.log(" " + mainContent, Debug.GREEN );
			
			mainContent = scrollPane.content;
			for each (var item:Item20 in Storage.getInstance().posts)
			{
				var pItem:postItem = new postItem(this);
				pItem.y = basey;
				pItem.data = item
				pItem.x = basex
				pItem.read = item.read;
				pItem.addEventListener(postItemEvent.ON_CATEGORY_CLICK, showOnlyOneCategory);
				pItem.addEventListener(postItem.CHANGE_SIZE, redrawItem)
				postInStage.push(pItem)
				
				pItem.resize(actualWidth, actualHeight)
				basey += pItem.height + 1;
				mainContent.addChild(pItem);
			}
			scrollPane.update();
		}
		private function redrawItem(e:Event)
		{
			var basey = 0;
			for each ( var post:postItem in postInStage)
			{
				post.y = basey;
				basey += post.height;
			}
			scrollPane.update();
		}
		private function showOnlyOneCategory(e:postItemEvent)
		{
			if ( ! isOnlyOneCategory ) 
			{
				Debug.log("Cargando solo una categoria......")
				isOnlyOneCategory = true
				var basey = 0;
				for each ( var post:postItem in postInStage)
				{
					if (post.data.categories.toString() == e.category)
					{
						//Debug.log("Match, Quedate visible")
						Tweener.addTween(post, { y:basey, time:.5, transition:"linear" } );
						basey += post.height;
					}else
					{
						//Debug.log("No es Quitalo de la vista....")
						//Tweener.addTween(post, { y:0, alpha:0,  time:.5, transition:"linear" } );
						post.enabled = false;
						post.visible = false;
						post.y = 0;
					}
				}
				timer.start();
				//scrollPane.update();
				showAll.visible = true;
			}
		}
		private function loadConfig(e:MouseEvent)
		{
			main = parent as Main
			main.goToConfig();
		}
		private function showAllPost(e:MouseEvent)
		{
			if (isOnlyOneCategory)
			{
				Debug.log("Mostrando Todos los posts...")
				isOnlyOneCategory = false
				var basey = 0 
				showAll.visible = false
				for each ( var post:postItem in postInStage )
				{
					//post.alpha = 0
					post.y = basey
					post.visible = true;
					post.enabled = true; 
					//Tweener.addTween(post, { y:basey,alpha:1, time:.5, transition:"linear" } );
					basey += post.height;
				}
				timer.start();
			}
		}
		private function markAllRead(e:MouseEvent)
		{
			for each ( var post:postItem in postInStage )
			{
				if ( post.visible )
				{
					post.read = false;
				}
			}
		}
		
		
		private function updateScroll(e:TimerEvent)
		{
			timer.stop();
			scrollPane.update();
		}
		public function resize(w:Number, h:Number)
		{
			var basey:Number = 0;
			var basex:Number = 5
			actualHeight = h
			actualWidth = w
			refresh.x = (w - 35);
			refresh.y = (h - 52);
			
			config_mc.x = refresh.x - config_mc.width - 5;
			config_mc.y = refresh.y
			
			
			readAll.x = config_mc.x - readAll.width - 5;
			readAll.y = config_mc.y
			
			
			showAll.x = readAll.x - showAll.width - 5;
			showAll.y = readAll.y
			
			
			scrollPane.setSize(w, ( h - scrollPane.y ) - 60 ) ;
			if ( loader_mc != null)
			{
				loader_mc.y = h - 48
			}
			for each (var post in postInStage)
			{
				post.resize(w, h);
				if (post.visible)
				{
					post.y = basey;
					basey += post.height + 1
				}
			}
			scrollPane.update();
		}
	}
}