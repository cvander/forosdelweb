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
	import com.adobe.xml.syndication.rss.Item20;
	
	/**
	 * 
	 * La Clase ArrayUtils es una utilidad para el manejo de Array
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class ArrayUtils
	{
		/**
		 * 
		 */
		
		public function ArrayUtils()
		{
		}
		
		/**
		 * Ordena los elementos de un array 
		 * 
		 * @param	source
		 * @return Array
		 */
		
		public function orderByDate(source:Array):Array
		{
			source.sort(sortByDate);
			return source;
		}
		
		/**
		 * Funcion personalizada que ordena los elementos de un array de acuerdo con la fecha 
		 * 
		 * @param	a
		 * @param	b
		 * @return Number
		 */
		
		
		private function sortByDate(a:Item20, b:Item20):Number
		{
			var aDate:Number = new Date ( a.pubDate ).getTime();
			var bDate:Number = new Date ( b.pubDate ).getTime();
			if (aDate < bDate)
			{
				return 1;
			} else if (aDate > bDate) 
			{
				return -1;
			} else  
			{
				return 0;
			}
			return 1;
		}

	}
}