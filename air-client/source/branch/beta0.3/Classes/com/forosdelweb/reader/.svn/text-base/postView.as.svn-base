package com.forosdelweb.reader
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
	import com.forosdelweb.reader.ArrayUtils
	import flash.utils.getTimer;
	import com.forosdelweb.reader.events.postItemEvent
	import caurina.transitions.Tweener;
	
	public class postView extends MovieClip
	{
		private var scrollPane:ScrollPane
		private var actualHeight:Number;
		private var actualWidth:Number;
		private var posts:Array;
		private var totalForums:Number
		private var countForums:Number
		private var mainContent	
		private var postInStage:Array
		private var loader_mc;
		private var timer:Timer
		private var isOnlyOneCategory:Boolean
		
		private const RSS_URL:String = "http://www.forosdelweb.com/external.php?type=RSS2&forumids="
		
		public function postView()
		{
			Debug.log("Vamos con la vista de posts....");
			showAll.visible = false;
			//loader_mc.label.text = "Actualizando informacion";
			scrollPane = scrollPane_stage;
			posts = [];
			postInStage = []
			refresh.addEventListener(MouseEvent.CLICK, loadForums)
			timer = new Timer(1000);
			timer.addEventListener(TimerEvent.TIMER, updateScroll)
			showAll.addEventListener(MouseEvent.CLICK, showAllPost)
			//resize(actualWidth, actualHeight)
			loadForums();
		}
		private function loadForums(e:MouseEvent = null)
		{
			//var soFwd = SharedObject.getLocal("fwdlist");\
			//loadSingleRss(RSS_URL)
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
			loader_mc.label.text = rss.channel.title + " completado.";
			for each(var item:Item20 in items)
			{
				var found:Boolean = false
				for each (var post:Item20 in posts)
				{
					//Debug.log(":: "+ post.guid +" == " + item.guid)
					if ( post.guid.id  == item.guid.id )
					{
						//Debug.log("Se encontro :: " + post.guid)
						found = true
						break;
						//continue
					}
				}
				if (!found)
				{
					//Debug.log("No se enncontro agregando :: " + item.guid )
					posts.push ( item );
				}
				
			}
			if ( ! ( ++countForums < totalForums) )
			{
				//scrollPane.source = new empty();
				Debug.log("Termino de cargar todos los foros.....", Debug.GREEN)
				Debug.log("Total de Posts  " + posts.length, Debug.GREEN)
				removeChild(loader_mc);
				var aUtil:ArrayUtils = new ArrayUtils;
				posts = aUtil.orderByDate ( posts );
				//scrollPane.update();
				drawItems();
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
			for each (var item:Item20 in posts)
			{
				var pItem:postItem = new postItem();
				pItem.y = basey;
				pItem.data = item
				pItem.x = basex
				pItem.read = item.read;
				pItem.addEventListener(postItemEvent.ON_CATEGORY_CLICK, showOnlyOneCategory);
				postInStage.push(pItem)
				
				pItem.resize(actualWidth, actualHeight)
				basey += pItem.height + 1;
				mainContent.addChild(pItem);
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
			showAll.x = refresh.x - showAll.width - 2;
			showAll.y = refresh.y
			scrollPane.setSize(w, ( h - scrollPane.y ) - 60 ) ;
			if ( loader_mc != null)
			{
				loader_mc.y = h - 45
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