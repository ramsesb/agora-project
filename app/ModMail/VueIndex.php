<script>
////	INIT
$(function(){
	////	PRÉSELECTIONNE LE TITRE DU MAIL
	$("[name='subject']").focus();

	////	INIT L'AFFICHAGE DE L'ARBORESCENCE DE CONTACTS
	$(".vMailsBlock").each(function(){
		var folderTreeLevel=$(this).attr("data-treeLevel");
		if(typeof folderTreeLevel!=="undefined" && folderTreeLevel>0)
			{$(this).css("padding-left",(folderTreeLevel*22)+"px");}
	});

	////	AFFICHE/MASQUE LES USERS D'UN ESPACE (SAUF ESPACE COURANT)
	$(".vMailsLabel").click(function(){
		$("#mailsContainer"+$(this).attr("data-targetObjId")).slideToggle();
	});
	
	////	RESPONSIVE : DÉPLACE LE MENU DES DESTINATAIRES À COTÉ DU MENU DES "OPTIONS"
	if(isMobile()){
		$("#recipientMainMenu>*").appendTo("#mobileRecipients");
		$("#mobileRecipients").show();
	}

	////	VISIO : AJOUTE UNE NOUVELLE UNE URL
	$("#visioUrlAdd").click(function(){
		if(confirm("<?= Txt::trad("VISIO_urlMail") ?> ?")){
			tinymce.activeEditor.insertContent("<br><h4><?= Txt::trad("VISIO_launch") ?> : <a href='<?= Ctrl::$agora->visioUrl() ?>' target='_blank'><?= Ctrl::$agora->visioUrl() ?></a></h4>");
		}
	});
});

////    On contrôle le formulaire
function formControl()
{
	//Sélection d'une personne, d'un titre et d'un message
	if($("[name='personList[]']:checked").length==0 && $("[name='groupList[]']:checked").length==0)	{notify("<?= Txt::trad("MAIL_specifyMail") ?>");	return false;}
	else if($("[name='subject']").isEmpty())														{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_subject") ?>");	return false;}
	else if(isEmptyEditor("message"))																{notify("<?= Txt::trad("requiredFields")." : ".Txt::trad("MAIL_message") ?>");	return false;}
}
</script>

<style>
/*Menu de gauche*/
#pageModuleMenu						{width:300px;}/*surcharge*/
#recipientLabel						{text-align:center; margin-top:5px;}
#recipientLabel hr					{margin:5px;}/*surcharge*/
#recipientLabel img					{height:20px;}
.vMailsBlock						{margin:20px 0px 0px 10px;}
.vMailsLabel 						{display:table;}
.vMailsLabel>div 					{display:table-cell; vertical-align:middle;}
.vMailsLabel img					{max-width:24px; margin-right:10px;}
.vMailsMenu							{padding-left:18px!important; display:none;}
.vMailsMenu.vMailsMenuDisplay		{display:block;}
.vMailsMenu>div						{padding:3px;}
/*formulaire principal*/
#mailContainer						{padding:10px;}
#mailContainer [name='subject']		{width:100%; margin-bottom:20px;}
#mailOptions						{display:table; width:100%; margin-top:20px;}
#mailOptions>div					{display:table-cell;}
#mailOptions>div:last-child			{text-align:right;}
#mailOptions>div>div				{margin:7px;}
div[id^='files']:not([id='files1'])	{display:none;}
#visioUrlAdd						{cursor:pointer;}

/*RESPONSIVE*/
@media screen and (max-width:1023px){
	#mobileRecipients, #mailOptions	{margin-top:30px; border:1px solid #ccc; border-radius:3px;}
	#mailOptions, #mailOptions>div	{display:inline-block;}
}
</style>

<form action="index.php" method="post" onsubmit="return formControl()" enctype="multipart/form-data">
<div id="pageCenter">
	<div id="pageModuleMenu">
		<div id="pageModMenu" class="miscContainer">
			<div id="recipientMainMenu">
				<div id="recipientLabel"><img src="app/img/mail.png"> <?= Txt::trad("MAIL_recipients") ?> :<hr></div>
				<?php
				////	LISTE DES DESTINATAIRES : USERS DE L'AGORA / DU MODULE CONTACT
				foreach($containerList as $tmpContainer)
				{
					//Init
					$cptPerson=0;
					$cptPersonLimits=1000;//limite l'affichage à 1000 personnes
					$tmpSwitchOption=$tmpGroupsFields=$tmpPersonsFields=null;
					$mailsMenuClass=($tmpContainer->_targetObjId==Ctrl::$curSpace->_targetObjId)  ?  "vMailsMenuDisplay"  :  null;//par défaut, on n'affiche que les users de l'espace courant
					//SWITCH LA SELECTION (3 à 1000 pers.)
					if(count($tmpContainer->personList)>=3 && count($tmpContainer->personList)<=$cptPersonLimits)
						{$tmpSwitchOption="<div class='sLink' onclick=\"$('#mailsContainer".$tmpContainer->_targetObjId." input[name^=personList]').trigger('click');\"><img src='app/img/switch.png'> ".Txt::trad("invertSelection")."</div>";}
					//GROUPES D'USERS (ESPACE)
					if($tmpContainer::objectType=="space"){
						foreach(MdlUserGroup::getGroups($tmpContainer) as $tmpGroup){
							$tmpBoxId=$tmpContainer->_targetObjId.$tmpGroup->_targetObjId;
							$tmpGroupsFields.="<div title=\"".$tmpGroup->usersLabel."\"><input type='checkbox' name=\"groupList[]\" value=\"".$tmpGroup->_targetObjId."\" id=\"".$tmpBoxId."\"> <label for=\"".$tmpBoxId."\"><img src='app/img/user/userGroup.png'> ".$tmpGroup->title."</label></div>";
						}
					}
					//PERSONNES DU CONTENEUR
					foreach($tmpContainer->personList as $tmpPerson)
					{
						if(empty($tmpPerson->mail) || ($tmpContainer::objectType=="contactFolder" && $cptPerson>$cptPersonLimits))	{continue;}//zap les personnes sans mails et Limite le nb de contacts
						if(Req::getParam("checkedMailto")==$tmpPerson->mail && empty($personsChecked[$tmpPerson->mail])){//Préselectionne le mail
							$tmpPerson->mailChecked="checked";
							$personsChecked[$tmpPerson->mail]=$tmpPerson->mail;//indique qu'il est déjà sélectionné
							$mailsMenuClass="vMailsMenuDisplay";//Affiche le menu (si besoin)
						}
						$tmpBoxId=$tmpContainer->_targetObjId.$tmpPerson->_targetObjId;
						$tmpPersonsFields.="<div title=\"".$tmpPerson->mail."\"><input type='checkbox' name=\"personList[]\" value=\"".$tmpPerson->_targetObjId."\" id=\"".$tmpBoxId."\" ".$tmpPerson->mailChecked."> <label for=\"".$tmpBoxId."\">".$tmpPerson->getLabel()."</label></div>";
						$cptPerson++;
					}
					////	AFFICHE CHAQUE BLOCK D'USERS / CONTACTS
					echo "<div class='vMailsBlock' ".($tmpContainer::objectType=="contactFolder"?"data-treeLevel='".$tmpContainer->treeLevel."'":null).">
							<div class='vMailsLabel sLink' data-targetObjId=\"".$tmpContainer->_targetObjId."\">
								<div><img src=\"app/img/mail/".($tmpContainer::objectType=="space"?"user":"contact").".png\"></div>
								<div>".$tmpContainer->name." <img src='app/img/arrowBottom.png'></div>
							</div>
							<div class='vMailsMenu ".$mailsMenuClass."' id=\"mailsContainer".$tmpContainer->_targetObjId."\">".$tmpGroupsFields.$tmpPersonsFields.$tmpSwitchOption."</div>
						</div>";
				}
				?>
				<hr>
			</div>
			<div class="menuLine sLink" onclick="lightboxOpen('?ctrl=mail&action=mailHistory');">
				<div class="menuIcon"><img src="app/img/log.png"></div>
				<div><?= Txt::trad("MAIL_historyTitle") ?></div>
			</div>
		</div>
	</div>

	<div id="pageCenterContent">
		<div id="mailContainer" class="miscContainer">

			<!--TITRE ET EDITEUR DU MAIL-->
			<input type="text" name="subject" placeholder="<?= txt::trad("MAIL_subject") ?>">
			<textarea name="message"></textarea>
			<?= CtrlMisc::initHtmlEditor("message") ?>

			<!--RESPONSIVE : LISTE DES DESTINATAIRES-->
			<div id="mobileRecipients"></div>

			<!--OPTIONS DU MAIL-->
			<div id="mailOptions">
				<div>
					<?php
					//Options "Ajouter 'ReplyTo'" & "Accusé de réception" si l'user a spécifié son email
					if(!empty(Ctrl::$curUser->mail)){
						echo "<div title=\"".Txt::trad("MAIL_addReplyToInfo")."\"><input type='checkbox' name='addReplyTo' value='1' id='addReplyTo'> <label for='addReplyTo'>".Txt::trad("MAIL_addReplyTo")."</label></div>
							  <div title=\"".Txt::trad("MAIL_receptionNotifInfo")."\"><input type='checkbox' name='receptionNotif' value='1' id='receptionNotif'> <label for='receptionNotif'>".Txt::trad("MAIL_receptionNotif")."</label></div>";
					}
					//Options "Masquer les destinataires" & "Ne pas signer le message"
					echo "<div title=\"".Txt::trad("MAIL_hideRecipientsInfo")."\"><input type='checkbox' name='hideRecipients' value='1' id='hideRecipients'> <label for='hideRecipients'>".Txt::trad("MAIL_hideRecipients")."</label></div>
						  <div title=\"".Txt::trad("MAIL_noFooterInfo")."\"><input type='checkbox' name='noFooter' value='1' id='noFooter'> <label for='noFooter'>".Txt::trad("MAIL_noFooter")."</label></div>";
					//Option : "Ajouter une visioconférence"
					if(Ctrl::$agora->visioEnabled())  {echo "<div id='visioUrlAdd' title=\"".Txt::trad("VISIO_urlMail")."\"><img src='app/img/visioSmall.png'>&nbsp;".Txt::trad("VISIO_urlAdd")."</div>";}
					?>
				</div>
				<div>
					<?php
					//Ajout de fichiers joints
					for($cpt=1; $cpt<=10; $cpt++)  {echo "<div id=\"files".$cpt."\">".Txt::trad("MAIL_attachedFile")."  <input type='file' name=\"files".$cpt."\" onChange=\"$('#files".($cpt+1)."').fadeIn();\" title=\"".Txt::trad("MAIL_fileMaxSize")."\"></div>";}
					?>
				</div>
			</div>

			<!--BOUTON "SUBMIT"-->
			<?= Txt::submitButton("send",true) ?>
		</div>
	</div>
</div>
</form>