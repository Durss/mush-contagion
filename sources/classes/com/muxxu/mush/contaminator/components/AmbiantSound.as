/**************
* DESCRIPTION *
* *************
* 
* Play an ambiant sound with a background and
* some random sounds.
* 
* Usage :
* -------
* 
* Create an instance and call start() method to
* start ambiant sound and and stop() method to stop it.
* Global volume can be changed with volume() setter.
*/

package com.muxxu.mush.contaminator.components
{
	import com.nurun.structure.environnement.configuration.Config;
	import flash.media.Sound;
	import flash.media.SoundChannel;
	import flash.media.SoundTransform;
	import flash.net.URLRequest;
	import flash.events.Event;
	import flash.utils.setInterval;
	import flash.utils.clearInterval;

	public class AmbiantSound
	{
		private var sounds:Array			= new Array();		//Array containing all random sounds
		private var backChannel:SoundChannel;
		private var backChannel2:SoundChannel;
		private var backSound:Sound;
		private var backSound2:Sound;
		private var background:String		= "background_v1";	//Name of the background sound
		private var directory:String		= Config.getPath("ambiantSounds");//Directory where sounds are
		private var fileExtension:String	= ".mp3";			//Files extensions
		private var exID:int				= -1;				//Contain the last id random sound played to be sure that the same sound will not be played 2 consecutives times.
		private var inter:Number;
		private var _volume:Number			= 1;				//Global volume (accesible with volume() setter)
		private var frequency:Number		= 0.96;				//Random sounds frequency 0 = hight frequency , 1 = low frequency


		/**************
		* CONSTRUCTOR *
		**************/
		public function AmbiantSound()
		{
			for(var i:int=1; i<9; i++)
				sounds.push(new Sound(new URLRequest(directory+"goute"+i+fileExtension)));
			for(i=1; i<5; i++)
				sounds.push(new Sound(new URLRequest(directory+"voice"+i+fileExtension)));
		}


		/*****************
		* PUBLIC METHODS *
		*****************/

		/**
		* Change global volume
		* @param	vol	{Number} : 0<->1
		* @return	void
		*/
		public function set volume(vol:Number):void
		{
			_volume = vol;
			updateBackgroundSound();
		}

		/**
		* Start ambiance
		* @return	void
		*/
		public function start():void
		{
			stop();
			inter		= setInterval(boucle, 50);
			backSound	= new Sound(new URLRequest(directory+background+fileExtension));
			backSound2	= new Sound(new URLRequest(directory+background+fileExtension));
			backChannel		= backSound.play(0);
			backChannel2	= backSound.play(30000);
			backChannel.addEventListener(Event.SOUND_COMPLETE, restartBack);
			backChannel2.addEventListener(Event.SOUND_COMPLETE, restartBack);
			updateBackgroundSound();
		}

		/**
		* Stop ambiance
		* @return	void
		*/
		public function stop():void
		{
			if(!isNaN(inter)){
				clearInterval(inter);
				backChannel.removeEventListener(Event.SOUND_COMPLETE, restartBack);
				backChannel.stop();
				backChannel2.removeEventListener(Event.SOUND_COMPLETE, restartBack);
				backChannel2.stop();
			}
		}


		/******************
		* PRIVATE METHODS *
		******************/

		/**
		* Restart background sound when finished (on SOUND_COMPLETE event)
		* @return	void
		*/
		private function restartBack(obj:Event):void
		{
			if(obj.target == backChannel){
				backChannel		= backSound.play(0);
				backChannel.addEventListener(Event.SOUND_COMPLETE, restartBack);
			}else{
				backChannel2	= backSound.play(0);
				backChannel2.addEventListener(Event.SOUND_COMPLETE, restartBack);
			}
			updateBackgroundSound();
		}


		/**
		* SetInterval which play random water/voice sounds
		* @return	void
		*/
		private function boucle():void
		{
			if(Math.random()>frequency){
				do{
					var id:int = Math.round(Math.random()*(sounds.length-1));
				}while(id == exID);

				var chanel:SoundChannel = Sound(sounds[id]).play();
				var transform:SoundTransform = chanel.soundTransform;
				transform.pan		= Math.random()-Math.random();
				transform.volume	= (Math.random() + 0.3)*_volume;
				chanel.soundTransform = transform;
				exID = id;
			}
		}

		/**
		* Change background sound at start and when setter "volume" is called
		* @return	void
		*/
		private function updateBackgroundSound():void
		{
			if(backChannel != null){
				var transform:SoundTransform	= backChannel.soundTransform;
				transform.volume				= _volume;
				backChannel.soundTransform		= transform;
				backChannel2.soundTransform		= transform;
			}
		}
	}
}