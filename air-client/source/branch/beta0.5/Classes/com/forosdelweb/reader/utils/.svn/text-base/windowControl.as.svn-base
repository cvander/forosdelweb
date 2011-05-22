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
	
	public class windowControl
	{
		private var _stage:Stage;
		private var _main:Main;
		
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
		private function dragIt(e:MouseEvent)
		{
			_stage.nativeWindow.startMove();
		}
		private function minimizeIt(e:MouseEvent)
		{
			_stage.nativeWindow.minimize();
			dock();
		}
		private function maximizeIt(e:MouseEvent)
		{
			_stage.nativeWindow.maximize();
		}
		private function closeIt(e:*)
		{
			_main.closeAll();
			_stage.nativeWindow.close();
		}
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
		public function dock(event:Event = null)
		{
			_stage.nativeWindow.visible = false;
			NativeApplication.nativeApplication.icon.bitmaps = [bitmapData()];
		}	
		
		public function undock(event:Event = null)
		{
			_stage.nativeWindow.visible = true;
			NativeApplication.nativeApplication.icon.bitmaps = [];
			_stage.nativeWindow.orderToFront();
			_stage.nativeWindow.activate();
		}
		private function bitmapData():BitmapData
		{
			var sysImage:BitmapData = new BitmapData(16, 16, true, 0x00ffffff);
			sysImage.draw(new sysIcon());
			return sysImage;
		}
		
		private var goToFDW:NativeMenuItem = new NativeMenuItem("Forosdelweb.com");
		private var openClient:NativeMenuItem = new NativeMenuItem("Ver Lector");
		
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
		private function goToFDWCommand(e:Event)
		{
			var url:URLRequest = new URLRequest("http://www.forosdelweb.com");
			navigateToURL(url);
		}
	}
}