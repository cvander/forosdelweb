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
	import flash.display.MovieClip;
	import com.carlcalderon.arthropod.Debug
	import flash.text.TextField;
	import fl.controls.CheckBox;
	import com.carlcalderon.arthropod.Debug
	
	/**
	 * 
	 * La Clase categoryItem tiene el control del item por foro o categoria
	 * 
	 * Clip relacionado : Library -> MainViews -> _Category -> categoryItem
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class categoryItem extends MovieClip
	{
		
		/**
		 * Constante para identificar una catagoria
		 */
		
		public static const CATEGORY:String = "category";
		
		/**
		 * Constante para identificar un foro
		 */
		
		public static const FORUM:String = "forum"
		
		/**
		 * Campo de texto para el nombre del foro o categoria
		 */
		
		public var label_txt:TextField;
		
		/**
		 * Indicador para saber si una categoria esta abierta o cerrada
		 */
		
		public var arrow:MovieClip
		
		/**
		 * Listado de la categoria y foros contenidos en ella.
		 */
		
		private var forumData:XML
		
		/**
		 * Ancho actual de la aplicacion.
		 */
		
		private var actualWidth:Number = 320;
		
		/**
		 * Alto actual de la aplicacion.
		 */
		
		private var actualHeight:Number = 500;
		
		/**
		 * Identificador de que clase de item es, categoria o foro
		 */
		
		private var _type:String
		
		/**
		 * Identificador para la categoria
		 */
		
		private var _cat:String
		
		/**
		 * Inicializacion de la interfaz
		 * */
		
		public function categoryItem()
		{
			label_txt = label_stage;
			arrow = arrow_stage;
		}
		/**
		 * Funcion set para asignar el tipo de item
		 * 
		 * @param str String tipo de item
		 */
		
		public function set type(str:String)
		{
			_type = str
		}
		
		/**
		 * Funcion que retorna el tipo de item
		 * 
		 * @return String tipo de item
		 */
		
		public function get type():String
		{
			return _type
		}
		
		/**
		 * Funcion set para asignar la categoria
		 * 
		 * @param str String categoria
		 */
		
		public function set category(str:String)
		{
			_cat = str
		}
		
		/**
		 * Funcion que retorna la categoria del item
		 * 
		 * @return String categoria
		 */
		
		public function get category():String
		{
			return _cat
		}
		
		/**
		 * Funcion set que asigna la informacion del item
		 * 
		 * @param info XML
		 */
		
		public function set data(info:XML)
		{
			forumData = info
			label_txt.text = info.name;
			//resize(actualWidth, actualHeight)
			
		}
		
		/**
		 * Funcion que retorna la informacion del item
		 * 
		 * @return XML informacion del item
		 */
		
		public function get data():XML
		{
			return forumData;
		}
		
		/**
		 * Funcion que selecciona el item
		 * 
		 * @param value Boolean
		 */
		
		public function set check(value:Boolean)
		{
			if (currentFrame == 2)
			{
				var where:Number = (value) ? 2 : 1;
				var checkBox = check_stage;
				checkBox.gotoAndStop(where)
			}
		}
		
		/**
		 * Funcion que retorna si un item esta seleccionado
		 * 
		 * @return Boolean si el item esta seleccionado
		 */
		
		public function get check():Boolean
		{
			if (currentFrame == 2)
			{
				var checkBox = check_stage;
				return (checkBox.currentFrame == 1) ? false : true
			}
			return false;
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
			var back = back_stage;
			if (currentFrame == 2)
			{
				var checkBox = check_stage;
				var line = line_stage;
				line.x = w - 50
				checkBox.x = w - 40
			}else
			{
				arrow_stage.x = w - 34
			}
			back.width = w - 20
		}
	}
}