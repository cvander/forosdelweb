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

package com.forosdelweb.reader.utils
{
	import flash.text.TextField;
	import flash.display.MovieClip;
	
	/**
	 * 
	 * La Clase Tooltip 
	 * 
	 * Clip relacionado : Library -> ToolTip -> Tooltip
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	
	public class Tooltip extends MovieClip
	{
		/**
		 * Inicializador, setea el autoSize del campo de texto como left
		 */
		
		public function Tooltip()
		{
			lbl.autoSize = "left";
		}
		/**
		 * Setea el texto, al tooltip y escala el tamaño del background
		 * 
		 * @param	l
		 */
		
		public function setLabel(l:String):void
		{
			lbl.htmlText = l;
			skin_mc.width = Math.round(lbl.textWidth) + 20;
		}
	}
}
