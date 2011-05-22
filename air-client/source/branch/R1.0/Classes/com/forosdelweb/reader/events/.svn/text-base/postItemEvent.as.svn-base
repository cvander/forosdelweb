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

package com.forosdelweb.reader.events
{
	import flash.events.Event;
	
	/**
	 * 
	 * La Clase postItemEvent crea un evento personalizado , extiende a la clase Event
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class postItemEvent extends Event
	{
		
		/**
		 * Identificado del evento
		 */
		
		static public var ON_CATEGORY_CLICK:String = "on_category_click"
		
		/**
		 * Identificado de la categoria
		 */
		
		public var category:String
		
		/**
		 * Crea un nuevo evento que contiene el identificador de la categoria
		 * 
		 * @param	type
		 * @param	cat
		 * @param	bubbles
		 * @param	cancelable
		 */
		public function postItemEvent(type:String, cat:String, bubbles:Boolean = false, cancelable:Boolean = false) 
		{
			super(type, bubbles, cancelable);
			category = cat;
		}
	}
}
