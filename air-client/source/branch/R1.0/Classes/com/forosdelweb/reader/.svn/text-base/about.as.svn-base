/**
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

package com.forosdelweb.reader
{
	
	import com.carlcalderon.arthropod.Debug
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.text.TextField
	
	/**
	 * 
	 * La Clase about es usada para llevar el control de las acciones de la
	 * interfaz, about del programa.
	 * 
	 * Clip relacionado: Library->About->about
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	
	public class about extends MovieClip
	{
		/**
		 * 
		 * Inicializa el MovieClip y asigna eventos de Mouse
		 * a los botones close y drag
		 * 
		 */
		
		public function about()
		{
			close.addEventListener(MouseEvent.CLICK, closeIt);
			drag.addEventListener(MouseEvent.MOUSE_DOWN, dragIt);
		}
		
		/**
		 * 
		 * Cierra la ventana nativa que contiene la interfaz
		 * 
		 * @param	e MouseEvent
		 * 
		 */
		
		private function closeIt(e:MouseEvent)
		{
			stage.nativeWindow.close();
		}
		
		/**
		 * 
		 * Inicia el arrastre de la ventana nativa que contiene
		 * la interfaz.
		 * 
		 * @param	e MouseEvent
		 */
		
		private function dragIt(e:MouseEvent)
		{
			stage.nativeWindow.startMove();
		}
	}

}