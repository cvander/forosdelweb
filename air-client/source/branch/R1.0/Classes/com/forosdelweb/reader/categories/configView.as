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

package com.forosdelweb.reader.categories
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
	import com.forosdelweb.reader.Main
	
	/**
	 * 
	 * La Clase configView tiene el control de la interfaz de configuracion
	 * de foros
	 * 
	 * Clip relacionado : Library -> MainViews -> configView
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class configView extends Sprite
	{
		/**
		 * Barra superior de la vista
		 * */
		
		private var backTop:MovieClip;
		
		/**
		 * Boton para salvar los cambios
		 */
		
		private var save:Button;
		
		/**
		 * Scrollpane contenedor de la informacion
		 */
		
		private var scrollPane:ScrollPane
		
		/**
		 * Listado de los Foros obtenidos del XML
		 */
		
		private var forumsList:XMLList
		
		/**
		 * Referencia al clip contenedor dentro del scrollpane
		 */
		
		private var mainContent	
		
		/**
		 * Array que contiene todos los clips generados de acuerdo al listado
		 * del XML
		 * */
		
		private var itemsOnStage:Array
		
		/**
		 * Temporizador para mostrar los items con efecto de retraso
		 */
		
		private var showTimer:Timer;
		
		/**
		 * Contador de items
		 */
		
		private var itemsCount:Number;
		
		/**
		 * Ventana de Alerta
		 */
		
		private var alert:alertWin
		
		/**
		 * Ancho actual de la aplicacion.
		 */
		
		private var actualWidth:Number = 310;
		
		/**
		 * Alto actual de la aplicacion.
		 */
		
		private var actualHeight:Number = 490;
		
		/**
		 * Cadena que contiene los foros seleccionados por el usuario
		 * que se almacenara en el sharedObject
		 */
		
		private var selectedForums:String;
		
		/**
		 * Referencia a la interfaz principal de la aplicacion.
		 */
		
		private var main:Main;
		
		/**
		 * Inicializacion de la interfaz
		 * */
		
		public function configView()
		{
			main = parent as Main
			backTop = backTop_stage;
			save = save_stage
			scrollPane = scrollPane_stage
			save.addEventListener(MouseEvent.CLICK, verifyCheck)
			loadForums();	
		}
		
		/**
		 * Cambia el tamaño de los elementos de la interfaz de acuerdo al nuevo tamaño.
		 * 
		 * @param	w ancho 
		 * @param	h alto
		 */
		
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
		
		/**
		 * Carga el XML de los foros disponibles
		 */
		
		private function loadForums()
		{
			backTop.width = actualWidth;
			save.x = (actualWidth - save.width) - 10
			var loader:URLLoader = new URLLoader(new URLRequest("http://www.forosdelweb.com/air/forums.xml"));
			loader.addEventListener(Event.COMPLETE, onForumsLoad);
		}
		
		/**
		 * Convierte la informacion obtenida en XML y dibuja los foros.
		 * 
		 * @param	e Event
		 */
		
		private function onForumsLoad(e:Event)
		{
			var data:XML = XML(e.target.data);
			forumsList = data.category;
			drawForums();
		}
		
		/**
		 * Dibuja los foros por categoria y foro individual sin mostrarlos en la interfaz.
		 */
		
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
		
		/**
		 * Agrega los elementos a la interfaz con efecto de retrazo
		 * 
		 * @param	e TimerEvent
		 */
		
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
		
		/**
		 * Selecciona o deselecciona el estado del foro segun su estado
		 * @param	e
		 */
		
		private function checkIt(e:MouseEvent)
		{
			var item:categoryItem = e.currentTarget as categoryItem;
			item.check = (e.currentTarget.check) ? false : true;
		}
		
		/**
		 * Verifica que al menos un foro este seleccionado, si es asi se almacena en un 
		 * sharedIbject, de lo contrario lanza una alerta.
		 * 
		 * @param	e
		 */
		
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
		
		/**
		 * Cierra la alerta
		 * 
		 * @param	e
		 */
		
		private function closeAlert(e:MouseEvent)
		{
			removeChild(alert)
			alert = null
			scrollPane.enabled = true;
		}
		
		/**
		 * Escribe la informacion de los foros seleccionados en un sharedObject
		 */
		
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
		/**
		 * Oculta o muestra los foros contenidos en la categeria
		 * 
		 * @param	e
		 */
		
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
					basey += forum.height;
				}
			}
		}
	}
	
}