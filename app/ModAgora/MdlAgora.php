<?php
/**
* This file is part of the Agora-Project Software package.
*
* @copyright (c) Agora-Project Limited <https://www.agora-project.net>
* @license GNU General Public License, version 2 (GPL-2.0)
*/


/*
 * Modele de la config de l'Agora
 */
class MdlAgora extends MdlObject
{
	const moduleName="agora";
	const objectType="agora";
	const dbTable="ap_agora";

	/*
	 * SURCHARGE : Constructeur
	 */
	function __construct()
	{
		parent::__construct(Db::getLine("select * from ap_agora"));
		if(empty($this->personsSort))  {$this->personsSort="name";}
	}

	/*
	 * PATH DU LOGO EN BAS DE PAGE
	 */
	public function pathLogoFooter()
	{
		return (!empty($this->logo) && is_file(PATH_DATAS.$this->logo))  ?  PATH_DATAS.$this->logo  :  "app/img/logoSmall.png";
	}

	/*
	 * PATH DU LOGO DE PAGE DE CONNEXION
	 */
	public function pathLogoConnect()
	{
		return (!empty($this->logoConnect) && is_file(PATH_DATAS.$this->logoConnect))  ?  PATH_DATAS.$this->logoConnect  :  null;
	}

	/*
	 * VISIO JITSI : VERIF S'IL EST ACTIVÉ (URL DISPONIBLE)
	 */
	public function visioEnabled()
	{
		return ($this->visioUrl());
	}

	/*
	 * VISIO JITSI : RENVOIE L'URL DU SERVEUR DE VISIO
	 */
	public function visioUrl($roomIdLength=10)
	{
		//Serveur de visio principal || Serveur de visio spécifique
		if(Ctrl::isHost())					{$visioUrl=Host::visioHost();}
		elseif(!empty($this->visioHost))	{$visioUrl=$this->visioHost;}
		//Renvoi si besoin l'url avec le nom de la "room"
		if(isset($visioUrl)){
			if(!isset($_SESSION["visioRoomId"]))  {$_SESSION["visioRoomId"]=str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");}				//Prefixe aléatoire le temps de la session (évite de céer une nouvelle Url à chaque proposition de visio)
			return $visioUrl."/".(Ctrl::isHost()?"visio":"omnispace-visio")."-".substr($_SESSION["visioRoomId"],0,$roomIdLength);	//Url avec le préfixe et le "visioRoomId" de la longeur souhaitée
		}
	}

	/*
	 * GOOGLE SIGNIN/OAUTH : VERIF S'IL EST ACTIVÉ
	 */
	public function gSigninEnabled()
	{
		return (!empty($this->gSignin) && $this->gSigninClientId() && Req::isMobileApp()==false);
	}

	/*
	 * GOOGLE SIGNIN/OAUTH : RENVOI "gSigninClientId" S'IL EST RENSEIGNÉ 
	 */
	public function gSigninClientId()
	{
		if(Ctrl::isHost())						{return Host::gSigninClientId();}
		elseif(!empty($this->gSigninClientId))	{return $this->gSigninClientId;}
	}

	/*
	 * GOOGLE CONTACTS/INVITATIONS : VERIF S'IL EST ACTIVÉ
	 */
	public function gPeopleEnabled()
	{
		return ($this->gSigninClientId() && $this->gPeopleApiKey() && Req::isMobileApp()==false);
	}

	/*
	 * GOOGLE CONTACTS/INVITATIONS : RENVOI "gPeopleApiKey" S'IL EST RENSEIGNÉ 
	 */
	public function gPeopleApiKey()
	{
		if(Ctrl::isHost())						{return Host::gPeopleApiKey();}
		elseif(!empty($this->gPeopleApiKey))	{return $this->gPeopleApiKey;}
	}

	/*
	 * GOOGLE MAP : VERIF S'IL EST ACTIVÉ
	 */
	public function gMapsEnabled()
	{
		return ($this->gMapsApiKey() && $this->mapTool=="gmap");
	}

	/*
	 *  GOOGLE MAP : RENVOI "mapApiKey" S'IL EST RENSEIGNÉ 
	 */
	public function gMapsApiKey()
	{
		if(Ctrl::isHost())					{return Host::gMapsApiKey();}
		elseif(!empty($this->mapApiKey))	{return $this->mapApiKey;}
	}
}