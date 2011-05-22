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

package com.forosdelweb.reader.data
{
    
	/**
	 * 
	 * La Clase Singleton Storage almacena los post recibidos
	 * 
	 * @author		Enrique Chavez aka Tmeister
	 * @version		1.0
	 *
	 * */
	
	public class Storage
    {
        
		/**
		 * Instancia de la misma clase Storage
		 */
		
		private static var singleton : Storage
		
		/**
		 * Array que contiene los posts recibidos
		 */
		
		private var _posts:Array = [];
		
		/**
		 * Verifica que no se haga una instancia de la clase, La instancia debe de ser creada u obtenida
		 * mediante el metodo getInstance
		 * 
		 * @param	caller
		 */
		
        public function Storage( caller : Function = null ) 
        {	
            if ( caller != Storage.getInstance )
			{
                throw new Error ("Storage is a singleton class, use getInstance() instead");
			}
            if ( Storage.singleton != null )
			{
                throw new Error( "Only one Singleton instance should be instantiated" );	
			}	
        }
		
		/**
		 * Verifica si existe la instancia de la clase, si no existe la crea y retorna la instancia
		 * de la clase
		 * 
		 * @return singleton instancia de la clase.
		 */
		
		public static function getInstance() : Storage
        {
            if ( singleton == null )
			{
                singleton = new Storage( arguments.callee );
			}
            return singleton;
        }
		
		/**
		 * Setea los post recibidos en la variable _posts
		 * 
		 * @param data Array de posts
		 */
		
	    public function set posts(data:Array)
		{
			_posts = data;
		}
		
		/**
		 * Retorna los posts almacenados
		 * 
		 * @return _posts Array de los posts almacenados.
		 */
		
		public function get posts():Array
		{
			return _posts;
		}
    }
}
