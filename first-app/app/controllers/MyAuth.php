<?php
namespace controllers;
use Ubiquity\utils\http\USession;
use Ubiquity\utils\http\URequest;
use Ubiquity\attributes\items\router\Route;
use Ubiquity\utils\flash\FlashMessage;

#[Route(path: "/login",inherited: true,automated: true)]
class MyAuth extends \Ubiquity\controllers\auth\AuthController{

	protected function onConnect($connected) {
		$urlParts=$this->getOriginalURL();
		USession::set($this->_getUserSessionKey(), $connected);
		if(isset($urlParts)){
			$this->_forward(implode("/",$urlParts));
		}else{
			//TODO
			//Forwarding to the default controller/action
		}
	}

	protected function _connect() {
		if(URequest::isPost()){
			$email=URequest::post($this->_getLoginInputName());
			$password=URequest::post($this->_getPasswordInputName());
			//TODO
			//Loading from the database the user corresponding to the parameters
			//Checking user creditentials
			//Returning the user
		}
		return;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Ubiquity\controllers\auth\AuthController::isValidUser()
	 */
	public function _isValidUser($action=null) {
		return USession::exists($this->_getUserSessionKey());
	}

	public function _getBaseRoute() {
		return '/login';
	}
	
	public function _displayInfoAsString() {
		return true;
	}
	 
	protected function finalizeAuth() {
		if(!URequest::isAjax()){
			$this->loadView('@activeTheme/main/vFooter.html');
		}
	}
	 
	protected function initializeAuth() {
		if(!URequest::isAjax()){
			$this->loadView('@activeTheme/main/vHeader.html');
		}
	}
	 
	public function _getBodySelector() {
			return '#page-container';
	}

protected function noAccessMessage(FlashMessage $fMessage){
	$fMessage->setTitle('Accès interdit !');
	$fMessage->setContent('Vous ne pouvez accèder à cette page');
}
protected function terminateMessage(FlashMessage $fMessage)
{
	$fMessage->setTitle('Fermeture');
	$fMessage->setContent('Vous avez été correctement deconnecté de l application');	
}



}