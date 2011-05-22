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
	
	/**
	 * 
	 * La Clase dateUtils es una utilidad para el manejo de fechas
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class dateUtils 
	{
		import com.carlcalderon.arthropod.Debug
		
		/**
		 * 
		 */
		
		public function dateUtils()
		{
		}
		
		/**
		 * Obtiene el tiempo que ha pasado de una fecha X al tiempo actual, regresando los minutos, horas o dias
		 * 
		 * @param	date
		 * @return String ej 1m
		 */
		
		public static function dateDiff(date:Date):String
		{
			var now:Date = new Date();
			var offsetMilliseconds:Number = now.getTimezoneOffset() * 60 * 1000;
			now.setTime(now.getTime() + offsetMilliseconds);	
			var minutes = Math.round( ( (now.getTime() - date.getTime()) / 1000 ) / 60)
			if ( minutes >= 60 )
			{
				var hours = Math.floor( minutes / 60 );
				if (hours >= 24 )
				{
					var days = Math.floor( hours / 24 ) 
					return String(days) + "d";
				}else
				{
					return String(hours) + "h";
				}
			}else
			{
				return String(minutes) + "m";
			}
		}
	}
}