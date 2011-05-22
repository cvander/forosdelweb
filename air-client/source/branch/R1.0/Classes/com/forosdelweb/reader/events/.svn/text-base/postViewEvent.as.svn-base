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
	 * La Clase postViewEvent crea un evento personalizado , extiende a la clase Event
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class postViewEvent extends Event
	{
		/**
		 * Identificado del evento
		 */
		
		static public var NEW_POSTS_LOADED:String = "new_posts_loaded"
		/**
		 * Numero de nuevos posts
		 */
		
		public var postCount:Number
		
		/**
		 * Crea un nuevo evento que contiene el numero de nuevos posts recibidos.
		 * 
		 * @param	type
		 * @param	_postCount
		 * @param	bubbles
		 * @param	cancelable
		 */
		
		public function postViewEvent(type:String, _postCount:Number, bubbles:Boolean = false, cancelable:Boolean = false) 
		{
			super(type, bubbles, cancelable);
			postCount = _postCount;
		}
	}
}
