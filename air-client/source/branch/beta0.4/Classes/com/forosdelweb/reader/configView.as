package com.forosdelweb.reader
{
	import flash.display.Sprite;
	import flash.display.MovieClip;
	import com.carlcalderon.arthropod.Debug
	import fl.controls.Button
	import fl.containers.ScrollPane
	import flash.net.SharedObject;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.TimerEvent;
	import flash.text.TextFormat;
	import flash.utils.Timer
	import caurina.transitions.Tweener;
	import flash.events.MouseEvent;
	
	public class configView extends Sprite
	{
		private var backTop:MovieClip;
		private var save:Button;
		private var scrollPane:ScrollPane
		private var forumsList:XMLList
		private var mainContent	
		private var itemsOnStage:Array
		private var showTimer:Timer;
		private var itemsCount:Number;
		private var alert:alertWin
		private var actualWidth:Number = 310;
		private var actualHeight:Number = 490;
		private var selectedForums:String;
		private var main:Main;
		
		public function configView()
		{
			main = parent as Main
			backTop = backTop_stage;
			save = save_stage
			scrollPane = scrollPane_stage
			save.addEventListener(MouseEvent.CLICK, verifyCheck)
			loadForums();	
		}
		public function resize(w:Number, h:Number)
		{
			actualHeight = h
			actualWidth = w
			backTop.width = actualWidth;
			save.x = (actualWidth - save.width) - 10
			scrollPane.setSize(actualWidth, ( actualHeight - scrollPane.y ) - 50 ) ;
			if ( loader_mc != null)
			{
				loader_mc.y = actualHeight - 45
			}
			for each ( var item in itemsOnStage)
			{
				item.resize(actualWidth, actualHeight)
			}
		}
		private function loadForums()
		{
			backTop.width = actualWidth;
			save.x = (actualWidth - save.width) - 10
			var loader:URLLoader = new URLLoader(new URLRequest("http://www.klr20mg.com/fdw/forums.xml"));
			loader.addEventListener(Event.COMPLETE, onForumsLoad);
			loader.addEventListener(IOErrorEvent.IO_ERROR, onForumsError);
		}
		private function onForumsLoad(e:Event)
		{
			var data:XML = XML(e.target.data);
			forumsList = data.category;
			drawForums();
		}
		private function onForumsError(e:IOErrorEvent)
		{
			//Debug.log("Algo esta mall...")
		}
		private function drawForums()
		{
			var basex:Number = 0;
			var basey:Number = 0;
			var tf:TextFormat = new TextFormat();
			tf.color  = 0x000000;
	
			mainContent = scrollPane.content;
			itemsOnStage = [];
			itemsCount = 0;
			for each ( var category in forumsList)
			{
				var item:categoryItem = new categoryItem();
				item.x = basex;
				item.y = basey;
				item.alpha = 0
				item.data = category
				item.type = "category";
				item.label_txt.setTextFormat(tf)
				item.addEventListener(MouseEvent.CLICK, showOnlyOneCategory)
				//item.resize(actualWidth, actualHeight)
				basey += 30;
				itemsOnStage.push(item);
				
				for each ( var forum in category.forums.forum)
				{
					var itemForum:categoryItem = new categoryItem();
					itemForum.gotoAndStop(2)
					itemForum.data = forum
					itemForum.x = basex;
					itemForum.y = basey;
					itemForum.type = "forum"
					itemForum.category = category.name
					//itemForum.resize(actualWidth, actualHeight)
					itemForum.alpha = 0
					basey += 30;
					itemForum.addEventListener(MouseEvent.CLICK, checkIt);
					itemsOnStage.push(itemForum)
				}
			}
			showTimer = new Timer(25);
			showTimer.addEventListener(TimerEvent.TIMER, showThem);
			showTimer.start();
		}
		private function showThem(e:TimerEvent)
		{
			var soFwd:SharedObject = SharedObject.getLocal("fwdlist");
			var selectedForums:Array = [];
			try
			{
				selectedForums = soFwd.data.forums.toString().split(",")
			}catch (e:Error)
			{
		
			}
			if (itemsCount < itemsOnStage.length)
			{
				mainContent.addChild( itemsOnStage[itemsCount] );
				if ( selectedForums.indexOf(String(itemsOnStage[itemsCount].data.id)) != -1 )
				{
					itemsOnStage[itemsCount].check = true;
				}
				try{
					itemsOnStage[itemsCount].resize(actualWidth, actualHeight)
				}catch (e:Error)
				{ }
				
				Tweener.addTween(itemsOnStage[itemsCount], { alpha:1, time:.5, transition:"linear" } );
				itemsCount++;
				scrollPane.update();
			}else
			{
				Debug.log("Termino.....")
				showTimer.stop()
				removeChild(loader_mc);
			}
		}
		private function checkIt(e:MouseEvent)
		{
			var item:categoryItem = e.currentTarget as categoryItem;
			item.check = (e.currentTarget.check) ? false : true;
		}
		private function verifyCheck(e:MouseEvent)
		{
			var found:Boolean = false;
			selectedForums = "";
			for each ( var item in itemsOnStage)
			{
				if (item.check)
				{
					found = true
					selectedForums += item.data.id.toString() + ",";
				}
			}
			if ( found )
			{
				writeData();
				return
			}
			Debug.log("No hay nada seleccionados...........")
			alert = new alertWin();
			alert.x = (actualWidth - 200 ) / 2
			alert.y = (actualHeight - 150 ) / 2
			alert.ok_btn.addEventListener(MouseEvent.CLICK, closeAlert)
			alert.title.text = "Error"
			alert.desc.text = "Por favor selecciona al menos un foro."
			scrollPane.enabled = false;
			addChild(alert);
		}
		private function closeAlert(e:MouseEvent)
		{
			removeChild(alert)
			alert = null
			scrollPane.enabled = true;
		}
		private function writeData()
		{
			Debug.log("Escribiendo los foros en el SO", Debug.RED);
			Debug.log(selectedForums, Debug.GREEN);
			var soFwd:SharedObject = SharedObject.getLocal("fwdlist");
			soFwd.data.configReady = true;
			soFwd.data.forums = selectedForums;
			soFwd.flush();
			Debug.log(" " + Main( parent ).loadPostViewFromConfig)
			Main( parent ).loadPostViewFromConfig()
		}
		private function showOnlyOneCategory(e:MouseEvent)
		{
			var item:categoryItem = e.currentTarget as categoryItem;
			var basey = 0;
			var forum:categoryItem
			item.arrow.rotation = ( item.arrow.rotation == 0 ) ? 180 : 0
			for each ( forum in itemsOnStage)
			{
				if (forum.category == item.data.name)
				{
					forum.visible = (forum.visible) ? false : true
				}
			}
			for each ( forum in itemsOnStage)
			{
				if (forum.visible)
				{
					forum.y = basey;
					//Tweener.addTween(forum, { y:basey, time:.5, transition:"linear" } );
					basey += forum.height;
				}
			}
			//scrollPane.update()
		}
	}
	
}