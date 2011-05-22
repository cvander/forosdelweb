package com.forosdelweb.reader
{
	import flash.display.Sprite;
	import flash.display.MovieClip
	import flash.events.MouseEvent;
	import flash.display.StageAlign
	import flash.display.StageScaleMode;
	import flash.desktop.NativeApplication;
	import flash.display.NativeWindow;
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
		
		public function Main()
		{
			chechUpdate()
			initElements();
			verifyFirstRun();
		}
		private function chechUpdate():void {
		   var request:URLRequest = new URLRequest("http://klr20mg.com/fdw/installer/forums.air");
		   var updater:AIRRemoteUpdater = new AIRRemoteUpdater();
		   updater.addEventListener(AIRRemoteUpdaterEvent.VERSION_CHECK, updaterVersionCheckHandler);
		   updater.addEventListener(AIRRemoteUpdaterEvent.UPDATE, updaterUpdateHandler);
		   updater.update(request);
		}
		 
		private function updaterVersionCheckHandler(event:AIRRemoteUpdaterEvent):void {
		   // The AIRRemoteUpdaterEvent.VERSION_CHECK event is fired
		   // as soon as both local and remote version numbers are known. 
		   var updater:AIRRemoteUpdater = event.target as AIRRemoteUpdater;
		   Debug.log("Local version: " + updater.localVersion, Debug.GREEN);
		   Debug.log("Remote version: " + updater.remoteVersion, Debug.GREEN);
		   // You can stop execution of AIR Remote Updater at this point 
		   // by calling event.preventDefault(), for example to inform the user 
		   // that a new version is available and/or ask if she likes to download 
		   // and install it. When the user confirms, call AIRRemoteUpdater.update()
		   // again with the versionCheck argument set to "false". This will
		   // circumvent the version checking procedure and immediately
		   // starts to download the remote .AIR installer file to a temporary
		   // file on the user's harddisk.
		}
		 
		private function updaterUpdateHandler(event:AIRRemoteUpdaterEvent):void {
		   // The AIRRemoteUpdaterEvent.UPDATE event is fired when
		   // the remote .AIR installer file has finished downloading.
		   // The event's "file" property contains a reference to the
		   // temporary file on the user's harddisk.
		   Debug.log("Installer: " + event.file.nativePath, Debug.GREEN);
		   // You can stop execution of AIR Remote Updater at this point 
		   // by calling event.preventDefault(), for example to inform the user 
		   // that the application is about to shut down and update itself.
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
			config_btn.addEventListener(MouseEvent.CLICK, goToConfig)
			config_btn.buttonMode = true;
			config_btn.useHandCursor = true;
			var winControl:windowControl =  new windowControl(stage, dragBar, minimize, null, close);
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
			config.resize(stage.stageWidth, stage.stageHeight)
			addChild(config)
		}
		private function setPostView()
		{
			posts = new postView();
			posts.y = 30;
			posts.resize(stage.stageWidth, stage.stageHeight)
			addChild(posts);
		}
		public function loadPostViewFromConfig()
		{
			removeChild(config)
			setPostView();
		}
		private function goToConfig(e:MouseEvent)
		{
			removeChild(posts)
			setConfigView();
		}
		
		private function resizeIt(e:Event)
		{
			var w:Number = stage.stageWidth;
			var h:Number = stage.stageHeight;
			background.width = w
			background.height = h
			topShadow.width = w
			resize.x = (w - resize.width) - 5
			resize.y = (h - resize.height) - 5
			close.x = w - close.width - 10
			minimize.x = (close.x - minimize.width ) - 5 
			dragBar.width = w - 50 
			bigTitle.x = ( w - bigTitle.width ) /2
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