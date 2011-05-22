package com.forosdelweb.reader
{
	import com.carlcalderon.arthropod.Debug
	import flash.display.MovieClip;
	import flash.events.MouseEvent;
	import flash.text.TextField
	
	public class about extends MovieClip
	{
		public function about()
		{
			close.addEventListener(MouseEvent.CLICK, closeIt);
			drag.addEventListener(MouseEvent.MOUSE_DOWN, dragIt);
		}
		private function closeIt(e:MouseEvent)
		{
			Debug.log("Cerrando ventana....")
			stage.nativeWindow.close();
		}
		private function dragIt(e:MouseEvent)
		{
			stage.nativeWindow.startMove();
		}
	}

}