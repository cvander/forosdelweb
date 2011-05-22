/**
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


package com.forosdelweb.reader
{
	import flash.display.Sprite;
	import flash.display.MovieClip
	import flash.events.MouseEvent;
	import flash.display.StageAlign
	import flash.display.StageScaleMode;
	import flash.display.NativeWindow;
	import flash.display.NativeWindowInitOptions
	import flash.display.NativeWindowResize
	import flash.display.NativeWindowType
	import flash.display.NativeWindowSystemChrome
	import flash.net.SharedObject;
	import flash.net.URLRequest
	import flash.events.Event
	import flash.text.TextField
	import flash.desktop.NativeApplication;
	import com.codeazur.utils.AIRRemoteUpdater
	import com.codeazur.utils.AIRRemoteUpdaterEvent
	import com.forosdelweb.reader.categories.configView
	import com.forosdelweb.reader.post.postView	
	import com.forosdelweb.reader.utils.windowControl;
	import com.carlcalderon.arthropod.Debug;
	import com.forosdelweb.reader.events.postViewEvent
	import flash.display.Screen;
	import flash.utils.Timer;
	import flash.events.TimerEvent;
	import com.forosdelweb.reader.events.categoryEvent;
	
	/**
	 * 
	 * La Clase Main tiene el control de la interfaz principal
	 * desde la cual se tiene el control de las vistas de la 
	 * aplicacion, asi como el control de la ventana nativa 
	 * principal de la aplicacion.
	 * 
	 * Clip relacionado : Escene 1 (Main Timeline).
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	
	public final class Main extends Sprite
	{
		/**
		 * MovieClip para minimizar la aplicacion
		 * */
		
		private var minimize:MovieClip;
		
		/**
		 * MovieClip para cerrar la aplicacion
		 * */
		
		private var close:MovieClip
		
		/**
		 * MovieClip para arrastrar la aplicacion
		 * */
		
		private var dragBar:MovieClip;
		
		/**
		 * MovieClip para cambiar el tamaño de la aplicacion
		 * */
		
		private var resize:MovieClip;
		
		/**
		 * MovieClip para llevar el control de "Always on top"
		 * de la aplicacion
		 * */
		
		private var onTop:MovieClip;
		
		/**
		 * MovieClip que lanza la vista de configuracion de foros
		 * */
		
		private var config_btn:MovieClip;
		
		/**
		 * MovieClip que es el fondo de la aplicacion
		 * */
		
		private var background:MovieClip;
		
		/**
		 * SharedObject que contendra la informacion de los
		 * foros seleccionados por el usuario
		 * */
		
		private var soFwd:SharedObject;
		
		/**
		 * Interfaz de configuracion de los foros
		 * */
		
		private var config:configView;
		
		/**
		 * Interfaz de la vista de mensajes
		 * */
		
		private var posts:postView;
		
		/**
		 * Instancia de la clase AIRRemoteUpdater para el manejo
		 * de actualizaciones automaticas
		 * */
		
		private var updater:AIRRemoteUpdater
		
		/**
		 * URLRequest hacia el archivo AIR, para la verificacion 
		 * de cambio de version de la aplicacion
		 * */
		
		private var updateURL:URLRequest
		
		/**
		 * Interfaz de la ventana de alerta
		 * */
		
		private var alert:MovieClip
		
		/**
		 * Instancia de una nueva ventana nativa que contendra 
		 * la interfaz de about "Acerca de"
		 * */
		
		private var aboutWin:NativeWindow
		
		/**
		 * Instancia de una nueva ventana nativa que contendra 
		 * la interfaz de aviso de nuevos mensajes
		 * */
		
		private var toastWin:NativeWindow
		
		/**
		 * Variable boleana que indica si la aplicacion esta con la 
		 * propiedad "always on top" activa
		 * */
		
		private var isOnTop:Boolean;
		
		/**
		 * lastY contiene la posicion Y final donde se mostrara la 
		 * ventana toastWin
		 * */
		
		private var lastY:Number;
		
		/**
		 * Temporizador que lleva el control de la verificacion 
		 * automatica de nuevos posts
		 * */
		
		private var tTimer:Timer;
		
		/**
		 * Instancia de windowControl que se encarga de llevar los eventos
		 * relativos de la ventana principal, minimizar, cerrar, resize, etc
		 * */
		
		private var winControl:windowControl;
		
		/**
		 * Iniciacion de la interfaz principal
		 * */
		
		public function Main()
		{
			chechUpdate()
			initElements();
		}
		
		/**
		 * Verifica si existe una nueva version de la aplicacion en el servidor
		 * */
		
		private function chechUpdate():void 
		{
			updateURL= new URLRequest("http://www.forosdelweb.com/air/forums.air");
			updater = new AIRRemoteUpdater();
			updater.addEventListener(AIRRemoteUpdaterEvent.VERSION_CHECK, updaterVersionCheckHandler);
			updater.update(updateURL);
		}
		
		/**
		 * Funcion detonada cuando se tiene la nueva informacion de la version
		 * de la apliacion, si la version en el servidor es mayor a la del
		 * cliente actual, lanza la ventana de aviso. de lo contrario la ejecucion
		 * de la apliacion sigue normal.
		 */
		
		private function updaterVersionCheckHandler(event:AIRRemoteUpdaterEvent):void 
		{
			Debug.log("Local version: " + Number ( updater.localVersion ) , Debug.GREEN);
			Debug.log("Remote version: " + Number ( updater.remoteVersion ), Debug.GREEN);
			version_txt.text = "v"+updater.localVersion;
			if ( Number ( updater.localVersion )  < Number ( updater.remoteVersion ) )
			{
				Debug.log("Hay una nueva version pregunta")
				Debug.log("Parando el Installer ")
				event.preventDefault();
				alert = new updateWin();
				alert.x = (stage.stageWidth - 200 ) / 2
				alert.y = (stage.stageHeight - 150 ) / 2
				alert.ok_btn.addEventListener(MouseEvent.CLICK, doUpdate);
				alert.cancel_btn.addEventListener(MouseEvent.CLICK, dontDoUpdate);
				addChild(alert);
			}else
			{
				verifyFirstRun();
			}
		}
		
		/**
		 * Si el usuario desea actualizar la aplicacion, se lanza una ventana de espera, 
		 * se descarga el nuevo instalador y se hace la actualizacion automaticamente.
		 */
		
		private function doUpdate(e:MouseEvent):void
		{
			alert.title.text = "Actualizando";
			alert.desc.text  = "Por favor espera, el cliente se esta actualizando y se reiniciara cuando el proceso acabe";
			alert.ok_btn.removeEventListener(MouseEvent.CLICK, doUpdate);
			alert.cancel_btn.removeEventListener(MouseEvent.CLICK, dontDoUpdate);
			alert.ok_btn.visible = false
			alert.cancel_btn.visible = false
			updater.update(updateURL, false);
			
		}
		
		/**
		 * Si el usuario no desea actualizar la aplicacion, se remueve la ventana
		 * de alerta y la apliacion sigue la ejecucion normal.
		 */
		
		private function dontDoUpdate(e:MouseEvent):void
		{
			removeChild(alert)
			alert = null;
			verifyFirstRun()
		}
		
		/**
		 * Inicializacion de la apliacion y elementos visuales
		 */
		
		private function initElements():void
		{
			tTimer = new Timer(5000);
			stage.scaleMode = StageScaleMode.NO_SCALE;
			stage.align = StageAlign.TOP_LEFT;
			minimize = mini_stage;
			close = close_stage;
			dragBar = dragbar_stage;
			resize = resize_stage;
			background = back_stage;
			config_btn = config_stage;
			onTop = onTop_btn;
			resize.addEventListener(MouseEvent.MOUSE_DOWN, startResize);
			stage.addEventListener(Event.RESIZE, resizeIt);
			config_btn.addEventListener(MouseEvent.CLICK, showAbout)
			onTop.addEventListener(MouseEvent.CLICK, setOnTop);
			config_btn.buttonMode = true;
			config_btn.useHandCursor = true;
			close.buttonMode = true;
			close.useHandCursor = true;
			minimize.buttonMode = true;
			minimize.useHandCursor = true;
			onTop.buttonMode = true;
			onTop.useHandCursor = true;
			winControl =  new windowControl(stage, dragBar, minimize, null, close, this);
		}
		
		/**
		 * Cierra la ventana de "acerca de" si es que esta abierta.
		 */
		public function closeAll():void
		{
			if (aboutWin != null)
			{
				aboutWin.close();
			}
		}
		/**
		 * Pone la aplicacion "always on top" o no dependiendo el estado actual
		 * 
		 * @param	e MouseEvent
		 */
		private function setOnTop(e:MouseEvent)
		{
			if ( isOnTop )
			{
				onTop.gotoAndStop(1)
				isOnTop = false
				stage.nativeWindow.alwaysInFront = false
			}else
			{
				onTop.gotoAndStop(2)
				isOnTop = true
				stage.nativeWindow.alwaysInFront = true
			}
		}
		
		/**
		 * Lanza la ventana de "Acerca de" de la aplicacion, siempre y cuando
		 * no este visible.
		 * 
		 * @param	e MouseEvent
		 */
		
		private function showAbout(e:MouseEvent):void
		{
			Debug.log("about :: "+aboutWin)
			if (aboutWin == null)
			{
				var opt:NativeWindowInitOptions = new NativeWindowInitOptions();
				opt.maximizable = false;
				opt.minimizable = false;
				opt.resizable = false;
				opt.systemChrome = "none"; 
				opt.transparent = true
				aboutWin = new NativeWindow(opt);
				aboutWin.addEventListener(Event.CLOSE, aboutClose);
				aboutWin.title = "Acerca de.."
				aboutWin.width = 190; 
				aboutWin.height = 190;
				aboutWin.x = stage.nativeWindow.x +  (stage.nativeWindow.width - aboutWin.width ) / 2  ;
				aboutWin.y = stage.nativeWindow.y +  (stage.nativeWindow.height - aboutWin.height ) / 2  ;
				aboutWin.stage.align = StageAlign.TOP_LEFT; 
				aboutWin.stage.scaleMode = StageScaleMode.NO_SCALE; 
				aboutWin.stage.addChild ( new about() );
				aboutWin.activate(); 
			}
		}
		
		/**
		 * Setea la variable aboutWin como null al cerrar la ventana
		 * 
		 * @param	e MouseEvent
		 */
		
		private function aboutClose(e:Event):void
		{
			aboutWin = null
		}
		
		/**
		 * Comienza el resize de la ventana nativa
		 * 
		 * @param	e MouseEvent
		 */
		
		private function startResize(e:MouseEvent):void
		{
			stage.nativeWindow.startResize(NativeWindowResize.BOTTOM_RIGHT);
		}
		
		/**
		 * Verifica si es la primera vez que el usuario lanza la aplicacion.
		 * Si es asi muestra la interfaz de configuracion, si no lanza la
		 * interfaz de visualizacion de posts
		 */
		
		private function verifyFirstRun():void
		{
			Debug.log("Checando SO...")
			soFwd = SharedObject.getLocal("fwdlist");
			if (soFwd.data.configReady)
			{
				Debug.log("Ya tiene foros configurados.");
				setPostView();
			}else
			{
				Debug.log("No tiene nada es nuevo..")
				setConfigView()
			}
		}
		
		/**
		 * Lanza la interfaz de configuracion
		 */
		
		private function setConfigView():void
		{
			config = new configView()
			config.y = 30;
			config.resize(stage.stageWidth-10, stage.stageHeight-10)
			addChild(config)
		}
		/**
		 * Lanza la interfaz de visualizacion de posts
		 */
		
		private function setPostView():void
		{
			posts = new postView();
			posts.y = 30;
			posts.addEventListener(postViewEvent.NEW_POSTS_LOADED, showAlert);
			posts.addEventListener(categoryEvent.ON_SHOW_ONE_CATEGORY, filterApply)
			posts.resize(stage.stageWidth-10, stage.stageHeight-10)
			addChild(posts);
		}
		
		/**
		 * Cambia el Titulo de la aplicacion de acuerdo al filtro aplicado
		 * 
		 * @param	e categoryEvent que contiene el nombre del foro de cual se 
		 * aplico el filtro
		 */
		
		private function filterApply(e:categoryEvent)
		{
			bigTitle.text = e.category;
		}
		
		/**
		 * Lanza la interfaz de visualizacion de posts desde la interfaz de configuracion
		 */
		
		public function loadPostViewFromConfig():void
		{
			removeChild(config)
			setPostView();
		}
		
		/**
		 * Lanza la interfaz de configuracion desde la interfaz de visualizacion de posts
		 * 
		 * @param	e MouseEvent
		 */
		
		public function goToConfig(e:MouseEvent = null):void
		{
			posts.refreshTimer.stop();
			posts.removeEventListener(postViewEvent.NEW_POSTS_LOADED, showAlert);
			removeChild(posts)
			setConfigView();
		}
		
		/**
		 * Setea la ventana de aviso de nuevos mensajes
		 * 
		 * @param	e postViewEvent que contiene el numero de nuevos mensajes
		 */
		
		private function showAlert(e:postViewEvent)
		{
			Debug.log("ShowAlert")
			if ( !stage.nativeWindow.visible )
			{
				Debug.log("Creando la ventana")
				var opt:NativeWindowInitOptions = new NativeWindowInitOptions();
				var screen:Screen = Screen.screens[0];
				var screenWidth = screen.visibleBounds.width
				var screenHeight = screen.visibleBounds.height
				var content = new toast();
				content.label_txt.text = e.postCount + " post nuevos";
				content.addEventListener(MouseEvent.CLICK, unDock)
				lastY = screenHeight - content.height - 10
				opt.type = NativeWindowType.LIGHTWEIGHT;
				opt.systemChrome = NativeWindowSystemChrome.NONE;
				opt.transparent = true;
				toastWin = new NativeWindow(opt);
				toastWin.width = content.width; 
				toastWin.height = content.height;
				toastWin.x = screenWidth - content.width - 10 
				toastWin.y = screenHeight
				toastWin.stage.align = StageAlign.TOP_LEFT; 
				toastWin.stage.scaleMode = StageScaleMode.NO_SCALE; 
				toastWin.stage.addChild ( content );
				toastWin.activate(); 
				addEventListener(Event.ENTER_FRAME, openToast);
			}
		}
		
		/**
		 * Quita el icono del taskbar y lanza la aplicacion a primer plano
		 * 
		 * @param	e MouseEvent
		 */
		
		private function unDock(e:MouseEvent)
		{
			winControl.undock();
		}
		
		/**
		 * Muestra la ventana de nuevos mensajes con un efecto de apertura
		 * 
		 * @param	e Event
		 */
		
		private function openToast(e:Event)
		{
			if ( toastWin.y > lastY )
			{
				toastWin.y -= 10
			}else
			{
				removeEventListener(Event.ENTER_FRAME, openToast);
				tTimer.start()
				tTimer.addEventListener(TimerEvent.TIMER, initCloseToast)
			}
		}
		
		/**
		 * Inicia el temporizador para cerrar la ventana de aviso de mensajes nuevos
		 * 
		 * @param	e TimerEvent
		 */
		
		private function initCloseToast(e:TimerEvent)
		{
			tTimer.removeEventListener(TimerEvent.TIMER, initCloseToast)
			tTimer.stop()
			addEventListener(Event.ENTER_FRAME, closeToast);
		}
		
		/**
		 * Cierra la ventana de aviso de mensajes nuevos con un efecto de cierre
		 * 
		 * @param	e Event
		 */
		
		private function closeToast(e:Event)
		{
			var screen:Screen = Screen.screens[0];
			var screenHeight = screen.visibleBounds.height
			if ( toastWin.y <  screenHeight)
			{
				toastWin.y += 10
			}else
			{
				removeEventListener(Event.ENTER_FRAME, closeToast);
				toastWin.close();
			}
		}
		
		/**
		 * Cambia el tamaño de los elementos de la interfaz de acuerdo al nuevo tamaño.
		 * 
		 * @param	e
		 */
		
		private function resizeIt(e:Event):void
		{
			var w:Number = stage.stageWidth - 10;
			var h:Number = stage.stageHeight - 10;
			background.width = w 
			background.height = h
			topShadow.width = w;
			resize.x = (w - resize.width) - 5;
			resize.y = (h - resize.height) - 5;
			close.x = w - close.width - 10;
			minimize.x = (close.x - minimize.width ) - 10;
			onTop.x = (minimize.x - onTop.width ) - 5;
			dragBar.width = w - 50;
			bigTitle.width = w - 100;
			version_txt.y = ( h - version_txt.height ) - 5;
			if (alert != null )
			{
				alert.x = (w - 200 ) / 2
				alert.y = (h - 150 ) / 2
			}
			try
			{
				config.resize(w, h);
			}catch (e:Error)
			{ }
			try
			{
				posts.resize(w, h);
			}catch (e:Error)
			{ }
			
		}
	}
}