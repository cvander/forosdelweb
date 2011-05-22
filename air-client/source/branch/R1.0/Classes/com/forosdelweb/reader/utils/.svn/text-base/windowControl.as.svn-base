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

package com.forosdelweb.reader.utils
{
	import flash.display.MovieClip;
	import flash.desktop.NativeApplication;
	import flash.events.MouseEvent;
	import flash.display.Stage
	import flash.desktop.DockIcon;
	import flash.desktop.NativeApplication;
	import flash.desktop.NotificationType;
	import flash.desktop.SystemTrayIcon;
	import flash.display.NativeMenu;
	import flash.display.NativeMenuItem;
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.InvokeEvent;
	import com.carlcalderon.arthropod.Debug
	import flash.display.BitmapData;
	import flash.net.navigateToURL;
	import flash.net.URLRequest;
	import com.forosdelweb.reader.Main
	
	/**
	 * 
	 * La Clase windowControl lleva el control de los eventos mas comunes de una ventana nativa
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class windowControl
	{
		/**
		 * Referencia al Stage principal de la aplicacion
		 */
		
		private var _stage:Stage;
		
		/**
		 * Referencia a la interfaz principal de la aplicacion "Main"
		 */
		
		private var _main:Main;
		
		/**
		 * Instancia de un nuevo MenuItem
		 */
		
		private var goToFDW:NativeMenuItem = new NativeMenuItem("Forosdelweb.com");
		
		/**
		 * Instancia de un nuevo MenuItem
		 */
		
		private var openClient:NativeMenuItem = new NativeMenuItem("Ver Lector");
		
		/**
		 * Iniciacion de la interfaz, seteando los eventos del Mouse
		 * 
		 * @param	stage referencia al stage principal
		 * @param	drag referencia al DisplyObject que sera el boton para arrastar la aplicacion
		 * @param	minimize referencia al DisplayObject que sera el boton para minimizar la aplicacion 
		 * @param	maximize referencia al DisplayObject que sera el boton para maximizar la aplicacion
		 * @param	close referencia al DisplayObject que sera el boton para cerrar la aplicacion
		 * @param	main referencia a la interfaz principal de la aplicacion
		 */
		
		public function windowControl(stage:Stage, drag:MovieClip = null, minimize:MovieClip = null, maximize:MovieClip = null, close:MovieClip = null, main:Main = null)
		{
			_stage = stage;
			_main = main
			if (drag != null)
			{
				drag.addEventListener(MouseEvent.MOUSE_DOWN, dragIt);
			}
			if (minimize != null)
			{
				minimize.addEventListener(MouseEvent.CLICK, minimizeIt);
			}
			if (maximize != null)
			{
				maximize.addEventListener(MouseEvent.CLICK, maximizeIt)
			}
			if (close != null)
			{
				close.addEventListener(MouseEvent.CLICK, closeIt);
			}
			setDockAndSystemTray();
		}
		
		/**
		 * Inicia el arrastre de la aplicacion
		 * 
		 * @param	e
		 */
		
		private function dragIt(e:MouseEvent)
		{
			_stage.nativeWindow.startMove();
		}
		
		/**
		 * Minimiza la aplicacion
		 * 
		 * @param	e
		 */
		
		private function minimizeIt(e:MouseEvent)
		{
			_stage.nativeWindow.minimize();
			dock();
		}
		
		/**
		 * Maximiza la aplicacion
		 * 
		 * @param	e
		 */
		
		private function maximizeIt(e:MouseEvent)
		{
			_stage.nativeWindow.maximize();
		}
		
		/**
		 * Cierra la aplicacion y ejecuta la funcion closeAll de Main por si la ventana de About esta abierta
		 * cerrandola tambien
		 * 
		 * @param	e
		 */
		
		private function closeIt(e:*)
		{
			_main.closeAll();
			_stage.nativeWindow.close();
		}
		
		/**
		 * Setea los parametros y propiedades para que al momento de minimizar la apliacion
		 * esta se muestra en el taskBar (windows) o en el Dock (Mac)
		 */
		
		private function setDockAndSystemTray()
		{
			if (NativeApplication.supportsDockIcon)
			{
				var dockIcon:DockIcon = NativeApplication.nativeApplication.icon as DockIcon;
				NativeApplication.nativeApplication.addEventListener(InvokeEvent.INVOKE,undock);
				dockIcon.menu = createIconMenu();
			} 
			else if (NativeApplication.supportsSystemTrayIcon)
			{
				var sysTrayIcon:SystemTrayIcon = NativeApplication.nativeApplication.icon as SystemTrayIcon;
				sysTrayIcon.tooltip = "ForosdelWeb.";
				sysTrayIcon.addEventListener(MouseEvent.CLICK,undock);
				sysTrayIcon.menu = createIconMenu();
			}
		}
		
		/**
		 * Oculta la aplicacion y crea el icono para el systray o dock
		 * 
		 * @param	event
		 */
		
		public function dock(event:Event = null)
		{
			_stage.nativeWindow.visible = false;
			NativeApplication.nativeApplication.icon.bitmaps = [bitmapData()];
		}	
		
		/**
		 * Lanza la aplicacion y la trae a primer plano y elminina el systray o dock
		 * 
		 * @param	event
		 */
		
		public function undock(event:Event = null)
		{
			_stage.nativeWindow.visible = true;
			NativeApplication.nativeApplication.icon.bitmaps = [];
			_stage.nativeWindow.orderToFront();
			_stage.nativeWindow.activate();
		}
		
		/**
		 * Crea un Bitmap del icono que se mostrara en el systray o Dock
		 * 
		 * @return
		 */
		
		private function bitmapData():BitmapData
		{
			var sysImage:BitmapData = new BitmapData(16, 16, true, 0x00ffffff);
			sysImage.draw(new sysIcon());
			return sysImage;
		}
		
		/**
		 * Crea el menu para el systray o dock setenado listener para cada accion del mismo
		 * 
		 * @return
		 */
		
		private function createIconMenu():NativeMenu
		{
			
			var iconMenu:NativeMenu = new NativeMenu();
			iconMenu.addItem(goToFDW);
			goToFDW.addEventListener(Event.SELECT, goToFDWCommand);
			iconMenu.addItem(openClient);
			openClient.addEventListener(Event.SELECT, undock);
			
			if (NativeApplication.supportsSystemTrayIcon)
			{
				iconMenu.addItem(new NativeMenuItem("", true));//Separator
				var exitCommand: NativeMenuItem = iconMenu.addItem(new NativeMenuItem("Salir"));
				exitCommand.addEventListener(Event.SELECT, closeIt);	
			}		
			return iconMenu;
		}
		
		/**
		 * Abre una nueva ventana del navegador con la pagina forosdelweb.com
		 * 
		 * @param	e
		 */
		
		private function goToFDWCommand(e:Event)
		{
			var url:URLRequest = new URLRequest("http://www.forosdelweb.com");
			navigateToURL(url);
		}
	}
}