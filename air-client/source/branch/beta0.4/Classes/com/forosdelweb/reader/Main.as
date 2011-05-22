package com.forosdelweb.reader
{
	import flash.display.Sprite;
	import flash.display.MovieClip
	import flash.events.MouseEvent;
	import flash.display.StageAlign
	import flash.display.StageScaleMode;
	import flash.desktop.NativeApplication;
	import flash.display.NativeWindow;
	import flash.display.NativeWindowInitOptions
	import flash.display.NativeWindowResize
	import flash.events.Event
	import flash.net.SharedObject;
	import flash.text.TextField
	import com.codeazur.utils.AIRRemoteUpdater
	import com.codeazur.utils.AIRRemoteUpdaterEvent
	import flash.net.URLRequest

	
	import com.carlcalderon.arthropod.Debug;
	import com.forosdelweb.reader.windowControl;
	
	public final class Main extends Sprite
	{
		private var minimize:MovieClip;
		private var close:MovieClip
		private var dragBar:MovieClip;
		private var resize:MovieClip;
		private var config_btn:MovieClip;
		private var background:MovieClip;
		private var soFwd:SharedObject;
		private var config:configView;
		private var posts;
		private var updater:AIRRemoteUpdater
		private var updateURL:URLRequest
		private var alert:MovieClip
		private var aboutWin:NativeWindow
		
		public function Main()
		{
			chechUpdate()
			initElements();
		}
		private function chechUpdate():void 
		{
			updateURL= new URLRequest("http://www.forosdelweb.com/air/forums.air");
			updater = new AIRRemoteUpdater();
			updater.addEventListener(AIRRemoteUpdaterEvent.VERSION_CHECK, updaterVersionCheckHandler);
			updater.addEventListener(AIRRemoteUpdaterEvent.UPDATE, updaterUpdateHandler);
			updater.update(updateURL);
		}
		 
		private function updaterVersionCheckHandler(event:AIRRemoteUpdaterEvent):void 
		{
			Debug.log("Local version: " + Number ( updater.localVersion ) , Debug.GREEN);
			Debug.log("Remote version: " + Number ( updater.remoteVersion ), Debug.GREEN);
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
		private function doUpdate(e:MouseEvent)
		{
			alert.title.text = "Actualizando";
			alert.desc.text  = "Por favor espera, el cliente se esta actualizando y se reiniciara cuando el proceso acabe";
			alert.ok_btn.removeEventListener(MouseEvent.CLICK, doUpdate);
			alert.cancel_btn.removeEventListener(MouseEvent.CLICK, dontDoUpdate);
			alert.ok_btn.visible = false
			alert.cancel_btn.visible = false
			updater.update(updateURL, false);
			
		}
		private function dontDoUpdate(e:MouseEvent)
		{
			removeChild(alert)
			alert = null;
			verifyFirstRun()
		}
		
		private function updaterUpdateHandler(event:AIRRemoteUpdaterEvent):void {
		   Debug.log("Installer: " + event.file.nativePath, Debug.GREEN);
		}
		private function initElements()
		{
			stage.scaleMode = StageScaleMode.NO_SCALE;
			stage.align = StageAlign.TOP_LEFT;
			minimize = mini_stage;
			close = close_stage;
			dragBar = dragbar_stage;
			resize = resize_stage;
			background = back_stage;
			config_btn = config_stage;
			resize.addEventListener(MouseEvent.MOUSE_DOWN, startResize);
			stage.addEventListener(Event.RESIZE, resizeIt);
			config_btn.addEventListener(MouseEvent.CLICK, showAbout)
			config_btn.buttonMode = true;
			config_btn.useHandCursor = true;
			var winControl:windowControl =  new windowControl(stage, dragBar, minimize, null, close, this);
		}
		public function closeAll()
		{
			Debug.log("Closing All " + aboutWin);
			if (aboutWin != null)
			{
				aboutWin.close();
			}
		}
		private function showAbout(e:MouseEvent)
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
				aboutWin.title = "About Foros del Web"
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
		private function aboutClose(e:Event)
		{
			aboutWin = null
		}
		
		private function startResize(e:MouseEvent)
		{
			stage.nativeWindow.startResize(NativeWindowResize.BOTTOM_RIGHT);
		}
		private function verifyFirstRun()
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
		private function setConfigView()
		{
			config = new configView()
			config.y = 30;
			config.resize(stage.stageWidth-10, stage.stageHeight-10)
			addChild(config)
		}
		private function setPostView()
		{
			posts = new postView();
			posts.y = 30;
			posts.resize(stage.stageWidth-10, stage.stageHeight-10)
			addChild(posts);
		}
		public function loadPostViewFromConfig()
		{
			removeChild(config)
			setPostView();
		}
		public function goToConfig(e:MouseEvent = null)
		{
			posts.refreshTimer.stop();
			removeChild(posts)
			setConfigView();
		}
		
		private function resizeIt(e:Event)
		{
			var w:Number = stage.stageWidth - 10;
			var h:Number = stage.stageHeight - 10;
			background.width = w 
			background.height = h
			topShadow.width = w
			resize.x = (w - resize.width) - 5
			resize.y = (h - resize.height) - 5
			close.x = w - close.width - 10
			minimize.x = (close.x - minimize.width ) - 10 
			dragBar.width = w - 50 
			bigTitle.x = Math.round ( ( ( w - bigTitle.width ) / 2 ) - 5 ) 
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