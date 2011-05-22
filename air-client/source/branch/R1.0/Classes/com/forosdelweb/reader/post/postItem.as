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
	import com.forosdelweb.reader.events.postItemEvent;
	import flash.display.MovieClip;
	import flash.events.Event;
	import flash.events.MouseEvent;
	import flash.events.TextEvent;
	import flash.text.TextField
	import com.adobe.xml.syndication.rss.Item20;
	import flash.text.TextFieldAutoSize;
	import com.carlcalderon.arthropod.Debug
	import com.forosdelweb.reader.utils.dateUtils
	import flash.ui.Mouse;
	import flash.net.URLRequest
	import flash.net.navigateToURL;
	import fl.controls.Button;
	import flash.display.SimpleButton
	import flash.html.HTMLLoader
	import flash.events.TimerEvent
	import flash.utils.Timer
	import com.forosdelweb.reader.utils.Tooltip
	
	
	/**
	 * 
	 * La Clase postItem tiene el control de la interfaz del post individual
	 * 
	 * Clip relacionado : Library -> MainViews -> _Forums -> postItem
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	
	public class postItem extends MovieClip
	{
		
		/**
		 * Identificador del Evento cuando el item cambia de tamaño
		 */
		
		public static const CHANGE_SIZE:String = "change_size";
		
		/**
		 * Referencia a la informacion del item
		 */
		
		private var itemData:Item20;
		
		/**
		 * Temporizador para mostrar el tooltip
		 */
		
		private var tTimer:Timer
		
		/**
		 * Instancia del Tooltip
		 */
		
		private var tooltip:Tooltip
		
		/**
		 * referencia a la interfaz principal de la aplicacion "Main"
		 */
		
		private var main
		
		/**
		 * Referencia para saber si se esta mostrando el preview del mensaje.
		 * 
		 */
		
		private var isShowingPreview:Boolean;
		
		/**
		 * Iniciacion de la interfaz 
		 * 
		 * Seteo de Temporizadores y eventos de Mouse a los botones de la interfaz
		 * 
		 * @param _main referencia a la interfaz principal de la aplicacion.
		 */
		
		
		public function postItem(_main)
		{
			main = _main
			postHome.visible = false;
			reply.visible = false;
			read_mc.addEventListener(MouseEvent.CLICK, readIt)
			addEventListener(TextEvent.LINK, checkLink);
			postHome..addEventListener(MouseEvent.CLICK, goPost)
			reply..addEventListener(MouseEvent.CLICK, goReply)
			
			postHome.buttonMode = true
			postHome.useHandCursor = true
			reply.buttonMode = true
			reply.useHandCursor = true
			read_mc.buttonMode = true
			read_mc.useHandCursor = true
			
			postHome.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			postHome.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			reply.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			reply.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			read_mc.addEventListener(MouseEvent.MOUSE_OVER,showToolTip)
			read_mc.addEventListener(MouseEvent.MOUSE_OUT, killToolTip)
			
			tTimer = new Timer(500);
			tTimer.addEventListener(TimerEvent.TIMER, showTip);
			
		}
		
		/**
		 * Crea un nuevo tooltip y dependiendo del target asigna el texto
		 * 
		 * @param	e
		 */
		
		private function showToolTip(e:MouseEvent)
		{
			var target = e.currentTarget;
			tooltip = new Tooltip();
			Debug.log("Tool :: "+ target.name)
			switch (target.name)
			{
				case "postHome":
					tooltip.setLabel("Ir al Tema")
					tooltip.x = ( main.mouseX ) 
					tooltip.y = ( main.mouseY - tooltip.height ) - 10
					break;
				case "reply":
					tooltip.setLabel("Responder al Tema")
					tooltip.x = ( main.mouseX ) 
					tooltip.y = ( main.mouseY - tooltip.height ) - 10
					break;
				case "config_mc":
					tooltip.setLabel("Configuración")
					tooltip.x = ( main.mouseX - tooltip.width ) 
					tooltip.y = ( main.mouseY - tooltip.height ) - 10
					break;
				case "read_mc":
					if (read)
					{
						tooltip.setLabel("Marcar como leido")
					}else
					{
						tooltip.setLabel("Marcar como no leido")
					}
					tooltip.x = ( main.mouseX - tooltip.width ) 
					tooltip.y = ( main.mouseY - tooltip.height ) - 10
					break;
			}
			tooltip.visible = false
			main.addChild(tooltip);
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
				main.removeChild(tooltip)
				tooltip = null
			}
		}
		
		/**
		 * Setea la informacion del item
		 * 
		 * @param info Item20
		 */
		
		public function set data(info:Item20)
		{
			itemData = info
			populateInfo()
		}
		
		/**
		 * Retorna la informacion del item
		 * 
		 * @return Item20
		 */
		
		public function get data():Item20
		{
			return itemData;
		}
		
		/**
		 * Asigna los valores del post a la interfaz
		 * 
		 */
		
		private function populateInfo()
		{
			var datePost:Date = new Date(itemData.pubDate)
			title_txt.text = itemData.title;
			name_txt.htmlText = "<a href=\"event:"+itemData.creator+"\">"+itemData.creator+"</a>";
			avatar.source = (itemData.avatar.length) ? itemData.avatar : "http://static.forosdelweb.com/images/misc/unknown.gif";
			avatar.buttonMode = true
			avatar.useHandCursor = true
			avatar.addEventListener(MouseEvent.CLICK, goUserPage);
			more_txt.htmlText = "Publicado en el foro <font color='#EEBB66'><a href=\"event:"+itemData.categories+"\">" + itemData.categories + "</a></font> hace " + dateUtils.dateDiff(itemData.pubDate );
			if (title_txt.width > 230)
			{
				title_txt.multiline = true;
				title_txt.width = 220;
				title_txt.autoSize = TextFieldAutoSize.LEFT
				
				desc_txt.multiline = true;
				desc_txt.width = 230;
				desc_txt.autoSize = TextFieldAutoSize.LEFT
				
				more_txt.multiline = true;
				more_txt.width = 230;
				more_txt.autoSize = TextFieldAutoSize.LEFT
				
				title_txt.htmlText = "<a href=\"event:"+itemData.link+"\">" + title_txt.text + "</a>";
				more_txt.y = ( title_txt.y + title_txt.height ) - 2;
				back.height = ( title_txt.height + name_txt.height + more_txt.height ) - 3; 
			}
		}
		
		/**
		 * Abre una nueva ventana del navegador apuntando al perfil del usuario.
		 * 
		 * @param	e
		 */
		
		private function goUserPage(e:MouseEvent)
		{
			navigateToURL ( new URLRequest ( "http://www.forosdelweb.com/miembros/" + itemData.creator ) );
		}
		
		/**
		 * Marca el post como leido o no leido
		 * 
		 * @param	e
		 */
		
		private function readIt(e:MouseEvent)
		{
			read = (read) ? false : true
		}
		
		/**
		 * Setea la propiedad read como true o false
		 * 
		 * @param	value Boolean
		 */
		
		public function set read( value:Boolean )
		{
			var where = ( value ) ? 1 : 2
			read_mc.gotoAndStop(where);
			itemData.read = value
		}
		
		/**
		 * Retorna si el post esta leido o no
		 * 
		 * @return Boolean
		 */
		
		public function get read ():Boolean
		{
			var what = ( read_mc.currentFrame == 1 ) ? true : false
			return what
		}
		
		/**
		 * Ejecutada al dar click en un link de texto, 
		 * Verifica que campo fue el target y ejecuta una funcion 
		 * dependiendo de esto.
		 * 
		 * @param	e
		 */
		
		private function checkLink(e:TextEvent)
		{
			switch ( e.target.name)
			{
				case "more_txt":
					var cEvent = new postItemEvent(postItemEvent.ON_CATEGORY_CLICK, e.text)
					dispatchEvent(cEvent)
					break
				case "title_txt":
					showPreview();
					break
				case "name_txt":
					navigateToURL ( new URLRequest ( "http://www.forosdelweb.com/miembros/" + itemData.creator ) );
					break
				case "desc_txt":
					navigateToURL( new URLRequest (itemData.link) )
					break
			}
		}
		
		/**
		 * Muestra u oculta el campo del previe del post y lo marca como liedo 
		 */
		
		private function showPreview()
		{
			if ( ! isShowingPreview )
			{
				read = false
				var text:String = itemData.description.split("\n").join("");
				desc_txt.htmlText = text.substr(0, 200) + "....<br><br>"; //"<br><a href=\"event:gotopost\"><font color='#EEBB66'>Leer Completo</font></a>"; ;
				desc_txt.y = ( title_txt.y + title_txt.height ) + 5;
				more_txt.y = ( desc_txt.y + desc_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5;
				arrow.rotation = 0;
				postHome.y = ( desc_txt.y + desc_txt.height )  - ( postHome.height - 5 ) 
				reply.y = postHome.y
				postHome.visible = true;
				reply.visible = true;
				isShowingPreview = true
			}else
			{
				desc_txt.text = "";
				postHome.visible = false;
				reply.visible = false;
				arrow.rotation = 180;
				more_txt.y = ( title_txt.y + title_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5; 
				postHome.y = 0
				reply.y = 0
				isShowingPreview = false
			}
			var cEvent:Event = new Event(CHANGE_SIZE);
			dispatchEvent(cEvent);
			
		}
		
		/**
		 * Abre una ventana del navegador con la pagina del post original
		 * 
		 * @param	e
		 */
		
		private function goPost(e:MouseEvent)
		{
			navigateToURL( new URLRequest (itemData.link) )
		}
		
		/**
		 * Abre una nueva ventana del navegador con la pagina para responder al post
		 * 
		 * @param	e
		 */
		
		private function goReply(e:MouseEvent)
		{
			Debug.log("Guid " + itemData.link)
			var link:String = itemData.link;
			var parts:Array = link.split("-")
			var id:String = String(parts[parts.length - 1]).substr(0, String ( parts[parts.length - 1] ).length - 1);
			Debug.log("ID " + id)
			navigateToURL( new URLRequest ( "http://www.forosdelweb.com/newreply.php?do=newreply&t=" + id ) )
		}
		
		/**
		 * Cambia el tamaño de los elementos de la interfaz de acuerdo al nuevo tamaño.
		 * 
		 * @param	w ancho 
		 * @param	h alto
		 */
		
		public function resize(w:Number, h:Number)
		{
			read_mc.x =  Math.round( w - 45 )
			arrow.x = ( read_mc.x + arrow.width ) - 1
			back.width = w - 25;
			title_txt.width = w - 100;
			more_txt.width = w - 90;
			desc_txt.width = w - 90;
			title_txt.multiline = true;
			more_txt.multiline = true;
			desc_txt.multiline = true;
			if ( ! isShowingPreview )
			{	
				more_txt.y = ( title_txt.y + title_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5; 
				postHome.y = 0
				reply.y = 0
			}else
			{
				desc_txt.y = ( title_txt.y + title_txt.height ) + 5;
				more_txt.y = ( desc_txt.y + desc_txt.height ) + 5;
				back.height = ( more_txt.y + more_txt.height ) + 5; 
				
				postHome.y = ( desc_txt.y + desc_txt.height )  - ( postHome.height - 5 ) 
				reply.y = postHome.y
			}
		}
	}
}