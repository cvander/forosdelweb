/*
 * Este programa es software libre; usted puede redistribuirlo y/o 
 * modificarlo bajo los terminos de la licencia GNU General Public License 
 * según lo publicado por la "Free Software Foundation"; versión 2 , 
 * o (en su defecto) cualquie versión posterior.
 * 
 * Este programa se distribuye con la esperanza de que sea útil, 
 * pero SIN NINGUNA GARANTÍA; Incluso sin la garantía implicada del 
 * COMERCIALIZACIóN o de la APTITUD PARA UN PROPÓSITO PARTICULAR.  
 * Vea la "GNU General Public License" para más detalles.
 * 
 * Usted debe haber recibido una copia de la "GNU General Public License" 
 * junto con este programa; si no, escriba a la "Free Software Foundation", 
 * inc., calle de 51 Franklin, quinto piso, Boston, MA 02110-1301 E.E.U.U.
 * 
 * */

package com.forosdelweb.reader.post
{
	import com.forosdelweb.reader.events.categoryEvent;
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
	
	/**
	 * 
	 * La Clase postView tiene el control del item de los posts
	 * 
	 * Clip relacionado : Library -> MainViews -> _Forums -> postItem
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class postView extends MovieClip
	{
		/**
		 * ScrollPane contenedor de la informacion.
		 */
		
		private var scrollPane:ScrollPane
		
		/**
		 * Ancho actual de la aplicacion.
		 */
		
		private var actualHeight:Number;
		
		/**
		 * Alto actual de la aplicacion.
		 */
		
		private var actualWidth:Number;
		
		/**
		 * Contador que lleva el total de los foros
		 */
		
		private var totalForums:Number
		
		/**
		 * Lleva el conteo de foros cargados
		 */
		
		private var countForums:Number
		
		/**
		 * Referencia al clip principal del scrollpane donde se hara el addChild
		 */
		
		private var mainContent	
		
		/**
		 * Array que almacena los item obtenidos.
		 */
		
		private var postInStage:Array
		
		/**
		 * Referencia al clip que hace de preloader
		 */
		private var loader_mc;
		
		/**
		 * Temporizador para actualizar el scroll cuando los posts hayan sido dibujados
		 */
		
		private var timer:Timer
		
		/**
		 * Referencia para saber si esta viendo posts filtrados o no
		 */
		
		private var isOnlyOneCategory:Boolean
		
		/**
		 * Referencia al nombre del foro por la cual estan siendo filtrados los post
		 */
		
		private var categoryFilter:String
		
		/**
		 * Referencia a la interfaz principal de la aplicacion "Main"
		 */
		
		private var main:Main;
		
		/**
		 * Instancia del tooltip
		 */
		
		private var tooltip:Tooltip;
		
		/**
		 * Temporizador para mostrar el tooltip despues de medio segundo
		 */
		
		private var tTimer:Timer;
		
		/**
		 * Temporizador para actualizar los posts cada 5 min.
		 */
		
		public var refreshTimer:Timer
		
		/**
		 * Referencia para saber cuantos post nuevos hay cada vez que se hace es refresh
		 */
		
		private var newPostCount:Number;
		
		/**
		 * Esqueleto del URL para obtener los nuevos post de cada foro
		 */
		
		private const RSS_URL:String = "http://www.forosdelweb.com/external.php?type=RSS2&forumids="
		
		/**
		 * Iniciacion de la interfaz 
		 * 
		 * Seteo de Temporizadores y eventos de Mouse a los botones de la interfaz
		 */
		
		
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
		
		/**
		 * Funcion ejecutada por el temporizador para actualizar los foros
		 * 
		 * @param	e
		 */
		
		private function refreshAll(e:TimerEvent)
		{
			Debug.log("Refrescando automaticamente")
			loadForums();
		}
		
		/**
		 * Crea un nuevo tooltip y dependiendo del target asigna el texto
		 * 
		 * @param	e
		 */
		
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
					tooltip.setLabel("Eliminar filtro de " + categoryFilter)
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
			tooltip.x = (tooltip.x < 0 ) ? 0 : tooltip.x
			tooltip.visible = false
			addChild(tooltip);
			tTimer.start();
		}
		
		/**
		 * Al pasar medio segundo de que le mouse este sobre el boton se muestra el tooltip
		 * 
		 * @param	e
		 */
		
		private function showTip(e:TimerEvent)
		{
			tTimer.stop();
			tooltip.visible = true
		}
		
		/**
		 * Remueve el tooltip y detiene el temporizador
		 * 
		 * @param	e
		 */
		
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
		
		/**
		 * Obtiene los foros almacenados en el SharedObject
		 * 
		 * @param	e
		 */
		
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
			loader_mc.x = 35;
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
		
		/**
		 * Carga los posts un foro en particular
		 * 
		 * @param	url URL del XML
		 */
		
		private function loadSingleRss(url:String)
		{
			var loader:URLLoader = new URLLoader();
			var request:URLRequest = new URLRequest(url);
			loader.addEventListener(Event.COMPLETE, onDataLoad);
			loader.addEventListener(IOErrorEvent.IO_ERROR, onIOError);
			loader.addEventListener(SecurityErrorEvent.SECURITY_ERROR, onSecurityError);
			loader.load(request);
		}
		
		/**
		 * Al cargarse la informacion del RSS se parsea
		 * 
		 * @param	e
		 */
		
		private function onDataLoad(e:Event)
		{
			parseRSS( XML( e.target.data )  ) ;
		} 
		
		/**
		 * Un error ocurrio al cargar el RSS
		 * 
		 * @param	e
		 */
		
		private function onIOError(e:IOErrorEvent)
		{
			Debug.log("IOError");
		}
		
		/**
		 * Un error de seguridad ocurrio
		 * 
		 * @param	e
		 */
		
		private function onSecurityError(e:SecurityErrorEvent)
		{
			Debug.log("SecurityErrorEvent");
		}
		
		/**
		 * Parsea el contenido del RSS, Obtiene los posts, si no existen en Storage.getInstance().posts
		 * los agrega futuras referencias, si hay post nuevos lanza el evento postViewEvent.NEW_POSTS_LOADED
		 * para que la interfaz principal lance el aviso en el desktop.
		 * 
		 * @param	data
		 */
		
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
		
		/**
		 * Dibuja los items contenidos en Storage.getInstance().posts en el escenario
		 */
		
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
				pItem.resize(actualWidth, actualHeight)
				postInStage.push(pItem)
				if ( isOnlyOneCategory )
				{
					if ( pItem.data.categories.toString() == categoryFilter )
					{
						basey += pItem.height + 1;
					}else
					{
						pItem.visible = false;
					}
				}else
				{
					basey += pItem.height + 1;
				}
				mainContent.addChild(pItem);
			}
			scrollPane.update();
		}
		
		/**
		 * Reordena la posicion Y de los items al cambiar el tamaño del item.
		 * ej. Al dar click en el preview del mensaje.
		 * 
		 * @param	e
		 */
		
		private function redrawItem(e:Event)
		{
			var basey = 0;
			for each ( var post:postItem in postInStage)
			{
				if ( post.visible )
				{
					post.y = basey;
					basey += post.height;
				}
			}
			scrollPane.update();
		}
		
		/**
		 * Reordena y solo muestra los posts contenidos en un foro. Filtro
		 * 
		 * @param	e
		 */
		
		private function showOnlyOneCategory(e:postItemEvent)
		{
			if ( ! isOnlyOneCategory ) 
			{
				Debug.log("Cargando solo una categoria......")
				scrollPane.verticalScrollPosition = 0
				isOnlyOneCategory = true
				categoryFilter = e.category
				var cEvent:categoryEvent = new categoryEvent(categoryEvent.ON_SHOW_ONE_CATEGORY, e.category);
				dispatchEvent(cEvent)
				var basey = 0;
				for each ( var post:postItem in postInStage)
				{
					if (post.data.categories.toString() == e.category)
					{
						Tweener.addTween(post, { y:basey, time:.5, transition:"linear" } );
						basey += post.height;
					}else
					{
						post.enabled = false;
						post.visible = false;
						post.y = 0;
					}
				}
				timer.start();
				showAll.visible = true;
			}
		}
		
		/**
		 * Cambia la vista a la interfaz de configuracion
		 * 
		 * @param	e
		 */
		
		private function loadConfig(e:MouseEvent)
		{
			main = parent as Main
			main.goToConfig();
		}
		
		/**
		 * Elimina el Filtro mostrando todos los posts almacenados en Storage.getInstance().posts
		 * 
		 * @param	e
		 */
		
		
		private function showAllPost(e:MouseEvent)
		{
			if (isOnlyOneCategory)
			{
				Debug.log("Mostrando Todos los posts...")
				scrollPane.verticalScrollPosition = 0
				var cEvent:categoryEvent = new categoryEvent(categoryEvent.ON_SHOW_ONE_CATEGORY, "Foros del Web");
				dispatchEvent(cEvent)
				isOnlyOneCategory = false
				categoryFilter = null
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
		
		/**
		 * Setea todos los posts visibles como leidos.
		 * 
		 * @param	e
		 */
		
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
		
		/**
		 * Actualiza el scrollpane
		 * 
		 * @param	e
		 */
		
		private function updateScroll(e:TimerEvent)
		{
			timer.stop();
			scrollPane.update();
		}
		
		/**
		 * Cambia el tamaño de los elementos de la interfaz de acuerdo al nuevo tamaño.
		 * 
		 * @param	w ancho 
		 * @param	h alto
		 */
		
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